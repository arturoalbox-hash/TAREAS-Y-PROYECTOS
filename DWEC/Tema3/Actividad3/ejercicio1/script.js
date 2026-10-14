// Ejercicio 1: mostrar las tablas de multiplicar de los números del 1 al 9

const resultado = document.getElementById("resultado");
let html = "";

// Bucle exterior: recorre los números del 1 al 9 (una tabla por cada número)
for (let numero = 1; numero <= 9; numero++) {
  html += "<table>";
  html += "<tr><th>Tabla del " + numero + "</th></tr>";

  // Bucle interior: multiplica el número por 1, 2, 3... hasta 10
  for (let i = 1; i <= 10; i++) {
    html += "<tr><td>" + numero + " x " + i + " = " + (numero * i) + "</td></tr>";
  }

  html += "</table>";
}

// Mostramos todas las tablas en la página
resultado.innerHTML = html;
