<?php
session_start();

// 1. Incluir archivo de conexión y tu archivo de envío de correo
// Asumo que tienes un archivo de conexión. Si no, ajusta las credenciales aquí.
$servername = "localhost";
$username_db = "root";
$password_db = "Alex";
$dbname = "scale_database";

require 'enviar_correo.php'; // Tu archivo con PHPMailer

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // 2. Verificar si el usuario y correo coinciden en la BD
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // 3. Generar código aleatorio de 6 dígitos
        $codigo = rand(100000, 999999);

        // 4. Guardar datos en sesión para verificar después
        $_SESSION['recovery_code'] = $codigo;
        $_SESSION['recovery_username'] = $username; // Guardamos el usuario para saber a quién cambiarle la pass

        // 5. Enviar el correo usando tu función
        if (enviarCorreoRecuperacion($email, $codigo)) {
            // Éxito: Redirigir a la pantalla de cambiar contraseña
            header("Location: cambiar_contraseña_user.php");
            exit();
        } else {
            $_SESSION['error'] = "Hubo un error al enviar el correo. Intenta más tarde.";
            header("Location: recuperar_contraseña_user.php");
            exit();
        }

    } else {
        $_SESSION['error'] = "Los datos no coinciden con nuestros registros.";
        header("Location: recuperar_contraseña_user.php");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>