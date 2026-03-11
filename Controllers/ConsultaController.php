<?php
// Controllers/ConsultaController.php
require_once 'Models/ConsultaModel.php';

class ConsultaController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        $this->model = new ConsultaModel();
    }

    public function index() {
        $titulo = "Panel Administración - Historial";
        $devoluciones = $this->model->obtenerHistorial();
        require_once 'Views/admin/consulta.php';
    }

    public function auxiliar() {
        $titulo = "Panel Auxiliar - Historial";
        $devoluciones = $this->model->obtenerHistorial();
        require_once 'Views/admin/consulta.php';
    }

    public function detalles() {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            $d = $this->model->obtenerPorId($id);
            if ($d) {
                ob_start();
                ?>
                <div class="detalles-container" style="font-family: sans-serif; font-size: 14px;">

                    <h3 style="color:#ff8c00; border-bottom: 2px solid #ff8c00; padding-bottom:8px; margin-bottom:16px;">
                        📋 Devolución #<?php echo $d['id']; ?>
                    </h3>

                    <!-- Cliente -->
                    <h4 style="margin-bottom:8px;">👤 Información del Cliente</h4>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold; width:40%;">NIT</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['nit'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Cliente</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['nombre_cliente'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold;">Dirección</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['direccion'] ?? 'N/A'); ?></td>
                        </tr>
                    </table>

                    <!-- Producto -->
                    <h4 style="margin-bottom:8px;">📦 Información del Producto</h4>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold; width:40%;">Código Producto</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['codigo_producto'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Descripción</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['descripcion_producto'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold;">Unidad</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['unidad'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">KG por Unidad</td>
                            <td style="padding:8px;"><?php echo $d['kg'] ?? '0'; ?></td>
                        </tr>
                    </table>

                    <!-- Devolución -->
                    <h4 style="margin-bottom:8px;">🔄 Detalles de la Devolución</h4>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold; width:40%;">Motivo</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['motivo'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Cantidad (UND)</td>
                            <td style="padding:8px;"><?php echo $d['cantidad_und'] ?? '0'; ?></td>
                        </tr>
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold;">Cantidad (KG)</td>
                            <td style="padding:8px;"><?php echo $d['cantidad_kg'] ?? '0'; ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Observación</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['observacion'] ?? 'N/A'); ?></td>
                        </tr>
                    </table>

                    <!-- Estado y revisión -->
                    <h4 style="margin-bottom:8px;">✅ Estado y Revisión</h4>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold; width:40%;">Estado</td>
                            <td style="padding:8px;"><strong><?php echo $d['estado'] ?? 'Pendiente'; ?></strong></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Creado por</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['usuario_creador'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold;">Fecha Creación</td>
                            <td style="padding:8px;"><?php echo $d['fecha_creacion'] ? date('d/m/Y H:i', strtotime($d['fecha_creacion'])) : 'N/A'; ?></td>
                        </tr>
                        <?php if (!empty($d['usuario_revisor'])): ?>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Revisado por</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['usuario_revisor']); ?></td>
                        </tr>
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold;">Fecha Revisión</td>
                            <td style="padding:8px;"><?php echo date('d/m/Y H:i', strtotime($d['fecha_revision'])); ?></td>
                        </tr>
                        <tr>
                            <td style="padding:8px; font-weight:bold;">Código Admin</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['codigo_admin']); ?></td>
                        </tr>
                        <tr style="background:#f9f9f9;">
                            <td style="padding:8px; font-weight:bold;">Observación Admin</td>
                            <td style="padding:8px;"><?php echo htmlspecialchars($d['observacion_admin']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>

                    <?php if (!empty($d['evidencia'])): ?>
                    <h4 style="margin-bottom:8px;">📷 Evidencia</h4>
                    <img src="<?php echo htmlspecialchars($d['evidencia']); ?>" 
                         alt="Evidencia" 
                         style="max-width:100%; border-radius:8px; border:1px solid #ddd;">
                    <?php endif; ?>

                </div>
                <?php
                $html = ob_get_clean();
                echo json_encode(['success' => true, 'html' => $html]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
        }
        exit;
    }
}