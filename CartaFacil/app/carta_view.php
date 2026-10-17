<?php
/**
 * Pinta la carta pública. Se usa en carta.php y en el generador de la demo.
 * $opts['css'] = URL de la hoja de estilos, o $opts['inline_css'] = CSS en línea.
 */
function render_carta(array $r, array $cats, array $byCat, array $usados, array $opts): void
{
    $fmt = fn($v) => $v === null ? '' : number_format((float)$v, 2, ',', '.') . ' €';
    ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($r['name']) ?> · Carta</title>
  <meta name="description" content="Carta de <?= e($r['name']) ?> con precios y alérgenos.">
  <meta name="theme-color" content="<?= e($r['color']) ?>">
  <?php if (!empty($opts['inline_css'])): ?><style><?= $opts['inline_css'] ?></style><?php else: ?><link rel="stylesheet" href="<?= e($opts['css']) ?>"><?php endif; ?>
  <style>:root{--brand:<?= e($r['color']) ?>}</style>
</head>
<body>
  <header class="c-hero">
    <div class="c-wrap">
      <h1><?= e($r['name']) ?></h1>
      <?php if (!empty($r['tagline'])): ?><p class="c-tag"><?= e($r['tagline']) ?></p><?php endif; ?>
    </div>
  </header>

  <?php if ($cats): ?>
  <nav class="c-nav" aria-label="Secciones de la carta">
    <div class="c-nav-in">
      <?php foreach ($cats as $c): ?><a href="#s<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
    </div>
  </nav>
  <?php endif; ?>

  <main class="c-wrap">
    <?php if ($usados): ?>
    <details class="c-filter">
      <summary><span>⚠️ ¿Tienes alguna alergia?</span> <span class="c-filter-hint">Filtrar platos</span></summary>
      <p>Marca lo que no puedes tomar y ocultaremos los platos que lo contienen.</p>
      <div class="c-chips">
        <?php foreach (ALERGENOS as $k => [$label, $icon]): if (!in_array($k, $usados, true)) continue; ?>
          <label class="c-chip"><input type="checkbox" value="<?= e($k) ?>"><span><?= $icon ?> <?= e($label) ?></span></label>
        <?php endforeach; ?>
      </div>
      <p class="c-filter-count" aria-live="polite"></p>
    </details>
    <?php endif; ?>

    <?php if (!$cats): ?><p class="c-empty">La carta se está preparando. ¡Vuelve pronto!</p><?php endif; ?>

    <?php foreach ($cats as $c): ?>
    <section class="c-sec" id="s<?= (int)$c['id'] ?>">
      <h2><?= e($c['name']) ?></h2>
      <ul>
        <?php foreach ($byCat[$c['id']] as $it): $al = alergenos_list($it['allergens']); ?>
        <li class="c-item<?= $it['available'] ? '' : ' is-off' ?>" data-al="<?= e(implode(' ', $al)) ?>">
          <div class="c-row">
            <h3><?= e($it['name']) ?></h3>
            <div class="c-price">
              <?php if ($it['price'] !== null && $it['price'] !== ''): ?><span><?= e($fmt($it['price'])) ?></span><?php endif; ?>
              <?php if ($it['half_price'] !== null && $it['half_price'] !== ''): ?><span class="c-half">Media <?= e($fmt($it['half_price'])) ?></span><?php endif; ?>
            </div>
          </div>
          <?php if (!empty($it['description'])): ?><p class="c-desc"><?= e($it['description']) ?></p><?php endif; ?>
          <?php if (!$it['available']): ?><p class="c-off">Agotado hoy</p><?php endif; ?>
          <?php if ($al): ?><p class="c-al"><?php foreach ($al as $a): ?><span title="<?= e(ALERGENOS[$a][0]) ?>"><?= ALERGENOS[$a][1] ?><em><?= e(ALERGENOS[$a][0]) ?></em></span><?php endforeach; ?></p><?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endforeach; ?>

    <footer class="c-foot">
      <?php if (!empty($r['footer_note'])): ?><p><?= e($r['footer_note']) ?></p><?php endif; ?>
      <p>Si tienes alguna alergia o intolerancia, consulta al personal antes de pedir.</p>
      <?php if (!empty($r['phone']) || !empty($r['address'])): ?>
        <p class="c-contact">
          <?php if (!empty($r['address'])): ?><span>📍 <?= e($r['address']) ?></span><?php endif; ?>
          <?php if (!empty($r['phone'])): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $r['phone'])) ?>">📞 <?= e($r['phone']) ?></a><?php endif; ?>
        </p>
      <?php endif; ?>
      <p class="c-powered">Carta digital creada con <?php if (!empty($opts['powered_url'])): ?><a href="<?= e($opts['powered_url']) ?>"><?= e($opts['powered']) ?></a><?php else: ?><?= e($opts['powered']) ?><?php endif; ?></p>
    </footer>
  </main>

  <script>
  (function () {
    var boxes = document.querySelectorAll('.c-chip input');
    if (!boxes.length) return;
    var items = document.querySelectorAll('.c-item');
    var countEl = document.querySelector('.c-filter-count');
    function apply() {
      var sel = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).map(function (b) { return b.value; });
      var hidden = 0;
      items.forEach(function (li) {
        var al = (li.getAttribute('data-al') || '').split(' ');
        var hide = sel.some(function (s) { return al.indexOf(s) !== -1; });
        li.hidden = hide;
        if (hide) hidden++;
      });
      document.querySelectorAll('.c-sec').forEach(function (sec) {
        sec.hidden = !sec.querySelector('.c-item:not([hidden])');
      });
      countEl.textContent = sel.length ? hidden + ' platos ocultos por contener lo que has marcado.' : '';
    }
    boxes.forEach(function (b) { b.addEventListener('change', apply); });
  })();
  </script>
</body>
</html>
<?php
}
