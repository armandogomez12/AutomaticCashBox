<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verificación de sesión de admin (Opcional, descomentar si lo deseas)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register_admin.php');
    exit;
}

// ---------------------------------------------------------
// BLOQUE DE VERIFICACIÓN GOOGLE RECAPTCHA
// ---------------------------------------------------------
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
$secret_key = "6LfmixssAAAAAGTflymIHm2SXzRUqXA-IObfj3CU"; // <--- PEGA TU CLAVE SECRETA AQUÍ

if (empty($recaptcha_response)) {
    header('Location: ../public/register_admin.php?error=Por favor, completa la verificación "No soy un robot".');
    exit;
}

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
    header('Location: ../public/register_admin.php?error=Verificación de seguridad fallida.');
    exit;
}
// ---------------------------------------------------------


$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    header('Location: ../public/register_admin.php?error=Todos los campos son requeridos');
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// ESTADO INICIAL: 0 (Desactivado/Lista de espera)
$is_active = 0;

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "INSERT INTO admins (username, email, password_hash, is_active) VALUES (:username, :email, :password_hash, :is_active)";
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':is_active', $is_active);
    
    if ($stmt->execute()) {
        $msg = "Registro creado. La cuenta está EN ESPERA de activación por un Super Usuario.";
        header('Location: ../public/register_admin.php?status=success&msg=' . urlencode($msg));
        exit;
    }
} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) {
        header('Location: ../public/register_admin.php?error=El usuario o el correo ya existen.');
    } else {
        header('Location: ../public/register_admin.php?error=Error DB: ' . $e->getMessage());
    }
    exit;
}
?>
