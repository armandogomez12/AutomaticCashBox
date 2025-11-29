<?php
session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: user_dashboard.php'); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login de Usuario</title>
    <!-- Script de Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        :root { --primary-color: #007bff; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { margin-bottom: 30px; }
        form label { display: block; text-align: left; font-weight: 500; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; }
        .message { padding: 15px; border-radius: 8px; margin-top: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .register-link { margin-top: 20px; }
        
        /* Centrar el reCAPTCHA */
        .g-recaptcha { display: flex; justify-content: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Iniciar Sesión</h1>
        
        <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
            <div class="message success">¡Registro exitoso! Ya puedes iniciar sesión.</div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <form action="../src/auth_user.php" method="POST">
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <!-- AQUI VA EL CAPTCHA DE GOOGLE -->
            <!-- Reemplaza el data-sitekey con tu clave pública -->
            <div class="g-recaptcha" data-sitekey="6LfmixssAAAAAMg_pnFQpifT7wgrD3sledW4uAE0"></div>

            <button type="submit">Entrar</button>
        </form>

        <div class="register-link">
            ¿No tienes una cuenta? <a href="register_user.php">Regístrate</a>
        </div>

        <div class="register-link">
            ¿No recuerdas tu contraseña? <a href="recuperar_contraseña_user.php">Recuperar</a>
        </div>
    </div>
</body>
</html>
