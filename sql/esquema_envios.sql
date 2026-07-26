-- Esquema para el modulo de gestion de envios
-- Ejecutar dentro de phpMyAdmin (pestana SQL) para crear la base de datos
-- y las tablas de prueba de este modulo.

CREATE DATABASE IF NOT EXISTS tienda_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda_ecommerce;

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS envios (
    id_envio INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    transportista VARCHAR(60) NOT NULL,
    numero_seguimiento VARCHAR(40) NOT NULL,
    direccion_destino VARCHAR(150) NOT NULL,
    estado ENUM('pendiente','en_transito','entregado','devuelto') NOT NULL DEFAULT 'pendiente',
    fecha_creacion DATETIME NOT NULL,
    fecha_actualizacion DATETIME NULL,
    CONSTRAINT fk_envios_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
) ENGINE=InnoDB;

-- Datos de prueba
INSERT INTO pedidos (id_cliente, total, estado) VALUES
    (101, 45990, 'confirmado'),
    (102, 128500, 'confirmado'),
    (103, 15990, 'pendiente');

INSERT INTO envios (id_pedido, transportista, numero_seguimiento, direccion_destino, estado, fecha_creacion) VALUES
    (1, 'Chilexpress', 'CHX-20260710-A1B2C3', 'Av. Providencia 1234, Santiago', 'en_transito', '2026-07-10 09:15:00'),
    (2, 'Correos de Chile', 'COR-20260712-D4E5F6', 'Los Aromos 456, Vina del Mar', 'pendiente', '2026-07-12 14:40:00');
