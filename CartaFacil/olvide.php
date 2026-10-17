<?php
// Paso 1 de la recuperación: el bar escribe su correo y recibe un enlace
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';
require __DIR__ . '/app/mailer.php';

if (current_restaurant()) redirect('panel/ajustes.php');

$sent = false;
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));

    // Límite: 3 peticiones cada 15 minutos por sesión
    $_SESSION['reset_reqs'] = array_values(array_filter($_SESSION['reset_reqs'] ?? [], fn($t) => $t > time() - 900));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe un correo electrónico válido.';
    } elseif (count($_SESSION['reset_reqs']) >= 3) {
        $error = 'Has pedido varios enlaces seguidos. Espera unos minutos y revisa también la carpeta de spam.';
    } else {
        $_SESSION['reset_reqs'][] = time();
        $r = q('SELECT id, name FROM restaurants WHERE email = ?', [$email])->fetch();
        if ($r) {
            // Invalida enlaces anteriores y crea uno nuevo válido 1 hora
            q('DELETE FROM password_resets WHERE restaurant_id = ?', [$r['id']]);
            $token = bin2hex(random_bytes(32));
            q('INSERT INTO password_resets (restaurant_id, token_hash, expires_at, created_at) VALUES (?,?,?,?)',
              [$r['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600), now()]);
            $link = url('restablecer.php?token=' . $token);
            $app = config('app_name');
            send_mail($email, "Restablece tu contraseña de $app",
                "Hola,\n\n"
              . "Hemos recibido una petición para cambiar la contraseña de la carta de {$r['name']}.\n\n"
              . "Para elegir una contraseña nueva, abre este enlace (caduca en 1 hora):\n$link\n\n"
              . "Si no lo has pedido tú, ignora este correo: tu contraseña actual sigue funcionando.\n\n"
              . "— $app");
        }
        // Mostramos siempre el mismo mensaje para no revelar qué correos están registrados
        $sent = true;
    }
}

page_header('Recuperar contraseña');
?>
<main class="wrap narrow">
  <div class="card auth">
    <h1>¿Has olvidado la contraseña?</h1>
    <?php if ($sent): ?>
      <div class="flash">Si <strong><?= e($email) ?></strong> tiene una cuenta, te hemos enviado un correo con un enlace para elegir una contraseña nueva. Caduca en 1 hora.</div>
      <p class="muted">¿No te llega? Revisa la carpeta de spam o correo no deseado. Si sigue sin llegar en unos minutos, escríbenos a <a href="mailto:<?= e(config('contact_email')) ?>"><?= e(config('contact_email')) ?></a>.</p>
      <p><a href="login.php">← Volver a entrar</a></p>
    <?php else: ?>
      <p class="muted">Escribe el correo con el que te registraste y te enviaremos un enlace para crear una contraseña nueva.</p>
      <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <label>Correo electrónico<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" autofocus></label>
        <button class="btn btn-block">Enviarme el enlace</button>
      </form>
      <p class="muted center"><a href="login.php">← Volver a entrar</a></p>
    <?php endif; ?>
  </div>
</main>
<?php page_footer();
