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

// Verificar si se envío el mensaje correctamente
$mensajeEnviado = isset($_GET['enviado']) && $_GET['enviado'] === 'ok';

$sqlArchivos = "SELECT * FROM archivos WHERE id_denuncia = ?";
$stmt2 = $conn->prepare($sqlArchivos);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$archivos = $stmt2->get_result();

$sqlRespuestas = "SELECT * FROM respuestas WHERE id_denuncia = ? ORDER BY fecha_respuesta ASC";
$stmt3 = $conn->prepare($sqlRespuestas);
$stmt3->bind_param("i", $id);
$stmt3->execute();
$respuestas = $stmt3->get_result();

$sqlRespDenunciante = "SELECT * FROM respuestas_denunciante WHERE id_denuncia = ? ORDER BY fecha ASC";
$stmt4 = $conn->prepare($sqlRespDenunciante);
$stmt4->bind_param("i", $id);
$stmt4->execute();
$respsDenunciante = $stmt4->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Denuncia</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>
<body class="bg-[#f8f9fb] min-h-screen p-6 flex justify-center">
    <div class="w-full max-w-6xl bg-white p-8 rounded-2xl shadow-2xl border border-gray-300 space-y-6">

        <?php if ($mensajeEnviado): ?>
            <div class="bg-green-100 border border-green-300 text-green-800 p-3 rounded-xl text-center animate-pulse">
                ✅ Respuesta enviada exitosamente al denunciante.
            </div>
        <?php endif; ?>

        <h1 class="text-2xl font-bold text-[#685f2f]">📄 Denuncia #<?= htmlspecialchars($denuncia['id']) ?></h1>

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
                            <img src="../<?= $archivo['ruta_archivo'] ?>" alt="Evidencia" class="w-full h-48 object-cover rounded-lg">
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

        <div>
            <h2 class="text-lg font-semibold text-[#a08e43] mb-2">📬 Respuestas del comité:</h2>
            <?php if ($respuestas->num_rows > 0): ?>
                <?php while ($respuesta = $respuestas->fetch_assoc()): ?>
                    <div class="border border-gray-200 p-4 bg-green-50 rounded-xl mb-4 shadow">
                        <?= nl2br(htmlspecialchars($respuesta['mensaje'])) ?>
                        <p class="mt-2 text-sm text-gray-600">📅 <?= $respuesta['fecha_respuesta'] ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-600">Aún no se ha respondido esta denuncia.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-[#942934] mb-2">✍️ Respuestas del denunciante:</h2>
            <?php if ($respsDenunciante->num_rows > 0): ?>
                <?php while ($r = $respsDenunciante->fetch_assoc()): ?>
                    <div class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-4 shadow">
                        <?= nl2br(htmlspecialchars($r['mensaje'])) ?>
                        <p class="text-sm text-gray-500 mt-2">📅 <?= $r['fecha'] ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-600">El denunciante aún no ha enviado respuestas adicionales.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-[#685f2f] mb-2">✉️ Responder al denunciante:</h2>
            <form action="responder_correo.php" method="POST" class="space-y-4">
                <input type="hidden" name="correo" value="<?= htmlspecialchars($denuncia['correo']) ?>">
                <input type="hidden" name="nombre" value="<?= htmlspecialchars($denuncia['nombre']) ?>">
                <input type="hidden" name="id_denuncia" value="<?= $denuncia['id'] ?>">

                <textarea name="respuesta" rows="6" class="w-full border border-gray-300 rounded-xl p-3 placeholder:text-gray-500 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#d32f57] invalid:border-red-500" placeholder="Escribe tu respuesta..."></textarea>

                <button type="submit" class="bg-[#942934] hover:bg-[#d32f57] text-white font-semibold px-6 py-2 rounded-xl transition-all duration-300 hover:scale-[1.01] active:scale-[0.98]">
                    Enviar respuesta
                </button>
            </form>
        </div>

        <div class="text-center pt-6">
            <a href="dashboard.php" class="text-[#942934] hover:underline font-medium">← Volver al panel</a>
        </div>
    </div>
</body>
</html>
