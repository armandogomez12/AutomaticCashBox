<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Temporalmente abierto para pruebas
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// La ruta correcta para llegar a src desde public
require_once __DIR__ . '/../src/WeightValidator.php';

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

if ($result['success'] && $result['is_valid']) {
    // Le pasamos el precio que obtuvimos en la validación
    $validator->logMeasurement($scale_id, $measured_weight, $result['is_valid'], $result['price']);
}

sendResponse($result);
?>