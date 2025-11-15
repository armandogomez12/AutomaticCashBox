<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a la Tienda Automática</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .portal-container {
            background-color: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1em;
            color: #6c757d;
            margin-bottom: 40px;
        }
        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .portal-button {
            text-decoration: none;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-block;
        }
        .portal-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        .user-button {
            background-color: #007bff; /* Azul para usuarios */
        }
        .admin-button {
            background-color: #6f42c1; /* Morado para admin */
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <h1>Bienvenido</h1>
        <p>Por favor, selecciona tu tipo de acceso para continuar.</p>
        <div class="button-group">
            <a href="login_user.php" class="portal-button user-button">Soy Cliente</a>
            <a href="login_admin.php" class="portal-button admin-button">Soy Administrador</a>
        </div>
    </div>
</body>
</html>