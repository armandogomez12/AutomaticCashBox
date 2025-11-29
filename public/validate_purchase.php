<?php
// filepath: c:\Users\Armando\OneDrive\Mis cosas\Documentos\Server De Xammps\htdocs\public\validate_purchase.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$scale_id = $_POST['scale_id'] ?? null;
$user_id = $_SESSION['user_id']; // ← DEBE SER DE LA SESIÓN

if (!$scale_id) {
    echo json_encode(['success' => false, 'error' => 'scale_id requerido']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Obtener estándar de peso
    $q = "SELECT expected_weight, tolerance, price FROM weight_standards WHERE scale_id = :scale_id LIMIT 1";
    $stmt = $conn->prepare($q);
    $stmt->bindParam(':scale_id', $scale_id);
    $stmt->execute();
    $standard = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$standard) {
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        exit;
    }

    // Insertar en validation_pending CON user_id de sesión
    $ins = "INSERT INTO validation_pending (user_id, scale_id, expected_weight, tolerance, price, status) 
            VALUES (:user_id, :scale_id, :expected_weight, :tolerance, :price, 'PENDING')";
    $iStmt = $conn->prepare($ins);
    $iStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $iStmt->bindParam(':scale_id', $scale_id);
    $iStmt->bindParam(':expected_weight', $standard['expected_weight']);
    $iStmt->bindParam(':tolerance', $standard['tolerance']);
    $iStmt->bindParam(':price', $standard['price']);
    $iStmt->execute();

    $validation_id = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'validation_id' => $validation_id,
        'expected_weight' => $standard['expected_weight'],
        'tolerance' => $standard['tolerance'],
        'price' => $standard['price'],
        'message' => 'Coloca el producto en la báscula...',
        'debug_user_id' => $user_id
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>