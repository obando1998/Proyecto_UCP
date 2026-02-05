<?php
// Controllers/PanelController.php
require_once 'Models/ProductoModel.php';
require_once 'Models/DevolucionModel.php';

class PanelController {
    private $prodModel;
    private $devModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verificar autenticación
        if (!isset($_SESSION['logged_in'])) {
            header('Location: index.php?url=auth/index');
            exit;
        }
        
        // Verificar permisos (Solo Admin Grado 1 o Auxiliar Grado 2)
        if (!isset($_SESSION['grado']) || ($_SESSION['grado'] != 1 && $_SESSION['grado'] != 2)) {
            header('Location: index.php?url=home/index');
            exit;
        }
        
        $this->prodModel = new ProductoModel();
        $this->devModel = new DevolucionModel();
    }

    /**
     * Mostrar formulario de registro de devolución
     */
    public function auxiliar() {
        $productos = $this->prodModel->listarTodos();
        $titulo = "Registro de Devolución - DevolutionSync";
        require_once 'Views/admin/panel_auxiliar.php';
    }

    /**
     * Procesar el registro de devolución
     */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // 1. Obtener datos del producto seleccionado
                $itemProducto = $_POST['producto'] ?? '';
                
                if (empty($itemProducto)) {
                    throw new Exception('Debe seleccionar un producto');
                }
                
                $producto = $this->prodModel->obtenerPorItem($itemProducto);
                
                if (!$producto) {
                    throw new Exception('Producto no encontrado');
                }
                
                // 2. Validar campos obligatorios
                $this->validarCampos($_POST);
                
                // 3. Manejar subida de evidencia (si existe)
                $rutaEvidencia = null;
                if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] == UPLOAD_ERR_OK) {
                    $rutaEvidencia = $this->subirEvidencia($_FILES['evidencia']);
                }
                
                // 4. Preparar array de datos para guardar
                $datos = [
                    'nit' => $this->limpiar($_POST['nit']),
                    'nombre_cliente' => $this->limpiar($_POST['nombre_cliente']),
                    'direccion' => $this->limpiar($_POST['direccion']),
                    'item_producto' => $producto['item'],
                    'descripcion_producto' => $producto['descripcion'],
                    'unidad' => $producto['unidad'] ?? 'UND',
                    'kg' => $producto['kg'] ?? 0,
                    'motivo' => $this->limpiar($_POST['motivo']),
                    'cantidad_und' => floatval($_POST['cantidad_und']),
                    'cantidad_kg' => floatval($_POST['cantidad_kg']),
                    'observacion' => $this->limpiar($_POST['observacion']),
                    'usuario_creador' => $_SESSION['user'],
                    'evidencia' => $rutaEvidencia
                ];
                
                // 5. Guardar en la base de datos
                if ($this->devModel->guardar($datos)) {
                    $_SESSION['alerta'] = [
                        'tipo' => 'success', 
                        'msg' => '✅ Devolución registrada correctamente. ID de registro generado.'
                    ];
                } else {
                    throw new Exception('Error al guardar en la base de datos');
                }
                
            } catch (Exception $e) {
                $_SESSION['alerta'] = [
                    'tipo' => 'error', 
                    'msg' => '❌ ' . $e->getMessage()
                ];
            }
            
            // Redirigir de vuelta al formulario
            header('Location: index.php?url=panel/auxiliar');
            exit;
        }
    }

    /**
     * Validar campos obligatorios del formulario
     */
    private function validarCampos($post) {
        $camposRequeridos = [
            'nit' => 'NIT del cliente',
            'nombre_cliente' => 'Nombre del cliente',
            'direccion' => 'Dirección',
            'producto' => 'Producto',
            'motivo' => 'Motivo',
            'cantidad_und' => 'Cantidad en unidades',
            'cantidad_kg' => 'Cantidad en kilogramos',
            'observacion' => 'Observaciones'
        ];
        
        foreach ($camposRequeridos as $campo => $nombre) {
            if (empty($post[$campo])) {
                throw new Exception("El campo '{$nombre}' es obligatorio");
            }
        }
        
        // Validar que las cantidades sean números positivos
        if (floatval($post['cantidad_und']) <= 0) {
            throw new Exception('La cantidad en unidades debe ser mayor a 0');
        }
        
        if (floatval($post['cantidad_kg']) < 0) {
            throw new Exception('La cantidad en kilogramos no puede ser negativa');
        }
    }

    /**
     * Subir archivo de evidencia
     */
    private function subirEvidencia($archivo) {
        // Configuración
        $directorioDestino = 'uploads/evidencias/';
        $tamañoMaximo = 5 * 1024 * 1024; // 5MB
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Crear directorio si no existe
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }
        
        // Validar tamaño
        if ($archivo['size'] > $tamañoMaximo) {
            throw new Exception('El archivo excede el tamaño máximo de 5MB');
        }
        
        // Validar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception('Formato de archivo no permitido. Use JPG, PNG o GIF');
        }
        
        // Generar nombre único
        $nombreArchivo = 'evidencia_' . time() . '_' . uniqid() . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $rutaCompleta;
        } else {
            throw new Exception('Error al subir el archivo de evidencia');
        }
    }

    /**
     * Limpiar y sanitizar entrada de texto
     */
    private function limpiar($texto) {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }
}