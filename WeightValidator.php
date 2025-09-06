<?php
require_once '../config/database.php';

class WeightValidator {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function validateWeight($scale_id, $measured_weight) {
        try {
            // Buscar el estándar para este scale_id
            $query = "SELECT expected_weight, tolerance FROM weight_standards 
                     WHERE scale_id = :scale_id AND is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':scale_id', $scale_id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $expected_weight = floatval($row['expected_weight']);
                $tolerance = floatval($row['tolerance']);
                
                // Calcular los límites
                $min_weight = $expected_weight - $tolerance;
                $max_weight = $expected_weight + $tolerance;
                
                // Verificar si está dentro del rango
                $is_valid = ($measured_weight >= $min_weight && $measured_weight <= $max_weight);
                
                // Calcular la diferencia
                $difference = $measured_weight - $expected_weight;
                
                return [
                    'success' => true,
                    'scale_id' => $scale_id,
                    'measured_weight' => $measured_weight,
                    'expected_weight' => $expected_weight,
                    'tolerance' => $tolerance,
                    'min_weight' => $min_weight,
                    'max_weight' => $max_weight,
                    'difference' => round($difference, 2),
                    'is_valid' => $is_valid,
                    'status' => $is_valid ? 'PASS' : 'FAIL',
                    'message' => $is_valid ? 'Peso dentro del rango permitido' : 'Peso fuera del rango permitido'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Scale ID no encontrado o inactivo',
                    'scale_id' => $scale_id
                ];
            }
        } catch(PDOException $exception) {
            return [
                'success' => false,
                'error' => 'Error de base de datos: ' . $exception->getMessage()
            ];
        }
    }
    
    public function getAllStandards() {
        try {
            $query = "SELECT * FROM weight_standards WHERE is_active = 1 ORDER BY scale_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'standards' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch(PDOException $exception) {
            return [
                'success' => false,
                'error' => 'Error de base de datos: ' . $exception->getMessage()
            ];
        }
    }
    
    public function logMeasurement($scale_id, $measured_weight, $is_valid) {
        try {
            // Crear tabla de logs si no existe
            $create_table = "CREATE TABLE IF NOT EXISTS measurement_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                scale_id VARCHAR(50) NOT NULL,
                measured_weight DECIMAL(10,2) NOT NULL,
                is_valid TINYINT(1) NOT NULL,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->conn->exec($create_table);
            
            // Insertar el log
            $query = "INSERT INTO measurement_logs (scale_id, measured_weight, is_valid) 
                     VALUES (:scale_id, :measured_weight, :is_valid)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':scale_id', $scale_id);
            $stmt->bindParam(':measured_weight', $measured_weight);
            $stmt->bindParam(':is_valid', $is_valid);
            
            return $stmt->execute();
        } catch(PDOException $exception) {
            return false;
        }
    }
}
?>
