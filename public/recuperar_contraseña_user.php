<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <style>
        :root { --primary-color: #007bff; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { margin-bottom: 20px; font-size: 24px; color: #333; }
        p.description { color: #666; font-size: 14px; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; transition: background 0.3s; }
        button:hover { background-color: #0056b3; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-link { margin-top: 20px; display: block; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Recuperar Contraseña</h1>
        <p class="description">Ingresa tu usuario y correo para recibir un código de verificación.</p>

        <?php 
        session_start();
        if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="send_email.php" method="POST">
            <input type="text" name="username" placeholder="Nombre de Usuario" required>
            <input type="email" name="email" placeholder="Correo Electrónico Registrado" required>
            <button type="submit">Enviar Código</button>
        </form>

        <a href="login_user.php" class="back-link">← Volver al Login</a>
    </div>
</body>
</html>