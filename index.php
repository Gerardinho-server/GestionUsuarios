<?php
// ===============================================
// Archivo: index.php
// Propósito: Página de bienvenida y redirección si el usuario ya está logueado.
// ===============================================

// Asegúrate de usar las funciones que definimos previamente para iniciar la sesión de forma segura
require_once 'includes/functions.php'; 
start_session_secure();

// Usamos la función is_logged_in() para verificar la sesión
if (is_logged_in()) {
    header("location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al Sistema de Autenticación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4 p-md-5 w-100" style="max-width: 500px;">
            <div class="card-body text-center">
                <h1 class="card-title mb-4 text-primary">
                    <i class="bi bi-shield-lock me-2"></i> Sistema de Autenticación
                </h1>
                <p class="lead mb-5">
                    Tu solución segura para registro y acceso de usuarios con PHP y Clever Cloud.
                </p>
                
                <div class="d-grid gap-3">
                    <a href="login.php" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
                    </a>
                    
                    <a href="register.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-person-plus me-2"></i> Crear Cuenta
                    </a>
                </div>
            </div>
        </div>
        <p class="mt-4 text-muted small">Desarrollado con PHP, MySQL & Bootstrap</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous">
    </script>
</body>
</html>