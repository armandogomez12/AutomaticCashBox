<?php
session_start();

// Seguridad: Si no hay código generado, volver al inicio
if (!isset($_SESSION['recovery_code']) || !isset($_SESSION['recovery_username'])) {
    header("Location: recuperar_contraseña_user.php");
    exit();
}

$message = "";
$message_type = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_code = $_POST['codigo'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // 1. Validar el código
    if ($user_code == $_SESSION['recovery_code']) {
        
        // 2. Validar contraseñas
        if ($new_pass === $confirm_pass) {
            
            // Conexión para actualizar
            $conn = new mysqli("localhost", "root", "Alex", "scale_database");
            
            $new_password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $username_to_update = $_SESSION['recovery_username'];

            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_password_hash, $username_to_update);

            if ($stmt->execute()) {
                // Éxito: Limpiar sesión y mostrar éxito
                session_destroy(); 
                $message = "¡Contraseña actualizada! Redirigiendo al login...";
                $message_type = "success";
                // Redirección automática después de 2 segundos
                header("refresh:2;url=login_user.php");
            } else {
                $message = "Error en la base de datos.";
                $message_type = "error";
            }
            $stmt->close();
            $conn->close();

        } else {
            $message = "Las contraseñas no coinciden.";
            $message_type = "error";
        }
    } else {
        $message = "El código ingresado es incorrecto.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña</title>
    <style>
        :root { --primary-color: #007bff; }
        body { font-family: -apple-system, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { margin-bottom: 20px; font-size: 24px; color: #333; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Validación de Código</h1>
        <p>Hemos enviado un código a tu correo. Ingrésalo abajo.</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($message_type !== "success"): ?>
        <form action="" method="POST">
            <input type="number" name="codigo" placeholder="Código de 6 dígitos" required>
            <input type="password" name="new_password" placeholder="Nueva Contraseña" required>
            <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
            <button type="submit">Actualizar Contraseña</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>