<?php
/*
 * Desarrollo Web Entorno Servidor - Tema 2. Actividad 2
 * Primeros pasos en PHP: operaciones aritméticas
 * Autor: Arturo Jimenes Navarrete - 2º DAW
 *
 * El programa crea varias variables para cada operación (suma, resta,
 * multiplicación y división), calcula el resultado con los operadores
 * aritméticos de PHP y muestra cada operación en una línea.
 * Además incluye un formulario para recoger nuevos datos y repetir los cálculos.
 */

// ---------- 1. Función auxiliar para recoger datos ----------

/**
 * Lee un número enviado por el formulario (método GET).
 * Si no se ha enviado o no es un número válido, devuelve el valor por defecto.
 */
function recogerNumero(string $nombre, float $porDefecto): float
{
    if (isset($_GET[$nombre]) && is_numeric($_GET[$nombre])) {
        return (float) $_GET[$nombre];
    }
    return $porDefecto;
}

/**
 * Da formato a un número: sin decimales si es entero, con hasta 2 si no lo es.
 */
function formatear(float $numero): string
{
    if ($numero == (int) $numero) {
        return (string) (int) $numero;
    }
    return rtrim(rtrim(number_format($numero, 2, ',', '.'), '0'), ',');
}

// ---------- 2. Variables de cada operación ----------
// Cada operación tiene sus propias variables (al menos dos por operación).

// Suma: tres sumandos
$suma1 = recogerNumero('suma1', 15);
$suma2 = recogerNumero('suma2', 27);
$suma3 = recogerNumero('suma3', 8);

// Resta: minuendo y dos sustraendos
$resta1 = recogerNumero('resta1', 100);
$resta2 = recogerNumero('resta2', 35);
$resta3 = recogerNumero('resta3', 12);

// Multiplicación: tres factores
$multi1 = recogerNumero('multi1', 4);
$multi2 = recogerNumero('multi2', 6);
$multi3 = recogerNumero('multi3', 2.5);

// División: dividendo y divisor
$divi1 = recogerNumero('divi1', 81);
$divi2 = recogerNumero('divi2', 4);

// ---------- 3. Cálculo de los resultados ----------

$resultadoSuma  = $suma1 + $suma2 + $suma3;     // operador +
$resultadoResta = $resta1 - $resta2 - $resta3;  // operador -
$resultadoMulti = $multi1 * $multi2 * $multi3;  // operador *

// En la división se comprueba antes que el divisor no sea 0,
// porque dividir entre 0 provoca un error (DivisionByZeroError).
if ($divi2 != 0) {
    $resultadoDivi = formatear($divi1 / $divi2); // operador /
    $divisionValida = true;
} else {
    $resultadoDivi = "no se puede dividir entre 0";
    $divisionValida = false;
}

// Datos del formulario: nombre del campo, etiqueta y valor actual
$campos = [
    'Suma'           => ['suma1' => $suma1, 'suma2' => $suma2, 'suma3' => $suma3],
    'Resta'          => ['resta1' => $resta1, 'resta2' => $resta2, 'resta3' => $resta3],
    'Multiplicación' => ['multi1' => $multi1, 'multi2' => $multi2, 'multi3' => $multi3],
    'División'       => ['divi1' => $divi1, 'divi2' => $divi2],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operaciones en PHP</title>
    <style>
        /* Estilos generales */
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f2f4f7;
            color: #2b2f36;
            margin: 0;
            padding: 30px 16px;
        }
        .contenedor {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 24px 28px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        h1 {
            margin-top: 0;
            color: #4f5b93; /* morado de PHP */
            font-size: 1.6em;
        }
        h2 {
            font-size: 1.15em;
            border-bottom: 2px solid #8892bf;
            padding-bottom: 4px;
            margin-top: 28px;
        }
        /* Cada operación ocupa su propia línea */
        .linea {
            display: block;
            padding: 8px 0;
            border-bottom: 1px solid #eceef3;
        }
        .operacion {
            font-weight: bold;
            display: inline-block;
            min-width: 260px;
        }
        .resultado {
            color: #1a7f37;
            font-weight: bold;
        }
        .error {
            color: #c62828;
            font-weight: bold;
        }
        /* Formulario */
        fieldset {
            border: 1px solid #dcdfea;
            border-radius: 8px;
            margin: 0 0 12px;
            padding: 8px 12px 12px;
        }
        legend {
            font-weight: bold;
            color: #4f5b93;
        }
        input[type="number"] {
            width: 90px;
            padding: 6px;
            margin: 4px 6px 0 0;
            border: 1px solid #c5c9d6;
            border-radius: 5px;
        }
        button, .reset {
            background: #4f5b93;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 0.95em;
            cursor: pointer;
            text-decoration: none;
        }
        .reset {
            background: #8a8fa3;
            margin-left: 8px;
        }
        footer {
            margin-top: 24px;
            font-size: 0.85em;
            color: #777;
        }
    </style>
</head>
<body>
<div class="contenedor">
    <h1>Operaciones aritméticas en PHP</h1>

    <h2>Resultados</h2>

    <!-- Suma: se muestran los tres sumandos y el resultado -->
    <span class="linea">
        <span class="operacion">El resultado de la suma:</span>
        <?= formatear($suma1) ?> + <?= formatear($suma2) ?> + <?= formatear($suma3) ?> =
        <span class="resultado"><?= formatear($resultadoSuma) ?></span>
    </span>

    <!-- Resta -->
    <span class="linea">
        <span class="operacion">El resultado de la resta:</span>
        <?= formatear($resta1) ?> - <?= formatear($resta2) ?> - <?= formatear($resta3) ?> =
        <span class="resultado"><?= formatear($resultadoResta) ?></span>
    </span>

    <!-- Multiplicación -->
    <span class="linea">
        <span class="operacion">El resultado de la multiplicación:</span>
        <?= formatear($multi1) ?> * <?= formatear($multi2) ?> * <?= formatear($multi3) ?> =
        <span class="resultado"><?= formatear($resultadoMulti) ?></span>
    </span>

    <!-- División: si el divisor es 0 se muestra el mensaje de error en rojo -->
    <span class="linea">
        <span class="operacion">El resultado de la división:</span>
        <?= formatear($divi1) ?> / <?= formatear($divi2) ?> =
        <span class="<?= $divisionValida ? 'resultado' : 'error' ?>"><?= $resultadoDivi ?></span>
    </span>

    <h2>Cambiar los datos</h2>
    <!-- Formulario para recoger nuevos datos; se envía a la misma página -->
    <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
        <?php foreach ($campos as $operacion => $variables): ?>
            <fieldset>
                <legend><?= $operacion ?></legend>
                <?php foreach ($variables as $nombre => $valor): ?>
                    <input type="number" step="any" name="<?= $nombre ?>"
                           value="<?= $valor ?>" aria-label="<?= $nombre ?>" required>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <button type="submit">Calcular</button>
        <a class="reset" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Valores iniciales</a>
    </form>

    <footer>Arturo Jimenes Navarrete · 2º DAW · DWES Tema 2, Actividad 2</footer>
</div>
</body>
</html>
