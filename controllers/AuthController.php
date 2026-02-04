<?php
// Controllers/AuthController.php
require_once 'Models/AuthModel.php';

class AuthController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AuthModel();
    }

    // Muestra la página de login
    public function index() {
        if (isset($_SESSION['logged_in'])) {
            $this->redirigirSegunGrado($_SESSION['grado']);
        }
        require_once 'Views/auth/login.php';
    }

    // Procesa el formulario (AJAX)
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

    private function getRedirectUrl($grado) {
        switch ($grado) {
            case 1: return 'index.php?url=usuario/crear'; // O tu menú principal
            case 2: return 'index.php?url=panel/auxiliar';
            case 3: return 'index.php?url=panel/consulta';
            default: return 'index.php?url=auth/index';
        }
    }
    
    private function redirigirSegunGrado($grado) {
        header('Location: ' . $this->getRedirectUrl($grado));
        exit;
    }
}