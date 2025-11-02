-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS boliche_db;
USE boliche_db;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'editor') NOT NULL,
    nombre VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- Tabla de eventos
CREATE TABLE IF NOT EXISTS eventos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    cover DECIMAL(10,2),
    imagen_portada VARCHAR(255),
    estado ENUM('programado', 'finalizado', 'cancelado') NOT NULL DEFAULT 'programado',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES usuarios(id)
);

-- Tabla de fotos
CREATE TABLE IF NOT EXISTS fotos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NOT NULL,
    ruta_foto VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255),
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES usuarios(id)
);

-- Tabla de categorías de eventos
CREATE TABLE IF NOT EXISTS categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Tabla de relación eventos-categorías
CREATE TABLE IF NOT EXISTS evento_categoria (
    evento_id INT,
    categoria_id INT,
    PRIMARY KEY (evento_id, categoria_id),
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (username, password, rol, nombre, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrador', 'admin@boliche.com')
ON DUPLICATE KEY UPDATE username = username;

-- Insertar algunas categorías básicas
INSERT INTO categorias (nombre, descripcion) VALUES
('Fiesta Regular', 'Eventos regulares de fin de semana'),
('Evento Especial', 'Fiestas temáticas y eventos especiales'),
('Aniversario', 'Celebraciones de aniversario del boliche'),
('DJ Invitado', 'Eventos con DJs invitados especiales')
ON DUPLICATE KEY UPDATE nombre = nombre;