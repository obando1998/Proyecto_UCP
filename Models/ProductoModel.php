<?php
// Models/ProductoModel.php
require_once 'Config/Conexion.php';

class ProductoModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    public function listarTodos() {
        $stmt = $this->db->prepare("SELECT item, descripcion FROM producto ORDER BY descripcion ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerPorItem($item) {
        $stmt = $this->db->prepare("SELECT item, descripcion FROM producto WHERE item = ?");
        $stmt->execute([$item]);
        return $stmt->fetch();
    }
}