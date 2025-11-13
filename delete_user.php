<?php
// ===============================================
// Archivo: delete_user.php
// Propósito: Elimina un usuario de la base de datos (Acción CRUD D).
// ===============================================

require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Incluye la conexión PDO ($pdo)

start_session_secure();

// 1. **SEGURIDAD CRÍTICA:** Solo permitir acceso a administradores
// Se asume que $_SESSION['role'] se carga correctamente.
if (!is_logged_in() || ($_SESSION['role'] ?? 'user') !== 'admin') {
    // Si no es admin, redirigir a un error o al dashboard
    header("Location: dashboard.php");
    exit;
}

$user_id = sanitize_input($_GET['id'] ?? null);
$redirect_url = 'dashboard.php'; // URL a la que redirigir después de la acción

// 2. Verificación de conexión y ID
if ($pdo === null) {
    // Si la conexión falló, redirige con un mensaje de error
    header("Location: $redirect_url?delete_status=error&message=Error de conexión a la BD.");
    exit;
}

if (!$user_id || !is_numeric($user_id)) {
    // ID inválido
    header("Location: $redirect_url?delete_status=error&message=ID de usuario inválido.");
    exit;
}

// 3. Evitar que un administrador se borre a sí mismo (opcional pero recomendado)
if ($user_id == ($_SESSION['user_id'] ?? 0)) {
    header("Location: $redirect_url?delete_status=error&message=No puedes eliminar tu propia cuenta.");
    exit;
}

// 4. Ejecutar la sentencia DELETE
$sql = "DELETE FROM users WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    // Verificar si se eliminó alguna fila
    if ($stmt->rowCount() > 0) {
        // Éxito
        header("Location: $redirect_url?delete_status=success&message=Usuario ID $user_id eliminado correctamente.");
        exit;
    } else {
        // No se encontró el usuario con ese ID
        header("Location: $redirect_url?delete_status=warning&message=El usuario ID $user_id no fue encontrado.");
        exit;
    }

} catch (PDOException $e) {
    // Error en la consulta SQL
    header("Location: $redirect_url?delete_status=error&message=Error de base de datos al eliminar el usuario.");
    // Opcional: registrar el error completo: error_log("Error al eliminar usuario: " . $e->getMessage());
    exit;
}
?>