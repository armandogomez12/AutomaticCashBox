<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// --- Medida de Seguridad Opcional pero Recomendada ---
// Solo permite registrar nuevos admins si ya hay un admin logueado.
// Si quieres que cualquiera pueda crear un admin, puedes comentar o borrar este bloque.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Si no hay sesión, podrías redirigir al login o mostrar un error.
    // Por ahora, lo dejaremos pasar para que puedas crear tu primer admin fácilmente.
    // die("Acceso denegado. Debes ser un administrador para registrar a otros.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register_admin.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ../public/register_admin.php?error=Todos los campos son requeridos');
    exit;
}

// Hashear la contraseña por seguridad
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $password_hash);
    
    if ($stmt->execute()) {
        header('Location: ../public/register_admin.php?status=success');
        exit;
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        header('Location: ../public/register_admin.php?error=El nombre de usuario ya existe.');
    } else {
        header('Location: ../public/register_admin.php?error=Error en la base de datos.');
    }
    exit;
}