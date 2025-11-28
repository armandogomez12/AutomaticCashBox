<?php
// La contraseña que queremos usar
$password = 'admin123';

// Generar el hash seguro
$hash = password_hash($password, PASSWORD_DEFAULT);

// Mostrar el hash en la pantalla
echo "<h1>Nuevo Hash Generado</h1>";
echo "<p>Copia y pega la siguiente línea completa en tu consulta SQL para actualizar la base de datos:</p>";
echo "<hr>";
echo "<strong>" . $hash . "</strong>";
echo "<hr>";
echo "<p><em><small>Nota: Cada vez que recargues esta página, se generará un hash diferente. Todos son válidos para la misma contraseña.</small></em></p>";
?>