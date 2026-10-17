<?php
// Paso 2 de la recuperación: el bar abre el enlace del correo y elige contraseña nueva
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';

header('Referrer-Policy: no-referrer'); // que el token no se filtre a otras webs

$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$reset = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $reset = q('SELECT pr.*, r.email, r.name FROM password_resets pr JOIN restaurants r ON r.id = pr.restaurant_id WHERE pr.token_hash = ?',
               [hash('sha256', $token)])->fetch();
    if ($reset && $reset['expires_at'] < now()) {
        q('DELETE FROM password_resets WHERE id = ?', [$reset['id']]);
        $reset = null;
    }
}

$error = '';
if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');
    if (strlen($p1) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($p1 !== $p2) {
        $error = 'Las dos contraseñas no coinciden.';
    } else {
        q('UPDATE restaurants SET password_hash = ? WHERE id = ?', [password_hash($p1, PASSWORD_DEFAULT), $reset['restaurant_id']]);
        q('DELETE FROM password_resets WHERE restaurant_id = ?', [$reset['restaurant_id']]);
        unset($_SESSION['login_fails'], $_SESSION['login_lock']);
        login_restaurant((int)$reset['restaurant_id']);
        flash('Contraseña cambiada. ¡Ya estás dentro!');
        redirect('panel/');
    }
}

page_header('Nueva contraseña');
?>
<main class="wrap narrow">
  <div class="card auth">
    <?php if (!$reset): ?>
      <h1>Enlace no válido</h1>
      <p>Este enlace ha caducado o ya se ha usado. Los enlaces para cambiar la contraseña duran 1 hora y solo sirven una vez.</p>
      <a class="btn btn-block" href="olvide.php">Pedir un enlace nuevo</a>
    <?php else: ?>
      <h1>Elige una contraseña nueva</h1>
      <p class="muted">Para la cuenta <strong><?= e($reset['email']) ?></strong> (<?= e($reset['name']) ?>).</p>
      <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label>Nueva contraseña<input type="password" name="password" required minlength="8" autocomplete="new-password" autofocus><small>Mínimo 8 caracteres.</small></label>
        <label>Repite la contraseña<input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
        <button class="btn btn-block">Guardar y entrar</button>
      </form>
    <?php endif; ?>
  </div>
</main>
<?php page_footer();
