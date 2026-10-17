<?php
// Solo para probar en local:  php -S localhost:8000 router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(app|sql|data)(/|$)#', $path)) { http_response_code(403); exit('Prohibido'); }
if (preg_match('#^/c/([a-z0-9\-]+)/?$#', $path, $m)) { $_GET['r'] = $m[1]; require __DIR__ . '/carta.php'; return true; }
if (preg_match('#\.(sql|sqlite|md|log)$#', $path)) { http_response_code(403); exit('Prohibido'); }
return false;
