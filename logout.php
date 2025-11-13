<?php
require_once 'includes/functions.php';

start_session_secure();

// 1. Destruir todas las variables de sesión
$_SESSION = array();

// 2. Si se usa la sesión (cookies), también destruye la cookie de sesión.
// Nota: Esto destruirá la sesión, y no solo los datos de la sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruye la sesión.
session_destroy();

// 4. Redirige al usuario a la página de login.
header("Location: login.php");
exit;
?>