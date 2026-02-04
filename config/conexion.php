<?php
// Archivo: proyecto_mvc/Config/Conexion.php

class Conexion {

    public static function Conectar() {
        // Credenciales directas (luego podremos pasarlas a un archivo .env para más seguridad)
        $host = '192.200.100.40';
        $db   = 'devolutionsync';
        $user = 'SANMARINO';
        $pass = 'sanmarino2021*';
        $charset = 'utf8'; // Es mejor definir el charset aquí

        // Cadena de conexión
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza errores como excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos por defecto
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Seguridad real de consultas preparadas
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $opciones);
            return $pdo;
        } catch (PDOException $e) {
            // En producción no deberíamos mostrar el error exacto al usuario, pero para desarrollo está bien
            die("Error de conexión (PDO): " . $e->getMessage());
        }
    }
}
?>