<?php
require_once "db/conexion.php";

// Activar errores durante desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Capturar datos
$id_denuncia = isset($_POST['id_denuncia']) ? intval($_POST['id_denuncia']) : 0;
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

// Validación
if ($id_denuncia <= 0 || empty($mensaje)) {
    die("❌ Datos inválidos.");
}

// 2. Insertar la respuesta en la tabla
$sql = "INSERT INTO respuestas_denunciante (id_denuncia, mensaje) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_denuncia, $mensaje);

if ($stmt->execute()) {
    // 3. Redirigir con mensaje de éxito
    header("Location: ver_estado.php?id=$id_denuncia&mensaje=✅ Tu respuesta fue enviada correctamente");
    exit;
} else {
    die("❌ Error al guardar la respuesta.");
}
?>
