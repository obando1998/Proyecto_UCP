<?php
// Models/ConsultaModel.php
require_once 'Config/Conexion.php';

class ConsultaModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    public function obtenerHistorial($limite = 100) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM devoluciones ORDER BY fecha_creacion DESC LIMIT ?");
            $stmt->bindValue(1, $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en ConsultaModel: " . $e->getMessage());
            return [];
        }
    }
	
	// método para buscar una devolución específica por su ID
	
	public function obtenerPorId($id) {
		$stmt = $this->db->prepare("SELECT * FROM devoluciones WHERE id = ?");
		$stmt->execute([$id]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}