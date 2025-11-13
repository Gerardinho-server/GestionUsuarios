<?php
// ===============================================
// Archivo: dashboard.php
// Propósito: Panel de usuario con contenido diferenciado por rol y gestión de usuarios para admin.
// ===============================================

// Incluye funciones de autenticación/sesión y la conexión PDO para cargar el rol
require_once 'includes/functions.php';
require_once 'includes/db_connect.php'; 

// Esta función detiene el script y redirige a 'login.php' 
// Asegura que $_SESSION['username'] y $_SESSION['role'] estén cargados.
require_login(); 

$username = $_SESSION['username'] ?? 'Usuario Desconocido';
$role = $_SESSION['role'] ?? 'guest'; 

$is_admin = ($role === 'admin');
$users = []; // Inicializamos el array de usuarios

// Lógica de administrador: Obtener todos los usuarios si es admin
if ($is_admin && $pdo !== null) {
    try {
        // Obtenemos todos los campos, excepto el hash de la contraseña por seguridad
        $sql_users = "SELECT id, username, email, is_active, role, created_at FROM users ORDER BY created_at DESC";
        $stmt_users = $pdo->query($sql_users);
        $users = $stmt_users->fetchAll();
    } catch (PDOException $e) {
        // En caso de error de BD, el admin verá un mensaje
        $users = [];
        $admin_error = "Error al cargar los usuarios: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .content-area { flex-grow: 1; padding: 20px; }
        /* Estilo para que la columna de acciones no sea demasiado ancha */
        .action-column { width: 150px; } 
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <?php echo $is_admin ? '🛡️ Panel de Administrador' : '🏠 Dashboard de Usuario'; ?>
            </a>
            
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item me-3">
                        <span class="navbar-text text-white-50">
                            Rol: <span class="badge bg-<?php echo $is_admin ? 'danger' : 'success'; ?>"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> 
                            **<?php echo htmlspecialchars($username); ?>**
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li><h6 class="dropdown-header">Opciones de Cuenta</h6></li>
                            <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="content-area container-fluid bg-light">
        <h1 class="mb-4">Bienvenido, <?php echo htmlspecialchars($username); ?></h1>

        <?php 
        // Parámetros usados por delete_user.php o edit_user.php para notificar resultados
        $delete_status = $_GET['delete_status'] ?? null;
        $message = $_GET['message'] ?? null;
        
        if ($delete_status && $message): 
            // Determina la clase de Bootstrap basada en el estado
            $alert_class = match($delete_status) {
                'success' => 'alert-success',
                'error' => 'alert-danger',
                'warning' => 'alert-warning',
                default => 'alert-info',
            };
            // Determina el ícono de Bootstrap
            $icon_class = match($delete_status) {
                'success' => 'bi-check-circle-fill',
                'error' => 'bi-x-octagon-fill',
                'warning' => 'bi-exclamation-triangle-fill',
                default => 'bi-info-circle-fill',
            };
        ?>
            <div class="alert <?php echo $alert_class; ?> d-flex align-items-center mb-4" role="alert">
                <i class="bi <?php echo $icon_class; ?> me-2"></i>
                <div>
                    **Mensaje del Sistema:** <?php echo htmlspecialchars($message); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="user-home-tab" data-bs-toggle="tab" data-bs-target="#user-home" type="button" role="tab" aria-controls="user-home" aria-selected="true">
                    <i class="bi bi-person me-1"></i> Mi Área
                </button>
            </li>
            
            <?php if ($is_admin): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="admin-tools-tab" data-bs-toggle="tab" data-bs-target="#admin-tools" type="button" role="tab" aria-controls="admin-tools" aria-selected="false">
                    <i class="bi bi-tools me-1"></i> Herramientas de Admin
                </button>
            </li>
            <?php endif; ?>

            </ul>
        
        <div class="tab-content pt-3" id="myTabContent">
            
            <div class="tab-pane fade show active" id="user-home" role="tabpanel" aria-labelledby="user-home-tab">
                <div class="card shadow p-4">
                    <h2 class="card-title text-primary mb-3">Tu Resumen</h2>
                    <p class="lead">Aquí puedes ver tus tareas, mensajes y actividades recientes.</p>
                    <p>Tu rol actual es: <strong><?php echo htmlspecialchars(ucfirst($role)); ?></strong></p>
                    <a href="#" class="btn btn-primary mt-2">Ver Actividad Completa</a>
                </div>
            </div>

            <?php if ($is_admin): ?>
            <div class="tab-pane fade" id="admin-tools" role="tabpanel" aria-labelledby="admin-tools-tab">
                <div class="card shadow p-4 bg-light">
                    <h2 class="card-title text-danger mb-4"><i class="bi bi-people-fill me-2"></i> Gestión de Usuarios</h2>
                    
                    <?php if (isset($admin_error)): ?>
                        <div class="alert alert-warning"><?php echo htmlspecialchars($admin_error); ?></div>
                    <?php elseif (empty($users)): ?>
                        <div class="alert alert-info">No hay usuarios registrados en el sistema.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Registro</th>
                                        <th class="action-column">Acciones (CRUD)</th> </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($user['role'] == 'admin') ? 'danger' : 'success'; ?>">
                                                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date("Y-m-d", strtotime($user['created_at'])); ?></td>
                                        
                                        <td class="action-column">
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info text-white me-1" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar a este usuario?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                        
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

            </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>