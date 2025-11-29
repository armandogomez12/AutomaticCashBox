<?php
session_start();
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

try {
    // borramos la tabla de'user_purchases esto elimina todo el historial visual del usuario actual
    $query = "DELETE FROM user_purchases WHERE user_id = :uid";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([':uid' => $userId])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo borrar']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos.']);
}
?>
