<?php
// ===============================================
// Archivo: register.php
// Propósito: Lógica de registro y formulario con diseño Bootstrap.
// ===============================================

// Incluir archivos necesarios
require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Incluye la conexión PDO ($pdo)

start_session_secure();

$error_message = '';
$success_message = '';
$internal_error = false; // Bandera para diferenciar errores de usuario vs. errores de servidor/DB
$username_val = $_POST['username'] ?? ''; // Mantener el valor si hay error
$email_val = $_POST['email'] ?? '';       // Mantener el valor si hay error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibir y sanear datos
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Mantener valores para la persistencia del formulario en caso de error
    $username_val = $username; 
    $email_val = $email;
    
    $username = sanitize_input($username);
    $email = sanitize_input($email);
    
    // 2. Validaciones básicas
    if (empty($username) || empty(trim($email)) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (strlen($password) < 6) {
        $error_message = "La contraseña debe tener al menos 6 caracteres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "El formato del correo electrónico no es válido.";
    } else {
        
        // 3. Verificación Crítica de Conexión PDO
        if (!isset($pdo) || $pdo === null) {
            $error_message = "Error FATAL: No se pudo conectar a la base de datos. Verifique la IP permitida en Clever Cloud.";
            $internal_error = true;
            
        } else {
            // 4. Procesamiento seguro de registro
            try {
                // Verificar si el usuario o email ya existen
                $check_sql = "SELECT username, email FROM users WHERE username = ? OR email = ?";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$username, $email]);
                $existing_user = $check_stmt->fetch();
                
                if ($existing_user) {
                    $error_message = "El nombre de usuario o email ya están registrados.";
                } else {
                    // Hashear la contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertar nuevo usuario
                    $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $email, $password_hash]);
                    
                    $success_message = "🎉 ¡Registro exitoso! Ya puedes <a href='login.php'>iniciar sesión</a>.";
                    
                    // Limpiar valores del formulario en caso de éxito
                    $username_val = '';
                    $email_val = '';
                }
                
            } catch (PDOException $e) {
                $error_message = "Error interno del servidor. Inténtalo más tarde. (Código: " . $e->getCode() . ")";
                $internal_error = true;
                // error_log("Error de BD: " . $e->getMessage()); 
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4 p-md-5" style="max-width: 450px; width: 100%;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-primary">✍️ Crear una Cuenta</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php elseif ($error_message): ?>
                    <?php 
                        // Determinar la clase de alerta basado en el tipo de error
                        $alert_class = $internal_error ? 'alert-warning' : 'alert-danger';
                        $icon_class = $internal_error ? 'bi-exclamation-triangle-fill' : 'bi-x-octagon-fill';
                    ?>
                    <div class="alert <?php echo $alert_class; ?>" role="alert">
                        <i class="bi <?php echo $icon_class; ?> me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="register.php">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username_val); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email_val); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres.</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                             <i class="bi bi-person-plus-fill me-2"></i> Registrar
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    ¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none">Iniciar Sesión</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>