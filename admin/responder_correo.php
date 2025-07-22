<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once "../correo/enviar_correo.php";
require_once "../db/conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST['correo'];
    $nombre = $_POST['nombre'];
    $id = $_POST['id_denuncia'];
    $respuesta = $_POST['respuesta'];

    if (empty($correo) || empty($respuesta)) {
        die("❌ Correo y mensaje son obligatorios.");
    }

    $correoDenuncia = new CorreoDenuncia();

    // asunto personalizado
    $asunto = "Respuesta a tu denuncia #$id";

    // cuerpo HTML
    $cuerpo = "
        <h2>Hola $nombre,</h2>
        <p>Hemos revisado tu denuncia con ID <strong>$id</strong> y queremos darte una respuesta:</p>
        <blockquote style='background:#f3f3f3;padding:15px;border-left:5px solid #ccc;'>"
        . nl2br(htmlspecialchars($respuesta)) .
        "</blockquote>
        <p>Gracias por confiar en nuestro equipo.</p>
    ";

    $correoEnviado = $correoDenuncia->sendConfirmacion($cuerpo, $correo, $asunto);

    if ($correoEnviado) {
        // ✅ Insertar respuesta en la base de datos
        $sql = "INSERT INTO respuestas (id_denuncia, mensaje, fecha_respuesta) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id, $respuesta);

        if ($stmt->execute()) {
            // Todo bien, redirigimos
            header("Location: ver_denuncia.php?id=$id&enviado=ok");
            exit;
        } else {
            echo "❌ Error al guardar en BD: " . $stmt->error;
            exit;
        }
    } else {
        echo "❌ El correo no se pudo enviar.";
        exit;
    }
} else {
    echo "⚠️ Acceso no válido.";
}
