<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register_user.php');
    exit;
}

$full_name = $_POST['full_name'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($full_name) || empty($username) || empty($password)) {
    // Manejar error de campos vacíos (idealmente con mensajes de sesión)
    die("Todos los campos son requeridos.");
}

// Hashear la contraseña por seguridad
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "INSERT INTO users (full_name, username, password_hash) VALUES (:full_name, :username, :password_hash)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $password_hash);
    
    if ($stmt->execute()) {
        // Redirigir al login con un mensaje de éxito
        header('Location: ../public/login_user.php?registration=success');
        exit;
    }
} catch (PDOException $e) {
    // Error 1062 es para entradas duplicadas (username ya existe)
    if ($e->errorInfo[1] == 1062) {
        die("Error: El nombre de usuario ya existe. Por favor, elige otro.");
    } else {
        die("Error al registrar el usuario: " . $e->getMessage());
    }
}