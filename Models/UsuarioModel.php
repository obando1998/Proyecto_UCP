<?php
// Models/UsuarioModel.php
require_once 'Config/Conexion.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::Conectar();
    }

    public function existeUsuario($usr) {
        $stmt = $this->db->prepare("SELECT USR FROM usuarios WHERE USR = ?");
        $stmt->execute([$usr]);
        return $stmt->rowCount() > 0;
    }

    public function guardar($usr, $pas, $nombre, $grado) {
        $sql = "INSERT INTO usuarios (USR, PAS, NOMBRE, GRADO) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usr, $pas, $nombre, $grado]);
    }

    public function listarTodos() {
        $stmt = $this->db->prepare("SELECT USR, NOMBRE, GRADO FROM usuarios ORDER BY GRADO, NOMBRE");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}