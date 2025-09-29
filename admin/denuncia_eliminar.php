<?php
// admin/denuncia_eliminar.php
session_start();
require_once __DIR__ . '/_auth.php';
require_admin();
require_once __DIR__ . '/../db/conexion.php';

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    exit('ID inválido');
}

$conn->begin_transaction();

try {
    // 1) Traer rutas de archivos para borrarlos del disco
    $q = $conn->prepare("SELECT ruta FROM archivos WHERE denuncia_id=?");
    $q->bind_param("i", $id);
    $q->execute();
    $files = $q->get_result()->fetch_all(MYSQLI_ASSOC);

    // 2) Borrar relacionados
    $stmt = $conn->prepare("DELETE FROM archivos WHERE denuncia_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM respuestas WHERE denuncia_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // 3) Borrar la denuncia
    $stmt = $conn->prepare("DELETE FROM denuncias WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->commit();

    // 4) Borrar del disco (después del commit)
    foreach ($files as $f) {
        $path = __DIR__ . '/../' . ltrim($f['ruta'], '/');
        if (is_file($path))
            @unlink($path);
    }

    echo "OK";
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
