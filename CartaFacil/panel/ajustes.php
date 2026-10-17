<?php
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/layout.php';

$r = require_login();
page_header('Ajustes', ['panel' => true, 'active' => 'ajustes']);
?>
<main class="wrap narrow">
  <div class="card">
    <h1>Datos de tu local</h1>
    <form method="post" action="acciones.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="settings">
      <label>Nombre del local<input name="name" value="<?= e($r['name']) ?>" maxlength="120" required></label>
      <label>Frase bajo el nombre <span class="muted">(opcional)</span><input name="tagline" value="<?= e($r['tagline']) ?>" maxlength="160" placeholder="Pescaíto frito frente al mar desde 1998"></label>
      <label>Dirección de tu carta
        <div class="prefix"><span><?= e(url('c/')) ?></span><input name="slug" value="<?= e($r['slug']) ?>" maxlength="60" pattern="[a-z0-9\-]+"></div>
        <small>Solo minúsculas, números y guiones. Si la cambias, los QR ya impresos dejarán de funcionar.</small>
      </label>
      <div class="row2">
        <label>Teléfono <span class="muted">(opcional)</span><input name="phone" value="<?= e($r['phone']) ?>" maxlength="30" inputmode="tel"></label>
        <label>Color principal<input type="color" name="color" value="<?= e($r['color']) ?>"></label>
      </div>
      <label>Dirección <span class="muted">(opcional)</span><input name="address" value="<?= e($r['address']) ?>" maxlength="200" placeholder="Paseo del Malecón s/n, Garrucha"></label>
      <label>Nota al pie de la carta <span class="muted">(opcional)</span><input name="footer_note" value="<?= e($r['footer_note']) ?>" maxlength="255" placeholder="Precios con IVA incluido. Pan y aceitunas: 1,50 € por persona."></label>
      <button class="btn">Guardar</button>
    </form>
  </div>

  <div class="card">
    <h2>Suscripción</h2>
    <?php if (is_active($r)): ?>
      <p>Tu carta está activa hasta el <strong><?= e(date('d/m/Y', strtotime($r['paid_until']))) ?></strong> (<?= days_left($r) ?> días).</p>
    <?php else: ?>
      <p><strong>Tu carta no está activa.</strong> Los clientes no pueden verla.</p>
    <?php endif; ?>
    <a class="btn btn-ghost" href="suscripcion.php">Gestionar pago y facturas</a>
  </div>

  <div class="card">
    <h2>Cambiar contraseña</h2>
    <form method="post" action="acciones.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <label>Contraseña actual<input type="password" name="current" required autocomplete="current-password"></label>
      <label>Nueva contraseña<input type="password" name="new" required minlength="8" autocomplete="new-password"></label>
      <button class="btn btn-ghost">Cambiar contraseña</button>
    </form>
  </div>

  <details class="card danger-zone">
    <summary>Eliminar mi cuenta</summary>
    <p>Se borrarán tu carta y todos tus datos de forma permanente.</p>
    <form method="post" action="acciones.php" onsubmit="return confirm('¿Seguro? Esto no se puede deshacer.')">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete_account">
      <label>Escribe tu contraseña para confirmar<input type="password" name="current" required></label>
      <button class="btn btn-danger">Eliminar definitivamente</button>
    </form>
  </details>
</main>
<?php page_footer();
