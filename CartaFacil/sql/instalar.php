<?php
/**
 * Instalador por línea de comandos.
 *   php sql/instalar.php           -> crea las tablas (SQLite) o comprueba la conexión (MySQL)
 *   php sql/instalar.php --demo    -> además crea el local de demostración (/c/demo)
 * En MySQL importa antes sql/mysql.sql con phpMyAdmin.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo desde la línea de comandos.');
}
define('CF_NO_SESSION', true);
require __DIR__ . '/../app/bootstrap.php';

$driver = config('db')['driver'];
if ($driver === 'sqlite') {
    $dir = dirname(config('db')['sqlite_path']);
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    db()->exec(file_get_contents(__DIR__ . '/sqlite.sql'));
    // Añade columnas nuevas a bases antiguas
    $cols = array_column(db()->query('PRAGMA table_info(restaurants)')->fetchAll(), 'name');
    foreach (['stripe_customer_id', 'stripe_subscription_id', 'subscription_status'] as $c) {
        if (!in_array($c, $cols, true)) db()->exec("ALTER TABLE restaurants ADD COLUMN $c TEXT NULL");
    }
    echo "Tablas SQLite listas en " . config('db')['sqlite_path'] . "\n";
} else {
    db()->query('SELECT 1 FROM restaurants LIMIT 1');
    echo "Conexión MySQL correcta y tablas encontradas.\n";
    try {
        db()->query('SELECT 1 FROM password_resets LIMIT 1');
    } catch (PDOException $e) {
        echo "AVISO: falta la tabla password_resets. Importa sql/actualizar_1_recuperar_contrasena.sql en phpMyAdmin.\n";
    }
    try {
        db()->query('SELECT stripe_customer_id FROM restaurants LIMIT 1');
        db()->query('SELECT 1 FROM payments LIMIT 1');
    } catch (PDOException $e) {
        echo "AVISO: faltan las tablas de pagos. Importa sql/actualizar_2_pagos.sql en phpMyAdmin.\n";
    }
}

if (in_array('--demo', $argv, true)) {
    $d = require __DIR__ . '/../app/demo_data.php';
    $r = $d['restaurant'];
    $old = q('SELECT id FROM restaurants WHERE slug = ?', [$r['slug']])->fetch();
    if ($old) {
        q('DELETE FROM items WHERE restaurant_id = ?', [$old['id']]);
        q('DELETE FROM categories WHERE restaurant_id = ?', [$old['id']]);
        q('DELETE FROM visits WHERE restaurant_id = ?', [$old['id']]);
        q('DELETE FROM password_resets WHERE restaurant_id = ?', [$old['id']]);
        q('DELETE FROM payments WHERE restaurant_id = ?', [$old['id']]);
        q('DELETE FROM restaurants WHERE id = ?', [$old['id']]);
    }
    q('INSERT INTO restaurants (email, password_hash, name, slug, tagline, phone, address, color, footer_note, paid_until, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)', [
        $r['email'], password_hash($r['password'], PASSWORD_DEFAULT), $r['name'], $r['slug'], $r['tagline'],
        $r['phone'], $r['address'], $r['color'], $r['footer_note'], '2099-12-31', now(),
    ]);
    $rid = (int)db()->lastInsertId();
    $cp = 0;
    foreach ($d['categories'] as $cat => $items) {
        q('INSERT INTO categories (restaurant_id, name, position) VALUES (?,?,?)', [$rid, $cat, $cp++]);
        $cid = (int)db()->lastInsertId();
        foreach ($items as $ip => [$name, $desc, $price, $half, $alg]) {
            $avail = in_array($name, $d['sold_out'], true) ? 0 : 1;
            q('INSERT INTO items (restaurant_id, category_id, name, description, price, half_price, allergens, available, position) VALUES (?,?,?,?,?,?,?,?,?)',
              [$rid, $cid, $name, $desc, $price, $half, $alg, $avail, $ip]);
        }
    }
    echo "Demo creada: " . carta_url($r['slug']) . "  (entrar con {$r['email']} / {$r['password']})\n";
}
