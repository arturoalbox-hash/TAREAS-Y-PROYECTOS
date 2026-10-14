// Ejercicio 4: contar las vocales de un texto y escribirlo al revés

// Cuenta las vocales (también con tilde o diéresis, en mayúsculas o minúsculas)
function contarVocales(texto) {
  const vocales = "aeiouáéíóúü";
  let contador = 0;

  for (const letra of texto.toLowerCase()) {
    if (vocales.includes(letra)) {
      contador++;
    }
  }
  return contador;
}

// Devuelve el texto al revés recorriéndolo desde el final
function alReves(texto) {
  let resultado = "";
  for (let i = texto.length - 1; i >= 0; i--) {
    resultado += texto[i];
  }
  return resultado;
  // Otra forma más corta: return texto.split("").reverse().join("");
}

function leerTexto() {
  const texto = prompt("Escribe una frase:");
  if (texto === null) return;   // ha pulsado "Cancelar"

  const salida = document.getElementById("resultado");
  salida.innerHTML = "";

  const crear = (etiqueta, contenido) => {
    const el = document.createElement(etiqueta);
    el.textContent = contenido;
    salida.appendChild(el);
  };

  crear("p", "Texto original: " + texto);
  crear("p", "Número de vocales: " + contarVocales(texto));
  crear("p", "Texto al revés: " + alReves(texto));
}

document.getElementById("btnLeer").addEventListener("click", leerTexto);
