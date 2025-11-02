-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS boliche;
USE boliche;

-- Crear tabla de eventos
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de fotos
CREATE TABLE IF NOT EXISTS fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);

-- Insertar algunos eventos de ejemplo
INSERT INTO eventos (titulo, descripcion, fecha) VALUES
('Noche de DJ - House Music', 'Una noche llena de los mejores beats house con nuestro DJ residente.', '2025-11-08'),
('Latin Party', 'Prepárate para bailar toda la noche con la mejor música latina.', '2025-11-15'),
('Rock Nacional', 'Tributo a las mejores bandas del rock nacional.', '2025-11-22'),
('Fiesta de Espuma', 'La fiesta más refrescante con la mejor música electrónica.', '2025-10-25'),
('Halloween Party', 'Noche de disfraces y música con premios al mejor disfraz.', '2025-10-31');