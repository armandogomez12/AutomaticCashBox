<?php
session_start(); 
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login_admin.php');
    exit;
}

// ---------------------------------------------------------
// BLOQUE DE VERIFICACIÓN GOOGLE RECAPTCHA
// ---------------------------------------------------------
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
$secret_key = "6LfmixssAAAAAGTflymIHm2SXzRUqXA-IObfj3CU"; // <--- PEGA TU CLAVE SECRETA AQUÍ

if (empty($recaptcha_response)) {
    $_SESSION['error_message'] = 'Por favor, completa la verificación "No soy un robot".';
    header('Location: ../public/login_admin.php');
    exit;
}

// Verificar con Google
$verify_url = "https://www.google.com/recaptcha/api/siteverify";
$data = [
    'secret' => $secret_key,
    'response' => $recaptcha_response,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($verify_url, false, $context);
$response_keys = json_decode($result, true);

if(!$response_keys["success"]) {
    $_SESSION['error_message'] = 'Verificación de seguridad fallida.';
    header('Location: ../public/login_admin.php');
    exit;
}
// ---------------------------------------------------------


$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = 'Usuario y contraseña son requeridos.';
    header('Location: ../public/login_admin.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Seleccionamos is_active para verificar si está aprobado
    $query = "SELECT username, password_hash, is_active FROM admins WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $admin['password_hash'])) {
            
            // Verificamos si la cuenta ha sido activada por el Super Usuario
            if ($admin['is_active'] == 0) {
                $_SESSION['error_message'] = 'Tu cuenta está en espera de aprobación por un Super Usuario.';
                header('Location: ../public/login_admin.php');
                exit;
            }

            // Login exitoso
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            
            header('Location: ../public/admin_panel.php');
            exit;
        }
    }

    $_SESSION['error_message'] = 'Credenciales incorrectas.';
    header('Location: ../public/login_admin.php');
    exit;

} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error en el servidor.';
    header('Location: ../public/login_admin.php');
    exit;
}
?>
