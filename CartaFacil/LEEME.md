# CartaFácil: carta digital con QR para bares y chiringuitos

Aplicación PHP + MySQL sin dependencias externas: no hace falta Composer ni npm.

## Qué incluye

| Parte | Archivos |
|---|---|
| Web de venta (portada con demo en un móvil, precio, preguntas frecuentes) | `index.php` |
| Registro, entrada y salida | `registro.php`, `login.php`, `logout.php` |
| Recuperar contraseña por correo (enlace de un solo uso, caduca en 1 hora) | `olvide.php`, `restablecer.php`, `app/mailer.php` |
| Panel del bar: categorías, platos, precios, media ración, 14 alérgenos, agotados, ordenar | `panel/index.php`, `panel/plato.php`, `panel/acciones.php` |
| Ajustes: nombre, dirección de la carta, color, teléfono, nota al pie, contraseña, borrar cuenta | `panel/ajustes.php` |
| Suscripción: pago mensual con Stripe (tarjeta o domiciliación), facturas, cambio de tarjeta y baja; o Bizum/transferencia | `panel/suscripcion.php`, `panel/pagar.php`, `webhook-stripe.php`, `app/stripe.php` |
| Código QR (PNG descargable y carteles imprimibles, 6 por A4) | `panel/qr.php`, `assets/js/qr.js` |
| Carta pública para el móvil, con filtro de alérgenos y contador de visitas | `carta.php`, `app/carta_view.php` |
| Aviso legal y privacidad | `legal.php` |
| Base de datos | `sql/mysql.sql` (hosting) y `sql/sqlite.sql` (local) |
| Demo en un solo archivo, para enseñarla sin servidor | `demo/carta-demo.html` |

Seguridad incluida: contraseñas con `password_hash`, consultas preparadas (PDO), token CSRF en todos los formularios, escape de HTML, comprobación de que cada plato pertenece al bar que lo edita, freno a intentos de login, cookie de sesión `HttpOnly` y `SameSite`, carpetas `app/`, `sql/` y `data/` bloqueadas. La carta pública no pone cookies.

## Probarlo en tu ordenador (sin instalar MySQL)

```bash
cp app/config.example.php app/config.php
# en app/config.php cambia 'driver' => 'mysql' por 'driver' => 'sqlite'
php sql/instalar.php --demo
php -S localhost:8000 router.php
```

Abre http://localhost:8000. Demo: http://localhost:8000/c/demo (entra con `demo@cartafacil.es` / `demo1234`).

## Subirlo a internet

Netlify **no sirve** porque no ejecuta PHP. Necesitas un hosting con PHP 8.1+ y MySQL (Hostinger, IONOS, Dinahosting, Raiola…; desde 2–4 €/mes) y un dominio.

1. Crea una base de datos MySQL en el panel del hosting.
2. Importa `sql/mysql.sql` con phpMyAdmin (pestaña *Importar*).
3. Copia `app/config.example.php` como `app/config.php` y rellena `base_url` (tu dominio con https), los datos de MySQL y tu correo.
4. Sube todos los archivos por FTP a la carpeta pública (`public_html`). No subas la carpeta `data/`.
5. Activa el certificado SSL gratuito (Let's Encrypt) en el panel del hosting.
6. Si tu hosting tiene terminal: `php sql/instalar.php --demo` crea la carta de ejemplo en `/c/demo`, que se ve en la portada. Si no tiene, ejecútalo en local con MySQL o crea tú una cuenta con dirección `demo` desde el registro.

Las URL bonitas (`/c/nombre-del-bar`) funcionan con el `.htaccess` incluido (Apache, lo normal en hostings compartidos). Si no funcionan, la carta también está en `carta.php?r=nombre-del-bar`.

## Correo y recuperación de contraseña

Si un bar olvida su contraseña, pulsa "¿Has olvidado la contraseña?" en la página de entrada, escribe su correo y recibe un enlace para elegir una nueva. El enlace caduca en 1 hora y solo sirve una vez. Por seguridad, la web nunca dice si un correo está registrado o no.

El envío se configura en el bloque `'mail'` de `app/config.php`:

- `'method' => 'log'` (por defecto): no se envía nada y cada correo se guarda en `data/correos.log`. Sirve para probar en XAMPP: abre ese archivo y copia el enlace.
- `'method' => 'mail'`: usa el correo del hosting. Funciona en la mayoría de hostings compartidos. Pon en `from` una dirección de tu dominio (por ejemplo `hola@tudominio.es`) creada en el panel del hosting, o los correos acabarán en spam.
- `'method' => 'smtp'`: rellena `host`, `port` (587 o 465), `user` y `pass` con los datos del correo de tu hosting o de un servicio como Brevo (300 correos al día gratis).

Si ya tenías la base de datos creada, importa `sql/actualizar_1_recuperar_contrasena.sql` en phpMyAdmin para añadir la tabla nueva.

## Cobrar a los bares

Cada cuenta empieza con 30 días de prueba (`trial_days`). En el panel, el bar tiene la sección **Suscripción**:

- **Con Stripe configurado:** pulsa "Activar el pago mensual", paga con tarjeta o domiciliación en la página segura de Stripe y su carta queda activa. Se le cobra solo cada mes, ve el historial con las facturas en PDF y puede cambiar de tarjeta o darse de baja desde el portal de Stripe. Si aún le quedan días de prueba, no se le cobra nada hasta que terminen.
- **Sin Stripe (o si lo prefiere):** ve tus datos de Bizum y transferencia (bloque `manual_payment` de `config.php`). Cuando te paguen, alargas su fecha en phpMyAdmin:

```sql
UPDATE restaurants SET paid_until = '2027-06-30' WHERE email = 'bar@ejemplo.com';
```

Cuando caduca, la carta muestra "pide la carta al personal" y el panel avisa al bar con un enlace a Suscripción.

### Configurar Stripe (una sola vez, unos 15 minutos)

1. Crea una cuenta en stripe.com y completa los datos de tu negocio (necesitarás tu NIF y una cuenta bancaria).
2. **Catálogo de productos → Añadir producto:** "CartaFácil", precio recurrente de 9 € al mes. Copia el ID del precio (`price_…`).
3. **Desarrolladores → Claves de API:** copia la clave secreta (`sk_test_…` para probar, `sk_live_…` para cobrar de verdad).
4. **Desarrolladores → Webhooks → Añadir endpoint:** URL `https://TU-DOMINIO/webhook-stripe.php` con los eventos `checkout.session.completed`, `invoice.paid`, `invoice.payment_failed`, `customer.subscription.updated` y `customer.subscription.deleted`. Copia la clave secreta de firma (`whsec_…`).
5. **Configuración → Facturación → Portal de clientes:** actívalo y permite cambiar el método de pago, ver facturas y cancelar la suscripción.
6. **Configuración → Métodos de pago:** activa tarjetas y, si quieres, "Débito directo SEPA" (domiciliación).
7. Pon las tres claves en el bloque `'stripe'` de `app/config.php`.
8. Prueba con las claves `sk_test_…` y la tarjeta de prueba `4242 4242 4242 4242` (cualquier fecha futura y CVC). Cuando funcione, cambia a las claves `sk_live_…` y crea el webhook también en modo real.

Si ya tenías la base de datos creada, importa `sql/actualizar_2_pagos.sql` en phpMyAdmin.

**Facturas e IVA:** Stripe genera una factura por cada cobro. En *Configuración → Facturación → Facturas* pon tu NIF y tus datos, y decide con tu gestor si el precio lleva IVA (21 %) incluido o añadido. Para cobrar y facturar normalmente necesitarás estar dado de alta como autónomo.

## Ideas para las siguientes versiones

1. **Carta en inglés y otros idiomas**: en la costa es lo que más valoran los bares con turistas.
2. Fotos de los platos.
3. Panel de administrador para ver todos los bares y renovarlos sin entrar en phpMyAdmin.
