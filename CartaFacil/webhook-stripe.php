<?php
// Webhook de Stripe: https://TU-DOMINIO/webhook-stripe.php
// Eventos que hay que marcar en Stripe: checkout.session.completed, invoice.paid,
// invoice.payment_failed, customer.subscription.updated, customer.subscription.deleted
define('CF_NO_SESSION', true);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/stripe.php';

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Solo POST');
}
$secret = (string)stripe_cfg('webhook_secret', '');
$payload = file_get_contents('php://input') ?: '';
$sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if ($secret === '' || !stripe_verify_signature($payload, $sig, $secret)) {
    http_response_code(400);
    exit('Firma no válida');
}
$event = json_decode($payload, true);
if (!is_array($event)) {
    http_response_code(400);
    exit('JSON no válido');
}

try {
    echo stripe_handle_event($event);
} catch (Throwable $e) {
    error_log('CartaFácil webhook: ' . $e->getMessage());
    http_response_code(500); // Stripe lo reintentará
    echo 'Error';
}
