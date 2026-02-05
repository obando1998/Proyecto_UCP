<?php
require_once 'Config/Conexion.php';

class DevolucionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    // =========================================================
    // 1. ZONA AUXILIAR (Registro de Devoluciones)
    // =========================================================
    
    public function guardar($datos) {
        // SQL ajustado a la estructura completa de tu base de datos
        // Se asume que recibes todos los campos necesarios desde el controlador
        $sql = "INSERT INTO devoluciones (
                    nit, nombre_cliente, direccion, item_producto, descripcion_producto, 
                    unidad, kg, motivo, cantidad_und, cantidad_kg, 
                    observacion, usuario_creador, estado, fecha_creacion, evidencia
                ) VALUES (
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, 
                    ?, ?, 'Pendiente', NOW(), ?
                )";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $datos['nit'],
            $datos['nombre_cliente'],
            $datos['direccion'],
            $datos['item_producto'],
            $datos['descripcion_producto'],
            $datos['unidad'],
            $datos['kg'],
            $datos['motivo'],
            $datos['cantidad_und'],
            $datos['cantidad_kg'],
            $datos['observacion'],
            $datos['usuario_creador'],
            $datos['evidencia'] ?? null // Maneja null si no hay evidencia
        ]);
    }

    // =========================================================
    // 2. ZONA ADMINISTRADOR (Revisión y Aprobación)
    // =========================================================

    public function obtenerPendientes() {
        // Busca todo lo que no esté revisado (Pendiente)
        $stmt = $this->db->prepare("SELECT * FROM devoluciones WHERE estado = 'Pendiente' ORDER BY fecha_creacion ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function procesarRevision($id, $accion, $codigo, $obs, $revisor) {
        try {
            $this->db->beginTransaction();

            // Actualizamos la devolución con la decisión del admin
            $sql = "UPDATE devoluciones 
                    SET estado = ?, 
                        codigo_admin = ?, 
                        observacion_admin = ?, 
                        usuario_revisor = ?, 
                        fecha_revision = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$accion, $codigo, $obs, $revisor, $id]);

            // (Opcional) Aquí podrías insertar una notificación si tu sistema lo requiere
            // $this->crearNotificacion($id, $accion);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // =========================================================
    // 3. ZONA DASHBOARD (Estadísticas Unificadas)
    // =========================================================
    // Este método reemplaza completamente a MenuModel::obtenerEstadisticas
    
    public function obtenerEstadisticas($fecha = null) {
        $where = $fecha ? "WHERE DATE(fecha_creacion) = :fecha" : "";
        
        // Usamos COALESCE para que devuelva 0 en vez de NULL si no hay datos
        $sql = "SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(cantidad_kg), 0) as total_kg,
                    COALESCE(SUM(cantidad_und), 0) as total_und,
                    
                    -- Conteo por Estados
                    COUNT(CASE WHEN estado = 'Pendiente' THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'Aprobado' THEN 1 END) as aprobados,
                    COUNT(CASE WHEN estado = 'Rechazado' THEN 1 END) as rechazados,
                    
                    -- Conteo por Motivos
                    COUNT(CASE WHEN motivo = 'Devolucion' THEN 1 END) as motivo_dev,
                    COUNT(CASE WHEN motivo = 'Faltante' THEN 1 END) as motivo_fal,
                    COUNT(CASE WHEN motivo = 'Sobrante' THEN 1 END) as motivo_sob
                FROM devoluciones 
                $where";

        $stmt = $this->db->prepare($sql);
        if ($fecha) $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Reemplaza a MenuModel::obtenerFechasDisponibles
    public function obtenerFechas() {
        $stmt = $this->db->query("SELECT DISTINCT DATE(fecha_creacion) as fecha FROM devoluciones ORDER BY fecha DESC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}