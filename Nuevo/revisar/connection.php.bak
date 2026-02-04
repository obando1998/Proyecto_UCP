<?php
function connection() {
    $host = "localhost"; // Host de la base de datos
    $user = "SANMARINO"; // Usuario de la base de datos
    $pass = "sanmarino2021*"; // Contraseña de la base de datos
    $bd = "mantenimiento"; // Nombre de la base de datos

    // Crear la conexión
    $conn = new mysqli($host, $user, $pass, $bd);

    // Verificar si hay errores de conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    return $conn; // Devolver el objeto de conexión
}
?>