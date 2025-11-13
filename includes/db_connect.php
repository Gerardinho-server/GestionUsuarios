<?php
// ===============================================
// Archivo: includes/db_connect.php
// Propósito: Establecer la conexión PDO con la BD remota de Clever Cloud.
// ===============================================

// Configuración de la base de datos de Clever Cloud
$host = 'bdhmh4qavov6mkp1mdv3-mysql.services.clever-cloud.com';
$db = 'bdhmh4qavov6mkp1mdv3';
$user = 'u4tct4pdj87hx4jb';
$pass = 'cUPKiCK3onqgoQPtf6z1';
$port = 3306; 

$pdo = null; // Inicializamos PDO a null

try {
    // Cadena de conexión DSN para MySQL con PDO
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    // Opciones de configuración
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en caso de error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Devolver resultados como array asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                // Usar prepared statements nativas
    ];
    
    // Intentar la conexión
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Si la conexión falla, $pdo permanece como null.
    // El script de registro usará la bandera de error FATAL.
    // Opcional para debugging: error_log("Error de conexión PDO: " . $e->getMessage()); 
}
// La variable global $pdo se usa en register.php.
?>