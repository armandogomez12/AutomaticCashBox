<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register_user.php');
    exit;
}

// 1. Capturar todos los datos, incluyendo el nuevo email
$full_name = $_POST['full_name'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? ''; // Nuevo campo
$password = $_POST['password'] ?? '';

// 2. Validar que no haya campos vacíos
if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
    // Podrías guardar un mensaje en $_SESSION['error'] y redirigir
    die("Todos los campos (incluyendo el correo) son requeridos.");
}

// 3. Hashear la contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$database = new Database();
$conn = $database->getConnection();

try {
    // 4. Actualizar la consulta SQL para incluir el email
    $query = "INSERT INTO users (full_name, username, email, password_hash) VALUES (:full_name, :username, :email, :password_hash)";
    $stmt = $conn->prepare($query);
    
    // 5. Vincular los parámetros
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email); // Nuevo vínculo
    $stmt->bindParam(':password_hash', $password_hash);
    
    if ($stmt->execute()) {
        header('Location: ../public/login_user.php?registration=success');
        exit;
    }
} catch (PDOException $e) {
    // El error 1062 ocurre cuando se viola una restricción UNIQUE (username o email duplicado)
    if ($e->errorInfo[1] == 1062) {
        // Mensaje más específico
        die("Error: El nombre de usuario o el correo electrónico ya están registrados. Intenta con otros datos.");
    } else {
        die("Error al registrar el usuario: " . $e->getMessage());
    }
}
?>
