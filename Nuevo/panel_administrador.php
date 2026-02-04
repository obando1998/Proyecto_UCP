<?php
require_once("conexion.php");
require_once 'login/session_timeout.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login/login.html');
    exit;
}

if ($_SESSION['grado'] != 1) {
    header('Location: menu.php');
    exit;
}

$mensaje = '';
$tipoMensaje = '';

// Procesar aprobación/rechazo de devolución
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_devolucion'])) {
    try {
        $connection = Conexion::Conectar();
        
        $id_devolucion = intval($_POST['id_devolucion']);
        $accion = $_POST['accion'];
        $codigo_admin = trim($_POST['codigo_admin']);
        $observacion_admin = trim($_POST['observacion_admin']);
        $usuario_revisor = $_SESSION['user']; // Cambiado de 'usuario' a 'user'
        
        // Actualizar devolución
        $sql = "UPDATE devoluciones 
                SET estado = ?, codigo_admin = ?, observacion_admin = ?, 
                    usuario_revisor = ?, fecha_revision = NOW() 
                WHERE id = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->execute([$accion, $codigo_admin, $observacion_admin, $usuario_revisor, $id_devolucion]);
        
        // Obtener información de la devolución para notificar
        $stmtInfo = $connection->prepare("SELECT usuario_creador FROM devoluciones WHERE id = ?");
        $stmtInfo->execute([$id_devolucion]);
        $devolucionInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);
        
        // Crear notificación para el auxiliar
        $estadoTexto = ($accion == 'aprobado') ? 'APROBADA ✅' : 'RECHAZADA ❌';
        $mensaje_notif = "Tu devolución #" . $id_devolucion . " ha sido " . $estadoTexto . 
                        ". Código: " . $codigo_admin . ". Observación: " . $observacion_admin;
        
        $sqlNotif = "INSERT INTO notificaciones (id_devolucion, mensaje, usuario_destino, leida, fecha) 
                     VALUES (?, ?, ?, FALSE, NOW())";
        $stmtNotif = $connection->prepare($sqlNotif);
        $stmtNotif->execute([$id_devolucion, $mensaje_notif, $devolucionInfo['usuario_creador']]);
        
        $mensaje = "✅ Devolución " . $accion . " correctamente";
        $tipoMensaje = "success";
        
    } catch (Exception $e) {
        $mensaje = "❌ Error al procesar la devolución: " . $e->getMessage();
        $tipoMensaje = "error";
        error_log("Error en panel_administrador: " . $e->getMessage());
    }
}

// Obtener devoluciones pendientes
$devolucionesPendientes = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("SELECT * FROM devoluciones WHERE estado = 'pendiente' ORDER BY fecha_creacion ASC");
    $stmt->execute();
    $devolucionesPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener devoluciones pendientes: " . $e->getMessage());
}

// Obtener todas las devoluciones para el historial
$todasDevoluciones = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("SELECT * FROM devoluciones ORDER BY fecha_creacion DESC LIMIT 100");
    $stmt->execute();
    $todasDevoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener historial: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <meta name="copyright" content="Sebastian Obando">
    <title>Panel Administrador - DevolutionSync</title>
    <link rel="icon" type="image/png" href="img/icono.png">
    
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

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
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

        /* MODAL STYLES - MEJORADOS CON SCROLL */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow: hidden; /* Cambiado de auto a hidden */
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 10px;
            width: 95%;
            max-width: 1000px;
            max-height: 90vh;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
            display: flex;
            flex-direction: column;
            overflow: hidden;
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

        .modal-header {
            padding: 20px 30px;
            border-bottom: 2px solid #ff8c00;
            background: linear-gradient(to right, #fff8f0, #ffffff);
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            color: #ff8c00;
            margin: 0;
            font-size: 24px;
        }

        .modal-body {
            padding: 30px;
            overflow-y: auto;
            max-height: calc(90vh - 80px);
            flex: 1;
        }

        /* Scrollbar personalizado para el modal */
        .modal-body::-webkit-scrollbar {
            width: 10px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #ff8c00;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #e67e00;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
            line-height: 1;
        }

        .close:hover {
            color: #000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
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
            min-height: 100px;
        }

        select.form-control {
            cursor: pointer;
        }

        .revision-section {
            background: #fff8f0;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #ff8c00;
            margin-top: 30px;
        }

        .revision-section h3 {
            color: #ff8c00;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #ff8c00;
            padding-bottom: 10px;
        }

        hr {
            border: none;
            border-top: 2px dashed #ddd;
            margin: 30px 0;
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

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .modal-content {
                width: 98%;
                margin: 1% auto;
                max-height: 95vh;
            }

            .modal-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 DevolutionSync - Panel Administrador</h1>
            <div class="user-info">
                <span>👤 <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                <a href="menu.php" class="btn">🏠 Menú Principal</a>
                <a href="consulta.php" class="btn btn-secondary">📋 Consultar Historial</a>
                <a href="logout.php" class="btn btn-secondary">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>⏳ Devoluciones Pendientes de Revisión (<?php echo count($devolucionesPendientes); ?>)</h2>
            <?php if (empty($devolucionesPendientes)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    ✅ No hay devoluciones pendientes de revisión
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Código Producto</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                                <th>Creado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devolucionesPendientes as $devolucion): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($devolucion['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($devolucion['nombre_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($devolucion['codigo_producto']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($devolucion['motivo'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($devolucion['fecha_creacion'])); ?></td>
                                    <td><?php echo htmlspecialchars($devolucion['usuario_creador']); ?></td>
                                    <td>
                                        <button class="btn" onclick="revisarDevolucion(<?php echo $devolucion['id']; ?>)">
                                            🔍 Revisar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>📚 Historial de Devoluciones (<?php echo count($todasDevoluciones); ?>)</h2>
            <?php if (empty($todasDevoluciones)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    No hay devoluciones registradas
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Código Producto</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Revisado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todasDevoluciones as $devolucion): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($devolucion['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($devolucion['nombre_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($devolucion['codigo_producto']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($devolucion['motivo'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($devolucion['estado']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($devolucion['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($devolucion['fecha_creacion'])); ?></td>
                                    <td><?php echo htmlspecialchars($devolucion['usuario_revisor'] ?: 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-secondary" onclick="verDetalles(<?php echo $devolucion['id']; ?>)">
                                            👁️ Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para revisar devolución -->
    <div id="revisionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔍 Revisar Devolución</h2>
                <span class="close" onclick="cerrarModal('revisionModal')">&times;</span>
            </div>
            <div class="modal-body" id="revisionContenido">
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div id="detallesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Detalles de la Devolución</h2>
                <span class="close" onclick="cerrarModal('detallesModal')">&times;</span>
            </div>
            <div class="modal-body" id="detallesContenido">
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function revisarDevolucion(id) {
            // Mostrar modal con loader
            document.getElementById('revisionModal').style.display = 'block';
            document.getElementById('revisionContenido').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            `;

            fetch('obtener_detalles.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Agregar formulario de revisión
                    let contenido = data.html;
                    contenido += `
                        <hr style="margin: 30px 0;">
                        <div class="revision-section">
                            <h3>✍️ Revisión Administrativa</h3>
                            <form id="formRevision" method="POST" action="panel_administrador.php">
                                <input type="hidden" name="id_devolucion" value="${id}">
                                
                                <div class="form-group">
                                    <label for="accion">Acción a Realizar</label>
                                    <select id="accion" name="accion" class="form-control" required>
                                        <option value="">-- Seleccione una acción --</option>
                                        <option value="aprobado">✅ Aprobar Devolución</option>
                                        <option value="rechazado">❌ Rechazar Devolución</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="codigo_admin">Código de Autorización</label>
                                    <input type="text" id="codigo_admin" name="codigo_admin" class="form-control" 
                                           placeholder="Ingrese el código de autorización" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="observacion_admin">Observación del Administrador</label>
                                    <textarea id="observacion_admin" name="observacion_admin" class="form-control" 
                                              rows="4" placeholder="Ingrese sus observaciones sobre esta devolución" required></textarea>
                                </div>
                                
                                <div style="display: flex; gap: 10px; margin-top: 20px;">
                                    <button type="submit" class="btn btn-success" style="flex: 1;">
                                        ✅ Enviar Revisión
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cerrarModal('revisionModal')" style="flex: 1;">
                                        ❌ Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    `;
                    
                    document.getElementById('revisionContenido').innerHTML = contenido;
                    
                    // Scroll al inicio del modal
                    document.querySelector('#revisionModal .modal-body').scrollTop = 0;
                    
                    // Manejar envío del formulario
                    document.getElementById('formRevision').addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const accion = document.getElementById('accion').value;
                        if (!accion) {
                            alert('⚠️ Por favor seleccione una acción');
                            return;
                        }
                        
                        if (!confirm('¿Está seguro de ' + (accion === 'aprobado' ? 'APROBAR' : 'RECHAZAR') + ' esta devolución?')) {
                            return;
                        }
                        
                        const formData = new FormData(this);
                        
                        fetch('panel_administrador.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (response.ok) {
                                location.reload();
                            } else {
                                alert('Error al procesar la solicitud');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error de conexión');
                        });
                    });
                } else {
                    document.getElementById('revisionContenido').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #c62828; font-size: 18px;">❌ ${data.message || 'Error al cargar los detalles'}</p>
                            <button class="btn" onclick="cerrarModal('revisionModal')" style="margin-top: 20px;">Cerrar</button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('revisionContenido').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <p style="color: #c62828; font-size: 18px;">❌ Error de conexión</p>
                        <button class="btn" onclick="cerrarModal('revisionModal')" style="margin-top: 20px;">Cerrar</button>
                    </div>
                `;
            });
        }

        function verDetalles(id) {
            document.getElementById('detallesModal').style.display = 'block';
            document.getElementById('detallesContenido').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            `;

            fetch('obtener_detalles.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detallesContenido').innerHTML = data.html;
                    document.querySelector('#detallesModal .modal-body').scrollTop = 0;
                } else {
                    document.getElementById('detallesContenido').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #c62828; font-size: 18px;">❌ Error al cargar los detalles</p>
                            <button class="btn" onclick="cerrarModal('detallesModal')" style="margin-top: 20px;">Cerrar</button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Función para abrir imagen completa (llamada desde obtener_detalles.php)
        function abrirImagenCompleta(rutaImagen) {
            window.open(rutaImagen, '_blank');
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>