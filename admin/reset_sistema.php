<?php
// admin/reset_sistema.php
session_start();
require_once __DIR__ . '/_auth.php';
require_admin();
require_once __DIR__ . '/../db/conexion.php';

// Carpeta donde guardas los uploads (ajusta si usas otra ruta)
$uploadDir = __DIR__ . '/../uploads';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === 'SI') {

    $conn->begin_transaction();
    try {
        // 1. Traer y eliminar archivos del disco
        $res = $conn->query("SELECT ruta FROM archivos");
        while ($row = $res->fetch_assoc()) {
            $path = __DIR__ . '/../' . ltrim($row['ruta'], '/');
            if (is_file($path)) {
                @unlink($path);
            }
        }

        // 2. Vaciar tablas relacionadas
        $conn->query("DELETE FROM respuestas");
        $conn->query("DELETE FROM archivos");
        $conn->query("DELETE FROM denuncias");
        $conn->query("DELETE FROM codigos_2fa");

        $conn->commit();

        // 3. (Opcional) Borrar físicamente toda la carpeta uploads
        if (is_dir($uploadDir)) {
            $it = new RecursiveDirectoryIterator($uploadDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    @rmdir($file->getRealPath());
                } else {
                    @unlink($file->getRealPath());
                }
            }
            // Mantener la carpeta base vacía
            @mkdir($uploadDir, 0777, true);
        }

        $msg = "✅ Sistema reseteado correctamente. Denuncias, respuestas, archivos y códigos 2FA eliminados.";
    } catch (Throwable $e) {
        $conn->rollback();
        $msg = "❌ Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resetear sistema</title>
    <link href="../assets/css/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <div class="bg-white p-8 rounded-2xl shadow-2xl border border-gray-300 w-full max-w-lg space-y-6">
        <h1 class="text-2xl font-bold text-[#942934]">⚠️ Resetear sistema</h1>
        <p class="text-gray-700">
            Esta acción eliminará <b>todas las denuncias, respuestas, archivos subidos (fotos, audios) y códigos
                2FA</b>.
            Los <b>usuarios</b> permanecerán intactos.
        </p>

        <?php if (!empty($msg)): ?>
            <div
                class="p-4 rounded-xl <?= str_contains($msg, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="hidden" name="confirmar" value="SI">
            <button type="submit"
                class="w-full bg-[#d32f57] hover:bg-[#942934] text-white font-semibold py-3 rounded-xl transition-all duration-300">
                🗑️ Sí, vaciar todo
            </button>
        </form>

        <a href="dashboard.php" class="block text-center text-[#685f2f] font-semibold hover:underline">⬅️ Volver al
            dashboard</a>
    </div>
</body>

</html>