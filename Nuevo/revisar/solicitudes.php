<?php
// Incluir la conexión a la base de datos
include 'connection.php';

// Establecer conexión
$conn = connection();

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_orden_trabajo = $_POST['no_orden_trabajo'];
    $fecha_solicitud = $_POST['fecha_solicitud'];
    $proceso = $_POST['proceso'];
    $area = $_POST['area'];
    $cargo = $_POST['cargo'];
    $nombre_solicitante = $_POST['nombre_solicitante'];
    $hallazgo = $_POST['hallazgo'];

    // Insertar la solicitud en la base de datos
    $sql = "INSERT INTO solicitudes (no_orden_trabajo, fecha_solicitud, proceso, area, cargo, nombre_solicitante, hallazgo)
            VALUES ('$no_orden_trabajo', '$fecha_solicitud', '$proceso', '$area', '$cargo', '$nombre_solicitante', '$hallazgo')";

    if ($conn->query($sql) === TRUE) {
        echo "Solicitud creada con éxito";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Solicitud de Mantenimiento</title>
	<!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
	</head>
	<body>
	<style>
			body {
				padding: 20px;
			}
			.form-container {
				max-width: 500px;
				margin: 0 auto;
				padding: 20px;
				border: 1px solid #ccc;
				border-radius: 10px;
				background-color: #f9f9f9;
			}
		</style>
	<div class="form-container">
		<h2>Solicitud de Mantenimiento</h2>
		<form method="post" action="">
		
			<div class="mb-3">
                <label for="no_orden_trabajo" class="form-label">No. Orden de Trabajo:</label>
                <input type="text" class="form-control" id="no_orden_trabajo" name="no_orden_trabajo" required>
            </div>
			<div class="mb-3">
                <label for="fecha_solicitud" class="form-label">Fecha de Solicitud:</label>
                <input type="date" class="form-control" id="fecha_solicitud" name="fecha_solicitud" required>
            </div>
			<div class="mb-3">
                <label for="proceso" class="form-label">Proceso:</label>
                <input type="text" class="form-control" id="proceso" name="proceso" required>
            </div>
			<div class="mb-3">
                <label for="area" class="form-label">Area:</label>
                <input type="text" class="form-control" id="area" name="area" required>
            </div>
			<div class="mb-3">
                <label for="cargo" class="form-label">Cargo:</label>
                <input type="text" class="form-control" id="cargo" name="cargo" required>
            </div>
			<div class="mb-3">
                <label for="nombre_solicitante" class="form-label">Nombre del Solicitante:</label>
                <input type="text" class="form-control" id="nombre_solicitante" name="nombre_solicitante" required>
            </div>
			<div class="mb-3">
                <label for="hallazgo" class="form-label">Requerimiento:</label>
                 <textarea class="form-control" id="hallazgo" name="hallazgo" required></textarea>
            </div>
			<button type="submit" class="btn btn-primary">Enviar Solicitud</button>
		</form>
	</div>
	<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

</body>
</html>