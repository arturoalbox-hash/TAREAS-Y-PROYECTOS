<?php
// Todas las acciones del panel que modifican datos (solo POST)
require __DIR__ . '/../app/bootstrap.php';

$r = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('panel/');
check_csrf();

$rid = (int)$r['id'];
$action = (string)($_POST['action'] ?? '');
$back = 'panel/';

/** Devuelve la categoría solo si pertenece a este local */
function own_category(int $rid, $id): ?array
{
    return q('SELECT * FROM categories WHERE id = ? AND restaurant_id = ?', [(int)$id, $rid])->fetch() ?: null;
}

function own_item(int $rid, $id): ?array
{
    return q('SELECT * FROM items WHERE id = ? AND restaurant_id = ?', [(int)$id, $rid])->fetch() ?: null;
}

/** Reordena posiciones 0..n y mueve un elemento arriba (-1) o abajo (+1) */
function move_row(string $table, string $scopeCol, int $scopeId, int $id, int $dir): void
{
    $rows = q("SELECT id FROM $table WHERE $scopeCol = ? ORDER BY position, id", [$scopeId])->fetchAll(PDO::FETCH_COLUMN);
    $ids = array_map('intval', $rows);
    $i = array_search($id, $ids, true);
    if ($i === false) return;
    $j = $i + $dir;
    if ($j < 0 || $j >= count($ids)) return;
    [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
    foreach ($ids as $pos => $rowId) {
        q("UPDATE $table SET position = ? WHERE id = ?", [$pos, $rowId]);
    }
}

function item_data(): array
{
    $name = trim((string)($_POST['name'] ?? ''));
    $desc = trim((string)($_POST['description'] ?? ''));
    return [
        'name'       => mb_substr($name, 0, 120),
        'description'=> $desc === '' ? null : mb_substr($desc, 0, 400),
        'price'      => parse_price($_POST['price'] ?? ''),
        'half_price' => parse_price($_POST['half_price'] ?? ''),
        'allergens'  => parse_alergenos($_POST['allergens'] ?? []),
        'available'  => !empty($_POST['available']) ? 1 : 0,
        'price_raw'  => trim((string)($_POST['price'] ?? '')),
        'half_raw'   => trim((string)($_POST['half_price'] ?? '')),
    ];
}

function item_errors(array $d): ?string
{
    if ($d['name'] === '') return 'El plato necesita un nombre.';
    if ($d['price_raw'] !== '' && $d['price'] === null) return 'El precio no es válido. Usa un formato como 12,50.';
    if ($d['half_raw'] !== '' && $d['half_price'] === null) return 'El precio de media ración no es válido.';
    return null;
}

switch ($action) {
    // ---------------- Categorías ----------------
    case 'cat_add':
        $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100);
        if ($name === '') { flash('Escribe un nombre para la categoría.', 'error'); break; }
        $pos = (int)q('SELECT COALESCE(MAX(position), -1) + 1 FROM categories WHERE restaurant_id = ?', [$rid])->fetchColumn();
        q('INSERT INTO categories (restaurant_id, name, position) VALUES (?,?,?)', [$rid, $name, $pos]);
        flash('Categoría "' . $name . '" creada.');
        $back = 'panel/#cat-' . db()->lastInsertId();
        break;

    case 'cat_rename':
        $cat = own_category($rid, $_POST['id'] ?? 0);
        $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100);
        if ($cat && $name !== '') {
            q('UPDATE categories SET name = ? WHERE id = ?', [$name, $cat['id']]);
            flash('Categoría renombrada.');
            $back = 'panel/#cat-' . $cat['id'];
        }
        break;

    case 'cat_delete':
        $cat = own_category($rid, $_POST['id'] ?? 0);
        if ($cat) {
            q('DELETE FROM items WHERE category_id = ? AND restaurant_id = ?', [$cat['id'], $rid]);
            q('DELETE FROM categories WHERE id = ?', [$cat['id']]);
            flash('Categoría "' . $cat['name'] . '" eliminada.');
        }
        break;

    case 'cat_move':
        $cat = own_category($rid, $_POST['id'] ?? 0);
        if ($cat) {
            move_row('categories', 'restaurant_id', $rid, (int)$cat['id'], ($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            $back = 'panel/#cat-' . $cat['id'];
        }
        break;

    // ---------------- Platos ----------------
    case 'item_add':
        $cat = own_category($rid, $_POST['category_id'] ?? 0);
        if (!$cat) break;
        $d = item_data();
        if ($err = item_errors($d)) { flash($err, 'error'); $back = 'panel/#cat-' . $cat['id']; break; }
        $pos = (int)q('SELECT COALESCE(MAX(position), -1) + 1 FROM items WHERE category_id = ?', [$cat['id']])->fetchColumn();
        q('INSERT INTO items (restaurant_id, category_id, name, description, price, half_price, allergens, available, position) VALUES (?,?,?,?,?,?,?,?,?)',
          [$rid, $cat['id'], $d['name'], $d['description'], $d['price'], $d['half_price'], $d['allergens'], 1, $pos]);
        flash('"' . $d['name'] . '" añadido a ' . $cat['name'] . '.');
        $back = 'panel/#cat-' . $cat['id'];
        break;

    case 'item_update':
        $item = own_item($rid, $_POST['id'] ?? 0);
        if (!$item) break;
        $d = item_data();
        $cat = own_category($rid, $_POST['category_id'] ?? $item['category_id']) ?? own_category($rid, $item['category_id']);
        if ($err = item_errors($d)) { flash($err, 'error'); $back = 'panel/plato.php?id=' . $item['id']; break; }
        $pos = (int)$item['position'];
        if ((int)$cat['id'] !== (int)$item['category_id']) {
            $pos = (int)q('SELECT COALESCE(MAX(position), -1) + 1 FROM items WHERE category_id = ?', [$cat['id']])->fetchColumn();
        }
        q('UPDATE items SET category_id=?, name=?, description=?, price=?, half_price=?, allergens=?, available=?, position=? WHERE id=?',
          [$cat['id'], $d['name'], $d['description'], $d['price'], $d['half_price'], $d['allergens'], $d['available'], $pos, $item['id']]);
        flash('Cambios guardados.');
        $back = 'panel/#item-' . $item['id'];
        break;

    case 'item_toggle':
        $item = own_item($rid, $_POST['id'] ?? 0);
        if ($item) {
            q('UPDATE items SET available = ? WHERE id = ?', [$item['available'] ? 0 : 1, $item['id']]);
            flash($item['available'] ? '"' . $item['name'] . '" marcado como agotado.' : '"' . $item['name'] . '" vuelve a estar disponible.');
            $back = 'panel/#item-' . $item['id'];
        }
        break;

    case 'item_delete':
        $item = own_item($rid, $_POST['id'] ?? 0);
        if ($item) {
            q('DELETE FROM items WHERE id = ?', [$item['id']]);
            flash('"' . $item['name'] . '" eliminado.');
            $back = 'panel/#cat-' . $item['category_id'];
        }
        break;

    case 'item_move':
        $item = own_item($rid, $_POST['id'] ?? 0);
        if ($item) {
            move_row('items', 'category_id', (int)$item['category_id'], (int)$item['id'], ($_POST['dir'] ?? '') === 'up' ? -1 : 1);
            $back = 'panel/#item-' . $item['id'];
        }
        break;

    // ---------------- Ajustes ----------------
    case 'settings':
        $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 120);
        if (mb_strlen($name) < 2) { flash('El nombre del local es obligatorio.', 'error'); $back = 'panel/ajustes.php'; break; }
        $slugIn = slugify((string)($_POST['slug'] ?? $name));
        $slug = unique_slug($slugIn, $rid);
        $clean = fn($k, $max) => ($v = mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max)) === '' ? null : $v;
        q('UPDATE restaurants SET name=?, slug=?, tagline=?, phone=?, address=?, color=?, footer_note=? WHERE id=?',
          [$name, $slug, $clean('tagline', 160), $clean('phone', 30), $clean('address', 200), valid_color((string)($_POST['color'] ?? '')), $clean('footer_note', 255), $rid]);
        flash($slug !== $slugIn ? 'Guardado. La dirección "' . $slugIn . '" estaba ocupada; tu carta queda en "' . $slug . '".' : 'Ajustes guardados.');
        $back = 'panel/ajustes.php';
        break;

    case 'password':
        $cur = (string)($_POST['current'] ?? '');
        $new = (string)($_POST['new'] ?? '');
        $back = 'panel/ajustes.php';
        if (!password_verify($cur, $r['password_hash'])) { flash('La contraseña actual no es correcta.', 'error'); break; }
        if (strlen($new) < 8) { flash('La nueva contraseña debe tener al menos 8 caracteres.', 'error'); break; }
        q('UPDATE restaurants SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $rid]);
        session_regenerate_id(true);
        flash('Contraseña cambiada.');
        break;

    case 'delete_account':
        $back = 'panel/ajustes.php';
        if (!password_verify((string)($_POST['current'] ?? ''), $r['password_hash'])) { flash('Contraseña incorrecta. No se ha borrado nada.', 'error'); break; }
        q('DELETE FROM visits WHERE restaurant_id = ?', [$rid]);
        q('DELETE FROM password_resets WHERE restaurant_id = ?', [$rid]);
        q('DELETE FROM payments WHERE restaurant_id = ?', [$rid]);
        q('DELETE FROM items WHERE restaurant_id = ?', [$rid]);
        q('DELETE FROM categories WHERE restaurant_id = ?', [$rid]);
        q('DELETE FROM restaurants WHERE id = ?', [$rid]);
        $_SESSION = [];
        session_destroy();
        redirect('');
}

redirect($back);
