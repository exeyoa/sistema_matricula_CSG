<?php
require_once __DIR__ . '/credenciales.php';
require_once __DIR__ . '/../libs/PHPMailer/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoRecuperacion(string $correoDestino, string $nombreDestino, string $link): bool
{
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = CORREO_SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = CORREO_REMITENTE;
        $mail->Password   = CORREO_APP_PASSWORD;
        $mail->Port       = CORREO_SMTP_PUERTO;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom(CORREO_REMITENTE, CORREO_REMITENTE_NOMBRE);
        $mail->addAddress($correoDestino, $nombreDestino);
        $mail->CharSet = 'UTF-8';

        $mail->Subject = 'Recuperacion de contrasena - Colegio Secundario de Guabito';
        $mail->Body    = "Hola {$nombreDestino},\n\n"
            . "Recibimos una solicitud para restablecer la contrasena de tu cuenta.\n"
            . "Para definir una nueva contrasena, abre el siguiente enlace (expira en 1 hora):\n\n"
            . $link . "\n\n"
            . "Si no solicitaste este cambio, ignora este correo.\n\n"
            . "-- Colegio Secundario de Guabito";

        return $mail->send();
    } catch (Exception $e) {
        error_log('Error enviando correo de recuperacion: ' . $e->getMessage());
        return false;
    }
}