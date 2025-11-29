<?php
session_start();

// Seguridad: Verificar que venimos del proceso de admin
if (!isset($_SESSION['admin_recovery_code']) || !isset($_SESSION['admin_recovery_username'])) {
    header("Location: recuperar_contraseña_admin.php");
    exit();
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_code = $_POST['codigo'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($user_code == $_SESSION['admin_recovery_code']) {
        if ($new_pass === $confirm_pass) {
            
            $conn = new mysqli("localhost", "root", "Alex", "scale_database");
            
            $new_password_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $username_to_update = $_SESSION['admin_recovery_username'];

            // Actualizamos en la tabla ADMINS
            $stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_password_hash, $username_to_update);

            if ($stmt->execute()) {
                session_destroy(); 
                $message = "¡Acceso de administrador restaurado! Redirigiendo...";
                $message_type = "success";
                header("refresh:2;url=login_admin.php");
            } else {
                $message = "Error crítico en base de datos.";
                $message_type = "error";
            }
            $stmt->close();
            $conn->close();

        } else {
            $message = "Las contraseñas no coinciden.";
            $message_type = "error";
        }
    } else {
        $message = "Código de seguridad inválido.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restaurar Credenciales Admin</title>
    <style>
        :root { --primary-color: #6f42c1; }
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
        <h1>Validación de Seguridad</h1>
        <p>Introduce el código enviado al correo del administrador.</p>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($message_type !== "success"): ?>
        <form action="" method="POST">
            <input type="number" name="codigo" placeholder="Código de 6 dígitos" required>
            <input type="password" name="new_password" placeholder="Nueva Contraseña Admin" required>
            <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
            <button type="submit">Restaurar Acceso</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>