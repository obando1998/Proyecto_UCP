<?php
session_start();
include 'connection.php'; // Incluir la conexión a la base de datos

// Establecer conexión
$conn = connection();

// Verificar si la conexión se estableció correctamente
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si los campos están definidos
    if (isset($_POST['cedula']) && isset($_POST['nombre']) && isset($_POST['password']) && isset($_POST['rol'])) {
        $cedula = $_POST['cedula'];
        $nombre = $_POST['nombre'];
        $password = $_POST['password']; // Contraseña en texto plano
        $rol = $_POST['rol'];

        // Insertar el usuario en la base de datos
        $sql = "INSERT INTO usuarios (cedula, nombre, password, rol) VALUES ('$cedula', '$nombre', '$password', '$rol')";

        if ($conn->query($sql) === TRUE) {
            echo "Usuario registrado con éxito.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Por favor, complete todos los campos del formulario.";
    }
}

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Creación de Usuario</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
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
</head>
<body>
    <div class="form-container">
        <h2 class="text-center">Creación de Usuario</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="cedula" class="form-label">Cédula:</label>
                <input type="text" class="form-control" id="cedula" name="cedula" required>
            </div>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol:</label>
                <select class="form-control" id="rol" name="rol" required>
                    <option value="admin">Administrador</option>
                    <option value="tecnico">Técnico</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>