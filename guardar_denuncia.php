<?php
require_once "db/conexion.php";
require_once "correo/enviar_correo.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Captura de datos
$nombre   = isset($_POST['nombre']) ? $_POST['nombre'] : null;
$cedula   = isset($_POST['cedula']) ? $_POST['cedula'] : null;
$correo   = isset($_POST['correo']) ? $_POST['correo'] : null;
$mensaje  = isset($_POST['mensaje']) ? $_POST['mensaje'] : null;
$proceso  = isset($_POST['proceso']) ? $_POST['proceso'] : null;
$cargo    = isset($_POST['cargo']) ? $_POST['cargo'] : null;
$firma    = isset($_POST['firma']) ? $_POST['firma'] : null;

// Validación simple
if (!$nombre || !$cedula || !$correo || !$mensaje || !$proceso || !$cargo || !$firma) {
    die("❌ Todos los campos son obligatorios.");
}

// 2. Insertar la denuncia
$sql = "INSERT INTO denuncias (nombre, cedula, correo, mensaje, proceso, cargo, firma) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $nombre, $cedula, $correo, $mensaje, $proceso, $cargo, $firma);
$stmt->execute();

$id_denuncia = $stmt->insert_id;

// 3. Guardar fotos
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

// 4. Guardar audios
if (!empty($_FILES['audios']['name'][0])) {
    foreach ($_FILES['audios']['tmp_name'] as $key => $tmp_name) {
        $nombre_archivo = basename($_FILES['audios']['name'][$key]);
        $ruta_destino = "uploads/audios/" . time() . "_" . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $ruta_destino)) {
            $sqlAudio = "INSERT INTO archivos (id_denuncia, tipo, ruta_archivo) VALUES (?, 'audio', ?)";
            $stmtAudio = $conn->prepare($sqlAudio);
            $stmtAudio->bind_param("is", $id_denuncia, $ruta_destino);
            $stmtAudio->execute();
        }
    }
}

// 5. Enviar correo
$correoDenuncia = new CorreoDenuncia();
$correoDenuncia->sendConfirmacion($nombre, $correo, $id_denuncia);

// 6. Redirigir
header("Location: denuncia_enviada.php");
exit;
?>
