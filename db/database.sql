-- Active: 1752523377656@@127.0.0.1@3306@taller_api
CREATE DATABASE IF NOT EXISTS taller_api;
USE taller_api;

DROP TABLE IF EXISTS promociones;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    categoria_id INT NOT NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    detalle_promocion TEXT,
    porcentaje_descuento DECIMAL(5, 2),
    producto_id INT NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO categorias (nombre) VALUES
('Electronicos'),
('Muebles'),
('Aventura');

INSERT INTO productos (nombre, precio, categoria_id) VALUES
('Tablet Huawei', 2499.99, 1),
('Smartwatch Xiaomi', 1799.50, 1),
('Horno Electrico', 899.00, 2),
('Escritorio Oficina', 620.00, 2),
('Patineta Urbana', 1200.00, 3);

INSERT INTO promociones (detalle_promocion, porcentaje_descuento, producto_id) VALUES
('Rebaja especial en tablet', 10.00, 1),
('Promocion exclusiva smartwatch', 15.00, 2),
('Descuento 25 por ciento en horno', 25.00, 3),
('Oferta escritorio oficina 30 por ciento', 30.00, 4);
