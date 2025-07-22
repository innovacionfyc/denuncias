<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CorreoDenuncia {
    public function sendConfirmacion($nombre, $correo, $id_denuncia = null, $subject = "Respuesta del equipo", $mensajePersonalizado = null) {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
            $mail->isSMTP();
            $mail->Host = "smtp-relay.gmail.com";
            $mail->Port = 25;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = false;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->From = "certificados@fycconsultores.com";
            $mail->FromName = "F&C Consultores";
            $mail->Username = "it@fycconsultores.com";
            $mail->Password = "ecym cwbl dfkg maea";

            $mail->Subject = $subject;
            $mail->addAddress($correo, $nombre);
            $mail->CharSet = 'UTF-8';

            // Determinar el cuerpo del mensaje
            if ($mensajePersonalizado) {
                $mensaje = $mensajePersonalizado;
                $mail->AltBody = strip_tags($mensajePersonalizado);
            } else {
                $mensaje = "
                    <h2>Hola, $nombre</h2>
                    <p>Hemos recibido tu denuncia con el ID <strong>$id_denuncia</strong>.</p>
                    <p>La estamos evaluando y te notificaremos cuando cambie el estado.</p>
                    <p>Gracias por tu confianza.</p>
                ";
                $mail->AltBody = "Hemos recibido tu denuncia #$id_denuncia. Te avisaremos cuando cambie el estado.";
            }

            $mail->MsgHTML($mensaje);

            return $mail->send(); // ✅ Importante: devuelve true/false
        } catch (Exception $e) {
            error_log("No se pudo enviar el correo: {$mail->ErrorInfo}");
            return false; // ❌ Si falla
        }
    }
}
