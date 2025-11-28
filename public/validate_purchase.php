<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../src/WeightValidator.php';

// ... (send_json_response y las primeras líneas son iguales) ...
function send_json_response($data) {
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$scale_id = $input['scale_id'] ?? '';
$paid_price = $input['price'] ?? null;
if (empty($scale_id) || !is_numeric($paid_price)) {
    send_json_response(['success' => false, 'error' => 'ID y costo son requeridos.']);
}

try {
    $validator = new WeightValidator();
    $conn = $validator->conn; 

    // MODIFICADO: Ahora también seleccionamos 'expected_weight'
    $query = "SELECT product_name, price, expected_weight FROM weight_standards WHERE scale_id = :scale_id AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':scale_id', $scale_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $correct_price = floatval($product['price']);
        $expected_weight = floatval($product['expected_weight']); // Obtenemos el peso

        if (abs(floatval($paid_price) - $correct_price) < 0.01) {
            $user_id_to_log = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            // MODIFICADO: Pasamos el peso esperado a la función de guardado
            $validator->logMeasurement($scale_id, 0, $expected_weight, 1, $correct_price, $user_id_to_log);

            send_json_response(['success' => true, 'status' => 'APROBADO', 'message' => '¡Compra registrada!']);
        } else {
            send_json_response(['success' => false, 'status' => 'RECHAZADO', 'message' => 'El costo no coincide.']);
        }
    } else {
        send_json_response(['success' => false, 'error' => 'Producto no encontrado.']);
    }
} catch (PDOException $e) {
    send_json_response(['success' => false, 'error' => 'Error de conexión.']);
}
?>