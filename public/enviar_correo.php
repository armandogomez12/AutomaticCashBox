<?php
// Cargar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

function enviarCorreoRecuperacion($destinatario, $codigo) {

    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURACIÓN DEL SERVIDOR SMTP DE GOOGLE ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cajadeautocobroo@gmail.com'; // <-- TU CORREO DE GMAIL
        $mail->Password   = 'gwud mvsy ytaa ktpe'; // <-- LA CONTRASEÑA DE APLICACIÓN QUE GENERASTE
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        // --------------------------------------------------

        // Remitente y Destinatario
        $mail->setFrom('cajadeautocobroo@gmail.com', 'Caja de autocobro'); 
        $mail->addAddress($destinatario);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Tu codigo de recuperacion de cuenta';
        $mail->Body    = 'Hola,<br><br>Has solicitado restablecer tu contraseña.<br><br>Tu código de recuperación es: <b>' . $codigo . '</b><br><br>Si no has sido tú, ignora este correo.<br><br>Saludos,<br>El equipo de Cajas de autocobro.';
        $mail->AltBody = 'Tu codigo de recuperacion es: ' . $codigo;

        $mail->send();
        return true; // El correo se envió con éxito

    } catch (Exception $e) {
        // En caso de error, puedes registrar el error en lugar de mostrarlo al usuario
        // error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false; // Hubo un error
    }
}
?>