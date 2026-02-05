<?php
// Controllers/AdminController.php
require_once 'Models/DevolucionModel.php';
require_once 'Models/ConsultaModel.php';

class AdminController {
    private $model;
    private $consultaModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificar autenticación y permisos (Solo Grado 1 - Administrador)
        if (!isset($_SESSION['logged_in']) || $_SESSION['grado'] != 1) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        
        $this->model = new DevolucionModel();
        $this->consultaModel = new ConsultaModel();
    }

    /**
     * Mostrar panel principal de administración
     */
    public function index() {
        $titulo = "Panel Administrador - DevolutionSync";
        
        // Obtener devoluciones pendientes
        $pendientes = $this->model->obtenerPendientes();
        
        // Obtener historial reciente (últimos 50 registros)
        $historial = $this->consultaModel->obtenerHistorial(50);
        
        // Cargar la vista
        require_once 'Views/admin/panel_administrador.php';
    }

    /**
     * Procesar revisión de devolución (Aprobar o Rechazar)
     */
    public function revisar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar datos recibidos
                $id = intval($_POST['id_devolucion'] ?? 0);
                $accion = trim($_POST['accion'] ?? '');
                $codigo = trim($_POST['codigo_admin'] ?? '');
                $obs = trim($_POST['observacion_admin'] ?? '');
                $revisor = $_SESSION['user'] ?? $_SESSION['nombre'];
                
                // Validaciones
                if ($id <= 0) {
                    throw new Exception('ID de devolución inválido');
                }
                
                if (!in_array($accion, ['aprobado', 'rechazado'])) {
                    throw new Exception('Acción inválida. Debe ser "aprobado" o "rechazado"');
                }
                
                if (empty($codigo)) {
                    throw new Exception('El código de autorización es obligatorio');
                }
                
                if (empty($obs)) {
                    throw new Exception('Las observaciones son obligatorias');
                }
                
                // Procesar la revisión
                $resultado = $this->model->procesarRevision($id, $accion, $codigo, $obs, $revisor);
                
                if ($resultado) {
                    // Intentar crear notificación (opcional, si existe el modelo)
                    $this->crearNotificacion($id, $accion, $obs);
                    
                    // Redirigir con mensaje de éxito
                    header('Location: index.php?url=admin/index&msg=success');
                } else {
                    throw new Exception('Error al procesar la revisión en la base de datos');
                }
                
            } catch (Exception $e) {
                // Log del error
                error_log("Error en AdminController::revisar - " . $e->getMessage());
                
                // Redirigir con mensaje de error
                header('Location: index.php?url=admin/index&msg=error');
            }
            exit;
        } else {
            // Si no es POST, redirigir al panel
            header('Location: index.php?url=admin/index');
            exit;
        }
    }

    /**
     * Crear notificación para el usuario que creó la devolución (opcional)
     */
    private function crearNotificacion($idDevolucion, $accion, $observacion) {
        try {
            // Verificar si existe el modelo de notificaciones
            if (!class_exists('NotificacionModel')) {
                return; // Si no existe, simplemente retornar
            }
            
            require_once 'Models/NotificacionModel.php';
            $notifModel = new NotificacionModel();
            
            // Obtener información de la devolución
            $devolucion = $this->consultaModel->obtenerPorId($idDevolucion);
            
            if ($devolucion && isset($devolucion['usuario_creador'])) {
                $estadoTexto = ($accion == 'aprobado') ? 'APROBADA ✅' : 'RECHAZADA ❌';
                $mensaje = "Tu devolución #{$idDevolucion} ha sido {$estadoTexto}. Observación: {$observacion}";
                
                $notifModel->crear([
                    'id_devolucion' => $idDevolucion,
                    'mensaje' => $mensaje,
                    'usuario_destino' => $devolucion['usuario_creador'],
                    'tipo' => $accion,
                    'leida' => false
                ]);
            }
        } catch (Exception $e) {
            // Solo registrar el error, no detener el flujo
            error_log("Error al crear notificación: " . $e->getMessage());
        }
    }

    /**
     * Ver estadísticas del panel (opcional, para futuras mejoras)
     */
    public function estadisticas() {
        $titulo = "Estadísticas - Panel Administrador";
        
        // Obtener estadísticas generales
        $stats = [
            'total_pendientes' => count($this->model->obtenerPendientes()),
            'total_aprobadas' => $this->model->contarPorEstado('aprobado'),
            'total_rechazadas' => $this->model->contarPorEstado('rechazado'),
            'promedio_revision' => $this->model->obtenerPromedioRevision()
        ];
        
        require_once 'Views/admin/estadisticas.php';
    }
}