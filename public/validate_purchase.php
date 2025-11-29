<?php
// filepath: c:\Users\Armando\OneDrive\Mis cosas\Documentos\Server De Xammps\htdocs\public\validate_purchase.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$user_id = $_SESSION['user_id'];
$scale_id = $_POST['scale_id'] ?? null;

if (!$scale_id) {
    echo json_encode(['success' => false, 'error' => 'scale_id requerido']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Obtener estándar de peso para ese scale_id
    $query = "SELECT expected_weight, tolerance, price FROM weight_standards 
              WHERE scale_id = :scale_id AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':scale_id', $scale_id);
    $stmt->execute();
    $standard = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$standard) {
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        exit;
    }

    // Crear validación pendiente
    $query = "INSERT INTO validation_pending (user_id, scale_id, expected_weight, tolerance, price, status) 
              VALUES (:user_id, :scale_id, :expected_weight, :tolerance, :price, 'PENDING')";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':scale_id', $scale_id);
    $stmt->bindParam(':expected_weight', $standard['expected_weight']);
    $stmt->bindParam(':tolerance', $standard['tolerance']);
    $stmt->bindParam(':price', $standard['price']);

    if ($stmt->execute()) {
        $validation_id = $conn->lastInsertId();
        echo json_encode([
            'success' => true,
            'validation_id' => $validation_id,
            'scale_id' => $scale_id,
            'expected_weight' => $standard['expected_weight'],
            'tolerance' => $standard['tolerance'],
            'price' => $standard['price'],
            'message' => 'Por favor, coloca el producto en la báscula...'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al crear validación']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
?>