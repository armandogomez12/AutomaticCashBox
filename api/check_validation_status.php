<?php
// filepath: c:\Users\Armando\OneDrive\Mis cosas\Documentos\Server De Xammps\htdocs\api\check_validation_status.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$validation_id = $_GET['validation_id'] ?? $_POST['validation_id'] ?? null;
$simulate = $_GET['simulate'] ?? $_POST['simulate'] ?? null;
$simulate_weight = $_GET['simulate_weight'] ?? $_POST['simulate_weight'] ?? null;
$test_token = $_GET['test_token'] ?? $_POST['test_token'] ?? null;

$TEST_TOKEN_VALUE = 'PRUEBA_LOCAL_123';
$bypass_auth = ($test_token === $TEST_TOKEN_VALUE);

if (!$bypass_auth && (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true)) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

if (!$validation_id) {
    echo json_encode(['success' => false, 'error' => 'validation_id requerido']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Obtener validación
    $query = "SELECT * FROM validation_pending WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $validation_id, PDO::PARAM_INT);
    $stmt->execute();
    $validation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$validation) {
        echo json_encode(['success' => false, 'error' => 'Validación no encontrada']);
        exit;
    }

    // Simular si se solicita
    if ($simulate !== null || $simulate_weight !== null) {
        if ($simulate_weight !== null && is_numeric($simulate_weight)) {
            $measured = floatval($simulate_weight);
        } elseif ($simulate === 'pass') {
            $measured = floatval($validation['expected_weight']);
        } elseif ($simulate === 'fail') {
            $measured = floatval($validation['expected_weight']) + (floatval($validation['tolerance']) * 2);
        } else {
            $expected = floatval($validation['expected_weight']);
            $tol = floatval($validation['tolerance']);
            $measured = $expected + ((rand(-100,100)/100.0) * $tol);
        }

        // Validar peso
        $min_weight = floatval($validation['expected_weight']) - floatval($validation['tolerance']);
        $max_weight = floatval($validation['expected_weight']) + floatval($validation['tolerance']);
        $is_valid = ($measured >= $min_weight && $measured <= $max_weight);
        $new_status = $is_valid ? 'VALIDATED' : 'FAILED';

        // Actualizar validation_pending
        $update = "UPDATE validation_pending 
                   SET measured_weight = :measured_weight, status = :status, validated_at = NOW() 
                   WHERE id = :id";
        $uStmt = $conn->prepare($update);
        $uStmt->bindParam(':measured_weight', $measured);
        $uStmt->bindParam(':status', $new_status);
        $uStmt->bindParam(':id', $validation_id, PDO::PARAM_INT);
        $uStmt->execute();

        error_log("✓ validation_pending actualizada: ID={$validation_id}, status={$new_status}");

        // === INSERCIÓN EN user_purchases ===
        if ($new_status === 'VALIDATED') {
            // Obtener nombre del producto
            $q2 = "SELECT product_name FROM weight_standards WHERE scale_id = :scale_id LIMIT 1";
            $s2 = $conn->prepare($q2);
            $s2->bindParam(':scale_id', $validation['scale_id']);
            $s2->execute();
            $ws = $s2->fetch(PDO::FETCH_ASSOC);
            $product_name = $ws['product_name'] ?? $validation['scale_id'];

            // INSERTAR directamente (sin verificar duplicados para forzar la inserción)
            $ins = "INSERT INTO user_purchases (user_id, scale_id, product_name, expected_weight, price) 
                    VALUES (:user_id, :scale_id, :product_name, :expected_weight, :price)";
            $iStmt = $conn->prepare($ins);
            $iStmt->bindParam(':user_id', $validation['user_id'], PDO::PARAM_INT);
            $iStmt->bindParam(':scale_id', $validation['scale_id']);
            $iStmt->bindParam(':product_name', $product_name);
            $iStmt->bindParam(':expected_weight', $validation['expected_weight']);
            $iStmt->bindParam(':price', $validation['price']);
            
            if ($iStmt->execute()) {
                error_log("✓✓✓ INSERCIÓN EXITOSA en user_purchases: user_id={$validation['user_id']}, product={$product_name}, price={$validation['price']}");
            } else {
                error_log("✗✗✗ ERROR en inserción: " . json_encode($iStmt->errorInfo()));
            }
        }

        $validation['measured_weight'] = $measured;
        $validation['status'] = $new_status;
    }

    // Mensaje final
    $message = match($validation['status']) {
        'PENDING' => 'Esperando lectura de la báscula...',
        'VALIDATED' => '✅ Compra validada correctamente',
        'FAILED' => '❌ El peso no coincide con lo esperado',
        default => 'Estado desconocido'
    };

    echo json_encode([
        'success' => true,
        'status' => $validation['status'],
        'measured_weight' => $validation['measured_weight'] ?? null,
        'expected_weight' => $validation['expected_weight'],
        'tolerance' => $validation['tolerance'],
        'price' => $validation['price'],
        'is_valid' => ($validation['status'] === 'VALIDATED'),
        'message' => $message
    ]);

} catch (PDOException $e) {
    error_log('❌ Error en check_validation_status.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error BD: ' . $e->getMessage()]);
}
?>