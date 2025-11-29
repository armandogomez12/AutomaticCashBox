<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificación de sesión (Opcional)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register_admin.php');
    exit;
}

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    header('Location: ../public/register_admin.php?error=Todos los campos son requeridos');
    exit;
}

// Hashear password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// ESTADO INICIAL: 0 (Desactivado/En lista de espera)
$is_active = 0;

$database = new Database();
$conn = $database->getConnection();

try {
    // Insertamos incluyendo el email y el estado is_active
    $query = "INSERT INTO admins (username, email, password_hash, is_active) VALUES (:username, :email, :password_hash, :is_active)";
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':is_active', $is_active);
    
    if ($stmt->execute()) {
        // Mensaje especial avisando que requiere activación
        $msg = "Registro creado. La cuenta está EN ESPERA de activación por un Super Usuario.";
        header('Location: ../public/register_admin.php?status=success&msg=' . urlencode($msg));
        exit;
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        header('Location: ../public/register_admin.php?error=El usuario o correo ya existen.');
    } else {
        header('Location: ../public/register_admin.php?error=Error DB: ' . $e->getMessage());
    }
    exit;
}
?>
