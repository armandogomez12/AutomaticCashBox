<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// --- ACCIÓN PÚBLICA: OBTENER PRODUCTOS ---
// Cualquiera puede realizar esta acción.
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_all') {
    try {
        $query = "SELECT scale_id, product_name, expected_weight, tolerance, price FROM weight_standards WHERE is_active = 1 ORDER BY product_name ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'products' => $products]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error de base de datos.']);
    }
    exit; // Importante salir aquí
}

// --- ZONA PROTEGIDA PARA ADMINISTRADORES ---
// A partir de aquí, se necesita haber iniciado sesión como admin.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado para esta acción.']);
    exit;
}

// Acción POST para añadir un producto (protegida)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    // ... (El resto de la lógica para añadir productos va aquí) ...
     try {
        $query = "INSERT INTO weight_standards (product_name, scale_id, expected_weight, tolerance, price) VALUES (:product_name, :scale_id, :expected_weight, :tolerance, :price)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':product_name', $input['product_name']);
        $stmt->bindParam(':scale_id', $input['scale_id']);
        $stmt->bindParam(':expected_weight', $input['expected_weight']);
        $stmt->bindParam(':tolerance', $input['tolerance']);
        $stmt->bindParam(':price', $input['price']);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Producto añadido exitosamente.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error de base de datos al añadir producto.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Acción no válida o no permitida.']);
}
?>