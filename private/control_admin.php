<?php
// --- CONFIGURACIÓN MANUAL DEL SUPER USUARIO ---

// 1. Escribe aquí el usuario que quieres modificar
$usuario_objetivo = 'Alejandro'; 

// 2. Define el estado: 1 para ACTIVAR, 0 para BLOQUEAR
$nuevo_estado = 1; 

// ----------------------------------------------

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$conn = $database->getConnection();

echo "<h1>Panel de Control Manual (Super Usuario)</h1>";

if (!empty($usuario_objetivo)) {
    try {
        // Verificar estado actual
        $check = $conn->prepare("SELECT id, is_active FROM admins WHERE username = ?");
        $check->execute([$usuario_objetivo]);
        $user = $check->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Ejecutar el cambio
            $sql = "UPDATE admins SET is_active = :estado WHERE username = :user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':user', $usuario_objetivo);
            
            if($stmt->execute()) {
                $estado_texto = ($nuevo_estado == 1) ? "<span style='color:green'>ACTIVADO</span>" : "<span style='color:red'>DESACTIVADO</span>";
                echo "<p>El usuario <strong>$usuario_objetivo</strong> ha sido $estado_texto exitosamente.</p>";
            } else {
                echo "<p>Error al ejecutar la actualización.</p>";
            }
        } else {
            echo "<p style='color:red'>El usuario '$usuario_objetivo' no existe en la base de datos.</p>";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "<p>Por favor, edita este archivo PHP y define la variable <code>\$usuario_objetivo</code>.</p>";
}
?>
