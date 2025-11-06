<?php
// ===============================================
// Archivo: includes/functions.php
// Propósito: Funciones de utilidad y seguridad.
// ===============================================

/**
 * Inicia una sesión PHP de forma segura, estableciendo cookies seguras.
 */
function start_session_secure() {
    // Configura parámetros de cookies de sesión más seguros
    // Usar solo cookies
    ini_set('session.use_only_cookies', 1);
    // Hace la cookie inaccesible a JavaScript
    ini_set('session.cookie_httponly', 1); 
    // Asegura la transmisión solo por HTTPS (si estás en producción con SSL)
    ini_set('session.cookie_secure', false); // Cambiar a 'true' en producción con HTTPS
    
    // Inicia la sesión si no está activa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Limpia y sanea los datos de entrada del usuario.
 * @param string $data El dato a sanear.
 * @return string El dato saneado.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // Usa ENT_QUOTES para codificar tanto comillas simples como dobles
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); 
    return $data;
}

/**
 * Verifica si el usuario está actualmente logueado.
 * @return bool True si la sesión contiene un 'user_id', False de lo contrario.
 */
function is_logged_in() {
    // Llamar a start_session_secure() si no se ha hecho
    if (session_status() == PHP_SESSION_NONE) {
        start_session_secure();
    }
    return isset($_SESSION['user_id']);
}

/**
 * Verifica si el usuario está logueado y carga el rol y username si es necesario (lazy loading).
 * Redirige a login.php si no está autenticado.
 */
function require_login() {
    start_session_secure();
    
    // Si no hay ID de usuario o el usuario está inactivo, redirigir inmediatamente
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Lazy load: Si el rol o username no están en la sesión, los cargamos de la BD
    if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
        
        // Incluir la conexión PDO. Usamos el global $pdo.
        // ADVERTENCIA: Esta función asume que el script principal ya ha incluido db_connect.php,
        // pero la incluimos aquí como fallback si es necesario.
        global $pdo; 
        if (!isset($pdo)) {
             // Esto intenta cargar $pdo, pero no es la mejor práctica si el archivo no existe o falla.
             @require_once 'includes/db_connect.php'; 
        }
        
        if ($pdo === null || !isset($pdo)) {
            // Error crítico si la BD no está disponible
            error_log("Fallo al cargar rol en require_login: BD no disponible.");
            // En un error de BD en este punto, destruimos y redirigimos.
            session_destroy();
            header('Location: login.php'); 
            exit;
        }

        try {
            $sql = "SELECT username, role, is_active FROM users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch();

            if ($user_data && $user_data['is_active']) {
                // Actualizar la sesión con el username y el rol
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];
            } else {
                // Usuario no encontrado o inactivo
                session_destroy();
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log("Error de BD al cargar rol: " . $e->getMessage());
            session_destroy();
            header('Location: login.php');
            exit;
        }
    }
}

/**
 * Requiere que el usuario sea administrador. Llama a require_login() primero.
 */
function require_admin() {
    require_login(); // Asegura que el usuario esté logueado y la sesión tenga el rol cargado

    // Después de require_login(), $_SESSION['role'] siempre debe estar disponible.
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Podrías redirigir a una página de acceso denegado o al dashboard
        header('Location: dashboard.php'); 
        exit;
    }
}