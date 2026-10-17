<?php
// Copia este archivo como config.php y rellena tus datos.
return [
    // Nombre del servicio (aparece en la web y en las cartas)
    'app_name' => 'CartaFácil',

    // URL pública SIN barra final, p. ej. https://cartafacil.es
    'base_url' => 'http://localhost:8000',

    // Base de datos: 'mysql' (hosting) o 'sqlite' (pruebas en local)
    'db' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'name'     => 'cartafacil',
        'user'     => 'usuario',
        'pass'     => 'contraseña',
        'sqlite_path' => __DIR__ . '/../data/cartafacil.sqlite',
    ],

    // Días de prueba gratis al registrarse
    'trial_days' => 30,

    // Precio que se muestra en la página de venta
    'price_month' => 9,

    // Correo de contacto que aparece en la web
    'contact_email' => 'hola@cartafacil.es',

    // Envío de correos (recuperar contraseña)
    //   'log'  -> no envía: guarda los correos en data/correos.log (para probar en XAMPP)
    //   'mail' -> mail() de PHP (vale en la mayoría de hostings)
    //   'smtp' -> servidor SMTP (rellena host, port, user y pass)
    'mail' => [
        'method'    => 'log',
        'from'      => 'hola@cartafacil.es',
        'from_name' => 'CartaFácil',
        'host'      => 'smtp.tudominio.es',
        'port'      => 587,          // 587 = STARTTLS, 465 = SSL
        'user'      => '',
        'pass'      => '',
    ],

    // Pagos con Stripe (tarjeta y domiciliación). Déjalo vacío para no mostrar el botón de pago.
    // secret_key: Stripe → Desarrolladores → Claves de API (sk_test_… para pruebas, sk_live_… real)
    // price_id:   Stripe → Catálogo de productos → tu producto de 9 €/mes → ID del precio (price_…)
    // webhook_secret: Stripe → Desarrolladores → Webhooks → tu endpoint → Clave secreta de firma (whsec_…)
    'stripe' => [
        'secret_key'     => '',
        'price_id'       => '',
        'webhook_secret' => '',
    ],

    // Pago manual que se muestra en el panel (déjalo vacío si no lo quieres)
    'manual_payment' => [
        'bizum'      => '',          // p. ej. '600 000 000'
        'iban'       => '',          // p. ej. 'ES00 0000 0000 0000 0000 0000'
        'holder'     => '[TITULAR DE LA CUENTA]',
        'year_price' => 90,          // precio si pagan un año entero
    ],

    // Titular del servicio (aviso legal y privacidad, obligatorio por la LSSI y el RGPD)
    'owner_name'    => '[TU NOMBRE Y APELLIDOS]',
    'owner_nif'     => '[TU NIF]',
    'owner_address' => '[TU DIRECCIÓN POSTAL]',
];
