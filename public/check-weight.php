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
if ($result['success']) {
    $database = new Database();
    $conn = $database->getConnection();

    try {
        // Buscar la validación pendiente más reciente para este scale_id
        $query = "SELECT id FROM validation_pending 
                  WHERE scale_id = :scale_id AND status = 'PENDING' 
                  ORDER BY created_at DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':scale_id', $scale_id);
        $stmt->execute();
        $pending = $stmt->fetch(PDO::FETCH_ASSOC);

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

            // Si es válido, registrar en measurement_logs
            if ($result['is_valid']) {
                $validator->logMeasurement($scale_id, $measured_weight, $result['is_valid'], $result['price']);
            }
        }
    } catch (PDOException $e) {
        // Log del error pero seguir respondiendo
        error_log('Error actualizando validación: ' . $e->getMessage());
    }
}

sendResponse($result);
?>