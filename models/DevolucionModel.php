<?php
// Models/DevolucionModel.php
require_once 'Config/Conexion.php';

class DevolucionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    public function guardar($datos) {
        $sql = "INSERT INTO devoluciones (nit, cliente, direccion, item_producto, descripcion_producto, unidad, kg, motivo, cantidad_und, cantidad_kg, observacion, usuario_creador, estado, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['nit'], $datos['cliente'], $datos['direccion'], $datos['item'], 
            $datos['descripcion'], $datos['unidad'], $datos['kg'], $datos['motivo'],
            $datos['cant_und'], $datos['cant_kg'], $datos['obs'], $datos['usuario']
        ]);
    }
}