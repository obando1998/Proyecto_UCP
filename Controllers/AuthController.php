<?php
// Controllers/AuthController.php
require_once 'Models/AuthModel.php';

class AuthController {
    private $model;
    private $recaptchaSecret = '6LcUafsrAAAAAL2xMNSvimYvzrMlC3YFSgUJGQPx';

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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        // 1. Verificar reCAPTCHA en el servidor
        $captchaToken = $_POST['recaptcha_token'] ?? '';

        if (empty($captchaToken)) {
            echo json_encode(['success' => false, 'message' => 'Por favor completa el reCAPTCHA']);
            return;
        }

        if (!$this->verificarCaptcha($captchaToken)) {
            echo json_encode(['success' => false, 'message' => 'Verificación reCAPTCHA fallida. Intenta de nuevo']);
            return;
        }

        // 2. Validar credenciales
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son obligatorios']);
            return;
        }

        $user = $this->model->buscarUsuario($username);

        if ($user && $password === $user['PAS']) {
            $_SESSION['user']          = $user['USR'];
            $_SESSION['nombre']        = $user['NOMBRE'];
            $_SESSION['grado']         = $user['GRADO'];
            $_SESSION['logged_in']     = true;
            $_SESSION['last_activity'] = time();

            session_regenerate_id(true);

            echo json_encode([
                'success'  => true,
                'redirect' => $this->getRedirectUrl($user['GRADO'])
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: index.php?url=auth/index');
        exit;
    }

    // ── Verificación reCAPTCHA con la API de Google ──────────
    private function verificarCaptcha($token) {
        try {
            $url      = 'https://www.google.com/recaptcha/api/siteverify';
            $data     = [
                'secret'   => $this->recaptchaSecret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ];

            $opciones = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context  = stream_context_create($opciones);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                error_log('reCAPTCHA: no se pudo conectar con Google');
                return false;
            }

            $result = json_decode($response, true);
            return isset($result['success']) && $result['success'] === true;

        } catch (Exception $e) {
            error_log('reCAPTCHA error: ' . $e->getMessage());
            return false;
        }
    }

    // ── Redirección según grado ──────────────────────────────
    private function getRedirectUrl($grado) {
        switch ($grado) {
            case 1:  return 'index.php?url=home/index';
            case 2:  return 'index.php?url=panel/auxiliar';
            case 3:  return 'index.php?url=consulta/index';
            default: return 'index.php?url=auth/index';
        }
    }

    private function redirigirSegunGrado($grado) {
        header('Location: ' . $this->getRedirectUrl($grado));
        exit;
    }
}