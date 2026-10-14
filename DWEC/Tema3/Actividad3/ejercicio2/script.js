// Ejercicio 2: comprobar la letra del DNI
// Se repite hasta que el usuario pulse "Cancelar"

const LETRAS = ["T", "R", "W", "A", "G", "M", "Y", "F", "P", "D", "X", "B",
                "N", "J", "Z", "S", "Q", "V", "H", "L", "C", "K", "E"];

const lista = document.getElementById("resultado");

// Devuelve la letra que le corresponde a un número de DNI
function calcularLetra(numero) {
  const resto = numero % 23;   // resto de la división entera entre 23 (de 0 a 22)
  return LETRAS[resto];
}

// Añade una línea al historial de la página
function mostrar(texto, clase) {
  const li = document.createElement("li");
  li.textContent = texto;
  li.className = clase;
  lista.appendChild(li);
}

function comprobarDNI() {
  let numeroTexto = prompt("Introduce el número del DNI (sin la letra):");

  // Si pulsa "Cancelar", prompt devuelve null y salimos del bucle
  while (numeroTexto !== null) {
    const numero = Number(numeroTexto);

    // Comprobamos que es un número entero entre 0 y 99999999
    if (numeroTexto.trim() === "" || !Number.isInteger(numero) || numero < 0 || numero > 99999999) {
      alert("El número no es válido. Debe estar entre 0 y 99999999.");
      mostrar(numeroTexto + " → número no válido", "error");
    } else {
      const letra = prompt("Introduce la letra del DNI:");
      if (letra === null) break;   // también se puede cancelar aquí

      const letraCorrecta = calcularLetra(numero);

      if (letra.trim().toUpperCase() === letraCorrecta) {
        alert("El DNI " + numero + letraCorrecta + " es correcto.");
        mostrar(numero + letra.toUpperCase() + " → correcto", "ok");
      } else {
        alert("La letra no es correcta. Al número " + numero + " le corresponde la letra " + letraCorrecta + ".");
        mostrar(numero + letra.toUpperCase() + " → incorrecto (le corresponde la " + letraCorrecta + ")", "error");
      }
    }

    numeroTexto = prompt("Introduce otro número de DNI (o pulsa Cancelar para terminar):");
  }

  mostrar("Has pulsado Cancelar. Fin de la comprobación.", "");
}

document.getElementById("btnEmpezar").addEventListener("click", comprobarDNI);
