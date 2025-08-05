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
                  <div style='font-family: Arial, sans-serif; background-color: #f8f9fb; padding: 20px;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden;'>

                      <div style='background-color: #942934; color: white; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>Confirmación de Comunicación</h2>
                      </div>

                      <div style='padding: 30px; color: #333333;'>
                        <p>Hola <strong>$nombre</strong>,</p>

                        <p>Hemos recibido tu mensaje con el número de radicado <strong style='color: #d32f57;'>#$id_denuncia</strong>.</p>

                        <p>Desde nuestro equipo estaremos revisando cuidadosamente tu comunicación y te notificaremos cuando tengamos novedades.</p>

                        <p>Gracias por tu confianza en <strong>F&C Consultores</strong>.</p>

                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;'>

                        <p style='font-size: 12px; color: #888888;'>Este es un mensaje automático. Por favor, no respondas a este correo.</p>
                      </div>

                      <div style='background-color: #f1f1f1; text-align: center; padding: 10px; font-size: 12px; color: #888888;'>
                        F&C Consultores © " . date('Y') . "
                      </div>

                    </div>
                  </div>
                ";
                $mail->AltBody = "Hemos recibido tu mensaje #$id_denuncia. Te avisaremos cuando cambie el estado.";
            }

            $mail->MsgHTML($mensaje);

            return $mail->send(); // ✅ Importante: devuelve true/false
        } catch (Exception $e) {
            error_log("No se pudo enviar el correo: {$mail->ErrorInfo}");
            return false; // ❌ Si falla
        }
    }
}
