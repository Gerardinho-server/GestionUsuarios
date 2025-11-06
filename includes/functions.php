<?php
// Inicia la sesión al comienzo de casi todos los scripts
function start_session_secure() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Función para verificar si el usuario está logueado
function is_logged_in() {
    start_session_secure();
    // Revisa si la clave 'user_id' existe en la sesión
    return isset($_SESSION['user_id']);
}

// Función de redirección a la página de login si no está autenticado
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

// Función para sanear (sanitizar) datos de entrada
function sanitize_input($data) {
    // Implementación básica: puedes mejorarla según tus necesidades
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}