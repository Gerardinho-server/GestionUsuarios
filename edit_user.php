<?php
// ===============================================
// Archivo: edit_user.php
// Propósito: Permite al administrador editar los detalles de un usuario.
// ===============================================

require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Incluye la conexión PDO ($pdo)

start_session_secure();

// 1. **SEGURIDAD CRÍTICA:** Solo permitir acceso si el usuario es administrador
// Se asume que $_SESSION['role'] se carga correctamente en el login.
if (!is_logged_in() || ($_SESSION['role'] ?? 'user') !== 'admin') {
    // Redirige al dashboard si no es admin
    header("Location: dashboard.php");
    exit;
}

$user_id = sanitize_input($_GET['id'] ?? null);
$error_message = '';
$success_message = '';
$user_data = [];
$current_password_message = ''; // Mensaje para indicar que la contraseña es opcional

// 2. Verificación de conexión y ID
if ($pdo === null) {
    die("<div class='alert alert-danger'>Error FATAL: No se pudo establecer la conexión a la base de datos.</div>");
}

if (!$user_id || !is_numeric($user_id)) {
    $error_message = "ID de usuario inválido o no especificado.";
} else {
    
    // 3. Procesar el formulario de actualización (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // 3A. Recibir y sanear datos del POST
        $new_username = sanitize_input($_POST['username'] ?? '');
        $new_email = sanitize_input($_POST['email'] ?? '');
        $new_password = $_POST['password'] ?? ''; // La contraseña no se sanea (se hashea)
        $new_is_active = isset($_POST['is_active']) ? 1 : 0; 
        $new_role = sanitize_input($_POST['role'] ?? 'user');

        // 3B. Validar campos obligatorios
        if (empty($new_username) || empty($new_email) || empty($new_role)) {
            $error_message = "Los campos de usuario, email y rol son obligatorios.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "El formato del correo electrónico no es válido.";
        } else {
            
            // 3C. Construir la consulta de actualización
            $fields = ['username = ?', 'email = ?', 'is_active = ?', 'role = ?'];
            $params = [$new_username, $new_email, $new_is_active, $new_role];
            
            // Si se proporcionó una nueva contraseña, hashearla y agregarla a la consulta
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $error_message = "La nueva contraseña debe tener al menos 6 caracteres.";
                } else {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $fields[] = 'password_hash = ?';
                    $params[] = $password_hash;
                }
            }
            
            if (!$error_message) {
                // Agregar el ID del usuario al final de los parámetros para la cláusula WHERE
                $params[] = $user_id;
                
                $sql_update = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
                
                try {
                    $stmt = $pdo->prepare($sql_update);
                    $stmt->execute($params);
                    
                    $success_message = "¡Información del usuario ID $user_id actualizada con éxito!";
                    // Redireccionar a sí mismo para evitar reenvío de formulario y mostrar datos actualizados
                    header("Location: edit_user.php?id=$user_id&status=success");
                    exit;

                } catch (PDOException $e) {
                    $error_message = "Error al actualizar la información: " . $e->getMessage();
                }
            }
        }
    }

    // 4. Obtener la información actual del usuario (para rellenar el formulario)
    // Esto se ejecuta al cargar la página o después de un error/éxito (si la redirección falla).
    if (!isset($_GET['status'])) { // Evita la carga de datos si hay una redirección pendiente
        $sql_fetch = "SELECT id, username, email, is_active, role FROM users WHERE id = ?";
        try {
            $stmt = $pdo->prepare($sql_fetch);
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user_data) {
                $error_message = "Error: Usuario con ID $user_id no encontrado.";
                $user_id = null;
            }
        } catch (PDOException $e) {
            $error_message = "Error al obtener la información del usuario.";
        }
    } else {
        // Muestra el mensaje de éxito después de la redirección
        $success_message = "¡Información del usuario ID $user_id actualizada con éxito!";
        
        // Cargar los datos más recientes para evitar errores visuales (podría ser redundante si el POST fue exitoso)
        $sql_fetch = "SELECT id, username, email, is_active, role FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql_fetch);
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario ID: <?php echo htmlspecialchars($user_id ?? 'N/A'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-primary">📝 Editar Usuario #<?php echo htmlspecialchars($user_id ?? 'N/A'); ?></h2>
                
                <p class="text-center mb-4"><a href="dashboard.php">← Volver al Dashboard (CRUD)</a></p>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert"><i class="bi bi-x-octagon-fill me-2"></i><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert"><i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <?php if ($user_data): ?>
                <form method="post" action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña (Opcional)</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Deja vacío para mantener la contraseña actual">
                        <div class="form-text">Mínimo 6 caracteres si deseas cambiarla.</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                               <?php echo ($user_data['is_active'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Cuenta Activa</label>
                    </div>
                    
                    <div class="mb-4">
                        <label for="role" class="form-label">Rol del Usuario</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user" <?php echo ($user_data['role'] ?? '') === 'user' ? 'selected' : ''; ?>>Usuario</option>
                            <option value="admin" <?php echo ($user_data['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                            <option value="guest" <?php echo ($user_data['role'] ?? '') === 'guest' ? 'selected' : ''; ?>>Invitado</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-floppy-fill me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>