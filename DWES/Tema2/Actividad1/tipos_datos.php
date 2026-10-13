<?php
/*
 * Desarrollo Web Entorno Servidor - Tema 2. Actividad 1
 * Primeros pasos en PHP: tipos de datos sencillos
 * Autor: Arturo Jimenes Navarrete - 2º DAW
 *
 * El programa crea una variable de cada tipo sencillo (entero, decimal,
 * booleano y cadena), muestra su contenido y comprueba su tipo con la
 * función booleana correspondiente: is_int(), is_float(), is_bool() e is_string().
 */

// ---------- 1. Creación de las variables ----------

// Entero (int): número sin decimales
$entero = 2026;

// Decimal (float): número con parte decimal, se escribe con punto
$decimal = 3.1416;

// Booleano (bool): solo puede valer true o false
$booleano = true;

// Cadena de texto (string): texto entre comillas
$cadena = "Hola desde PHP";

// ---------- 2. Función auxiliar ----------

/**
 * Convierte un booleano en el texto "true" o "false".
 * Hace falta porque echo muestra true como "1" y false como cadena vacía.
 */
function textoBooleano(bool $valor): string
{
    return $valor ? "true" : "false";
}

// ---------- 3. Datos que se van a mostrar ----------
// Cada fila guarda: nombre del tipo, la variable, el valor para mostrar,
// la función de comprobación usada y su resultado.
$filas = [
    ["Entero",  '$entero',   $entero,                  "is_int",    is_int($entero)],
    ["Decimal", '$decimal',  $decimal,                 "is_float",  is_float($decimal)],
    ["Booleano", '$booleano', textoBooleano($booleano), "is_bool",   is_bool($booleano)],
    ["Cadena",  '$cadena',   $cadena,                  "is_string", is_string($cadena)],
];

// Comprobación cruzada: una función de tipo devuelve false si la variable es de otro tipo
$cruzadas = [
    ['is_int($decimal)',   is_int($decimal)],
    ['is_string($entero)', is_string($entero)],
    ['is_bool($cadena)',   is_bool($cadena)],
    ['is_float($entero)',  is_float($entero)],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de datos en PHP</title>
    <style>
        /* Estilos de la página */
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
        /* Cada dato va en su propia línea */
        .linea {
            display: block;
            padding: 6px 0;
            border-bottom: 1px solid #eceef3;
        }
        code {
            background: #eef0f8;
            padding: 1px 6px;
            border-radius: 4px;
            font-family: Consolas, monospace;
        }
        .tipo {
            font-weight: bold;
            display: inline-block;
            min-width: 90px;
        }
        .true  { color: #1a7f37; font-weight: bold; }
        .false { color: #c62828; font-weight: bold; }
        footer {
            margin-top: 24px;
            font-size: 0.85em;
            color: #777;
        }
    </style>
</head>
<body>
<div class="contenedor">
    <h1>Tipos de datos sencillos en PHP</h1>

    <h2>Contenido de las variables</h2>
    <?php foreach ($filas as $fila): ?>
        <!-- Se imprime el contenido de cada variable en una línea -->
        <span class="linea">
            <span class="tipo"><?= $fila[0] ?>:</span>
            <code><?= $fila[1] ?></code> = <?= htmlspecialchars((string) $fila[2]) ?>
        </span>
    <?php endforeach; ?>

    <h2>Comprobación del tipo (funciones booleanas)</h2>
    <?php foreach ($filas as $fila): ?>
        <!-- Se imprime el resultado de la función de comprobación de cada tipo -->
        <span class="linea">
            <span class="tipo"><?= $fila[0] ?>:</span>
            <code><?= $fila[3] ?>(<?= $fila[1] ?>)</code> →
            <span class="<?= textoBooleano($fila[4]) ?>"><?= textoBooleano($fila[4]) ?></span>
        </span>
    <?php endforeach; ?>

    <h2>Comprobación cruzada (tipo incorrecto)</h2>
    <?php foreach ($cruzadas as $c): ?>
        <!-- Al comprobar un tipo que no es el suyo, la función devuelve false -->
        <span class="linea">
            <code><?= $c[0] ?></code> →
            <span class="<?= textoBooleano($c[1]) ?>"><?= textoBooleano($c[1]) ?></span>
        </span>
    <?php endforeach; ?>

    <h2>Tipo real con gettype() y var_dump()</h2>
    <?php
    // Se recorren las variables originales para ver su tipo real
    $variables = ['$entero' => $entero, '$decimal' => $decimal, '$booleano' => $booleano, '$cadena' => $cadena];
    foreach ($variables as $nombre => $valor):
    ?>
        <span class="linea">
            <code><?= $nombre ?></code> es de tipo <strong><?= gettype($valor) ?></strong>
            → <code><?php var_dump($valor); ?></code>
        </span>
    <?php endforeach; ?>

    <footer>Arturo Jimenes Navarrete · 2º DAW · DWES Tema 2, Actividad 1</footer>
</div>
</body>
</html>
