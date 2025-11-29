<?php
// Forzar todas las PENDING a VALIDATED e insertar en user_purchases
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$secret = $_GET['secret'] ?? $_POST['secret'] ?? null;
if ($secret !== 'PRUEBA_LOCAL_123') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $q = "SELECT * FROM validation_pending WHERE status = 'PENDING' ORDER BY created_at ASC";
    $stmt = $conn->prepare($q);
    $stmt->execute();
    $pendings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $processed = 0;
    foreach ($pendings as $validation) {
        // Marcar VALIDATED
        $update = "UPDATE validation_pending SET measured_weight = expected_weight, status = 'VALIDATED', validated_at = NOW() WHERE id = :id";
        $u = $conn->prepare($update);
        $u->bindParam(':id', $validation['id']);
        $u->execute();

        // Obtener nombre del producto
        $q2 = "SELECT product_name FROM weight_standards WHERE scale_id = :scale_id LIMIT 1";
        $s2 = $conn->prepare($q2);
        $s2->bindParam(':scale_id', $validation['scale_id']);
        $s2->execute();
        $ws = $s2->fetch(PDO::FETCH_ASSOC);
        $product_name = $ws['product_name'] ?? $validation['scale_id'];

        // Evitar duplicados
        $check = "SELECT COUNT(*) FROM user_purchases 
                  WHERE user_id = :user_id AND scale_id = :scale_id 
                    AND expected_weight = :expected_weight AND price = :price 
                    AND timestamp >= :created_at";
        $c = $conn->prepare($check);
        $c->bindParam(':user_id', $validation['user_id']);
        $c->bindParam(':scale_id', $validation['scale_id']);
        $c->bindParam(':expected_weight', $validation['expected_weight']);
        $c->bindParam(':price', $validation['price']);
        $c->bindParam(':created_at', $validation['created_at']);
        $c->execute();
        $exists = (int)$c->fetchColumn();

        if ($exists === 0) {
            $ins = "INSERT INTO user_purchases (user_id, scale_id, product_name, expected_weight, price) 
                    VALUES (:user_id, :scale_id, :product_name, :expected_weight, :price)";
            $i = $conn->prepare($ins);
            $i->bindParam(':user_id', $validation['user_id']);
            $i->bindParam(':scale_id', $validation['scale_id']);
            $i->bindParam(':product_name', $product_name);
            $i->bindParam(':expected_weight', $validation['expected_weight']);
            $i->bindParam(':price', $validation['price']);
            $i->execute();
        }

        $processed++;
    }

    echo json_encode(['success' => true, 'processed' => $processed]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>