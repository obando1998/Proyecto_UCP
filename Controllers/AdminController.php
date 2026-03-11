<?php
// Controllers/AdminController.php
require_once 'Models/DevolucionModel.php';
require_once 'Models/ConsultaModel.php';
require_once 'Config/EmailHelper.php';

class AdminController {
    private $model;
    private $consultaModel;
    private $emailHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['logged_in']) || $_SESSION['grado'] != 1) {
            header('Location: index.php?url=auth/index');
            exit;
        }

        $this->model         = new DevolucionModel();
        $this->consultaModel = new ConsultaModel();
        $this->emailHelper   = new EmailHelper();
    }

    public function index() {
        $titulo     = "Panel Administrador - DevolutionSync";
        $pendientes = $this->model->obtenerPendientes();
        $historial  = $this->consultaModel->obtenerHistorial(50);
        require_once 'Views/admin/panel_administrador.php';
    }

    public function revisar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id      = intval($_POST['id_devolucion'] ?? 0);
                $accion  = trim($_POST['accion'] ?? '');
                $codigo  = trim($_POST['codigo_admin'] ?? '');
                $obs     = trim($_POST['observacion_admin'] ?? '');
                $revisor = $_SESSION['user'] ?? $_SESSION['nombre'];

                if ($id <= 0) {
                    throw new Exception('ID de devolución inválido');
                }

                if (!in_array($accion, ['aprobado', 'rechazado'])) {
                    throw new Exception('Acción inválida');
                }

                if (empty($codigo)) {
                    throw new Exception('El código de autorización es obligatorio');
                }

                if (empty($obs)) {
                    throw new Exception('Las observaciones son obligatorias');
                }

                // Obtener datos de la devolución ANTES de actualizarla (necesitamos el correo)
                $devolucion = $this->consultaModel->obtenerPorId($id);

                // Procesar la revisión en BD
                $resultado = $this->model->procesarRevision($id, $accion, $codigo, $obs, $revisor);

                if ($resultado) {
                    // Notificar al solicitante si tiene correo registrado
                    if (!empty($devolucion['correo_solicitante'])) {
                        $this->emailHelper->notificarEstadoDevolucion($devolucion, $accion, $obs);
                    }

                    header('Location: index.php?url=admin/index&msg=success');
                } else {
                    throw new Exception('Error al procesar la revisión en la base de datos');
                }

            } catch (Exception $e) {
                error_log("Error en AdminController::revisar - " . $e->getMessage());
                header('Location: index.php?url=admin/index&msg=error');
            }
            exit;
        } else {
            header('Location: index.php?url=admin/index');
            exit;
        }
    }

    public function estadisticas() {
        $titulo = "Estadísticas - Panel Administrador";
        $stats  = [
            'total_pendientes'  => count($this->model->obtenerPendientes()),
            'total_aprobadas'   => $this->model->contarPorEstado('aprobado'),
            'total_rechazadas'  => $this->model->contarPorEstado('rechazado'),
            'promedio_revision' => $this->model->obtenerPromedioRevision()
        ];
        require_once 'Views/admin/estadisticas.php';
    }
}