<?php
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/layout.php';

$app = config('app_name');
$owner = config('owner_name');
$nif = config('owner_nif');
$addr = config('owner_address');
$mail = config('contact_email');
page_header('Aviso legal y privacidad');
?>
<main class="wrap narrow legal">
  <div class="card">
    <h1>Aviso legal y política de privacidad</h1>

    <h2>1. Titular</h2>
    <p>En cumplimiento del artículo 10 de la Ley 34/2002 (LSSI-CE): <?= e($app) ?> es un servicio de <strong><?= e($owner) ?></strong>, NIF <?= e($nif) ?>, con domicilio en <?= e($addr) ?>. Contacto: <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a>.</p>

    <h2>2. El servicio</h2>
    <p><?= e($app) ?> permite a establecimientos de hostelería publicar su carta en internet y generar un código QR. Cada establecimiento es el único responsable del contenido de su carta: platos, precios e información de alérgenos. <?= e($app) ?> no verifica esa información.</p>

    <h2>3. Datos que tratamos</h2>
    <p><strong>De los establecimientos (clientes):</strong> correo electrónico, contraseña cifrada, datos del local (nombre, dirección, teléfono) y el contenido de la carta.</p>
    <ul>
      <li><strong>Finalidad:</strong> prestar el servicio, gestionar la cuenta y la facturación, y atender consultas.</li>
      <li><strong>Base jurídica:</strong> ejecución del contrato (art. 6.1.b RGPD) y obligaciones legales de facturación (art. 6.1.c).</li>
      <li><strong>Conservación:</strong> mientras la cuenta esté activa y, después, durante los plazos legales (normalmente, seis años para datos de facturación).</li>
    </ul>
    <p><strong>De las personas que consultan una carta:</strong> no pedimos datos ni usamos cookies. Solo contamos de forma agregada cuántas veces se abre cada carta al día, sin guardar direcciones IP ni identificadores.</p>
    <p><strong>Destinatarios:</strong> no cedemos datos a terceros salvo obligación legal. El proveedor de alojamiento web actúa como encargado del tratamiento.</p>

    <h2>4. Tus derechos</h2>
    <p>Puedes ejercer los derechos de acceso, rectificación, supresión, oposición, limitación y portabilidad escribiendo a <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a>. También puedes borrar tu cuenta y todos sus datos desde el panel, en Ajustes. Si consideras que no se han respetado tus derechos, puedes reclamar ante la Agencia Española de Protección de Datos (<a href="https://www.aepd.es" target="_blank" rel="noopener">aepd.es</a>).</p>

    <h2>5. Cookies</h2>
    <p>Solo se usa una cookie técnica de sesión (<code>cf_sess</code>) para mantener abierta la sesión de los establecimientos en el panel. Es necesaria para el funcionamiento y está exenta de consentimiento (art. 22.2 LSSI). Las cartas públicas no instalan cookies.</p>

    <h2>6. Condiciones de uso</h2>
    <ul>
      <li>El periodo de prueba es gratuito. Después, el servicio cuesta <?= (int)config('price_month') ?> € al mes, sin permanencia.</li>
      <li>El cliente se compromete a publicar información veraz y lícita.</li>
      <li>El titular procurará la máxima disponibilidad del servicio, pero no responde de interrupciones ajenas a su control.</li>
      <li>Se aplica la legislación española.</li>
    </ul>
  </div>
</main>
<?php page_footer();
