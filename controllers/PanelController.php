<?php
// Controllers/PanelController.php
require_once 'Models/ProductoModel.php';
require_once 'Models/DevolucionModel.php';

class PanelController {
    private $prodModel;
    private $devModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        $this->prodModel = new ProductoModel();
        $this->devModel = new DevolucionModel();
    }

    public function auxiliar() {
        $productos = $this->prodModel->listarTodos();
        $titulo = "Registro de Devolución";
        require_once 'Views/admin/panel_auxiliar.php';
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Obtener datos del producto
            $prod = $this->prodModel->obtenerPorItem($_POST['producto']);
            
            // 2. Preparar array de datos
            $datos = [
                'nit' => $_POST['nit'],
                'cliente' => $_POST['nombre_cliente'],
                'direccion' => $_POST['direccion'],
                'item' => $prod['item'],
                'descripcion' => $prod['descripcion'],
                'unidad' => $_POST['unidad'],
                'kg' => $_POST['kg'],
                'motivo' => $_POST['motivo'],
                'cant_und' => $_POST['cantidad_und'],
                'cant_kg' => $_POST['cantidad_kg'],
                'obs' => $_POST['observacion'],
                'usuario' => $_SESSION['user']
            ];

            if ($this->devModel->guardar($datos)) {
                $_SESSION['alerta'] = ['tipo' => 'success', 'msg' => 'Devolución registrada correctamente'];
            } else {
                $_SESSION['alerta'] = ['tipo' => 'error', 'msg' => 'Error al guardar'];
            }
            header('Location: index.php?url=panel/auxiliar');
        }
    }
}