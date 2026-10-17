<?php
// Carta pública: /c/{slug}  (o carta.php?r={slug} si no hay reescritura de URLs)
define('CF_NO_SESSION', true);
require __DIR__ . '/app/bootstrap.php';

$slug = (string)($_GET['r'] ?? '');
$r = $slug !== '' ? q('SELECT * FROM restaurants WHERE slug = ?', [$slug])->fetch() : null;

if (!$r) {
    http_response_code(404);
    $title = 'Carta no encontrada';
    $msg = 'No encontramos esta carta. Comprueba el enlace o pregunta al personal.';
} elseif (!is_active($r)) {
    $title = $r['name'];
    $msg = 'La carta digital no está disponible en este momento. Pide la carta al personal.';
}

if (isset($msg)) {
    ?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title><?= e($title) ?></title><link rel="stylesheet" href="<?= e(url('assets/css/carta.css')) ?>"></head>
    <body><main class="c-wrap c-msg"><h1><?= e($title) ?></h1><p><?= e($msg) ?></p></main></body></html><?php
    exit;
}

// Contador de visitas diario (ignora bots)
$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$enIframe = ($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '') === 'iframe'; // la demo de la portada no cuenta
if ($ua !== '' && !$enIframe && !preg_match('/bot|crawl|spider|preview|curl|wget/', $ua)) {
    $day = date('Y-m-d');
    if (config('db')['driver'] === 'sqlite') {
        q('INSERT INTO visits (restaurant_id, day, hits) VALUES (?,?,1) ON CONFLICT(restaurant_id, day) DO UPDATE SET hits = hits + 1', [$r['id'], $day]);
    } else {
        q('INSERT INTO visits (restaurant_id, day, hits) VALUES (?,?,1) ON DUPLICATE KEY UPDATE hits = hits + 1', [$r['id'], $day]);
    }
}

$cats = q('SELECT * FROM categories WHERE restaurant_id = ? ORDER BY position, id', [$r['id']])->fetchAll();
$items = q('SELECT * FROM items WHERE restaurant_id = ? ORDER BY position, id', [$r['id']])->fetchAll();
$byCat = [];
$usados = [];
foreach ($items as $it) {
    $byCat[$it['category_id']][] = $it;
    foreach (alergenos_list($it['allergens']) as $a) $usados[$a] = true;
}
$cats = array_values(array_filter($cats, fn($c) => !empty($byCat[$c['id']])));
$color = valid_color($r['color']);

require __DIR__ . '/app/carta_view.php';
render_carta([
    'name' => $r['name'], 'tagline' => $r['tagline'], 'phone' => $r['phone'], 'address' => $r['address'],
    'footer_note' => $r['footer_note'], 'color' => $color,
], $cats, $byCat, array_keys($usados), [
    'css' => url('assets/css/carta.css'),
    'powered' => config('app_name'),
    'powered_url' => url(''),
]);
