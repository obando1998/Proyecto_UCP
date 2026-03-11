<?php
// Controllers/PanelController.php
require_once 'Models/ProductoModel.php';
require_once 'Models/DevolucionModel.php';
require_once 'Config/EmailHelper.php';

class PanelController {
    private $prodModel;
    private $devModel;
    private $emailHelper;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }

        if (!isset($_SESSION['grado']) || ($_SESSION['grado'] != 1 && $_SESSION['grado'] != 2)) {
            header('Location: index.php?url=home/index');
            exit;
        }

        $this->prodModel   = new ProductoModel();
        $this->devModel    = new DevolucionModel();
        $this->emailHelper = new EmailHelper();
    }

    public function auxiliar() {
        $productos = $this->prodModel->listarTodos();
        $titulo    = "Registro de Devolución - DevolutionSync";
        require_once 'Views/admin/panel_auxiliar.php';
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $itemProducto = $_POST['producto'] ?? '';

                if (empty($itemProducto)) {
                    throw new Exception('Debe seleccionar un producto');
                }

                $producto = $this->prodModel->obtenerPorItem($itemProducto);
                if (!$producto) {
                    throw new Exception('Producto no encontrado');
                }

                $this->validarCampos($_POST);

                // Subida de evidencia
                $rutaEvidencia = null;
                if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] == UPLOAD_ERR_OK) {
                    $rutaEvidencia = $this->subirEvidencia($_FILES['evidencia']);
                }

                // Correo del solicitante (opcional)
                $correoSolicitante = trim($_POST['correo_solicitante'] ?? '');
                if (!empty($correoSolicitante) && !filter_var($correoSolicitante, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('El formato del correo electrónico no es válido');
                }

                $datos = [
                    'nit'                  => $this->limpiar($_POST['nit']),
                    'nombre_cliente'       => $this->limpiar($_POST['nombre_cliente']),
                    'direccion'            => $this->limpiar($_POST['direccion']),
                    'correo_solicitante'   => $correoSolicitante,
                    'item_producto'        => $producto['item'],
                    'descripcion_producto' => $producto['descripcion'],
                    'unidad'               => $producto['unidad'] ?? 'UND',
                    'kg'                   => $producto['kg'] ?? 0,
                    'motivo'               => $this->limpiar($_POST['motivo']),
                    'cantidad_und'         => floatval($_POST['cantidad_und']),
                    'cantidad_kg'          => floatval($_POST['cantidad_kg']),
                    'observacion'          => $this->limpiar($_POST['observacion']),
                    'usuario_creador'      => $_SESSION['user'],
                    'evidencia'            => $rutaEvidencia
                ];

                $idNuevo = $this->devModel->guardar($datos);

                if ($idNuevo) {
                    // Notificar al admin por correo
                    $datos['id'] = $idNuevo;
                    $this->emailHelper->notificarNuevaDevolucion($datos);

                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'msg'  => '✅ Devolución registrada correctamente. ID asignado: #' . $idNuevo
                    ];
                } else {
                    throw new Exception('Error al guardar en la base de datos');
                }

            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'error',
                    'msg'  => '❌ ' . $e->getMessage()
                ];
            }

            header('Location: index.php?url=panel/auxiliar');
            exit;
        }
    }

    private function validarCampos($post) {
        $camposRequeridos = [
            'nit'           => 'NIT del cliente',
            'nombre_cliente'=> 'Nombre del cliente',
            'direccion'     => 'Dirección',
            'producto'      => 'Producto',
            'motivo'        => 'Motivo',
            'cantidad_und'  => 'Cantidad en unidades',
            'cantidad_kg'   => 'Cantidad en kilogramos',
            'observacion'   => 'Observaciones'
        ];

        foreach ($camposRequeridos as $campo => $nombre) {
            if (empty($post[$campo])) {
                throw new Exception("El campo '{$nombre}' es obligatorio");
            }
        }

        if (floatval($post['cantidad_und']) <= 0) {
            throw new Exception('La cantidad en unidades debe ser mayor a 0');
        }

        if (floatval($post['cantidad_kg']) < 0) {
            throw new Exception('La cantidad en kilogramos no puede ser negativa');
        }
    }

    private function subirEvidencia($archivo) {
        $directorioDestino    = 'uploads/evidencias/';
        $tamañoMaximo         = 5 * 1024 * 1024;
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        if ($archivo['size'] > $tamañoMaximo) {
            throw new Exception('El archivo excede el tamaño máximo de 5MB');
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception('Formato no permitido. Use JPG, PNG o GIF');
        }

        $nombreArchivo = 'evidencia_' . time() . '_' . uniqid() . '.' . $extension;
        $rutaCompleta  = $directorioDestino . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $rutaCompleta;
        }

        throw new Exception('Error al subir el archivo de evidencia');
    }

    private function limpiar($texto) {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }
}