<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// --- SEGURIDAD ---
// Solo un usuario logueado puede borrar su propio historial
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado.']);
    exit;
}

// Asegurarnos de que la petición sea por el método correcto (POST para acciones destructivas)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

try {
    // Consulta para borrar todos los registros del usuario actual
    $query = "DELETE FROM measurement_logs WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Historial de compras limpiado.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo limpiar el historial.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos.']);
}
?>