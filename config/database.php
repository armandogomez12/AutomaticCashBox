<?php
// Carga el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Carga las variables de entorno del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Este error SÍ aparecería en la respuesta si la conexión falla
            die(json_encode([
                'success' => false, 
                'error' => 'Error de base de datos: ' . $exception->getMessage()
            ]));
        }
        return $this->conn;
    }
}
?>