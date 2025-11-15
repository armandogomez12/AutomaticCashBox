<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login_user.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = 'Usuario y contraseña son requeridos.';
    header('Location: ../public/login_user.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Buscamos en la tabla 'users'
    $query = "SELECT id, username, password_hash FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password_hash'])) {
            // ¡Login correcto! Guardamos los datos del usuario en la sesión
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id']; // ¡Muy importante para tu idea!
            $_SESSION['user_username'] = $user['username'];
            header('Location: ../public/user_dashboard.php'); // Redirigir al panel de usuario
            exit;
        }
    }

    $_SESSION['error_message'] = 'Credenciales incorrectas.';
    header('Location: ../public/login_user.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error en el servidor.';
    header('Location: ../public/login_user.php');
    exit;
}