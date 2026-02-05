<?php
// Controllers/HomeController.php
require_once 'Models/MenuModel.php';

class HomeController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        $this->model = new MenuModel();
    }

    public function index() {
        $fechaFiltro = $_GET['fecha'] ?? date('Y-m-d');
        
        // Datos específicos del día filtrado
        $statsHoy = $this->model->obtenerEstadisticas($fechaFiltro);
        
        // Datos generales (histórico)
        $statsGeneral = $this->model->obtenerEstadisticas();
        
        // Lista de fechas para el selector
        $fechas = $this->model->obtenerFechasDisponibles();

        $titulo = "Dashboard General";
        require_once 'Views/admin/menu.php';
    }
}