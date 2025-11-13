<?php
// ===============================================
// Archivo: edit_profile.php
// Propósito: Permite al usuario logueado editar su nombre de usuario, correo y contraseña.
// ===============================================

require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; 

/**
 * @var PDO $pdo La conexión a la base de datos PDO.
 * @global PDO $pdo
 */
global $pdo; 

require_login(); 

$current_user_id = $_SESSION['user_id'] ?? null;
$error_message = '';
$profile_data = [];

// 1. Obtener el ID del usuario a editar
$user_to_edit_id = $_GET['id'] ?? null;

// Seguridad: Asegurar que el usuario solo pueda editar su propio perfil
if ($user_to_edit_id != $current_user_id) {
    header('Location: perfil.php?delete_status=error&message=' . urlencode('Acceso Denegado. Solo puedes editar tu propio perfil.'));
    exit;
}

// 2. Lógica de procesamiento (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = sanitize_input($_POST['username'] ?? '');
    $new_email = sanitize_input($_POST['email'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $update_fields = [];
    $update_values = [];
    $needs_update = false;

    // --- Validación de datos básicos (Nombre y Email) ---
    if (empty($new_username) || empty($new_email)) {
        $error_message = "El Nombre de Usuario y el Correo Electrónico son obligatorios.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del correo electrónico es inválido.";
    } else {
        // 2a. Prevenir duplicados (excepto con el ID actual)
        try {
            $check_sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([$new_username, $new_email, $current_user_id]);

            if ($check_stmt->fetch()) {
                $error_message = "El nombre de usuario o correo electrónico ya están registrados por otra cuenta.";
            } else {
                $update_fields[] = "username = ?";
                $update_values[] = $new_username;
                $update_fields[] = "email = ?";
                $update_values[] = $new_email;
                $needs_update = true;
                
                // 2b. Validación de Contraseña (Solo si se ingresó)
                if (!empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $error_message = "La nueva contraseña y la confirmación no coinciden.";
                    } elseif (strlen($new_password) < 6) {
                        $error_message = "La contraseña debe tener al menos 6 caracteres.";
                    } else {
                        // Hashear la contraseña de forma segura
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_fields[] = "password_hash = ?";
                        $update_values[] = $password_hash;
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error al verificar duplicados: " . $e->getMessage());
            $error_message = "Error en la base de datos al validar datos. Inténtalo más tarde.";
        }
    }

    // 2c. Ejecutar la Actualización si no hay errores
    if ($needs_update && empty($error_message)) {
        $update_values[] = $current_user_id; // El ID va al final para la cláusula WHERE
        
        $update_sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";

        try {
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute($update_values);
            
            // Actualizar la sesión si el nombre de usuario cambió
            if ($_SESSION['username'] !== $new_username) {
                $_SESSION['username'] = $new_username;
            }
            
            // Redirigir con mensaje de éxito
            header('Location: perfil.php?delete_status=success&message=' . urlencode('Tu perfil ha sido actualizado exitosamente.'));
            exit;

        } catch (PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            $error_message = "Error en la base de datos al intentar actualizar el perfil.";
        }
    }
} 

// 3. Cargar los datos actuales del usuario (Para mostrar en el formulario)
try {
    $sql = "SELECT id, username, email FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_user_id]);
    $profile_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile_data) {
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    // Si falla la carga inicial, usamos valores vacíos y mostramos el error
    $error_message = "No se pudieron cargar los datos actuales. Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .profile-header { background-color: #343a40; color: #ffffff; padding: 30px; border-radius: .375rem .375rem 0 0; }
        .info-card { border-top: none; border-radius: 0 0 .375rem .375rem; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <span class="navbar-text text-white">
                <span class="badge bg-<?php echo $_SESSION['role'] === 'admin' ? 'danger' : 'success'; ?> me-2"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></span>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </span>
        </div>
    </nav>
    
    <div class="container py-5">
        <div class="card shadow-lg mx-auto" style="max-width: 600px;">
            <div class="profile-header text-center">
                <i class="bi bi-person-gear display-4 mb-2"></i>
                <h1 class="h3 mb-0">Editar mi Perfil</h1>
                <p class="text-white-50 mt-1">Modifica tu información y contraseña</p>
            </div>
            
            <div class="card-body info-card">

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <?php if (!$profile_data): ?>
                    <div class="alert alert-warning">No se pudieron cargar los datos de tu perfil.</div>
                <?php else: ?>

                <form method="POST" action="edit_profile.php?id=<?php echo $current_user_id; ?>">
                    
                    <h5 class="mb-3 text-primary"><i class="bi bi-info-circle me-1"></i> Datos de la Cuenta</h5>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($profile_data['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile_data['email'] ?? ''); ?>" required>
                    </div>

                    <h5 class="mb-3 text-primary"><i class="bi bi-lock me-1"></i> Cambiar Contraseña (Opcional)</h5>
                    <p class="text-muted small">Deja estos campos vacíos si no deseas cambiar la contraseña.</p>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 6 caracteres">
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-bold">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm">
                            <i class="bi bi-arrow-repeat me-2"></i> Actualizar Perfil
                        </button>
                        <a href="perfil.php" class="btn btn-outline-secondary">
                            ← Cancelar y Volver al Perfil
                        </a>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>