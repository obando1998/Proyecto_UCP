<?php
session_start();

// Verificar si el usuario es técnico
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'tecnico') {
    header("Location: login.php");
    exit();
}

include 'connection.php';

// Establecer conexión
$conn = connection();

// Verificar si la conexión se estableció correctamente
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener las solicitudes asignadas al técnico
$tecnico_id = $_SESSION['id'];
$sql_solicitudes = "SELECT * FROM solicitudes WHERE asignado_a = '$tecnico_id' AND estado = 'asignada'";
$result_solicitudes = $conn->query($sql_solicitudes);

// Verificar si la consulta se ejecutó correctamente
if ($result_solicitudes === false) {
    die("Error en la consulta: " . $conn->error);
}

// Procesar el formulario de hallazgos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $solicitud_id = $_POST['solicitud_id'];
    $contratista_proveedor = $_POST['contratista_proveedor'];
    $responsables = $_POST['responsables'];
    $tipo_mantenimiento = $_POST['tipo_mantenimiento'];
    $fecha_asignacion = $_POST['fecha_asignacion'];
    $fecha_cierre = $_POST['fecha_cierre'];
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'];

    // Insertar el hallazgo en la base de datos
    $sql_hallazgo = "INSERT INTO hallazgos (solicitud_id, contratista_proveedor, responsables, tipo_mantenimiento, fecha_asignacion, fecha_cierre, estado, observaciones)
                     VALUES ('$solicitud_id', '$contratista_proveedor', '$responsables', '$tipo_mantenimiento', '$fecha_asignacion', '$fecha_cierre', '$estado', '$observaciones')";

    if ($conn->query($sql_hallazgo) === TRUE) {
        // Actualizar el estado de la solicitud a 'realizada'
        $sql_update_solicitud = "UPDATE solicitudes SET estado = 'realizada' WHERE id = '$solicitud_id'";
        $conn->query($sql_update_solicitud);
        echo "Hallazgo registrado y solicitud marcada como realizada con éxito.";
        header("Location: hallazgo.php"); // Recargar la página
        exit();
    } else {
        echo "Error: " . $sql_hallazgo . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro de Hallazgos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .form-container { max-width: 1000px; margin: 0 auto; }
		
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center">Registro de Hallazgos</h2>
        <h3>Solicitudes Asignadas</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>No. Orden de Trabajo</th>
                    <th>Fecha de Solicitud</th>
                    <th>Proceso</th>
                    <th>Área</th>
                    <th>Cargo</th>
                    <th>Nombre del Solicitante</th>
                    <th>Hallazgo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_solicitudes->num_rows > 0) {
                    while ($row = $result_solicitudes->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['no_orden_trabajo']}</td>
                                <td>{$row['fecha_solicitud']}</td>
                                <td>{$row['proceso']}</td>
                                <td>{$row['area']}</td>
                                <td>{$row['cargo']}</td>
                                <td>{$row['nombre_solicitante']}</td>
                                <td>{$row['hallazgo']}</td>
                                <td>
                                    <button onclick=\"mostrarFormularioHallazgo({$row['id']})\">Registrar Hallazgo</button>
                                    <div id=\"form-hallazgo-{$row['id']}\" class=\"hallazgo-form\" style=\"display: none;\">
                                        <form method=\"post\" action=\"\">
                                            <input type=\"hidden\" name=\"solicitud_id\" value=\"{$row['id']}\">
                                            Contratista: <input type=\"text\" name=\"contratista_proveedor\" required><br>
                                            Responsables: <input type=\"text\" name=\"responsables\" required><br>
                                            Tipo de Mantenimiento: <input type=\"text\" name=\"tipo_mantenimiento\" required><br>
                                            Fecha Asignación: <input type=\"date\" name=\"fecha_asignacion\" required><br>
                                            Fecha Cierre: <input type=\"date\" name=\"fecha_cierre\"><br>
                                            Estado: <select name=\"estado\">
                                                <option value=\"en_proceso\">En Proceso</option>
                                                <option value=\"completado\">Completado</option>
                                            </select><br>
                                            Observaciones: <textarea name=\"observaciones\"></textarea><br>
                                            <input type=\"submit\" value=\"Registrar\">
                                        </form>
                                    </div>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No hay solicitudes asignadas.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function mostrarFormularioHallazgo(solicitudId) {
            var form = document.getElementById('form-hallazgo-' + solicitudId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
