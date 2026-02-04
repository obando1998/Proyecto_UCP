<?php
class Conexion
{
    public static function Conectar()
    {
        // Definir constantes solo si no existen
        if (!defined('servidor')) {
            define('servidor', '192.200.100.40');
        }
        if (!defined('nombre_bd')) {
            define('nombre_bd', 'devolutionsync');
        }
        if (!defined('usuario')) {
            define('usuario', 'SANMARINO');
        }
        if (!defined('password')) {
            define('password', 'sanmarino2021*');
        }
        
        $opciones = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
        
        try {
            $conexion = new PDO("mysql:host=" . servidor . "; dbname=" . nombre_bd, usuario, password, $opciones);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (Exception $e) {
            die("El error de Conexión es: " . $e->getMessage());
        }
    }
}

// Crear conexión mysqli solo si no existe
if (!isset($conection)) {
    $conection = mysqli_connect("192.200.100.40", "SANMARINO", "sanmarino2021*", "devolutionsync");
    if (!$conection) {
        die("Error de conexión mysqli: " . mysqli_connect_error());
    }
}