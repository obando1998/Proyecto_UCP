<?php
// 1. Cargar configuración
require_once 'Config/Conexion.php';

// 2. Iniciar sesión
if (session_status() === PHP_SESSION_NONE) session_start();

// 3. Determinar la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($url)) {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        // --- LÓGICA DE REDIRECCIÓN POR GRADO ---
        switch ($_SESSION['GRADO']) {
            case 1: 
                $url = 'home/index';      // Admin va al Dashboard (antiguo menu.php)
                break;
            case 2: 
                $url = 'devolucion/crear'; // Auxiliar va directo a registrar (antiguo panel_auxiliar.php)
                break;
            case 3: 
                $url = 'consulta/index';   // Consulta va directo al historial
                break;
            default:
                $url = 'auth/index';
        }
    } else {
        $url = 'auth/index';
    }
}

// 4. Enrutamiento estándar (Descomponer URL)
$urlParts = explode('/', rtrim($url, '/'));
$controllerName = ucfirst($urlParts[0]) . 'Controller';
$method = isset($urlParts[1]) ? $urlParts[1] : 'index';

// 5. Ejecución del Controlador
$controllerPath = 'Controllers/' . $controllerName . '.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $method)) {
            $controller->{$method}();
        } else {
            die("Error: El método '$method' no existe.");
        }
    }
} else {
    die("Error: El controlador '$controllerName' no existe en $controllerPath");
}