<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Administrador</title>
    <!-- Script de Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        :root { --primary-color: #6f42c1; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; color: #343a40; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { margin-bottom: 30px; font-weight: 600; }
        form label { display: block; text-align: left; font-weight: 500; margin-bottom: 8px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; font-weight: 500; transition: background-color 0.2s; }
        button:hover { background-color: #5a32a3; }
        .error-message { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px; }
        .link-container { margin-top: 20px; text-align: center; }
        .link-container a { color: var(--primary-color); text-decoration: none; }
        .link-container a:hover { text-decoration: underline; }
        
        /* Centrar Captcha */
        .g-recaptcha { display: flex; justify-content: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Acceso de Administrador</h1>
        <form action="../src/auth_admin.php" method="POST">
            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            
            <!-- CAPTCHA GOOGLE -->
            <div class="g-recaptcha" data-sitekey="6LfmixssAAAAAMg_pnFQpifT7wgrD3sledW4uAE0"></div>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <div class="link-container">
            <a href="register_admin.php">Registrar nuevo administrador</a>
        </div>
        
        <div class="link-container">
            <a href="recuperar_contraseña_admin.php">¿Olvidaste tu contraseña?</a>
        </div>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']); 
        }
        ?>
    </div>
</body>
</html>
