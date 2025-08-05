<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once "../db/conexion.php";

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    die("❌ ID de denuncia no especificado.");
}

$sql = "SELECT * FROM denuncias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows != 1) {
    die("❌ Denuncia no encontrada.");
}

$denuncia = $resultado->fetch_assoc();
$mensajeEnviado = isset($_GET['enviado']) && $_GET['enviado'] === 'ok';

$sqlArchivos = "SELECT * FROM archivos WHERE id_denuncia = ?";
$stmt2 = $conn->prepare($sqlArchivos);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$archivos = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle de Comunicación</title>
  <link href="../assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen p-6 flex justify-center">
  <div class="w-full max-w-6xl bg-white p-8 rounded-2xl shadow-2xl border border-gray-300 space-y-6">
    <h1 class="text-2xl font-bold text-[#685f2f]">📄 Comunicación #<?= htmlspecialchars($denuncia['id']) ?></h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <p><strong>Nombre:</strong> <?= htmlspecialchars($denuncia['nombre']) ?></p>
      <p><strong>Cédula:</strong> <?= htmlspecialchars($denuncia['cedula']) ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($denuncia['correo']) ?></p>
      <p><strong>Estado:</strong> <?= ucfirst($denuncia['estado']) ?></p>
      <p><strong>Fecha:</strong> <?= $denuncia['fecha_creacion'] ?></p>
    </div>

    <div>
      <h2 class="text-lg font-semibold text-[#942934] mb-2">📝 Mensaje:</h2>
      <div class="border border-gray-200 p-4 bg-gray-50 rounded-xl whitespace-pre-line shadow"><?= htmlspecialchars($denuncia['mensaje']) ?></div>
    </div>

    <?php if (!empty($denuncia['firma'])): ?>
    <div>
      <p class="mt-4 font-semibold">Firma del colaborador:</p>
      <img src="<?= htmlspecialchars($denuncia['firma']) ?>" alt="Firma" class="w-60 border border-gray-200 mt-2 rounded-2xl shadow">
    </div>
    <?php endif; ?>

    <div>
      <h2 class="text-lg font-semibold text-[#942934] mb-2">📎 Archivos:</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php while ($archivo = $archivos->fetch_assoc()): ?>
          <?php if ($archivo['tipo'] === 'foto'): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-md p-2">
              <img src="../<?= $archivo['ruta_archivo'] ?>" alt="Evidencia"
                class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition"
                onclick="ampliarImagen(this.src)">
            </div>
          <?php elseif ($archivo['tipo'] === 'audio'): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-md p-4 flex flex-col items-center justify-center">
              <p class="text-sm font-medium text-[#942934] mb-2">🎧 Evidencia de audio</p>
              <audio controls class="w-full">
                <source src="../<?= $archivo['ruta_archivo'] ?>" type="audio/mpeg">
                Tu navegador no soporta el elemento de audio.
              </audio>
            </div>
          <?php endif; ?>
        <?php endwhile; ?>
      </div>
    </div>
<!-- Conversación intercalada con recarga -->
<div class="bg-white p-6 rounded-2xl shadow border border-gray-300 space-y-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold text-[#a08e43]">💬 Conversación</h2>
    <button onclick="recargarMensajes()"
      class="bg-[#685f2f] hover:bg-[#a08e43] text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
      🔄 Revisar nuevos mensajes
    </button>
  </div>

  <div id="contenedor-conversacion">
    <p class="text-gray-500 text-sm">⏳ Cargando conversación...</p>
  </div>
</div>
<!-- Formulario de respuesta del comité -->
<div>
  <h2 class="text-lg font-semibold text-[#685f2f] mb-2">✉️ Responder al emisor del reporte:</h2>
  <form action="responder_correo.php" method="POST" class="space-y-4">
    <input type="hidden" name="correo" value="<?= htmlspecialchars($denuncia['correo']) ?>">
    <input type="hidden" name="nombre" value="<?= htmlspecialchars($denuncia['nombre']) ?>">
    <input type="hidden" name="id_denuncia" value="<?= $denuncia['id'] ?>">

    <textarea name="respuesta" rows="6" class="w-full border border-gray-300 rounded-xl p-3 placeholder:text-gray-500 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" placeholder="Escribe tu respuesta..."></textarea>

    <button type="submit" class="bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
      Enviar respuesta
    </button>

    <?php if ($mensajeEnviado): ?>
      <div class="bg-green-100 border border-green-300 text-green-800 p-3 rounded-xl text-center animate-pulse">
        ✅ Respuesta enviada exitosamente al emisor del reporte.
      </div>
    <?php endif; ?>
  </form>
</div>

<div class="text-center pt-6">
  <a href="dashboard.php" class="text-[#942934] hover:underline font-medium">← Volver al panel</a>
</div>

<!-- Modal para imagen ampliada -->
<div id="modalImagen" class="fixed inset-0 bg-black/80 hidden justify-center items-center z-50">
  <img id="imagenGrande" class="max-w-4xl max-h-[90vh] rounded-xl shadow-xl border-4 border-white" />
</div>

<!-- Script imagen + recarga chat -->
<script>
  let ultimaConversacion = "";

  function ampliarImagen(src) {
    const modal = document.getElementById('modalImagen');
    const img = document.getElementById('imagenGrande');
    img.src = src;
    modal.classList.remove('hidden');
  }

  document.getElementById('modalImagen').addEventListener('click', () => {
    document.getElementById('modalImagen').classList.add('hidden');
  });

  function recargarMensajes() {
    const contenedor = document.getElementById("contenedor-conversacion");
    const sonido = document.getElementById("sonido-pop");
    const id = <?= json_encode($denuncia['id']) ?>;

    fetch(`cargar_conversacion_admin.php?id=${id}`)
      .then(res => {
        if (!res.ok) throw new Error("Error al cargar");
        return res.text();
      })
      .then(html => {
        if (html.trim() !== ultimaConversacion.trim()) {
          contenedor.innerHTML = html;
          sonido.play().catch(() => {});
          ultimaConversacion = html;
        }
      })
      .catch(() => {
        contenedor.innerHTML = "<p class='text-red-500 text-sm'>❌ Error al actualizar mensajes.</p>";
      });
  }

  document.addEventListener("DOMContentLoaded", () => {
    recargarMensajes();
    setInterval(recargarMensajes, 20000);
  });
</script>

<audio id="sonido-pop" src="../assets/sounds/pop.mp3" preload="auto"></audio>

</div> <!-- cierre del div grande -->
</body>
</html>
