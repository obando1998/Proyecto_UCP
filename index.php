<?php
/**
 * ARCHIVO: index.php (RAÍZ)
 * FUNCIÓN: Enrutador principal del sistema MVC
 */

// 1. Cargar configuración base (Asegúrate de que la ruta sea correcta)
require_once 'Config/Conexion.php';

// 2. Iniciar sesión de forma global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Determinar la URL solicitada
// Si no hay URL, enviamos al Dashboard (home/index) o al Login según sesión
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $url = 'home/index';
    } else {
        $url = 'auth/index';
    }
}

// 4. Descomponer la URL (ejemplo: 'home/index' -> ['home', 'index'])
$urlParts = explode('/', rtrim($url, '/'));

$controllerName = ucfirst($urlParts[0]) . 'Controller'; // Ej: HomeController
$method = isset($urlParts[1]) ? $urlParts[1] : 'index'; // Ej: index

// 5. Cargar el archivo del controlador
$controllerPath = 'Controllers/' . $controllerName . '.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    
    // Verificar si la clase existe dentro del archivo
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        
        // Ejecutar el método si existe
        if (method_exists($controller, $method)) {
            $controller->{$method}();
        } else {
            // Error: El método no existe
            header("HTTP/1.0 404 Not Found");
            echo "<h1>404 - El método '$method' no existe en el controlador '$controllerName'.</h1>";
        }
    } else {
        echo "<h1>Error: La clase '$controllerName' no está definida.</h1>";
    }
} else {
    // Error: El controlador no existe
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - No se encontró el controlador '$controllerName'.</h1>";
    echo "<p>Ruta buscada: $controllerPath</p>";
}