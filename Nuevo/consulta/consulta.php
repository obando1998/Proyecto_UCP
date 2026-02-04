<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener todas las devoluciones para el historial
$devoluciones = [];
$sql = "SELECT * FROM devoluciones ORDER BY fecha_creacion DESC";
$result = $conection->query($sql);
while ($row = $result->fetch_assoc()) {
    $devoluciones[] = $row;
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
                <?php if ($_SESSION['grado'] == 2): ?>
                    <a href="panel_administrador.php" class="btn">Panel Administrador</a>
                <?php else: ?>
                    <a href="panel_auxiliar.php" class="btn">Panel Auxiliar</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>

        <div class="card">
            <h2>Historial Completo de Devoluciones</h2>
            <?php if (empty($devoluciones)): ?>
                <p>No hay devoluciones registradas</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NIT</th>
                            <th>Cliente</th>
                            <th>Código Producto</th>
                            <th>Descripción</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Revisión</th>
                            <th>Creado por</th>
                            <th>Revisado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devoluciones as $devolucion): ?>
                            <tr>
                                <td><?php echo $devolucion['id']; ?></td>
                                <td><?php echo $devolucion['nit']; ?></td>
                                <td><?php echo $devolucion['nombre_cliente']; ?></td>
                                <td><?php echo $devolucion['codigo_producto']; ?></td>
                                <td><?php echo substr($devolucion['descripcion_producto'], 0, 50) . '...'; ?></td>
                                <td><?php echo ucfirst($devolucion['motivo']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $devolucion['estado']; ?>">
                                        <?php echo ucfirst($devolucion['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo $devolucion['fecha_creacion']; ?></td>
                                <td><?php echo $devolucion['fecha_revision'] ?: 'N/A'; ?></td>
                                <td><?php echo $devolucion['usuario_creador']; ?></td>
                                <td><?php echo $devolucion['usuario_revisor'] ?: 'N/A'; ?></td>
                                <td>
                                    <button class="btn btn-secondary" onclick="verDetalles(<?php echo $devolucion['id']; ?>)">Ver Detalles</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div id="detallesModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Detalles de la Devolución</h2>
            <div id="detallesContenido"></div>
        </div>
    </div>

    <script>
        function verDetalles(id) {
            fetch('obtener_detalles.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detallesContenido').innerHTML = data.html;
                    document.getElementById('detallesModal').style.display = 'block';
                }
            });
        }

        function cerrarModal() {
            document.getElementById('detallesModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('detallesModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>