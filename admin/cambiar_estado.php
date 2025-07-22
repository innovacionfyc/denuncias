<?php
session_start();
    if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once "../db/conexion.php";

if (isset($_POST['id']) && isset($_POST['nuevo_estado'])) {
    $id = $_POST['id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    $sql = "UPDATE denuncias SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?mensaje=ok");
    } else {
        echo "❌ Error al actualizar el estado.";
    }
} else {
    echo "⚠️ Faltan datos para cambiar el estado.";
}
?>
