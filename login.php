<?php
// ===============================================
// Archivo: login.php
// Propósito: Lógica de inicio de sesión y formulario con diseño Bootstrap.
// ===============================================

// Incluir archivos necesarios
require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Incluye la conexión PDO ($pdo)

start_session_secure();

$error_message = '';
$username_val = ''; // Para mantener el valor en el formulario

// Redirigir si el usuario ya está logueado (ej. si existe 'user_id' en la sesión)
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibir y sanear datos
    $username_or_email = sanitize_input($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $username_val = $username_or_email;
    
    // 2. Validaciones
    if (empty($username_or_email) || empty($password)) {
        $error_message = "Por favor, introduce usuario/email y contraseña.";
    } else {
        
        // 3. Verificación de Conexión
        if (!isset($pdo) || $pdo === null) {
            $error_message = "Error FATAL: Problema con la base de datos. Inténtalo más tarde.";
            
        } else {
            // 4. Autenticación
            try {
                // MODIFICACIÓN CLAVE: Se añade 'role' a la consulta SELECT
                $sql = "SELECT id, username, password_hash, role FROM users WHERE username = ? OR email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username_or_email, $username_or_email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    // ÉXITO: Contraseña correcta
                    
                    // Regenerar el ID de sesión para prevenir Session Fixation
                    session_regenerate_id(true);
                    
                    // Almacenar datos del usuario en la sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    // MODIFICACIÓN CLAVE: Almacenar el rol en la sesión
                    $_SESSION['role'] = $user['role']; 
                    
                    // Redirigir al dashboard
                    header('Location: dashboard.php');
                    exit;
                    
                } else {
                    // FALLO: Credenciales inválidas
                    $error_message = "Credenciales inválidas. Verifica tu usuario/email y contraseña.";
                }
                
            } catch (PDOException $e) {
                $error_message = "Error interno del servidor. Inténtalo más tarde.";
                // error_log("Error de BD en login: " . $e->getMessage()); 
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
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4 p-md-5" style="max-width: 400px; width: 100%;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-primary">🔐 Iniciar Sesión</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="login.php">
                    
                    <div class="mb-3">
                        <label for="username_or_email" class="form-label">Usuario o Correo Electrónico</label>
                        <input type="text" class="form-control" id="username_or_email" name="username_or_email" value="<?php echo htmlspecialchars($username_val); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    ¿No tienes cuenta? <a href="register.php" class="text-decoration-none">Regístrate aquí</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous">
    </script>
</body>
</html>