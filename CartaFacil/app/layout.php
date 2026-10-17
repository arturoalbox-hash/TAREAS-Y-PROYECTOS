<?php
function page_header(string $title, array $opts = []): void
{
    $app = config('app_name');
    $r = current_restaurant();
    $panel = $opts['panel'] ?? false;
    $active = $opts['active'] ?? '';
    $f = flash();
    ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · <?= e($app) ?></title>
  <?php if (!empty($opts['description'])): ?><meta name="description" content="<?= e($opts['description']) ?>"><?php endif; ?>
  <?php if ($panel): ?><meta name="robots" content="noindex"><?php endif; ?>
  <link rel="icon" href="<?= e(url('assets/img/icon.svg')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
</head>
<body class="<?= $panel ? 'is-panel' : 'is-site' ?>">
  <header class="topbar">
    <div class="wrap topbar-in">
      <a class="brand" href="<?= e(url($r ? 'panel/' : '')) ?>"><span class="brand-mark">▦</span> <?= e($app) ?></a>
      <nav class="topnav">
        <?php if ($r): ?>
          <a href="<?= e(url('panel/')) ?>" class="<?= $active === 'carta' ? 'on' : '' ?>">Mi carta</a>
          <a href="<?= e(url('panel/ajustes.php')) ?>" class="<?= $active === 'ajustes' ? 'on' : '' ?>">Ajustes</a>
          <a href="<?= e(url('panel/qr.php')) ?>" class="<?= $active === 'qr' ? 'on' : '' ?>">Código QR</a>
          <a href="<?= e(url('panel/suscripcion.php')) ?>" class="<?= $active === 'suscripcion' ? 'on' : '' ?>">Suscripción</a>
          <a href="<?= e(carta_url($r['slug'])) ?>" target="_blank" rel="noopener">Ver carta ↗</a>
          <form method="post" action="<?= e(url('logout.php')) ?>" class="inline"><?= csrf_field() ?><button class="linkish">Salir</button></form>
        <?php else: ?>
          <a href="<?= e(url('login.php')) ?>">Entrar</a>
          <a href="<?= e(url('registro.php')) ?>" class="btn btn-sm">Probar gratis</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <?php if ($r && $panel): ?>
    <?php $dl = days_left($r); ?>
    <?php if (!is_active($r)): ?>
      <div class="banner banner-warn wrap">Tu periodo de prueba o suscripción ha terminado y tu carta no se muestra al público. <a href="<?= e(url('panel/suscripcion.php')) ?>">Actívala aquí</a>.</div>
    <?php elseif ($dl <= 7 && !in_array($r['subscription_status'] ?? '', ['active', 'trialing'], true)): ?>
      <div class="banner wrap">Te quedan <?= $dl ?> días de carta activa. <a href="<?= e(url('panel/suscripcion.php')) ?>">Activa el pago mensual</a> para que no se corte.</div>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($f): ?><div class="wrap"><div class="flash flash-<?= e($f['type']) ?>" role="status"><?= e($f['msg']) ?></div></div><?php endif; ?>
<?php
}

function page_footer(): void
{
    ?>
  <footer class="sitefoot">
    <div class="wrap">
      <span>© <?= date('Y') ?> <?= e(config('app_name')) ?></span>
      <a href="<?= e(url('legal.php')) ?>">Aviso legal y privacidad</a>
      <a href="mailto:<?= e(config('contact_email')) ?>"><?= e(config('contact_email')) ?></a>
    </div>
  </footer>
</body>
</html>
<?php
}
