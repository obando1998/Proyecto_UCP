<?php
// Models/AuthModel.php
require_once 'Config/Conexion.php';

class AuthModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    public function buscarUsuario($username) {
        $sql = "SELECT USR, PAS, GRADO, NOMBRE FROM usuarios WHERE USR = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}