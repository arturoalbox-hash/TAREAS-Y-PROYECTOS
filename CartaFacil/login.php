<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';

if (current_restaurant()) redirect('panel/');

$error = '';
$email = '';
$lock = $_SESSION['login_lock'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $pass  = (string)($_POST['password'] ?? '');

    if ($lock > time()) {
        $error = 'Demasiados intentos. Espera unos minutos y vuelve a probar.';
    } else {
        $r = q('SELECT id, password_hash FROM restaurants WHERE email = ?', [$email])->fetch();
        if ($r && password_verify($pass, $r['password_hash'])) {
            unset($_SESSION['login_fails'], $_SESSION['login_lock']);
            if (password_needs_rehash($r['password_hash'], PASSWORD_DEFAULT)) {
                q('UPDATE restaurants SET password_hash = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $r['id']]);
            }
            login_restaurant((int)$r['id']);
            redirect('panel/');
        }
        usleep(400000); // frena ataques de fuerza bruta
        $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
        if ($_SESSION['login_fails'] >= 5) {
            $_SESSION['login_lock'] = time() + 300;
            $_SESSION['login_fails'] = 0;
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}

page_header('Entrar');
?>
<main class="wrap narrow">
  <div class="card auth">
    <h1>Entrar</h1>
    <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label>Correo electrónico<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" autofocus></label>
      <label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
      <button class="btn btn-block">Entrar</button>
    </form>
    <p class="muted center">¿No tienes cuenta? <a href="registro.php">Pruébalo gratis</a><br><a href="olvide.php">¿Has olvidado la contraseña?</a></p>
  </div>
</main>
<?php page_footer();
