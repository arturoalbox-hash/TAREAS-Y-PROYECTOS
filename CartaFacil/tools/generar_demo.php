<?php
// Genera demo/carta-demo.html: la carta de ejemplo en un solo archivo, para enseñarla sin servidor.
//   php tools/generar_demo.php
if (PHP_SAPI !== 'cli') exit;
define('CF_NO_SESSION', true);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/carta_view.php';

$d = require __DIR__ . '/../app/demo_data.php';
$cats = []; $byCat = []; $usados = []; $id = 1;
foreach ($d['categories'] as $name => $items) {
    $cid = $id++;
    $cats[] = ['id' => $cid, 'name' => $name];
    foreach ($items as [$n, $desc, $price, $half, $alg]) {
        $byCat[$cid][] = ['name' => $n, 'description' => $desc, 'price' => $price, 'half_price' => $half,
                          'allergens' => $alg, 'available' => in_array($n, $d['sold_out'], true) ? 0 : 1];
        foreach (alergenos_list($alg) as $a) $usados[$a] = true;
    }
}
ob_start();
render_carta($d['restaurant'], $cats, $byCat, array_keys($usados), [
    'inline_css' => file_get_contents(__DIR__ . '/../assets/css/carta.css'),
    'powered' => config('app_name'), 'powered_url' => in_array('--fragmento', $argv, true) ? '' : config('base_url'),
]);
$html = ob_get_clean();
if (in_array('--fragmento', $argv, true)) {
    // Versión sin <html>/<head>/<body> (para publicarla como página alojada)
    $html = preg_replace(['#<!doctype html>\s*#i', '#</?html[^>]*>\s*#i', '#</?head>\s*#i', '#</?body>\s*#i', '#<meta charset[^>]*>\s*#i', '#<meta name="viewport"[^>]*>\s*#i'], '', $html);
    $html = preg_replace('#<title>[^<]*</title>#', "<title>Chiringuito La Ola</title>\n  <meta name=\"claude:viewport-fit-sides\" content=\"cover\">", $html, 1);
}
@mkdir(__DIR__ . '/../demo');
$out = $argv[1] ?? __DIR__ . '/../demo/carta-demo.html';
if (str_starts_with($out, '--')) $out = __DIR__ . '/../demo/carta-demo.html';
file_put_contents($out, $html);
echo "Demo generada: $out\n";
