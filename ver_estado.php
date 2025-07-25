<?php require_once "db/conexion.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultar denuncia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen p-4 flex items-center justify-center">

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

// Buscar la denuncia
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

</div>

</body>
</html>
