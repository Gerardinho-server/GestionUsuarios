<?php
require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Ahora incluye la conexión MySQLi ($conn)

start_session_secure();

$error_message = '';
$username_or_email = ''; 

// Si el usuario ya está logueado, redirigimos al dashboard
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanear los datos de entrada
    $username_or_email = sanitize_input($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? ''; 
    
    if (empty($username_or_email) || empty($password)) {
        $error_message = "Por favor, ingresa tu usuario/email y contraseña.";
    } else {
        
        // 2. Verificación de Conexión (CRÍTICO: Maneja el caso $conn = null)
        if (!isset($conn) || $conn === null) {
            $error_message = "Ocurrió un error en el sistema de login. No se pudo conectar a la base de datos remota.";
            
        } else {
            
            // 3. Buscar el usuario usando MySQLi Sentencias Preparadas (¡Seguro!)
            $sql = "SELECT id, username, password_hash, is_active FROM users WHERE username = ? OR email = ? LIMIT 1";
            
            // Usamos $conn para preparar la sentencia
            if ($stmt = $conn->prepare($sql)) {
                
                // Enlazar parámetros (bind_param): 'ss' indica dos cadenas (strings)
                $stmt->bind_param("ss", $username_or_email, $username_or_email);
                $stmt->execute();
                
                // Obtener el resultado
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                // 4. Lógica de Autenticación
                if ($user) {
                    
                    // A. Verificar si la cuenta está inactiva
                    if ($user['is_active'] === '0' || $user['is_active'] === 0) {
                        $error_message = "Tu cuenta está inactiva. Contacta al administrador.";
                    } 
                    // B. Verificar el hash de la contraseña
                    elseif (password_verify($password, $user['password_hash'])) {
                        
                        // ** AUTENTICACIÓN EXITOSA **
                        $_SESSION['user_id'] = $user['id']; 
                        $_SESSION['username'] = $user['username'];
                        
                        // Redirigir al dashboard (¡El objetivo!)
                        $stmt->close();
                        $conn->close();
                        header("Location: dashboard.php");
                        exit; 
                        
                    } else {
                        // Contraseña INCORRECTA
                        $error_message = "Credenciales incorrectas.";
                    }
                    
                } else {
                    // Usuario NO encontrado
                    $error_message = "Credenciales incorrectas.";
                }
                
                // Cerrar la sentencia preparada
                $stmt->close();
            } else {
                // Error si la preparación de la consulta falla
                $error_message = "Error interno del sistema al preparar la consulta.";
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
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4 p-md-5" style="max-width: 450px; width: 100%;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-success">🚀 Iniciar Sesión</h2>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label for="username_or_email" class="form-label">Usuario o Email</label>
                        <input type="text" class="form-control" id="username_or_email" name="username_or_email" required value="<?php echo htmlspecialchars($username_or_email); ?>">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Entrar</button>
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