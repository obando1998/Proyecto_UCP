<?php
// Incluir la conexión a la base de datos
include 'connection.php';

// Establecer conexión
$conn = connection();

// Obtener todas las solicitudes
$sql_solicitudes = "SELECT * FROM solicitudes";
$result_solicitudes = $conn->query($sql_solicitudes);

// Obtener todos los hallazgos
$sql_hallazgos = "SELECT * FROM hallazgos";
$result_hallazgos = $conn->query($sql_hallazgos);

// Obtener todas las aprobaciones
$sql_aprobaciones = "SELECT * FROM aprobaciones";
$result_aprobaciones = $conn->query($sql_aprobaciones);

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html>
	<head>

		<title>Registros de Mantenimiento</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>
			body { padding: 20px; }
			
			.form-container { max-width: 1000px; 
			white-space: nowrap; /* Evita que el texto se divida en varias líneas */
			margin: 0 auto; /* Centra el bloque horizontalmente */
			}
			
			.titulo-centrado {
            text-align: center; /* Centra el texto horizontalmente */
            white-space: nowrap; /* Evita que el texto se divida en varias líneas */
			width: 250px; /* Ancho opcional */
            margin: 0 auto; /* Centra el bloque horizontalmente */
			}
			.subtitulo-centrado {
            text-align: center; /* Centra el texto horizontalmente */
            white-space: nowrap; /* Evita que el texto se divida en varias líneas */
            margin: 0 auto; /* Centra el bloque horizontalmente */
			}
		</style>
	</head>
	<body>
		<div class="form-container">
			<h2 class="titulo-centrado">Registros de Mantenimiento</h2><br><br>
			<hr>
			
			<h3 class="subtitulo-centrado">Solicitudes</h3><br>
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
							<th>Estado</th>
						</tr>
					</thead>
				<tbody>
				<?php while($row = $result_solicitudes->fetch_assoc()): ?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><?php echo $row['no_orden_trabajo']; ?></td>
					<td><?php echo $row['fecha_solicitud']; ?></td>
					<td><?php echo $row['proceso']; ?></td>
					<td><?php echo $row['area']; ?></td>
					<td><?php echo $row['cargo']; ?></td>
					<td><?php echo $row['nombre_solicitante']; ?></td>
					<td><?php echo $row['hallazgo']; ?></td>
					<td><?php echo $row['estado']; ?></td>
				</tr>
				<?php endwhile; ?>
			</table>

			<br><hr>
			<h3 class="subtitulo-centrado">Hallazgos</h3><br>
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>ID</th>
								<th>ID de Solicitud</th>
								<th>Contratista/Proveedor</th>
								<th>Responsables</th>
								<th>Tipo de Mantenimiento</th>
								<th>Fecha de Asignación</th>
								<th>Fecha de Cierre</th>
								<th>Estado</th>
								<th>Observaciones</th>
							</tr>
						</thead>
					<tbody>
					
				<?php while($row = $result_hallazgos->fetch_assoc()): ?>
				<tr>
					<td><?php echo $row['id']; ?></td>
					<td><?php echo $row['solicitud_id']; ?></td>
					<td><?php echo $row['contratista_proveedor']; ?></td>
					<td><?php echo $row['responsables']; ?></td>
					<td><?php echo $row['tipo_mantenimiento']; ?></td>
					<td><?php echo $row['fecha_asignacion']; ?></td>
					<td><?php echo $row['fecha_cierre']; ?></td>
					<td><?php echo $row['estado']; ?></td>
					<td><?php echo $row['observaciones']; ?></td>
				</tr>
				<?php endwhile; ?>
			</table>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>