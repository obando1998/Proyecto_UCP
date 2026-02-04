<?php
// Controllers/UsuarioController.php
require_once 'Models/UsuarioModel.php';

class UsuarioController {
    private $model;

    public function __construct() {
        // Aquí podrías incluir la lógica de session_timeout.php
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['logged_in']) || $_SESSION['grado'] != 1) {
            header('Location: index.php?url=login/index');
            exit;
        }
        $this->model = new UsuarioModel();
    }

    public function crear() {
        $mensaje = '';
        $tipoMensaje = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
            $usr = strtoupper(trim($_POST['usr']));
            $pas = trim($_POST['pas']);
            $nombre = strtoupper(trim($_POST['nombre']));
            $grado = intval($_POST['grado']);

            if (empty($usr) || empty($pas) || empty($nombre) || empty($grado)) {
                $mensaje = 'Todos los campos son obligatorios';
                $tipoMensaje = 'error';
            } elseif ($this->model->existeUsuario($usr)) {
                $mensaje = 'El usuario ya existe en el sistema';
                $tipoMensaje = 'error';
            } else {
                if ($this->model->guardar($usr, $pas, $nombre, $grado)) {
                    $mensaje = 'Usuario creado exitosamente';
                    $tipoMensaje = 'success';
                }
            }
        }

        $usuarios = $this->model->listarTodos();
        // Cargamos la vista pasándole los datos
        require_once 'Views/admin/crearUsuario.php';
    }
}