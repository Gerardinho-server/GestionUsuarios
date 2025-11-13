<?php
// ===============================================
// Archivo: perfil.php (Anteriormente perfil_admin.php)
// Propósito: Muestra la información de perfil del usuario logueado con diseño mejorado.
// ===============================================

require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; // Incluye la conexión PDO ($pdo)

start_session_secure();

// 1. **SEGURIDAD CRÍTICA:** Asegura que el usuario esté logueado
require_login(); 

// 2. Obtener el ID del usuario de la sesión
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Usuario';
$role = $_SESSION['role'] ?? 'guest';
$profile_data = null;
$error_message = '';

if ($pdo === null) {
    $error_message = "Error FATAL: No se pudo conectar a la base de datos.";
} elseif (!$user_id) {
    $error_message = "Error: ID de usuario no encontrado en la sesión.";
} else {
    // 3. Consultar la base de datos para obtener los detalles del usuario
    $sql = "SELECT id, username, email, role, is_active, created_at FROM users WHERE id = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $profile_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$profile_data) {
            $error_message = "No se encontraron datos para este usuario.";
        }
        
    } catch (PDOException $e) {
        $error_message = "Error al cargar el perfil: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo htmlspecialchars(ucfirst($username)); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .profile-header {
            background-color: #343a40; /* Dark background */
            color: #ffffff;
            padding: 30px;
            border-radius: .375rem .375rem 0 0;
            margin-bottom: 0;
        }
        .info-card {
            border-top: none;
            border-radius: 0 0 .375rem .375rem;
        }
        .list-group-item strong {
            color: #495057;
        }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <span class="navbar-text text-white">
                <span class="badge bg-<?php echo ($role === 'admin') ? 'danger' : 'success'; ?> me-2"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
                <?php echo htmlspecialchars($username); ?>
            </span>
        </div>
    </nav>
    
    <div class="container py-5">
        <div class="card shadow-lg mx-auto" style="max-width: 600px;">
            
            <div class="profile-header text-center">
                <i class="bi bi-person-circle display-4 mb-2"></i>
                <h1 class="h3 mb-0">Perfil de Usuario</h1>
                <p class="text-white-50 mt-1">Gestión de tu cuenta principal</p>
            </div>
            
            <div class="card-body info-card">
                
                <?php 
                // Muestra mensajes de éxito/error después de una actualización (redirigidos desde edit_profile.php)
                $status = $_GET['delete_status'] ?? null;
                $message = $_GET['message'] ?? null;
                if ($status && $message):
                    $alert_class = ($status === 'success') ? 'alert-success' : 'alert-danger';
                ?>
                    <div class="alert <?php echo $alert_class; ?> d-flex align-items-center mb-4" role="alert">
                        <i class="bi <?php echo ($status === 'success') ? 'bi-check-circle-fill' : 'bi-x-octagon-fill'; ?> me-2"></i>
                        <div>
                            <?php echo htmlspecialchars(urldecode($message)); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-outline-secondary">← Volver al Dashboard</a></p>
                <?php elseif ($profile_data): ?>
                    
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            **Nombre de Usuario:**
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($profile_data['username']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            **Correo Electrónico:**
                            <span class="text-muted"><?php echo htmlspecialchars($profile_data['email']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            **Rol Asignado:**
                            <span class="badge fs-6 bg-<?php echo ($profile_data['role'] == 'admin') ? 'danger' : 'success'; ?>">
                                <?php echo htmlspecialchars(ucfirst($profile_data['role'])); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            **Estado de la Cuenta:**
                            <span class="badge bg-<?php echo $profile_data['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $profile_data['is_active'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            **Miembro Desde:**
                            <span class="text-secondary small"><?php echo date("d/m/Y H:i", strtotime($profile_data['created_at'])); ?></span>
                        </li>
                    </ul>
                    
                    <div class="d-grid gap-2">
                        <a href="edit_profile.php?id=<?php echo $profile_data['id']; ?>" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-pencil-square me-2"></i> Modificar Datos
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            ← Volver al Dashboard
                        </a>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>