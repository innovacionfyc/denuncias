<?php
require_once "db/conexion.php";               // Conexión a la base de datos
require_once "correo/enviar_correo.php";       // Clase para enviar correos

// Activar errores (solo durante desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Recibir datos del formulario
/* $nombre   = $_POST['nombre'] ?? null;
$cedula   = $_POST['cedula'] ?? null;
$correo   = $_POST['correo'] ?? null;
$mensaje  = $_POST['mensaje'] ?? null; */

$nombre  = isset($_POST['nombre']) ? $_POST['nombre'] : null;
$cedula  = isset($_POST['cedula']) ? $_POST['cedula'] : null;
$correo  = isset($_POST['correo']) ? $_POST['correo'] : null;
$mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : null;

// Validar campos obligatorios
if (!$nombre || !$cedula || !$correo || !$mensaje) {
    die("❌ Todos los campos son obligatorios.");
}

// 2. Insertar en la tabla denuncias
$sql = "INSERT INTO denuncias (nombre, cedula, correo, mensaje) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nombre, $cedula, $correo, $mensaje);
$stmt->execute();

$id_denuncia = $stmt->insert_id; // ID generado por la BD

// 3. Manejar archivos de fotos
if (!empty($_FILES['fotos']['name'][0])) {
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        $nombre_archivo = basename($_FILES['fotos']['name'][$key]);
        $ruta_destino = "uploads/fotos/" . time() . "_" . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $ruta_destino)) {
            $sqlFoto = "INSERT INTO archivos (id_denuncia, tipo, ruta_archivo) VALUES (?, 'foto', ?)";
            $stmtFoto = $conn->prepare($sqlFoto);
            $stmtFoto->bind_param("is", $id_denuncia, $ruta_destino);
            $stmtFoto->execute();
        }
    }
}

// 4. Manejar archivos de audios (con verificación temporal)
if (!empty($_FILES['audios']['name'][0])) {
    foreach ($_FILES['audios']['tmp_name'] as $key => $tmp_name) {
        $nombre_archivo = $_FILES['audios']['name'][$key];
        $tmp_name = $_FILES['audios']['tmp_name'][$key];
        $tipo = $_FILES['audios']['type'][$key];
        $error = $_FILES['audios']['error'][$key];
        $size = $_FILES['audios']['size'][$key];

        // Validación completa
        if ($error === 0 && $size > 0 && is_uploaded_file($tmp_name)) {
            $nombre_limpio = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($nombre_archivo));
            $ruta_destino = "uploads/audios/" . time() . "_" . $nombre_limpio;

            if (move_uploaded_file($tmp_name, $ruta_destino)) {
                $sqlAudio = "INSERT INTO archivos (id_denuncia, tipo, ruta_archivo) VALUES (?, 'audio', ?)";
                $stmtAudio = $conn->prepare($sqlAudio);
                $stmtAudio->bind_param("is", $id_denuncia, $ruta_destino);
                $stmtAudio->execute();
            } else {
                echo "❌ No se pudo mover el archivo: $nombre_archivo<br>";
            }
        } else {
            echo "⚠️ Archivo inválido: $nombre_archivo<br>";
        }
    }
}

// 5. Enviar correo de confirmación
$correoDenuncia = new CorreoDenuncia();
$correoDenuncia->sendConfirmacion($nombre, $correo, $id_denuncia);

// 6. Redireccionar a pantalla de éxito
header("Location: denuncia_enviada.php");
exit;
?>
