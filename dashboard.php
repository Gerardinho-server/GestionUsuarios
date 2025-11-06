<?php
require_once 'includes/functions.php';

// Esta función detiene el script y redirige a 'login.php' 
// si el usuario no tiene una sesión activa ('user_id' en $_SESSION).
require_login(); 

// Si el script llega a este punto, significa que el usuario está logueado.
// Recuperamos el nombre de usuario de la sesión para mostrarlo.
$username = $_SESSION['username'] ?? 'Usuario Desconocido';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-5 text-center" style="max-width: 600px; width: 100%;">
            <h1 class="card-title text-success mb-4">🎉 ¡Bienvenido al Dashboard!</h1>
            <p class="fs-4">Has iniciado sesión correctamente, **<?php echo htmlspecialchars($username); ?>**.</p>
            
            <a href="logout.php" class="btn btn-danger mt-4">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>