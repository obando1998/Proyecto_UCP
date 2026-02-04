-- Base de datos para DevolutionSync
CREATE DATABASE IF NOT EXISTS devolutionsync;
USE devolutionsync;

-- Tabla de devoluciones
CREATE TABLE IF NOT EXISTS devoluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nit VARCHAR(20) NOT NULL,
    nombre_cliente VARCHAR(255) NOT NULL,
    direccion TEXT,
    codigo_producto VARCHAR(50) NOT NULL,
    descripcion_producto TEXT,
    unidad VARCHAR(20),
    kg DECIMAL(10,2),
    motivo ENUM('devolucion', 'faltante', 'sobrante') NOT NULL,
    cantidad_und INT,
    cantidad_kg DECIMAL(10,2),
    evidencia VARCHAR(255),
    observacion TEXT,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    observacion_admin TEXT,
    codigo_admin VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision TIMESTAMP NULL,
    usuario_creador VARCHAR(50) NOT NULL,
    usuario_revisor VARCHAR(50)
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_devolucion INT NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_destino VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_devolucion) REFERENCES devoluciones(id)
);

--  Tabla login_attempts
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 0,
    last_attempt DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--  Tabla usuarios
CREATE TABLE `usuarios` (
    `USR` VARCHAR(50) NOT NULL,
    `PAS` VARCHAR(50) NOT NULL,
    `NOMBRE` VARCHAR(100) NOT NULL,
    `GRADO` INT NOT NULL,
    PRIMARY KEY (`USR`)
)

-- Inserciones de datos
INSERT INTO `usuarios` (`USR`, `PAS`, `NOMBRE`, `GRADO`) VALUES ('ANALISTA', '1088350785', 'SEBASTIAN OBANDO', 1);
INSERT INTO `usuarios` (`USR`, `PAS`, `NOMBRE`, `GRADO`) VALUES ('AUXILIAR', '895623', 'AUXILIAR', 2);
INSERT INTO `usuarios` (`USR`, `PAS`, `NOMBRE`, `GRADO`) VALUES ('CONSULTA', '895623', 'CONSULTA', 3);

--  Tabla Producto

CREATE TABLE producto (
    id INT(11) NOT NULL PRIMARY KEY,
    item INT(11) NOT NULL,
    description VARCHAR(50) NOT NULL,
    minimo FLOAT NOT NULL,
    maximo FLOAT NOT NULL,
    pesoProm FLOAT NOT NULL
);
-- Inserciones de datos
INSERT INTO `producto` (`id`, `Item`, `descripcion`, `minimo`, `maximo`, `pesoProm`) VALUES (1, 15440, 'ALAS MR CONG BLS', 4, 6, 5);