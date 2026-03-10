<?php
// 1. Cargar configuración
require_once 'Config/Conexion.php';

// 2. Iniciar sesión
if (session_status() === PHP_SESSION_NONE) session_start();

// 3. Determinar la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : '';

// 4. Redirigir si la URL está vacía
if (empty($url)) {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        switch ($_SESSION['grado']) {
            case 1: header('Location: index.php?url=home/index'); break;
            case 2: header('Location: index.php?url=panel/auxiliar'); break;
            case 3: header('Location: index.php?url=consulta/index'); break;
            default: header('Location: index.php?url=auth/index'); break;
        }
    } else {
        header('Location: index.php?url=auth/index');
    }
    exit;
}

// 5. Enrutamiento estándar (Descomponer URL)
$urlParts = explode('/', rtrim($url, '/'));
$controllerName = ucfirst($urlParts[0]) . 'Controller';
$method = isset($urlParts[1]) ? $urlParts[1] : 'index';

// 6. Ejecución del Controlador
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
