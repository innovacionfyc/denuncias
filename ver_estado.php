<?php require_once "db/conexion.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultar denuncia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen p-4 flex items-center justify-center flex-col">

<?php if (isset($_GET['mensaje'])): ?>
  <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded relative text-sm mb-6 animate-pulse text-center max-w-2xl w-full">
    <?= htmlspecialchars($_GET['mensaje']) ?>
  </div>
<?php endif; ?>


<?php
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id):
?>

  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-300 space-y-4">
    <h2 class="text-xl font-bold text-center text-[#942934]">🔍 Consultar estado de denuncia</h2>
    <form method="GET" class="space-y-4">
      <input type="number" name="id" placeholder="Ingresa el ID de tu denuncia" required
        class="w-full border border-gray-300 rounded-lg px-4 py-2 placeholder:text-gray-500 placeholder:font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" />
      <button type="submit"
        class="w-full bg-[#685f2f] hover:bg-[#a08e43] text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
        Consultar
      </button>
    </form>
  </div>

<?php
  exit;
endif;

// 1. Buscar la denuncia
$sql = "SELECT * FROM denuncias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0):
?>

  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-red-300 text-center text-red-700 space-y-4">
    <h2 class="text-xl font-bold">❌ Denuncia no encontrada</h2>
    <p>No se encontró ninguna denuncia con ese ID.</p>
    <a href="ver_estado.php"
      class="inline-block bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
      Intentar de nuevo
    </a>
  </div>

<?php
  exit;
endif;

$denuncia = $resultado->fetch_assoc();
?>

<!-- Detalles de la denuncia -->
<div class="w-full max-w-2xl space-y-6">

  <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-300">
    <h2 class="text-xl font-bold mb-4 text-[#685f2f]">📋 Detalles de tu denuncia #<?= htmlspecialchars($denuncia['id']) ?></h2>
    <p><strong>Nombre:</strong> <?= htmlspecialchars($denuncia['nombre']) ?></p>
    <p><strong>Correo:</strong> <?= htmlspecialchars($denuncia['correo']) ?></p>
    <p><strong>Estado:</strong> <span class="capitalize"><?= htmlspecialchars($denuncia['estado']) ?></span></p>
    <p class="mt-4"><strong>Mensaje:</strong></p>
    <div class="bg-gray-50 border border-gray-300 p-4 rounded-lg mt-2 whitespace-pre-line">
      <?= htmlspecialchars($denuncia['mensaje']) ?>
    </div>

    <?php if (!empty($denuncia['firma'])): ?>
      <p class="mt-4"><strong>Firma:</strong></p>
      <img src="<?= htmlspecialchars($denuncia['firma']) ?>" alt="Firma del colaborador" class="w-60 border mt-2 rounded shadow">
    <?php endif; ?>
  </div>

  <!-- Archivos -->
  <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-300">
    <h2 class="text-xl font-bold mb-4 text-[#942934]">📎 Archivos adjuntos</h2>

    <?php
    $sqlArchivos = "SELECT * FROM archivos WHERE id_denuncia = ?";
    $stmtArchivos = $conn->prepare($sqlArchivos);
    $stmtArchivos->bind_param("i", $denuncia['id']);
    $stmtArchivos->execute();
    $archivos = $stmtArchivos->get_result();

    $hayArchivos = false;
    while ($archivo = $archivos->fetch_assoc()):
      $hayArchivos = true;
      $ruta = htmlspecialchars($archivo['ruta_archivo']);
    ?>

      <?php if ($archivo['tipo'] === 'foto'): ?>
        <img src="<?= $ruta ?>" alt="Foto" class="w-full max-w-sm mb-4 border rounded-lg">
      <?php elseif ($archivo['tipo'] === 'audio'): ?>
        <audio controls class="w-full mb-4">
          <source src="<?= $ruta ?>" type="audio/mpeg">
          Tu navegador no soporta la reproducción de audio.
        </audio>
      <?php endif; ?>

    <?php endwhile; ?>

    <?php if (!$hayArchivos): ?>
      <p class="text-gray-600">No se subieron archivos.</p>
    <?php endif; ?>
  </div>

  <!-- Respuestas del denunciante -->
  <div class="bg-white p-6 rounded-2xl shadow border border-gray-300">
    <h2 class="text-lg font-bold mb-4 text-[#942934]">✍️ Tus respuestas</h2>
    <?php
    $sqlRespuestas = "SELECT * FROM respuestas_denunciante WHERE id_denuncia = ? ORDER BY fecha ASC";
    $stmtResp = $conn->prepare($sqlRespuestas);
    $stmtResp->bind_param("i", $denuncia['id']);
    $stmtResp->execute();
    $resps = $stmtResp->get_result();

    if ($resps->num_rows > 0):
      while ($r = $resps->fetch_assoc()):
    ?>
        <div class="mb-4 bg-gray-50 border border-gray-300 rounded p-4">
          <?= nl2br(htmlspecialchars($r['mensaje'])) ?>
          <p class="text-sm text-gray-500 mt-2">📅 <?= $r['fecha'] ?></p>
        </div>
    <?php
      endwhile;
    else:
      echo "<p class='text-gray-600'>Aún no has enviado respuestas adicionales.</p>";
    endif;
    ?>
  </div>

  <!-- Formulario de respuesta (solo si en proceso) -->
  <?php if ($denuncia['estado'] === 'en_proceso'): ?>
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-300">
      <h2 class="text-lg font-bold mb-4 text-[#685f2f]">📨 Enviar una nueva respuesta</h2>
      <form action="responder_denunciante.php" method="POST" class="space-y-4">
        <input type="hidden" name="id_denuncia" value="<?= $denuncia['id'] ?>">
        <textarea name="mensaje" rows="5" required
          class="w-full border border-gray-300 rounded-lg p-3 placeholder:text-gray-500 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500"
          placeholder="Escribe tu respuesta..."></textarea>
        <button type="submit"
          class="bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
          Enviar respuesta
        </button>
      </form>
    </div>
  <?php endif; ?>

</div>

</body>
</html>
