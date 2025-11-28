<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'No has iniciado sesión.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

try {
    // --- CAMBIO AQUÍ ---
    // Añadimos "ml.expected_weight" a la consulta para obtener el peso guardado.
    $query = "SELECT ws.product_name, ml.price, ml.expected_weight, ml.timestamp
              FROM measurement_logs ml
              JOIN weight_standards ws ON ml.scale_id = ws.scale_id
              WHERE ml.user_id = :user_id
              ORDER BY ml.timestamp DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'purchases' => $purchases]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos.']);
}
?>