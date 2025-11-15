<?php
session_start(); // Siempre inicia la sesión al principio

// Incluir el archivo de la base de datos
require_once __DIR__ . '/../config/database.php';

// Verificar que los datos lleguen por POST para seguridad
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login_admin.php');
    exit;
}

// Obtener datos del formulario
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validar que los campos no estén vacíos
if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = 'Usuario y contraseña son requeridos.';
    header('Location: ../public/login_admin.php');
    exit;
}

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

try {
    // Buscar al administrador por su nombre de usuario
    $query = "SELECT username, password_hash FROM admins WHERE username = :username";
    $stmt = $conn->prepare($query);
  $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña con el hash de la BD
        if (password_verify($password, $admin['password_hash'])) {
            // ¡ÉXITO! La contraseña es correcta.
            // Establecemos las variables de sesión para "recordar" al admin.
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            
            // Redirigimos al panel de administración.
            header('Location: ../public/admin_panel.php');
            exit;
        }
    }

    // Si el usuario no existe o la contraseña fue incorrecta, preparamos un mensaje de error.
    $_SESSION['error_message'] = 'Credenciales incorrectas. Intente de nuevo.';
    header('Location: ../public/login_admin.php');
    exit;

} catch (PDOException $e) {
    // Si hay un error de base de datos, mostramos un error genérico.
    error_log("Error de autenticación: " . $e->getMessage()); // Guardamos el error real para nosotros
    $_SESSION['error_message'] = 'Ocurrió un error en el servidor.';
    header('Location: ../public/login_admin.php');
    exit;
}
?>