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

// 1. Obtener denuncia
$sql = "SELECT * FROM denuncias WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows != 1) {
    die("❌ Denuncia no encontrada.");
}

$denuncia = $resultado->fetch_assoc();

// 2. Obtener archivos
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
    <title>Detalle de Denuncia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">📄 Denuncia #<?php echo $denuncia['id']; ?></h1>

        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($denuncia['nombre']); ?></p>
        <p><strong>Cédula:</strong> <?php echo htmlspecialchars($denuncia['cedula']); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($denuncia['correo']); ?></p>
        <p><strong>Estado:</strong> <?php echo ucfirst($denuncia['estado']); ?></p>
        <p><strong>Fecha:</strong> <?php echo $denuncia['fecha_creacion']; ?></p>

        <div class="mt-4">
            <h2 class="text-lg font-semibold mb-2">📝 Mensaje:</h2>
            <div class="border p-4 bg-gray-50 rounded"><?php echo nl2br(htmlspecialchars($denuncia['mensaje'])); ?></div>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">📎 Archivos:</h2>
            <div class="grid grid-cols-2 gap-4">
                <?php while ($archivo = $archivos->fetch_assoc()): ?>
                    <?php if ($archivo['tipo'] === 'foto'): ?>
                        <img src="../<?php echo $archivo['ruta_archivo']; ?>" class="w-full h-auto rounded border" alt="Imagen">
                    <?php elseif ($archivo['tipo'] === 'audio'): ?>
                        <audio controls class="w-full">
                            <source src="../<?php echo $archivo['ruta_archivo']; ?>" type="audio/mpeg">
                            Tu navegador no soporta audio.
                        </audio>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
        </div>

        <hr class="my-6">

        <h2 class="text-xl font-semibold mb-2">✉️ Responder al denunciante</h2>

        <form action="responder_correo.php" method="POST" class="space-y-4">
            <input type="hidden" name="correo" value="<?= htmlspecialchars($denuncia['correo']) ?>">
            <input type="hidden" name="nombre" value="<?= htmlspecialchars($denuncia['nombre']) ?>">
            <input type="hidden" name="id_denuncia" value="<?= $denuncia['id'] ?>">

            <textarea name="respuesta" rows="6" class="w-full border rounded p-2" placeholder="Escribe tu respuesta..."></textarea>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Enviar respuesta
            </button>
        </form>

        <div class="mt-6">
            <a href="dashboard.php" class="text-blue-600 hover:underline">← Volver al panel</a>
        </div>
    </div>
</body>
</html>
