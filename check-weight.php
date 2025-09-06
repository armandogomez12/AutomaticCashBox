<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../classes/WeightValidator.php';

// Función para enviar respuesta JSON
function sendResponse($data) {
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Verificar método de petición
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Leer datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse([
            'success' => false,
            'error' => 'Datos JSON inválidos'
        ]);
    }
    
    $scale_id = isset($input['scale_id']) ? trim($input['scale_id']) : '';
    $measured_weight = isset($input['weight']) ? floatval($input['weight']) : 0;
    
} elseif ($method === 'GET') {
    // Leer datos de parámetros GET
    $scale_id = isset($_GET['scale_id']) ? trim($_GET['scale_id']) : '';
    $measured_weight = isset($_GET['weight']) ? floatval($_GET['weight']) : 0;
    
} else {
    sendResponse([
        'success' => false,
        'error' => 'Método no permitido. Use GET o POST'
    ]);
}

// Validar parámetros
if (empty($scale_id)) {
    sendResponse([
        'success' => false,
        'error' => 'scale_id es requerido'
    ]);
}

if ($measured_weight <= 0) {
    sendResponse([
        'success' => false,
        'error' => 'weight debe ser mayor que 0'
    ]);
}

// Procesar la validación
$validator = new WeightValidator();
$result = $validator->validateWeight($scale_id, $measured_weight);

// Registrar la medición si fue exitosa
if ($result['success']) {
    $validator->logMeasurement($scale_id, $measured_weight, $result['is_valid']);
}

sendResponse($result);
?>
