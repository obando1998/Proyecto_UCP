<?php
require_once("conexion.php");
require_once 'login/session_timeout.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login/login.html');
    exit;
}

$mensaje = '';
$tipoMensaje = '';

// Procesar formulario de nueva devolución
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_devolucion'])) {
    try {
        $connection = Conexion::Conectar();
        
        // Obtener datos del formulario
        $nit = trim($_POST['nit']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $direccion = trim($_POST['direccion']);
        $producto_seleccionado = $_POST['producto']; // Este es el item del producto
        $unidad = trim($_POST['unidad']);
        $kg = floatval($_POST['kg']);
        $motivo = $_POST['motivo'];
        $cantidad_und = intval($_POST['cantidad_und']);
        $cantidad_kg = floatval($_POST['cantidad_kg']);
        $observacion = trim($_POST['observacion']);
        $usuario_creador = $_SESSION['user']; // Usar 'user' en lugar de 'usuario'
        
        // Obtener código y descripción del producto seleccionado
        $stmt = $connection->prepare("SELECT item, descripcion FROM producto WHERE item = ?");
        $stmt->execute([$producto_seleccionado]);
        $producto_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto_info) {
            throw new Exception("Producto no encontrado");
        }
        
        $codigo_producto = $producto_info['item'];
        $descripcion_producto = $producto_info['descripcion'];
        
        // Procesar evidencia (imagen/video)
        $evidencia = '';
        if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] == 0) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['evidencia']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['evidencia']['tmp_name'], $uploadFile)) {
                $evidencia = $fileName;
            }
        }
        
        // Insertar devolución en la base de datos
        $sql = "INSERT INTO devoluciones (
                    nit, nombre_cliente, direccion, codigo_producto, descripcion_producto, 
                    unidad, kg, motivo, cantidad_und, cantidad_kg, evidencia, observacion, 
                    usuario_creador, estado, fecha_creacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            $nit, 
            $nombre_cliente, 
            $direccion, 
            $codigo_producto, 
            $descripcion_producto, 
            $unidad, 
            $kg, 
            $motivo, 
            $cantidad_und, 
            $cantidad_kg, 
            $evidencia, 
            $observacion, 
            $usuario_creador
        ]);
        
        $mensaje = "✅ Devolución registrada correctamente";
        $tipoMensaje = "success";
        
        // Limpiar POST para evitar reenvío
        $_POST = array();
        
    } catch (Exception $e) {
        $mensaje = "❌ Error al registrar la devolución: " . $e->getMessage();
        $tipoMensaje = "error";
        error_log("Error en panel_auxiliar: " . $e->getMessage());
    }
}

// Obtener notificaciones no leídas
$notificaciones = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("
        SELECT n.*, d.codigo_producto 
        FROM notificaciones n 
        JOIN devoluciones d ON n.id_devolucion = d.id 
        WHERE n.usuario_destino = ? AND n.leida = FALSE 
        ORDER BY n.fecha DESC
    ");
    $stmt->execute([$_SESSION['user']]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener notificaciones: " . $e->getMessage());
}

// Obtener devoluciones del usuario actual
$devoluciones = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("
        SELECT * FROM devoluciones 
        WHERE usuario_creador = ? 
        ORDER BY fecha_creacion DESC
    ");
    $stmt->execute([$_SESSION['user']]);
    $devoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener devoluciones: " . $e->getMessage());
}

// Obtener lista de productos para el select
$productos = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("SELECT item, descripcion FROM producto ORDER BY descripcion");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener productos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <meta name="copyright" content="Sebastian Obando">
    <title>Panel Auxiliar - DevolutionSync</title>
    <link rel="icon" type="image/png" href="img/icono.png">
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(to right, #e2e2e2, #ffe5c9);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #ff8c00;
            font-size: 28px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            position: relative;
        }

        .user-info span {
            color: #666;
            font-weight: 500;
        }

        .btn {
            background: #ff8c00;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn:hover {
            background: #e67e00;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .notification-container {
            position: relative;
        }

        .notification-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
        }

        #notificationPanel {
            position: absolute;
            top: 45px;
            right: 0;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
            margin-bottom: 10px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #ff8c00;
            font-size: 22px;
        }

        .card h3 {
            color: #ff8c00;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff8c00;
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table thead {
            background: linear-gradient(to right, #ff8c00, #ff6b00);
            color: white;
        }

        .table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .table tbody tr {
            transition: background 0.3s ease;
        }

        .table tbody tr:hover {
            background: #fff8f0;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .badge-pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .badge-aprobado {
            background: #d4edda;
            color: #155724;
        }

        .badge-rechazado {
            background: #f8d7da;
            color: #721c24;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow: auto;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        .modal-content-large {
            background-color: white;
            margin: 2% auto;
            padding: 30px;
            border-radius: 10px;
            width: 95%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .imagen-modal-content {
            background-color: transparent;
            margin: auto;
            padding: 0;
            width: 90%;
            max-width: 1200px;
            text-align: center;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .imagen-modal-content img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }

        .imagen-modal-content .close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 40px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #000;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff8c00;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Estilos para Select2 */
        .select2-container--default .select2-selection--single {
            height: 46px !important;
            border: 2px solid #ddd !important;
            border-radius: 8px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px !important;
            padding-left: 12px !important;
            color: #333 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #ff8c00 !important;
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1) !important;
        }

        .select2-dropdown {
            border: 2px solid #ff8c00 !important;
            border-radius: 8px !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #ff8c00 !important;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            #notificationPanel {
                width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 DevolutionSync - Panel Auxiliar</h1>
            <div class="user-info">
                <div class="notification-container">
                    <button class="btn" onclick="toggleNotifications()">
                        🔔 Notificaciones 
                        <?php if (count($notificaciones) > 0): ?>
                            <span class="notification-badge"><?php echo count($notificaciones); ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notificationPanel" class="card" style="display: none;">
                        <h3>Notificaciones</h3>
                        <?php if (empty($notificaciones)): ?>
                            <p style="color: #666;">No hay notificaciones nuevas</p>
                        <?php else: ?>
                            <?php foreach ($notificaciones as $notif): ?>
                                <div class="alert alert-info">
                                    <p><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                    <small><?php echo htmlspecialchars($notif['fecha']); ?></small>
                                    <button class="btn btn-secondary" style="margin-top: 5px; padding: 5px 10px; font-size: 12px;" onclick="marcarLeida(<?php echo $notif['id']; ?>)">
                                        Marcar como leída
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <span>👤 <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                <a href="consualAux.php" class="btn">🏠 Historial Devoluciones</a>
                <a href="logout.php" class="btn btn-secondary">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>➕ Nueva Devolución</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nit">NIT</label>
                        <input type="text" id="nit" name="nit" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_cliente">Nombre del Cliente</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea id="direccion" name="direccion" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="producto">Producto</label>
                        <select class="form-control" name="producto" id="producto" required>
                            <option value="" selected disabled>Selecciona el Producto</option>
                            <?php foreach ($productos as $prod): ?>
                                <option value="<?php echo htmlspecialchars($prod['item']); ?>">
                                    <?php echo htmlspecialchars($prod['item']) . " - " . htmlspecialchars($prod['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="unidad">Unidad</label>
                        <input type="text" id="unidad" name="unidad" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="kg">KG</label>
                        <input type="number" step="0.01" id="kg" name="kg" class="form-control" value="0">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <select id="motivo" name="motivo" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="devolucion">Devolución</option>
                            <option value="faltante">Faltante</option>
                            <option value="sobrante">Sobrante</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cantidad_und">Cantidad Devolución (UND)</label>
                        <input type="number" id="cantidad_und" name="cantidad_und" class="form-control" value="0">
                    </div>
                    <div class="form-group">
                        <label for="cantidad_kg">Cantidad Devolución (KG)</label>
                        <input type="number" step="0.01" id="cantidad_kg" name="cantidad_kg" class="form-control" value="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="evidencia">Evidencia (Imagen/Video)</label>
                    <input type="file" id="evidencia" name="evidencia" class="form-control" accept="image/*,video/*">
                </div>
                
                <div class="form-group">
                    <label for="observacion">Observación</label>
                    <textarea id="observacion" name="observacion" class="form-control" rows="3"></textarea>
                </div>
                
                <button type="submit" name="registrar_devolucion" class="btn">✅ Registrar Devolución</button>
            </form>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div id="detallesModal" class="modal">
        <div class="modal-content-large">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 style="color: #ff8c00; margin-bottom: 20px;">📋 Detalles de la Devolución</h2>
            <div id="detallesContenido">
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver imagen completa -->
    <div id="imagenModal" class="modal" onclick="cerrarImagenModal()">
        <div class="imagen-modal-content">
            <span class="close" onclick="cerrarImagenModal()">&times;</span>
            <img id="imagenCompleta" src="" alt="Evidencia completa">
        </div>
    </div>

    <script>
        // Inicializar Select2
        $(document).ready(function() {
            $('#producto').select2({
                placeholder: 'Selecciona el Producto',
                allowClear: true,
                width: '100%'
            });
        });

        function toggleNotifications() {
            const panel = document.getElementById('notificationPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }

        function marcarLeida(idNotificacion) {
            fetch('marcar_notificacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + idNotificacion
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function verDetalles(id) {
            // Mostrar el modal con loader
            document.getElementById('detallesModal').style.display = 'block';
            document.getElementById('detallesContenido').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            `;
            
            // Cargar los detalles
            fetch('obtener_detalles.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detallesContenido').innerHTML = data.html;
                } else {
                    document.getElementById('detallesContenido').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #c62828; font-size: 18px;">❌ ${data.message || 'Error al cargar los detalles'}</p>
                            <button class="btn" onclick="cerrarModal()" style="margin-top: 20px;">Cerrar</button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detallesContenido').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <p style="color: #c62828; font-size: 18px;">❌ Error de conexión</p>
                        <p style="color: #666; margin-top: 10px;">No se pudieron cargar los detalles de la devolución</p>
                        <button class="btn" onclick="cerrarModal()" style="margin-top: 20px;">Cerrar</button>
                    </div>
                `;
            });
        }

        function cerrarModal() {
            document.getElementById('detallesModal').style.display = 'none';
        }

        function abrirImagenCompleta(rutaImagen) {
            document.getElementById('imagenCompleta').src = rutaImagen;
            document.getElementById('imagenModal').style.display = 'block';
        }

        function cerrarImagenModal() {
            document.getElementById('imagenModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const detallesModal = document.getElementById('detallesModal');
            const imagenModal = document.getElementById('imagenModal');
            
            if (event.target == detallesModal) {
                detallesModal.style.display = 'none';
            }
            if (event.target == imagenModal) {
                imagenModal.style.display = 'none';
            }
        }

        // Cerrar panel de notificaciones al hacer clic fuera
        document.addEventListener('click', function(event) {
            const notifPanel = document.getElementById('notificationPanel');
            const notifContainer = document.querySelector('.notification-container');
            
            if (!notifContainer.contains(event.target)) {
                notifPanel.style.display = 'none';
            }
        });

        // Prevenir que el clic en el contenido del modal lo cierre
        document.querySelector('.modal-content-large')?.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>
</body>
</html>