<?php
require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Incluye la conexión PDO

start_session_secure();

$error_message = '';
$success_message = '';
$internal_error = false; // Nueva bandera para errores internos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanear y validar datos
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (strlen($password) < 6) {
        $error_message = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // 2. Hashear la contraseña de forma segura
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // 3. Insertar usuario en la BD usando Sentencias Preparadas
        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        
        // **MODIFICACIÓN CLAVE: Verificación de la Conexión PDO**
        if (!isset($pdo) || $pdo === null) {
            $error_message = "Error FATAL: No se pudo conectar a la base de datos. Revisa las credenciales en db_connect.php o la conexión a tu servidor.";
            $internal_error = true;
            
        } else {
            // Si la conexión existe, intentamos la consulta.
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $email, $password_hash]);
                
                $success_message = "🎉 ¡Registro exitoso! Ya puedes <a href='login.php'>iniciar sesión</a>.";
                
            } catch (PDOException $e) {
                // Error code 23000 es típicamente una violación de unicidad
                if ($e->getCode() == '23000') {
                    $error_message = "El nombre de usuario o email ya están registrados.";
                } else {
                    $error_message = "Error en el registro. Inténtalo más tarde.";
                    $internal_error = true;
                    // Para debugging avanzado: error_log("Error de registro: " . $e->getMessage()); 
                }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4 p-md-5" style="max-width: 450px; width: 100%;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-primary">📝 Crear una Cuenta</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php elseif ($error_message): ?>
                    <div class="alert <?php echo $internal_error ? 'alert-warning' : 'alert-danger'; ?>" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="register.php">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">Mínimo 6 caracteres.</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Registrar</button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    ¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none">Iniciar Sesión</a>
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