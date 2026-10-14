// Ejercicio 3: calcular la nota final
// Examen 30 %, Prácticas 60 %, Comportamiento 10 %

const PESO_EXAMEN = 0.3;
const PESO_PRACTICAS = 0.6;
const PESO_COMPORTAMIENTO = 0.1;

// Pide una nota y repite la pregunta hasta que sea un número entre 0 y 10.
// Devuelve null si el usuario pulsa "Cancelar".
function pedirNota(nombre) {
  while (true) {
    const texto = prompt("Introduce la nota de " + nombre + " (de 0 a 10):");
    if (texto === null) return null;

    const nota = Number(texto.replace(",", "."));   // admite 7,5 o 7.5
    if (texto.trim() !== "" && !isNaN(nota) && nota >= 0 && nota <= 10) {
      return nota;
    }
    alert("Nota no válida. Tiene que ser un número entre 0 y 10.");
  }
}

function calcularNota() {
  const examen = pedirNota("EXAMEN");
  if (examen === null) return;
  const practicas = pedirNota("PRÁCTICAS");
  if (practicas === null) return;
  const comportamiento = pedirNota("COMPORTAMIENTO");
  if (comportamiento === null) return;

  const notaFinal = examen * PESO_EXAMEN + practicas * PESO_PRACTICAS + comportamiento * PESO_COMPORTAMIENTO;

  // Calificación según la nota final
  let calificacion;
  if (notaFinal < 5) calificacion = "Suspenso";
  else if (notaFinal < 6) calificacion = "Aprobado";
  else if (notaFinal < 7) calificacion = "Bien";
  else if (notaFinal < 9) calificacion = "Notable";
  else calificacion = "Sobresaliente";

  document.getElementById("resultado").innerHTML =
    "<table>" +
    "<tr><th>Apartado</th><th>Nota</th><th>Peso</th><th>Puntos</th></tr>" +
    "<tr><td>Examen</td><td>" + examen + "</td><td>30 %</td><td>" + (examen * PESO_EXAMEN).toFixed(2) + "</td></tr>" +
    "<tr><td>Prácticas</td><td>" + practicas + "</td><td>60 %</td><td>" + (practicas * PESO_PRACTICAS).toFixed(2) + "</td></tr>" +
    "<tr><td>Comportamiento</td><td>" + comportamiento + "</td><td>10 %</td><td>" + (comportamiento * PESO_COMPORTAMIENTO).toFixed(2) + "</td></tr>" +
    "</table>" +
    "<h2>Nota final: " + notaFinal.toFixed(2) + " → <span class='" + (notaFinal >= 5 ? "ok" : "error") + "'>" + calificacion + "</span></h2>";
}

document.getElementById("btnCalcular").addEventListener("click", calcularNota);
