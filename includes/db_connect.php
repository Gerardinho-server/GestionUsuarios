<?php
// Configuración de la base de datos de Clever Cloud
$host = 'bdhmh4qavov6mkp1mdv3-mysql.services.clever-cloud.com';
$db = 'bdhmh4qavov6mkp1mdv3';
$user = 'u4tct4pdj87hx4jb';
$pass = 'cUPKiCK3onqgoQPtf6z1';
$port = 3306; // El puerto debe ser un entero

// Inicializamos $conn a null.
$conn = null;

// Intentamos la conexión
try {
    // Usamos el constructor de mysqli
    $conn = new mysqli($host, $user, $pass, $db, $port);
    
    // Si hay un error de conexión (que mysqli maneja internamente)
    if ($conn->connect_error) {
        throw new Exception("Fallo en la conexión a MySQLi: " . $conn->connect_error);
    }
    
    // Establecemos el charset para evitar problemas de codificación
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Si la conexión falla, $conn permanece como null o se cierra.
    $conn = null;
    // error_log("Error crítico de conexión a la BD: " . $e->getMessage()); 
}

// Ahora, la variable $conn contiene el objeto de conexión O es null.
?>