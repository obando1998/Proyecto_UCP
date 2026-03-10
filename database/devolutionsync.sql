/*
Navicat MySQL Data Transfer

Source Server         : Server 40
Source Server Version : 50736
Source Host           : 192.200.100.40:3306
Source Database       : devolutionsync

Target Server Type    : MYSQL
Target Server Version : 50736
File Encoding         : 65001

Date: 2026-03-10 17:54:43
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for devoluciones
-- ----------------------------
DROP TABLE IF EXISTS `devoluciones`;
CREATE TABLE `devoluciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `direccion` text,
  `correo_solicitante` varchar(150) DEFAULT NULL,
  `codigo_producto` varchar(50) NOT NULL,
  `descripcion_producto` text,
  `unidad` varchar(20) DEFAULT NULL,
  `kg` decimal(10,2) DEFAULT NULL,
  `motivo` enum('devolucion','faltante','sobrante') NOT NULL,
  `cantidad_und` int(11) DEFAULT NULL,
  `cantidad_kg` decimal(10,2) DEFAULT NULL,
  `evidencia` varchar(255) DEFAULT NULL,
  `observacion` text,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `observacion_admin` text,
  `codigo_admin` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` timestamp NULL DEFAULT NULL,
  `usuario_creador` varchar(50) NOT NULL,
  `usuario_revisor` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for login_attempts
-- ----------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) NOT NULL,
  `attempts` int(11) DEFAULT '1',
  `last_attempt` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_username` (`ip_address`,`username`),
  KEY `idx_last_attempt` (`last_attempt`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for notificaciones
-- ----------------------------
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_devolucion` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_destino` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_devolucion` (`id_devolucion`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for producto
-- ----------------------------
DROP TABLE IF EXISTS `producto`;
CREATE TABLE `producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Item` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `minimo` float NOT NULL,
  `maximo` float NOT NULL,
  `pesoProm` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codItem` (`Item`)
) ENGINE=MyISAM AUTO_INCREMENT=768 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `USR` varchar(10) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `PAS` varchar(10) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `NOMBRE` varchar(60) COLLATE utf8mb4_spanish2_ci NOT NULL,
  `GRADO` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
