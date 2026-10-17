<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/layout.php';

$r = require_login();
$link = carta_url($r['slug']);
page_header('Código QR', ['panel' => true, 'active' => 'qr']);
?>
<main class="wrap">
  <div class="card no-print">
    <h1>Tu código QR</h1>
    <p>Este código lleva a <a href="<?= e($link) ?>" target="_blank" rel="noopener"><?= e($link) ?></a>. Si cambias platos o precios, <strong>no hace falta volver a imprimirlo</strong>: la carta se actualiza sola.</p>
    <div class="qr-big"><div id="qr-main"></div></div>
    <div class="row wrap-row">
      <button class="btn" id="dl">Descargar PNG</button>
      <button class="btn btn-ghost" onclick="window.print()">Imprimir carteles para mesas</button>
    </div>
    <p class="muted">Consejo: plastifica los carteles o ponlos en un portamenús de metacrilato.</p>
  </div>

  <h2 class="no-print">Vista previa de impresión (6 por hoja A4)</h2>
  <div class="print-sheet">
    <?php for ($i = 0; $i < 6; $i++): ?>
      <div class="table-card" style="--brand: <?= e(valid_color($r['color'])) ?>">
        <div class="tc-name"><?= e($r['name']) ?></div>
        <div class="tc-qr" data-qr></div>
        <div class="tc-cta">Escanea para ver la carta</div>
        <div class="tc-sub">Con información de alérgenos</div>
      </div>
    <?php endfor; ?>
  </div>
</main>
<script src="<?= e(url('assets/js/qr.js')) ?>"></script>
<script>
  (function () {
    var link = <?= json_encode($link, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;
    document.getElementById('qr-main').innerHTML = CFQR.svg(link, 300);
    var small = CFQR.svg(link, 160);
    document.querySelectorAll('[data-qr]').forEach(function (el) { el.innerHTML = small; });
    document.getElementById('dl').addEventListener('click', function () {
      var a = document.createElement('a');
      a.href = CFQR.canvas(link, 1200).toDataURL('image/png');
      a.download = 'qr-<?= e($r['slug']) ?>.png';
      document.body.appendChild(a); a.click(); a.remove();
    });
  })();
</script>
<?php page_footer();
