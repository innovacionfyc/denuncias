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

    $dirAudiosFS  = __DIR__ . "/uploads/audios/"; // ruta física
    $dirAudiosURL = "uploads/audios/";            // ruta pública (se guarda en BD)

    if (!is_dir($dirAudiosFS)) {
        @mkdir($dirAudiosFS, 0775, true);
    }

    foreach ($_FILES['audios']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['audios']['error'][$key] !== UPLOAD_ERR_OK) {
            error_log("AUDIO error index $key: " . $_FILES['audios']['error'][$key]);
            continue;
        }

        $nombre_original = basename($_FILES['audios']['name'][$key]);

        // extensión segura
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        // si viene sin extensión, intenta deducir por type (fallback)
        if (!$ext) {
            $type = isset($_FILES['audios']['type'][$key]) ? $_FILES['audios']['type'][$key] : '';
            $map = [
                'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3', 'audio/mpeg3' => 'mp3', 'audio/x-mpeg-3' => 'mp3',
                'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'audio/aac' => 'aac',
                'audio/ogg' => 'ogg', 'audio/webm' => 'webm',
                'audio/wav' => 'wav', 'audio/x-wav' => 'wav'
            ];
            $ext = $map[$type] ?? 'mp3';
        }
        $ext = '.' . preg_replace('/[^a-z0-9]/', '', $ext);

        // nombre seguro y único (quita caracteres raros del original por si lo usas en logs)
        $filename = 'audio_' . $id_denuncia . '_' . uniqid() . $ext;

        $destFS  = $dirAudiosFS . $filename;
        $destURL = $dirAudiosURL . $filename;

        if (move_uploaded_file($tmp_name, $destFS)) {
            $sqlAudio = "INSERT INTO archivos (id_denuncia, tipo, ruta_archivo) VALUES (?, 'audio', ?)";
            $stmtAudio = $conn->prepare($sqlAudio);
            $stmtAudio->bind_param("is", $id_denuncia, $destURL);
            $stmtAudio->execute();
        } else {
            error_log("AUDIO no se pudo mover: tmp=$tmp_name -> $destFS");
        }
    }
}

// 5. Enviar correo
$correoDenuncia = new CorreoDenuncia();
$correoDenuncia->sendConfirmacion($nombre, $correo, $id_denuncia);

// 6. Redirigir
header("Location: denuncia_enviada.php?id=" . $id_denuncia);
exit;
?>
