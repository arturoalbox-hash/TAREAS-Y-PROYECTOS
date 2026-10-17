<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';

if (current_restaurant()) redirect('panel/');

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $old['name']  = trim((string)($_POST['name'] ?? ''));
    $old['email'] = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $pass  = (string)($_POST['password'] ?? '');
    $acepta = !empty($_POST['acepto']);

    if (mb_strlen($old['name']) < 2 || mb_strlen($old['name']) > 120) $errors[] = 'Escribe el nombre de tu local (entre 2 y 120 caracteres).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo electrónico no es válido.';
    if (strlen($pass) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    if (!$acepta) $errors[] = 'Debes aceptar el aviso legal y la política de privacidad.';
    if (!$errors && q('SELECT id FROM restaurants WHERE email = ?', [$old['email']])->fetch()) {
        $errors[] = 'Ya existe una cuenta con ese correo. ¿Quieres <a href="login.php">entrar</a>?';
    }

    if (!$errors) {
        $slug = unique_slug($old['name']);
        $trial = date('Y-m-d', strtotime('+' . (int)config('trial_days', 30) . ' days'));
        q('INSERT INTO restaurants (email, password_hash, name, slug, color, paid_until, created_at) VALUES (?,?,?,?,?,?,?)',
          [$old['email'], password_hash($pass, PASSWORD_DEFAULT), $old['name'], $slug, '#0e7490', $trial, now()]);
        $id = (int)db()->lastInsertId();
        // Categorías de ejemplo para no empezar en blanco
        foreach (['Para picar', 'Raciones', 'Bebidas'] as $i => $cat) {
            q('INSERT INTO categories (restaurant_id, name, position) VALUES (?,?,?)', [$id, $cat, $i]);
        }
        login_restaurant($id);
        flash('¡Cuenta creada! Tienes ' . (int)config('trial_days', 30) . ' días de prueba gratis. Empieza añadiendo tus platos.');
        redirect('panel/');
    }
}

page_header('Crear cuenta');
?>
<main class="wrap narrow">
  <div class="card auth">
    <h1>Crea la carta de tu local</h1>
    <p class="muted"><?= (int)config('trial_days', 30) ?> días gratis. Sin tarjeta.</p>
    <?php if ($errors): ?><div class="flash flash-error"><?= implode('<br>', array_map(fn($x) => str_contains($x, '<a ') ? $x : e($x), $errors)) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <label>Nombre del local<input name="name" value="<?= e($old['name']) ?>" required maxlength="120" placeholder="Chiringuito La Ola"></label>
      <label>Correo electrónico<input type="email" name="email" value="<?= e($old['email']) ?>" required autocomplete="email"></label>
      <label>Contraseña<input type="password" name="password" required minlength="8" autocomplete="new-password"><small>Mínimo 8 caracteres.</small></label>
      <label class="check"><input type="checkbox" name="acepto" value="1"> Acepto el <a href="legal.php" target="_blank">aviso legal y la política de privacidad</a></label>
      <button class="btn btn-block">Crear mi carta</button>
    </form>
    <p class="muted center">¿Ya tienes cuenta? <a href="login.php">Entra aquí</a></p>
  </div>
</main>
<?php page_footer();
