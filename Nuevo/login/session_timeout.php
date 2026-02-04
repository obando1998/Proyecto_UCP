<?php
// session_timeout.php - Control de timeout de sesión

// Solo iniciar sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tiempo de inactividad en segundos (3 minutos)
$inactivity_timeout = 180;

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Limpiar cualquier sesión existente
    session_unset();
    session_destroy();
    
    // Redirigir al login - RUTA RELATIVA
    header('Location: login/login.html');
    exit;
}

// Verificar inactividad
if (isset($_SESSION['last_activity'])) {
    $session_duration = time() - $_SESSION['last_activity'];
    
    if ($session_duration > $inactivity_timeout) {
        // Destruir sesión y redirigir por timeout
        session_unset();
        session_destroy();
        
        // Redirigir al login con parámetro de timeout - RUTA RELATIVA
        header('Location: login/login.html?timeout=1');
        exit;
    }
}

// Actualizar tiempo de última actividad
$_SESSION['last_activity'] = time();

// Regenerar ID de sesión periódicamente para prevenir fixation
if (!isset($_SESSION['regenerated_time'])) {
    $_SESSION['regenerated_time'] = time();
} else {
    $regenerate_interval = 300; // 5 minutos
    if (time() - $_SESSION['regenerated_time'] > $regenerate_interval) {
        session_regenerate_id(true);
        $_SESSION['regenerated_time'] = time();
    }
}

// Verificar que el usuario tenga los datos mínimos requeridos
if (!isset($_SESSION['user']) || !isset($_SESSION['grado']) || !isset($_SESSION['nombre'])) {
    // Datos de sesión corruptos, redirigir a login
    session_unset();
    session_destroy();
    
    // RUTA RELATIVA
    header('Location: login/login.html?error=session_corrupted');
    exit;
}
?>