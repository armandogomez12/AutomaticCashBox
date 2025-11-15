<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Nuevo Administrador</title>
    <style>
        :root { --primary-color: #6f42c1; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { margin-bottom: 30px; }
        form label { display: block; text-align: left; font-weight: 500; margin-bottom: 8px; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        button { width: 100%; padding: 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 18px; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .login-link { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrar Administrador</h1>
        
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="message success">¡Administrador registrado con éxito!</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="message error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="../src/register_admin_logic.php" method="POST">
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Crear Administrador</button>
        </form>
        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="login_admin.php">Inicia sesión</a>
        </div>
    </div>
</body>
</html>