<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/layout.php';

$r = require_login();
$it = q('SELECT * FROM items WHERE id = ? AND restaurant_id = ?', [(int)($_GET['id'] ?? 0), (int)$r['id']])->fetch();
if (!$it) {
    flash('Ese plato no existe.', 'error');
    redirect('panel/');
}
$cats = q('SELECT id, name FROM categories WHERE restaurant_id = ? ORDER BY position, id', [(int)$r['id']])->fetchAll();

page_header('Editar plato', ['panel' => true, 'active' => 'carta']);
?>
<main class="wrap narrow">
  <p><a href="./#item-<?= (int)$it['id'] ?>">← Volver a la carta</a></p>
  <div class="card">
    <h1>Editar plato</h1>
    <form method="post" action="acciones.php" class="item-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="item_update"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
      <?php include __DIR__ . '/_item_fields.php'; ?>
      <label>Categoría
        <select name="category_id">
          <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$it['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="check"><input type="checkbox" name="available" value="1" <?= $it['available'] ? 'checked' : '' ?>> Disponible (desmárcalo si se ha agotado)</label>
      <button class="btn btn-block">Guardar cambios</button>
    </form>
  </div>
</main>
<?php page_footer();
