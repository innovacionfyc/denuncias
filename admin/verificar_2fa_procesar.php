<?php
require_once "../db/conexion.php";
session_start();

if (!isset($_SESSION['pending_2fa'])) {
    header("Location: login.php");
    exit;
}

$uid = (int) $_SESSION['pending_2fa']['user_id'];
$codigo = $_POST['codigo'] ?? '';

$stmt = $conn->prepare("SELECT * FROM sesiones_2fa WHERE user_id=? AND used=0 AND expires_at>NOW() ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $uid);
$stmt->execute();
$token = $stmt->get_result()->fetch_assoc();

if (!$token || $token['codigo'] !== $codigo) {
    header("Location: verificar_2fa.php?e=1");
    exit;
}

// Marcar usado
$upd = $conn->prepare("UPDATE sesiones_2fa SET used=1 WHERE id=?");
$upd->bind_param("i", $token['id']);
$upd->execute();

// Cargar usuario y crear sesión real
$uStmt = $conn->prepare("SELECT id, usuario AS nombre, correo, rol FROM usuarios WHERE id=? AND activo=1");
$uStmt->bind_param("i", $uid);
$uStmt->execute();
$u = $uStmt->get_result()->fetch_assoc();

unset($_SESSION['pending_2fa']);
$_SESSION['usuario'] = $u;

// Ir al dashboard
header("Location: dashboard.php");
