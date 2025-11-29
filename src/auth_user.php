<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login_user.php');
    exit;
}

// ---------------------------------------------------------
// BLOQUE DE VERIFICACIÓN GOOGLE RECAPTCHA
// ---------------------------------------------------------
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
$secret_key = "6LfmixssAAAAAGTflymIHm2SXzRUqXA-IObfj3CU"; // <--- IMPORTANTE: Pega tu clave secreta aquí

if (empty($recaptcha_response)) {
    $_SESSION['error_message'] = 'Por favor, marca la casilla "No soy un robot".';
    header('Location: ../public/login_user.php');
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
    $_SESSION['error_message'] = 'Verificación de robot fallida. Intenta de nuevo.';
    header('Location: ../public/login_user.php');
    exit;
}
// ---------------------------------------------------------

// Capturar datos del usuario
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = 'Usuario y contraseña son requeridos.';
    header('Location: ../public/login_user.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "SELECT id, username, password_hash FROM users WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['user_username'] = $user['username'];
            
            header('Location: ../public/user_dashboard.php');
            exit;
        }
    }

    $_SESSION['error_message'] = 'Credenciales incorrectas.';
    header('Location: ../public/login_user.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error en el servidor.';
    header('Location: ../public/login_user.php');
    exit;
}
?>
