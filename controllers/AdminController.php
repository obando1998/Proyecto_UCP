<?php
require_once 'Models/DevolucionModel.php';
require_once 'Models/ConsultaModel.php'; // Para el historial

class AdminController {
    private $model;
    private $consultaModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Verificar Logueo y Permiso (Grado 1)
        if (!isset($_SESSION['logged_in']) || $_SESSION['grado'] != 1) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        $this->model = new DevolucionModel();
        $this->consultaModel = new ConsultaModel();
    }

    public function index() {
        $titulo = "Panel Administrador - DevolutionSync";
        $pendientes = $this->model->obtenerPendientes();
        $historial = $this->consultaModel->obtenerHistorial(50); // Últimos 50
        
        require_once 'Views/admin/panel_administrador.php';
    }

    public function revisar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_devolucion'];
            $accion = $_POST['accion'];
            $codigo = $_POST['codigo_admin'];
            $obs = $_POST['observacion_admin'];
            $revisor = $_SESSION['user'];

            if ($this->model->procesarRevision($id, $accion, $codigo, $obs, $revisor)) {
                // Redirigir con éxito
                header('Location: index.php?url=admin/index&msg=success');
            } else {
                header('Location: index.php?url=admin/index&msg=error');
            }
        }
    }
}