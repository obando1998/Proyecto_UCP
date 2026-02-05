<?php
// Controllers/ConsultaController.php
require_once 'Models/ConsultaModel.php';

class ConsultaController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad: Si no está logueado, al login
        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        $this->model = new ConsultaModel();
    }

    // Ruta: index.php?url=consulta/index (Para Admin)
    public function index() {
        $titulo = "Panel Administración - Historial";
        $devoluciones = $this->model->obtenerHistorial();
        require_once 'Views/admin/consulta.php';
    }

    // Ruta: index.php?url=consulta/auxiliar
    public function auxiliar() {
        $titulo = "Panel Auxiliar - Historial";
        $devoluciones = $this->model->obtenerHistorial();
        require_once 'Views/admin/consulta.php'; // Pueden compartir la misma vista
    }
	
	// método detalles
	public function detalles() {
		header('Content-Type: application/json');
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

		if ($id > 0) {
			$data = $this->model->obtenerPorId($id);
			if ($data) {
				// Aquí puedes procesar el HTML tal como lo tenías en obtener_detalles.php
				// O mejor aún, enviar solo los datos y que JS arme el HTML
				ob_start();
				?>
				<div class="detalles-container">
					<h3>👤 Información: <?php echo htmlspecialchars($data['cliente']); ?></h3>
					<p><strong>Estado:</strong> <?php echo $data['estado']; ?></p>
					<p><strong>Observaciones:</strong> <?php echo htmlspecialchars($data['observaciones']); ?></p>
					</div>
				<?php
				$html = ob_get_clean();
				echo json_encode(['success' => true, 'html' => $html]);
			} else {
				echo json_encode(['success' => false, 'message' => 'No encontrado']);
			}
		}
		exit;
	}
}