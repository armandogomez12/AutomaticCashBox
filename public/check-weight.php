<?php
// filepath: c:\Users\Armando\OneDrive\Mis cosas\Documentos\Server De Xammps\htdocs\public\check-weight.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../src/WeightValidator.php';
require_once __DIR__ . '/../config/database.php';

function sendResponse($data) {
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$scale_id = isset($input['scale_id']) ? trim($input['scale_id']) : '';
$measured_weight = isset($input['weight']) ? floatval($input['weight']) : 0;

if (empty($scale_id) || $measured_weight <= 0) {
    sendResponse(['success' => false, 'error' => 'scale_id y weight son requeridos.']);
}

$validator = new WeightValidator();
$result = $validator->validateWeight($scale_id, $measured_weight);

// Si la validación fue exitosa, buscar si hay una validación pendiente
if ($pending) {
    // Actualizar el estado de validación pendiente
    $new_status = $result['is_valid'] ? 'VALIDATED' : 'FAILED';
    
    $updateQuery = "UPDATE validation_pending 
                   SET measured_weight = :measured_weight, status = :status, validated_at = NOW() 
                   WHERE id = :id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':measured_weight', $measured_weight);
    $updateStmt->bindParam(':status', $new_status);
    $updateStmt->bindParam(':id', $pending['id']);
    $updateStmt->execute();

    // --- INSERCIÓN: si es válido, crear registro de compra compatible con user_purchases.php ---
    if ($result['is_valid']) {
        $insertPurchase = "INSERT INTO purchases (validation_id, user_id, scale_id, price, measured_weight, status, created_at)
                           VALUES (:validation_id, :user_id, :scale_id, :price, :measured_weight, 'COMPLETED', NOW())";
        $insStmt = $conn->prepare($insertPurchase);
        $insStmt->bindParam(':validation_id', $pending['id']);
        $insStmt->bindParam(':user_id', $pending['user_id']);
        $insStmt->bindParam(':scale_id', $pending['scale_id']);
        // Si no guardaste price en validation_pending, usa $result['price'] o $pending['price']
        $priceToInsert = $pending['price'] ?? $result['price'] ?? 0;
        $insStmt->bindParam(':price', $priceToInsert);
        $insStmt->bindParam(':measured_weight', $measured_weight);
        $insStmt->execute();

        // (Opcional) registrar en measurement_logs también si existe esa tabla
        // ...
    }
}

sendResponse($result);
?>