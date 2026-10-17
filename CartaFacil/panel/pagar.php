<?php
// Inicia el pago (Stripe Checkout) o abre el portal de cliente de Stripe
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/stripe.php';

$r = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('panel/suscripcion.php');
check_csrf();
if (!stripe_enabled()) {
    flash('El pago con tarjeta todavía no está disponible. Usa Bizum o transferencia.', 'error');
    redirect('panel/suscripcion.php');
}

try {
    if (($_POST['action'] ?? '') === 'portal') {
        if (empty($r['stripe_customer_id'])) redirect('panel/suscripcion.php');
        $s = stripe_api('POST', 'billing_portal/sessions', [
            'customer'   => $r['stripe_customer_id'],
            'return_url' => url('panel/suscripcion.php'),
            'locale'     => 'es',
        ]);
        redirect($s['url']);
    }

    // Nueva suscripción
    $params = [
        'mode'                 => 'subscription',
        'line_items'           => [['price' => stripe_cfg('price_id'), 'quantity' => 1]],
        'client_reference_id'  => (string)$r['id'],
        'metadata'             => ['restaurant_id' => (string)$r['id']],
        'subscription_data'    => ['metadata' => ['restaurant_id' => (string)$r['id']]],
        'success_url'          => url('panel/suscripcion.php') . '?ok={CHECKOUT_SESSION_ID}',
        'cancel_url'           => url('panel/suscripcion.php') . '?cancelado=1',
        'locale'               => 'es',
        'allow_promotion_codes'=> 'true',
        'billing_address_collection' => 'required',
        'tax_id_collection'    => ['enabled' => 'true'],
    ];
    if (!empty($r['stripe_customer_id'])) {
        $params['customer'] = $r['stripe_customer_id'];
        $params['customer_update'] = ['name' => 'auto', 'address' => 'auto'];
    } else {
        $params['customer_email'] = $r['email'];
    }
    // Si aún le quedan días de prueba (más de 2), el primer cobro se hace al terminar la prueba
    if (!empty($r['paid_until'])) {
        $trialEnd = strtotime($r['paid_until'] . ' 12:00:00');
        if ($trialEnd > time() + 2 * 86400 + 3600) {
            $params['subscription_data']['trial_end'] = (string)$trialEnd;
        }
    }
    $s = stripe_api('POST', 'checkout/sessions', $params);
    redirect($s['url']);
} catch (Throwable $e) {
    error_log('CartaFácil pagar: ' . $e->getMessage());
    flash('No hemos podido conectar con la pasarela de pago. Inténtalo de nuevo en unos minutos.', 'error');
    redirect('panel/suscripcion.php');
}
