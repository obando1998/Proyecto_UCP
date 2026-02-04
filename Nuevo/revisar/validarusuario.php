<?php
session_start();
include 'connection.php'; // Incluir la conexión a la base de datos

// Establecer conexión
$conn = connection();

// Verificar si la conexión se estableció correctamente
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se enviaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si los campos están definidos
    if (isset($_POST['cedula']) && isset($_POST['password'])) {
        $cedula = $_POST['cedula'];
        $password = $_POST['password'];

        // Buscar el usuario en la base de datos
        $sql = "SELECT * FROM usuarios WHERE cedula = '$cedula'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            // Verificar la contraseña (comparación directa)
            if ($password === $usuario['password']) {
                // Guardar datos del usuario en la sesión
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redirigir según el rol
                switch ($usuario['rol']) {
                    case 'admin':
                        header("Location: administrador.php");
                        exit();
                    case 'tecnico':
                        header("Location: hallazgo.php");
                        exit();
                    default:
                        echo "Rol no válido.";
                        break;
                }
            } else {
                echo "Contraseña incorrecta.";
            }
        } else {
            echo "Usuario no encontrado.";
        }
    } else {
        echo "Por favor, complete todos los campos del formulario.";
    }
}

// Cerrar conexión
$conn->close();
?>