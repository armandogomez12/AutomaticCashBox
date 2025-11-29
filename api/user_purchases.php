<?php
session_start();
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

try {
    // Obtenemos las últimas 20 compras del usuario desde la tabla NUEVA (user_purchases)
    $query = "SELECT * FROM user_purchases WHERE user_id = :uid ORDER BY timestamp DESC LIMIT 20";
    $stmt = $conn->prepare($query);
    $stmt->execute([':uid' => $userId]);
    
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'purchases' => $purchases]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al cargar historial']);
}
?>
