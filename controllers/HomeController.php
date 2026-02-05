<?php
// Controllers/HomeController.php
require_once 'Models/DevolucionModel.php';

class HomeController {
    private $model;

    public function __construct() {
        // Iniciamos sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificamos autenticación
        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        
        // Usamos DevolucionModel que ya tiene la lógica de estadísticas centralizada
        $this->model = new DevolucionModel();
    }

    public function index() {
        // Capturar fecha del filtro o usar la actual
        $fechaFiltro = $_GET['fecha'] ?? date('Y-m-d');
        
        // 1. Obtener estadísticas del día seleccionado
        // Nota: Asegúrate de que tu DevolucionModel tenga el método obtenerEstadisticas($fecha)
        $statsHoy = $this->model->obtenerEstadisticas($fechaFiltro);
        
        // 2. Obtener estadísticas históricas (sin pasar fecha)
        $statsGeneral = $this->model->obtenerEstadisticas();
        
        // 3. Obtener fechas disponibles para el select
        $fechas = $this->model->obtenerFechas(); // Método optimizado en DevolucionModel

        $titulo = "Dashboard General - DevolutionSync";
        
        // Cargamos la vista con el nombre estándar de MVC
        require_once 'Views/home/dashboard.php';
    }
}