<?php
require_once __DIR__ . '/../config/database.php';

class WeightValidator {
   public $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Valida el peso de un producto y devuelve su información, incluido el precio.
     */
    public function validateWeight($scale_id, $measured_weight) {
        try {
            // Modificamos la consulta para que también traiga el precio
            $query = "SELECT expected_weight, tolerance, price FROM weight_standards 
                      WHERE scale_id = :scale_id AND is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':scale_id', $scale_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $expected_weight = floatval($row['expected_weight']);
                $tolerance = floatval($row['tolerance']);
                $price = floatval($row['price']); // Obtenemos el precio
                
                $min_weight = $expected_weight - $tolerance;
                $max_weight = $expected_weight + $tolerance;
                
                $is_valid = ($measured_weight >= $min_weight && $measured_weight <= $max_weight);
                
                return [
                    'success' => true,
                    'is_valid' => $is_valid,
                    'status' => $is_valid ? 'PASS' : 'FAIL',
                    'message' => $is_valid ? 'Peso dentro del rango permitido' : 'El peso no coincide con el estándar del producto.',
                    'expected_weight' => $expected_weight,
                    'tolerance' => $tolerance,
                    'min_weight' => $min_weight,
                    'max_weight' => $max_weight,
                    'price' => $price // Devolvemos el precio en la respuesta
                ];
            } else {
                return ['success' => false, 'error' => 'Producto no encontrado o inactivo.'];
            }
        } catch(PDOException $exception) {
            return ['success' => false, 'error' => 'Error de base de datos: ' . $exception->getMessage()];
        }
    }

    /**
     * Guarda el registro de la medición, incluido el precio.
     */
   public function logMeasurement($scale_id, $measured_weight, $expected_weight, $is_valid, $price, $user_id = null) {
    try {
        // Modificamos la consulta para insertar el peso esperado
        $query = "INSERT INTO measurement_logs (user_id, scale_id, measured_weight, expected_weight, is_valid, price) 
                  VALUES (:user_id, :scale_id, :measured_weight, :expected_weight, :is_valid, :price)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':scale_id', $scale_id);
        $stmt->bindParam(':measured_weight', $measured_weight);
        $stmt->bindParam(':expected_weight', $expected_weight); // Nuevo parámetro
        $stmt->bindParam(':is_valid', $is_valid);
        $stmt->bindParam(':price', $price);
        
        return $stmt->execute();
    } catch(PDOException $exception) {
        // Opcional: registrar el error en un log
        error_log("Error en logMeasurement: " . $exception->getMessage());
        return false;
    }
}
}
?>