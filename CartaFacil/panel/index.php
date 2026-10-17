<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/layout.php';

$r = require_login();
$rid = (int)$r['id'];

$cats = q('SELECT * FROM categories WHERE restaurant_id = ? ORDER BY position, id', [$rid])->fetchAll();
$items = q('SELECT * FROM items WHERE restaurant_id = ? ORDER BY position, id', [$rid])->fetchAll();
$byCat = [];
foreach ($items as $it) $byCat[$it['category_id']][] = $it;

$since = date('Y-m-d', strtotime('-29 days'));
$visitas = (int)q('SELECT COALESCE(SUM(hits),0) FROM visits WHERE restaurant_id = ? AND day >= ?', [$rid, $since])->fetchColumn();

function action_btn(string $action, int $id, string $label, array $extra = [], string $class = 'icon-btn', string $confirm = ''): string
{
    $h = '<form method="post" action="acciones.php" class="inline"' . ($confirm ? ' onsubmit="return confirm(' . e(json_encode($confirm, JSON_UNESCAPED_UNICODE)) . ')"' : '') . '>' . csrf_field()
       . '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="id" value="' . $id . '">';
    foreach ($extra as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    return $h . '<button class="' . e($class) . '" title="' . e(strip_tags($label)) . '" aria-label="' . e(strip_tags($label)) . '">' . $label . '</button></form>';
}

page_header('Mi carta', ['panel' => true, 'active' => 'carta']);
?>
<main class="wrap">
  <div class="panel-head">
    <div>
      <h1><?= e($r['name']) ?></h1>
      <p class="muted">Tu carta: <a href="<?= e(carta_url($r['slug'])) ?>" target="_blank" rel="noopener"><?= e(carta_url($r['slug'])) ?></a></p>
    </div>
    <div class="stats">
      <div class="stat"><span class="stat-n"><?= count($items) ?></span><span class="stat-l">platos</span></div>
      <div class="stat"><span class="stat-n"><?= number_format($visitas, 0, ',', '.') ?></span><span class="stat-l">visitas (30 días)</span></div>
    </div>
  </div>

  <?php if (!$items): ?>
    <div class="card tip"><strong>Primeros pasos:</strong> añade tus platos en cada categoría, revisa los alérgenos y después imprime tu <a href="qr.php">código QR</a> para las mesas.</div>
  <?php endif; ?>

  <?php foreach ($cats as $ci => $c): $list = $byCat[$c['id']] ?? []; ?>
  <section class="card cat" id="cat-<?= (int)$c['id'] ?>">
    <div class="cat-head">
      <details class="rename">
        <summary><h2><?= e($c['name']) ?> <span class="count"><?= count($list) ?></span></h2></summary>
        <form method="post" action="acciones.php" class="row">
          <?= csrf_field() ?><input type="hidden" name="action" value="cat_rename"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <input name="name" value="<?= e($c['name']) ?>" maxlength="100" required aria-label="Nuevo nombre">
          <button class="btn btn-sm">Renombrar</button>
        </form>
      </details>
      <div class="tools">
        <?php if ($ci > 0) echo action_btn('cat_move', (int)$c['id'], '↑', ['dir' => 'up']); ?>
        <?php if ($ci < count($cats) - 1) echo action_btn('cat_move', (int)$c['id'], '↓', ['dir' => 'down']); ?>
        <?= action_btn('cat_delete', (int)$c['id'], '🗑', [], 'icon-btn danger', '¿Eliminar la categoría "' . $c['name'] . '" y sus ' . count($list) . ' platos?') ?>
      </div>
    </div>

    <?php if ($list): ?>
    <ul class="items">
      <?php foreach ($list as $ii => $it): ?>
      <li class="item <?= $it['available'] ? '' : 'is-off' ?>" id="item-<?= (int)$it['id'] ?>">
        <div class="item-main">
          <div class="item-name"><?= e($it['name']) ?><?php if (!$it['available']): ?> <span class="tag">Agotado</span><?php endif; ?></div>
          <?php if ($it['description']): ?><div class="item-desc"><?= e($it['description']) ?></div><?php endif; ?>
          <?php if ($al = alergenos_list($it['allergens'])): ?>
            <div class="alg"><?php foreach ($al as $a): ?><span title="<?= e(ALERGENOS[$a][0]) ?>"><?= ALERGENOS[$a][1] ?></span><?php endforeach; ?></div>
          <?php endif; ?>
        </div>
        <div class="item-price">
          <?= e(money($it['price'] !== null ? (float)$it['price'] : null)) ?>
          <?php if ($it['half_price'] !== null): ?><small>½ <?= e(money((float)$it['half_price'])) ?></small><?php endif; ?>
        </div>
        <div class="tools">
          <?php if ($ii > 0) echo action_btn('item_move', (int)$it['id'], '↑', ['dir' => 'up']); ?>
          <?php if ($ii < count($list) - 1) echo action_btn('item_move', (int)$it['id'], '↓', ['dir' => 'down']); ?>
          <?= action_btn('item_toggle', (int)$it['id'], $it['available'] ? 'Agotar' : 'Activar', [], 'btn btn-sm btn-ghost') ?>
          <a class="btn btn-sm btn-ghost" href="plato.php?id=<?= (int)$it['id'] ?>">Editar</a>
          <?= action_btn('item_delete', (int)$it['id'], '🗑', [], 'icon-btn danger', '¿Eliminar "' . $it['name'] . '"?') ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
      <p class="muted empty">Todavía no hay platos en esta categoría.</p>
    <?php endif; ?>

    <details class="add-item" <?= !$list ? 'open' : '' ?>>
      <summary class="btn btn-sm">+ Añadir plato</summary>
      <form method="post" action="acciones.php" class="item-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="item_add"><input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>">
        <?php $it = null; include __DIR__ . '/_item_fields.php'; ?>
        <button class="btn">Añadir a <?= e($c['name']) ?></button>
      </form>
    </details>
  </section>
  <?php endforeach; ?>

  <section class="card">
    <h2>Nueva categoría</h2>
    <form method="post" action="acciones.php" class="row">
      <?= csrf_field() ?><input type="hidden" name="action" value="cat_add">
      <input name="name" placeholder="Ej.: Arroces, Postres, Cócteles…" maxlength="100" required aria-label="Nombre de la categoría">
      <button class="btn">Crear</button>
    </form>
  </section>
</main>
<?php page_footer();
