<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';

$price = (int)config('price_month');
$trial = (int)config('trial_days');
page_header('Carta digital con QR para bares y chiringuitos', [
    'description' => "Crea la carta digital de tu bar o chiringuito con código QR y alérgenos. Cambia precios al momento, sin reimprimir. {$trial} días gratis.",
]);
?>
<main>
  <section class="wrap hero">
    <div>
      <h1>La carta de tu bar, <em>en el móvil de tus clientes</em>.</h1>
      <p class="lead">Un código QR en cada mesa y tus clientes ven la carta al momento, con precios y alérgenos. ¿Se acaba el calamar o sube un precio? Lo cambias desde el móvil en 10 segundos. Sin reimprimir nada.</p>
      <div class="hero-cta">
        <a class="btn" href="registro.php">Pruébalo gratis <?= $trial ?> días</a>
        <a class="btn btn-ghost" href="<?= e(carta_url('demo')) ?>" target="_blank" rel="noopener">Ver una carta de ejemplo</a>
      </div>
      <p class="muted"><small>Sin tarjeta. Sin permanencia. <?= $price ?> € al mes después.</small></p>
    </div>
    <div class="phone" aria-hidden="true"><iframe src="<?= e(carta_url('demo')) ?>" title="Carta de ejemplo" loading="lazy" tabindex="-1"></iframe></div>
  </section>

  <section class="wrap section">
    <h2>Pensado para la hostelería de verdad</h2>
    <div class="features">
      <div class="feature"><span class="ico">⚡</span><b>Cambios al instante</b><p>Precios, platos nuevos o fuera de carta. Lo editas desde el móvil y se actualiza en todas las mesas.</p></div>
      <div class="feature"><span class="ico">🚫</span><b>Marca lo agotado</b><p>Un toque y el plato aparece como "agotado hoy". Menos idas y venidas a cocina.</p></div>
      <div class="feature"><span class="ico">⚠️</span><b>Alérgenos en regla</b><p>Los 14 alérgenos del Reglamento UE 1169/2011 en cada plato, y un filtro para que el cliente oculte lo que no puede tomar.</p></div>
      <div class="feature"><span class="ico">🖨️</span><b>QR listo para imprimir</b><p>Descarga el QR o imprime carteles para las mesas con el nombre de tu local.</p></div>
      <div class="feature"><span class="ico">🌞</span><b>Se lee a pleno sol</b><p>Diseño claro, letra grande y pensado para el móvil. Carga en un segundo, incluso con poca cobertura.</p></div>
      <div class="feature"><span class="ico">📈</span><b>Cuántos la miran</b><p>Ve cuántas veces se abre tu carta cada mes.</p></div>
    </div>
  </section>

  <section class="wrap section">
    <h2>Listo en 10 minutos</h2>
    <ol class="steps">
      <li><b>Crea tu cuenta</b><br><span class="muted">Solo el nombre del local, un correo y una contraseña.</span></li>
      <li><b>Añade tus platos</b><br><span class="muted">Por categorías, con precio, media ración y alérgenos.</span></li>
      <li><b>Imprime el QR</b><br><span class="muted">Ponlo en las mesas y listo. Los cambios futuros no requieren reimprimir.</span></li>
    </ol>
  </section>

  <section class="wrap section" id="precio">
    <h2 class="center">Un precio sencillo</h2>
    <div class="card price-card">
      <div class="amount"><?= $price ?> € <small>/ mes</small></div>
      <ul>
        <li>Platos y categorías ilimitados</li>
        <li>Código QR y carteles para mesas</li>
        <li>Alérgenos y filtro para clientes</li>
        <li>Estadística de visitas</li>
        <li>Ayuda por correo o WhatsApp</li>
      </ul>
      <a class="btn btn-block" href="registro.php">Empezar <?= $trial ?> días gratis</a>
    </div>
  </section>

  <section class="wrap section faq">
    <h2>Preguntas frecuentes</h2>
    <details><summary>¿Mis clientes tienen que descargar una aplicación?</summary><p>No. Solo apuntan con la cámara del móvil al código QR y la carta se abre en el navegador.</p></details>
    <details><summary>¿Tengo que volver a imprimir el QR si cambio la carta?</summary><p>No. El QR siempre lleva a la misma dirección y la carta se actualiza sola.</p></details>
    <details><summary>¿Es obligatorio informar de los alérgenos?</summary><p>Sí. Desde 2014 los establecimientos deben informar de los 14 alérgenos de declaración obligatoria. La carta digital te ayuda a tenerlo siempre a la vista del cliente, aunque debes seguir atendiendo las consultas en sala.</p></details>
    <details><summary>¿Puedo seguir teniendo carta en papel?</summary><p>Claro. Muchos locales usan las dos: el QR en las mesas y algunas cartas impresas para quien las prefiera.</p></details>
    <details><summary>¿Hay permanencia?</summary><p>No. Pagas mes a mes y puedes darte de baja cuando quieras desde los ajustes.</p></details>
  </section>
</main>
<?php page_footer();
