<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/layout.php';
require __DIR__ . '/../app/stripe.php';

$r = require_login();
$rid = (int)$r['id'];

// Vuelta desde Stripe Checkout: confirmamos el pago sin esperar al webhook
if (!empty($_GET['ok']) && stripe_enabled()) {
    $sid = (string)$_GET['ok'];
    try {
        if (!preg_match('/^cs_[A-Za-z0-9_]+$/', $sid)) throw new RuntimeException('id no válido');
        $s = stripe_api('GET', 'checkout/sessions/' . rawurlencode($sid));
        if ((int)($s['client_reference_id'] ?? 0) === $rid && ($s['status'] ?? '') === 'complete' && !empty($s['subscription'])) {
            apply_subscription($rid, stripe_api('GET', 'subscriptions/' . rawurlencode($s['subscription'])));
            flash('¡Listo! Tu suscripción está activa. Gracias por confiar en ' . config('app_name') . '.');
        }
    } catch (Throwable $e) {
        error_log('CartaFácil suscripcion: ' . $e->getMessage());
        flash('Pago recibido. Tu carta se activará en unos minutos.');
    }
    redirect('panel/suscripcion.php');
}
if (!empty($_GET['cancelado'])) {
    flash('Has cancelado el pago. No se ha cobrado nada.', 'error');
    redirect('panel/suscripcion.php');
}

$r = q('SELECT * FROM restaurants WHERE id = ?', [$rid])->fetch();
[$label, $tone] = subscription_label($r['subscription_status'] ?? null);
$hasSub = in_array($r['subscription_status'] ?? '', ['active', 'trialing', 'canceling', 'past_due', 'unpaid'], true);
$pagos = q('SELECT * FROM payments WHERE restaurant_id = ? ORDER BY created_at DESC LIMIT 24', [$rid])->fetchAll();
$price = (int)config('price_month');
$manual = config('manual_payment', []);
$trialLeft = is_active($r) ? days_left($r) : 0;

page_header('Suscripción', ['panel' => true, 'active' => 'suscripcion']);
?>
<main class="wrap narrow">
  <div class="card">
    <h1>Tu suscripción</h1>
    <div class="sub-status">
      <div>
        <div class="muted">Carta pública</div>
        <?php if (is_active($r)): ?>
          <div class="sub-big ok">Activa hasta el <?= e(date('d/m/Y', strtotime($r['paid_until']))) ?></div>
        <?php else: ?>
          <div class="sub-big error">No visible para tus clientes</div>
        <?php endif; ?>
      </div>
      <div>
        <div class="muted">Pago mensual</div>
        <div class="pill pill-<?= e($tone) ?>"><?= e($label) ?></div>
      </div>
    </div>

    <?php if (stripe_enabled()): ?>
      <?php if (!$hasSub): ?>
        <div class="plan">
          <div class="plan-price"><?= $price ?> € <span>/ mes</span></div>
          <ul>
            <li>Se cobra solo cada mes, con tarjeta o domiciliación bancaria.</li>
            <li>Factura de cada pago, descargable aquí mismo.</li>
            <li>Sin permanencia: te das de baja cuando quieras.</li>
            <?php if ($trialLeft > 2): ?><li><strong>Te quedan <?= $trialLeft ?> días de prueba:</strong> no se te cobrará nada hasta que terminen.</li><?php endif; ?>
          </ul>
          <form method="post" action="pagar.php">
            <?= csrf_field() ?><input type="hidden" name="action" value="checkout">
            <button class="btn btn-block">Activar el pago mensual</button>
          </form>
          <p class="muted small center">Pago seguro con Stripe. Nosotros nunca vemos los datos de tu tarjeta.</p>
        </div>
      <?php else: ?>
        <?php if (in_array($r['subscription_status'], ['past_due', 'unpaid'], true)): ?>
          <div class="flash flash-error">No hemos podido cobrar el último recibo. Actualiza tu tarjeta para que tu carta siga visible.</div>
        <?php endif; ?>
        <form method="post" action="pagar.php">
          <?= csrf_field() ?><input type="hidden" name="action" value="portal">
          <button class="btn btn-block">Cambiar tarjeta, descargar facturas o darme de baja</button>
        </form>
        <p class="muted small center">Se abrirá la página segura de Stripe.</p>
      <?php endif; ?>
    <?php else: ?>
      <p>Cuota: <strong><?= $price ?> € al mes</strong>, sin permanencia.</p>
    <?php endif; ?>
  </div>

  <?php if (!empty($manual['bizum']) || !empty($manual['iban'])): ?>
  <div class="card">
    <h2><?= stripe_enabled() ? '¿Prefieres pagar por Bizum o transferencia?' : 'Cómo pagar' ?></h2>
    <p>Envía <?= $price ?> € por mes (o <?= (int)($manual['year_price'] ?? $price * 10) ?> € por un año entero) e indica en el concepto: <strong>CartaFácil <?= e($r['email']) ?></strong></p>
    <dl class="paydata">
      <?php if (!empty($manual['bizum'])): ?><dt>Bizum</dt><dd><?= e($manual['bizum']) ?></dd><?php endif; ?>
      <?php if (!empty($manual['iban'])): ?><dt>Transferencia</dt><dd><?= e($manual['iban']) ?><?php if (!empty($manual['holder'])): ?><br><span class="muted">Titular: <?= e($manual['holder']) ?></span><?php endif; ?></dd><?php endif; ?>
    </dl>
    <p class="muted">Activamos tu carta en cuanto lo recibamos (normalmente el mismo día). Si necesitas factura, escríbenos a <a href="mailto:<?= e(config('contact_email')) ?>"><?= e(config('contact_email')) ?></a>.</p>
  </div>
  <?php endif; ?>

  <?php if ($pagos): ?>
  <div class="card">
    <h2>Historial de pagos</h2>
    <div class="table-scroll">
      <table class="pagos">
        <thead><tr><th>Fecha</th><th>Periodo hasta</th><th class="num">Importe</th><th>Estado</th><th>Factura</th></tr></thead>
        <tbody>
        <?php foreach ($pagos as $p): ?>
          <tr>
            <td><?= e(date('d/m/Y', strtotime($p['created_at']))) ?></td>
            <td><?= $p['period_end'] ? e(date('d/m/Y', strtotime($p['period_end']))) : '—' ?></td>
            <td class="num"><?= e(number_format($p['amount_cents'] / 100, 2, ',', '.')) ?> €</td>
            <td><?= $p['status'] === 'paid' ? '<span class="pill pill-ok">Pagado</span>' : '<span class="pill pill-error">Pendiente</span>' ?></td>
            <td><?php if ($p['pdf_url']): ?><a href="<?= e($p['pdf_url']) ?>" target="_blank" rel="noopener">PDF</a><?php elseif ($p['hosted_url']): ?><a href="<?= e($p['hosted_url']) ?>" target="_blank" rel="noopener">Ver</a><?php else: ?>—<?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</main>
<?php page_footer();
