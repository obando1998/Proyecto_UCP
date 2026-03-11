<?php
// Controllers/AuthController.php
require_once 'Models/AuthModel.php';

class AuthController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AuthModel();
    }

    public function index() {
        if (isset($_SESSION['logged_in'])) {
            $this->redirigirSegunGrado($_SESSION['grado']);
            return;
        }
        require_once 'Views/auth/login.php';
    }

    public function login() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->model->buscarUsuario($username);

            if ($user && $password === $user['PAS']) {
                $_SESSION['user'] = $user['USR'];
                $_SESSION['nombre'] = $user['NOMBRE'];
                $_SESSION['grado'] = $user['GRADO'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                
                session_regenerate_id(true);

                echo json_encode([
                    'success' => true,
                    'redirect' => $this->getRedirectUrl($user['GRADO'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: index.php?url=auth/index');
        exit;
    }

    // ESTE ES EL MÉTODO QUE CORREGIMOS
    private function getRedirectUrl($grado) {
        switch ($grado) {
            case 1: 
                // Antes decía 'usuario/crear', ahora lo enviamos al Dashboard
                return 'index.php?url=home/index'; 
            case 2: 
                // Ajusta esto según el nombre de tu controlador de devoluciones
                return 'index.php?url=devolucion/crear'; 
            case 3: 
                return 'index.php?url=consulta/index';
            default: 
                return 'index.php?url=auth/index';
        }
    }

    private function redirigirSegunGrado($grado) {
        header('Location: ' . $this->getRedirectUrl($grado));
        exit;
    }
}