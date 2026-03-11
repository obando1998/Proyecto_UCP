<?php
require_once 'Config/Conexion.php';

class DevolucionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    // =========================================================
    // 1. ZONA AUXILIAR - Guardar devolución
    //    Retorna el ID insertado (o false si falla)
    // =========================================================
    public function guardar($datos) {
        $sql = "INSERT INTO devoluciones (
                    nit, nombre_cliente, direccion, correo_solicitante,
                    codigo_producto, descripcion_producto,
                    unidad, kg, motivo, cantidad_und, cantidad_kg,
                    observacion, usuario_creador, estado, fecha_creacion, evidencia
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, 'Pendiente', NOW(), ?
                )";

        $stmt = $this->db->prepare($sql);
        $ok   = $stmt->execute([
            $datos['nit'],
            $datos['nombre_cliente'],
            $datos['direccion'],
            $datos['correo_solicitante'] ?? null,
            $datos['item_producto'],
            $datos['descripcion_producto'],
            $datos['unidad'],
            $datos['kg'],
            $datos['motivo'],
            $datos['cantidad_und'],
            $datos['cantidad_kg'],
            $datos['observacion'],
            $datos['usuario_creador'],
            $datos['evidencia'] ?? null
        ]);

        return $ok ? $this->db->lastInsertId() : false;
    }

    // =========================================================
    // 2. ZONA ADMINISTRADOR - Revisión y Aprobación
    // =========================================================
    public function obtenerPendientes() {
        $stmt = $this->db->prepare("SELECT * FROM devoluciones WHERE estado = 'Pendiente' ORDER BY fecha_creacion ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function procesarRevision($id, $accion, $codigo, $obs, $revisor) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE devoluciones 
                    SET estado = ?, 
                        codigo_admin = ?, 
                        observacion_admin = ?, 
                        usuario_revisor = ?, 
                        fecha_revision = NOW() 
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$accion, $codigo, $obs, $revisor, $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en procesarRevision: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // 3. ZONA DASHBOARD - Estadísticas
    // =========================================================
    public function obtenerEstadisticas($fecha = null) {
        $where = $fecha ? "WHERE DATE(fecha_creacion) = :fecha" : "";

        $sql = "SELECT 
                    COUNT(*) as total,
                    COALESCE(SUM(cantidad_kg), 0) as total_kg,
                    COALESCE(SUM(cantidad_und), 0) as total_und,
                    COUNT(CASE WHEN estado = 'Pendiente'  THEN 1 END) as pendientes,
                    COUNT(CASE WHEN estado = 'aprobado'   THEN 1 END) as aprobados,
                    COUNT(CASE WHEN estado = 'rechazado'  THEN 1 END) as rechazados,
                    COUNT(CASE WHEN motivo = 'devolucion' THEN 1 END) as motivo_dev,
                    COUNT(CASE WHEN motivo = 'faltante'   THEN 1 END) as motivo_fal,
                    COUNT(CASE WHEN motivo = 'sobrante'   THEN 1 END) as motivo_sob
                FROM devoluciones $where";

        $stmt = $this->db->prepare($sql);
        if ($fecha) $stmt->bindValue(':fecha', $fecha);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerFechas() {
        $stmt = $this->db->query("SELECT DISTINCT DATE(fecha_creacion) as fecha FROM devoluciones ORDER BY fecha DESC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}