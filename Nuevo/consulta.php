<?php
require_once("conexion.php");
require_once 'login/session_timeout.php';


// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Usar ruta relativa desde la raíz del servidor web
    $login_url = 'login/login.html';
    header('Location: ' . $login_url);
    exit;
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
    <title>Consulta - DevolutionSync</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DevolutionSync - Consulta de Historial</h1>
            <div class="user-info">
                <span>Bienvenido, <?php echo $_SESSION['nombre']; ?></span>
                <?php if ($_SESSION['grado'] == 3): ?>
                    
                    <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
                <?php endif; ?>
                
            </div>
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