<?php
session_start();

// Configuración de base de datos
$servername = "localhost";
$username_db = "root";
$password_db = "Alex";
$dbname = "scale_database";

// Reutilizamos tu archivo de envío de correo (¡Es universal!)
require 'enviar_correo.php'; 

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // --- NOTA: Asegúrate que la tabla se llame 'admins' o cámbialo aquí ---
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $codigo = rand(100000, 999999);

        // Guardamos en sesión variables DIFERENTES a las del usuario normal para no mezclar
        $_SESSION['admin_recovery_code'] = $codigo;
        $_SESSION['admin_recovery_username'] = $username;

        // Enviamos el correo
        if (enviarCorreoRecuperacion($email, $codigo)) {
            header("Location: cambiar_contraseña_admin.php");
            exit();
        } else {
            $_SESSION['error_admin'] = "Error al enviar el correo. Verifica tu configuración SMTP.";
            header("Location: recuperar_contraseña_admin.php");
            exit();
        }

    } else {
        $_SESSION['error_admin'] = "Credenciales de administrador incorrectas.";
        header("Location: recuperar_contraseña_admin.php");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>