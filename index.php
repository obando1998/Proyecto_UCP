<?php
// 1. Cargar configuración base y utilidades
require_once 'Config/Conexion.php';

// 2. Iniciar sesión de forma global
if (session_status() === PHP_SESSION_NONE) session_start();

// 3. Capturar la URL (ejemplo: usuario/crear o consulta/admin)
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/index';
$url = explode('/', rtrim($url, '/'));

// 4. Determinar Controlador y Método
$controllerName = ucfirst($url[0]) . 'Controller'; // Ejemplo: ConsultaController
$method = isset($url[1]) ? $url[1] : 'index';       // Ejemplo: admin

// 5. Cargar el archivo del controlador
$controllerPath = 'Controllers/' . $controllerName . '.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    $controller = new $controllerName();
    
    // Ejecutar el método
    if (method_exists($controller, $method)) {
        $controller->{$method}();
    } else {
        die("Error: El método '$method' no existe.");
    }
} else {
    die("Error: El controlador '$controllerName' no existe.");
}