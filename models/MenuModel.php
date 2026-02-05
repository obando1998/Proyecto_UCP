<?php
// Models/MenuModel.php
require_once 'Config/Conexion.php';

class MenuModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    public function obtenerEstadisticas($fecha = null) {
        $where = $fecha ? "WHERE DATE(fecha_creacion) = :fecha" : "";
        
        $sql = "SELECT 
                    COUNT(*) as total_dev,
                    SUM(cantidad_kg) as total_kg,
                    SUM(cantidad_und) as total_und,
                    COUNT(CASE WHEN estado = 'Pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'Completado' THEN 1 END) as completados
                FROM devoluciones 
                $where";
        
        $stmt = $this->db->prepare($sql);
        if ($fecha) $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function obtenerFechasDisponibles() {
        $stmt = $this->db->prepare("SELECT DISTINCT DATE(fecha_creacion) as fecha FROM devoluciones ORDER BY fecha DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}