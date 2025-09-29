<?php
// admin/reset_sistema.php
// --- Protección inline (sin _auth.php) ---
session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../db/conexion.php';

// Carpeta donde guardas los uploads (ajústala si usas otra)
$uploadDir = __DIR__ . '/../uploads';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirmar'] ?? '') === 'SI') {

    // Habilita/ajusta si necesitas ver errores durante pruebas:
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    $conn->begin_transaction();

    try {
        // 1) Borrar archivos físicos listados en BD (si existe tabla 'archivos' y columna 'ruta')
        if ($res = $conn->query("SELECT ruta FROM archivos")) {
            while ($row = $res->fetch_assoc()) {
                // Ruta absoluta segura
                $path = __DIR__ . '/../' . ltrim((string) $row['ruta'], '/');
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $res->free();
        }

        // 2) Vaciar tablas (ajusta nombres si difieren en tu esquema)
        //    Orden recomendado: tablas hijas -> tabla padre
        if (!$conn->query("DELETE FROM respuestas")) {
            throw new Exception($conn->error);
        }
        if (!$conn->query("DELETE FROM archivos")) {
            throw new Exception($conn->error);
        }
        if (!$conn->query("DELETE FROM denuncias")) {
            throw new Exception($conn->error);
        }
        // Opcional: limpiar códigos 2FA
        if ($conn->query("SHOW TABLES LIKE 'codigos_2fa'")->num_rows > 0) {
            if (!$conn->query("DELETE FROM codigos_2fa")) {
                throw new Exception($conn->error);
            }
        }

        $conn->commit();

        // 3) Borrar físicamente TODO el contenido de /uploads (y dejar la carpeta vacía)
        if (is_dir($uploadDir)) {
            $it = new \RecursiveDirectoryIterator($uploadDir, \FilesystemIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $p = $file->getRealPath();
                if ($file->isDir()) {
                    @rmdir($p);
                } else {
                    @unlink($p);
                }
            }
            // Asegurar que la carpeta base exista de nuevo
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
        }

        $msg = "✅ Sistema reseteado correctamente. Denuncias, respuestas, archivos (físicos) y códigos 2FA eliminados. Usuarios intactos.";
    } catch (Throwable $e) {
        $conn->rollback();
        $msg = "❌ Error durante el reset: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Resetear sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../assets/css/output.css" rel="stylesheet">
</head>

<body class="bg-[#f8f9fb] min-h-screen flex items-center justify-center p-6">
    <div class="bg-white border border-gray-300 rounded-2xl shadow-2xl max-w-lg w-full p-8 space-y-6">
        <h1 class="text-2xl font-bold text-[#942934]">⚠️ Resetear sistema</h1>
        <p class="text-gray-700">
            Esta acción eliminará <b>todas</b> las <b>denuncias</b>, <b>respuestas</b>, <b>archivos subidos
                (fotos/audios)</b> y <b>códigos 2FA</b>.<br>
            La tabla de <b>usuarios</b> <span class="font-semibold">no se toca</span>.
        </p>

        <?php if (!empty($msg)): ?>
            <div
                class="p-4 rounded-xl <?= (strpos($msg, '✅') !== false) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-3">
            <input type="hidden" name="confirmar" value="SI">
            <button type="submit"
                class="w-full bg-[#d32f57] hover:bg-[#942934] text-white font-semibold py-3 rounded-xl transition-all duration-300">
                🗑️ Sí, vaciar todo
            </button>
        </form>

        <a href="dashboard.php" class="block text-center text-[#685f2f] font-semibold hover:underline">
            ⬅️ Volver al dashboard
        </a>
    </div>
</body>

</html>