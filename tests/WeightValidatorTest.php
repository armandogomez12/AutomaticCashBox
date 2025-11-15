<?php
// Auqi le decimos a PHP que vamos a usar la clase TestCase de PHPUnit
use PHPUnit\Framework\TestCase;

// Incluimos la clase que queremos probar
require_once __DIR__ . '/../src/WeightValidator.php';

class WeightValidatorTest extends TestCase
{
    
     #Prueba 1: Un peso que está DENTRO del rango permitido.
    public function testWeightIsValidWhenWithinTolerance()
    {
        // Creamos una simulación (un "mock") de la base de datos para no depender de una conexión real.
        $databaseMock = $this->createMock(Database::class);
        $pdoMock = $this->createMock(PDO::class);
        $stmtMock = $this->createMock(PDOStatement::class);

        // Le decimos a nuestra simulación qué debe devolver cuando se le pregunte por la manzana
        $productData = ['expected_weight' => 150.00, 'tolerance' => 10.00, 'price' => 30.25];
        $stmtMock->method('fetch')->willReturn($productData);
        $stmtMock->method('rowCount')->willReturn(1); // Simula que encontró 1 producto
        $pdoMock->method('prepare')->willReturn($stmtMock);
        $databaseMock->method('getConnection')->willReturn($pdoMock);

        // Aqui se aplica la "inyección de dependencias" para aislar la prueba.
        $validator = new WeightValidator();

        //Nos aseguramos de que el producto 'MANZANA_ROJA' exista en la tabla 
        $validatorForRealDB = new WeightValidator();
        $scale_id = 'MANZANA_ROJA';
        $measured_weight = 155; // Un peso válido

        // Actuación (Act) 
        $result = $validatorForRealDB->validateWeight($scale_id, $measured_weight);

        // Afirmación (Assert) 
        // Verificamos que el resultado es el que esperamos.
        $this->assertTrue($result['is_valid']);
        $this->assertEquals('PASS', $result['status']);
    }
    
    # Prueba 2: Un peso que está FUERA del rango permitido.
    public function testWeightIsInvalidWhenOutsideTolerance()
    {
        // 1. Arrange
        $validator = new WeightValidator();
        $scale_id = 'MANZANA_ROJA';
        $measured_weight = 200; // Un peso inválido

        // 2. Act
        $result = $validator->validateWeight($scale_id, $measured_weight);

        // 3. Assert
        $this->assertFalse($result['is_valid']);
        $this->assertEquals('FAIL', $result['status']);
    }

    
    #Prueba 3: Un producto que no existe.
    public function testReturnsErrorForNonExistentProduct()
    {
        // 1. Arrange
        $validator = new WeightValidator();
        $scale_id = 'PRODUCTO_FALSO_123';
        $measured_weight = 100;

        // 2. Act
        $result = $validator->validateWeight($scale_id, $measured_weight);
        
        // 3. Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Producto no encontrado o inactivo.', $result['error']);
    }
}
