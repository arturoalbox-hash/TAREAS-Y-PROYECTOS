<?php
/**
 * Integración mínima con Stripe (sin SDK): Checkout para suscribirse,
 * Portal de cliente para cambiar tarjeta, ver facturas o darse de baja,
 * y webhook para activar la carta cuando se cobra cada mes.
 */

function stripe_cfg(string $key, $default = null)
{
    $s = config('stripe', []);
    return $s[$key] ?? $default;
}

function stripe_enabled(): bool
{
    return stripe_cfg('secret_key') && stripe_cfg('price_id');
}

/** Llama a la API de Stripe. Devuelve el JSON decodificado o lanza RuntimeException. */
function stripe_api(string $method, string $path, array $params = []): array
{
    $base = rtrim(stripe_cfg('api_base', 'https://api.stripe.com'), '/');
    $url = $base . '/v1/' . ltrim($path, '/');
    $ch = curl_init();
    $body = http_build_query($params, '', '&');
    if ($method === 'GET' && $body !== '') {
        $url .= '?' . $body;
    }
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . stripe_cfg('secret_key')],
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($res === false) throw new RuntimeException('No se pudo conectar con Stripe: ' . $err);
    $json = json_decode($res, true);
    if ($code >= 400 || !is_array($json)) {
        $msg = $json['error']['message'] ?? ('HTTP ' . $code);
        throw new RuntimeException('Stripe: ' . $msg);
    }
    return $json;
}

/** Comprueba la firma del webhook (cabecera Stripe-Signature). */
function stripe_verify_signature(string $payload, string $header, string $secret, int $tolerance = 300): bool
{
    $t = null;
    $sigs = [];
    foreach (explode(',', $header) as $part) {
        [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($k === 't') $t = (int)$v;
        if ($k === 'v1') $sigs[] = $v;
    }
    if (!$t || !$sigs || abs(time() - $t) > $tolerance) return false;
    $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
    foreach ($sigs as $s) {
        if (hash_equals($expected, $s)) return true;
    }
    return false;
}

/** Fin del periodo pagado de una suscripción (Stripe lo movió a los items en versiones recientes). */
function stripe_sub_period_end(array $sub): ?int
{
    $end = $sub['current_period_end'] ?? ($sub['items']['data'][0]['current_period_end'] ?? null);
    if (!$end && !empty($sub['trial_end'])) $end = $sub['trial_end'];
    return $end ? (int)$end : null;
}

/** Alarga la carta hasta el fin del periodo pagado + 2 días de margen (nunca la acorta). */
function extend_until(int $rid, int $periodEndTs): void
{
    $new = date('Y-m-d', $periodEndTs + 2 * 86400);
    $cur = q('SELECT paid_until FROM restaurants WHERE id = ?', [$rid])->fetchColumn();
    if (!$cur || $cur < $new) {
        q('UPDATE restaurants SET paid_until = ? WHERE id = ?', [$new, $rid]);
    }
}

function restaurant_by_customer(?string $customer): ?array
{
    if (!$customer) return null;
    return q('SELECT * FROM restaurants WHERE stripe_customer_id = ?', [$customer])->fetch() ?: null;
}

/** Guarda la suscripción en el local y actualiza su fecha de carta activa. */
function apply_subscription(int $rid, array $sub): void
{
    q('UPDATE restaurants SET stripe_customer_id = ?, stripe_subscription_id = ?, subscription_status = ? WHERE id = ?',
      [$sub['customer'] ?? null, $sub['id'] ?? null, $sub['status'] ?? null, $rid]);
    if (in_array($sub['status'] ?? '', ['active', 'trialing'], true) && ($end = stripe_sub_period_end($sub))) {
        extend_until($rid, $end);
    }
}

/** Guarda (o actualiza) una factura en el historial de pagos del local. */
function record_invoice(int $rid, array $inv): void
{
    $id = $inv['id'] ?? null;
    if (!$id) return;
    $periodEnd = $inv['lines']['data'][0]['period']['end'] ?? ($inv['period_end'] ?? null);
    $vals = [
        (int)($inv['amount_paid'] ?? 0), strtoupper((string)($inv['currency'] ?? 'eur')), (string)($inv['status'] ?? ''),
        $periodEnd ? date('Y-m-d', (int)$periodEnd) : null, $inv['hosted_invoice_url'] ?? null, $inv['invoice_pdf'] ?? null,
        $inv['number'] ?? null,
    ];
    if (q('SELECT id FROM payments WHERE stripe_invoice_id = ?', [$id])->fetch()) {
        q('UPDATE payments SET amount_cents=?, currency=?, status=?, period_end=?, hosted_url=?, pdf_url=?, number=? WHERE stripe_invoice_id=?', array_merge($vals, [$id]));
    } else {
        q('INSERT INTO payments (amount_cents, currency, status, period_end, hosted_url, pdf_url, number, restaurant_id, stripe_invoice_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)',
          array_merge($vals, [$rid, $id, date('Y-m-d H:i:s', (int)($inv['created'] ?? time()))]));
    }
}

/** Procesa un evento de Stripe (desde el webhook). Devuelve un texto para el log. */
function stripe_handle_event(array $event): string
{
    $obj = $event['data']['object'] ?? [];
    switch ($event['type'] ?? '') {
        case 'checkout.session.completed':
            $rid = (int)($obj['client_reference_id'] ?? ($obj['metadata']['restaurant_id'] ?? 0));
            if (!$rid || empty($obj['subscription'])) return 'ignorado';
            $sub = stripe_api('GET', 'subscriptions/' . rawurlencode($obj['subscription']));
            apply_subscription($rid, $sub);
            return "local $rid suscrito";

        case 'invoice.paid':
        case 'invoice.payment_succeeded':
            $r = restaurant_by_customer($obj['customer'] ?? null);
            if (!$r) return 'cliente desconocido';
            record_invoice((int)$r['id'], $obj);
            $end = $obj['lines']['data'][0]['period']['end'] ?? null;
            if ($end) extend_until((int)$r['id'], (int)$end);
            q('UPDATE restaurants SET subscription_status = ? WHERE id = ?', ['active', $r['id']]);
            return "local {$r['id']} pagado";

        case 'invoice.payment_failed':
            $r = restaurant_by_customer($obj['customer'] ?? null);
            if (!$r) return 'cliente desconocido';
            record_invoice((int)$r['id'], $obj);
            q('UPDATE restaurants SET subscription_status = ? WHERE id = ?', ['past_due', $r['id']]);
            return "local {$r['id']} pago fallido";

        case 'customer.subscription.updated':
        case 'customer.subscription.deleted':
            $r = restaurant_by_customer($obj['customer'] ?? null);
            if (!$r) return 'cliente desconocido';
            $status = ($event['type'] === 'customer.subscription.deleted') ? 'canceled' : ($obj['status'] ?? null);
            if (!empty($obj['cancel_at_period_end']) && $status === 'active') $status = 'canceling';
            q('UPDATE restaurants SET subscription_status = ?, stripe_subscription_id = ? WHERE id = ?', [$status, $obj['id'] ?? null, $r['id']]);
            if (in_array($status, ['active', 'trialing', 'canceling'], true) && ($end = stripe_sub_period_end($obj))) {
                extend_until((int)$r['id'], $end);
            }
            return "local {$r['id']} estado $status";
    }
    return 'evento no usado';
}

function subscription_label(?string $status): array
{
    return match ($status) {
        'active'    => ['Activa · se renueva sola cada mes', 'ok'],
        'trialing'  => ['Tarjeta guardada · el primer cobro será al terminar la prueba', 'ok'],
        'canceling' => ['Cancelada · seguirá activa hasta el final del periodo pagado', 'warn'],
        'past_due', 'unpaid' => ['Pago pendiente · revisa tu tarjeta', 'error'],
        'canceled'  => ['Cancelada', 'warn'],
        'incomplete', 'incomplete_expired' => ['Pago no completado', 'warn'],
        default     => ['Sin suscripción', 'none'],
    };
}
