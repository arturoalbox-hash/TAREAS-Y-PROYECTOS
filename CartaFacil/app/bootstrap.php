<?php
declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Falta app/config.php. Copia app/config.example.php como app/config.php y rellénalo.');
}
$CONFIG = require $configFile;

date_default_timezone_set('Europe/Madrid');
mb_internal_encoding('UTF-8');

// ---------- Sesión segura ----------
// La carta pública define CF_NO_SESSION para no poner cookies a los clientes del bar
if (!defined('CF_NO_SESSION') && session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('cf_sess');
    session_start();
}

// ---------- Utilidades ----------
function config(string $key, $default = null)
{
    global $CONFIG;
    return $CONFIG[$key] ?? $default;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = config('db');
    if ($c['driver'] === 'sqlite') {
        $pdo = new PDO('sqlite:' . $c['sqlite_path']);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['user'], $c['pass']);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

/** Escapa texto para HTML */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(config('base_url'), '/') . '/' . ltrim($path, '/');
}

function carta_url(string $slug): string
{
    return url('c/' . rawurlencode($slug));
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function flash(?string $msg = null, string $type = 'ok')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function check_csrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('La sesión ha caducado. Vuelve atrás y recarga la página.');
    }
}

// ---------- Autenticación ----------
function current_restaurant(): ?array
{
    static $r = false;
    if ($r !== false) return $r;
    $id = $_SESSION['restaurant_id'] ?? null;
    $r = $id ? (q('SELECT * FROM restaurants WHERE id = ?', [$id])->fetch() ?: null) : null;
    return $r;
}

function require_login(): array
{
    $r = current_restaurant();
    if (!$r) {
        redirect('login.php');
    }
    return $r;
}

function login_restaurant(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['restaurant_id'] = $id;
}

// ---------- Negocio ----------
const ALERGENOS = [
    'gluten'      => ['Gluten', '🌾'],
    'crustaceos'  => ['Crustáceos', '🦐'],
    'huevos'      => ['Huevos', '🥚'],
    'pescado'     => ['Pescado', '🐟'],
    'cacahuetes'  => ['Cacahuetes', '🥜'],
    'soja'        => ['Soja', '🫘'],
    'lacteos'     => ['Lácteos', '🥛'],
    'frutos'      => ['Frutos de cáscara', '🌰'],
    'apio'        => ['Apio', '🥬'],
    'mostaza'     => ['Mostaza', '🟡'],
    'sesamo'      => ['Sésamo', '⚪'],
    'sulfitos'    => ['Sulfitos', '🍷'],
    'altramuces'  => ['Altramuces', '🫛'],
    'moluscos'    => ['Moluscos', '🦪'],
];

function parse_alergenos($input): string
{
    if (!is_array($input)) return '';
    $ok = array_values(array_intersect(array_keys(ALERGENOS), $input));
    return implode(',', $ok);
}

function alergenos_list(?string $csv): array
{
    if (!$csv) return [];
    return array_values(array_intersect(array_keys(ALERGENOS), explode(',', $csv)));
}

/** Convierte "12,50" o "12.50" en float; null si vacío o inválido */
function parse_price($s): ?float
{
    $s = trim(str_replace(['€', ' '], '', (string)$s));
    if ($s === '') return null;
    $s = str_replace(',', '.', $s);
    if (!preg_match('/^\d{1,5}(\.\d{1,2})?$/', $s)) return null;
    return (float)$s;
}

function money(?float $n): string
{
    if ($n === null) return '';
    return number_format($n, 2, ',', '.') . ' €';
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $map = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','é'=>'e','è'=>'e','ë'=>'e','ê'=>'e','í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
            'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o','ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ñ'=>'n','ç'=>'c'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? substr($text, 0, 60) : 'carta';
}

function unique_slug(string $name, ?int $exceptId = null): string
{
    $base = slugify($name);
    $slug = $base;
    $i = 2;
    while (true) {
        $row = q('SELECT id FROM restaurants WHERE slug = ?', [$slug])->fetch();
        if (!$row || ($exceptId !== null && (int)$row['id'] === $exceptId)) return $slug;
        $slug = $base . '-' . $i++;
    }
}

function valid_color(string $c): string
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? strtolower($c) : '#0e7490';
}

/** ¿Está la suscripción (o prueba) vigente? */
function is_active(array $r): bool
{
    return !empty($r['paid_until']) && $r['paid_until'] >= date('Y-m-d');
}

function days_left(array $r): int
{
    if (empty($r['paid_until'])) return 0;
    $diff = (new DateTime(date('Y-m-d')))->diff(new DateTime($r['paid_until']));
    return $diff->invert ? 0 : $diff->days;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}
