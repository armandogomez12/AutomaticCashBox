<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <style>
        /* Puedes copiar y pegar el mismo CSS del login_admin.php y cambiar el color primario */
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
        .login-link { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crear una Cuenta</h1>
        <form action="../src/register_logic.php" method="POST">
            <label for="full_name">Nombre Completo:</label>
            <input type="text" id="full_name" name="full_name" required>
            <label for="username">Nombre de Usuario:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Registrarse</button>
        </form>
        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="login_user.php">Inicia sesión</a>
        </div>
    </div>
</body>
</html>