-- ============================================
-- SISTEMA DE PERSONAS DESAPARECIDAS - VENEZUELA
-- ============================================
-- Base de datos para gestión de personas afectadas por terremoto
-- Compatible con PHP + MySQL
-- ============================================

-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS desaparecidos_db;
USE desaparecidos_db;

-- 2. Crear la tabla de personas
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    localidad VARCHAR(100) NOT NULL,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50) NULL,
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50) NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    edad INT NOT NULL CHECK (edad >= 0 AND edad <= 120),
    hospital VARCHAR(100) NULL,
    zona_hospital VARCHAR(150) NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(100) NULL,
    casa_destruida ENUM('no', 'si', 'averiada') DEFAULT 'no',
    estado ENUM('desaparecido', 'encontrado') DEFAULT 'desaparecido',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Insertar datos de ejemplo (Venezuela)
INSERT INTO personas (
    localidad, 
    primer_nombre, 
    segundo_nombre, 
    primer_apellido, 
    segundo_apellido, 
    cedula, 
    edad, 
    hospital, 
    zona_hospital, 
    telefono, 
    correo, 
    casa_destruida, 
    estado
) VALUES
('Caracas', 'María', 'José', 'González', 'López', '12345678', 45, 'Hospital Universitario de Caracas', 'Pabellón A, Sala 302', '+58 412 1234567', 'maria.gonzalez@email.com', 'no', 'desaparecido'),
('Vargas', 'Carlos', 'Andrés', 'Mendoza', 'Rojas', '87654321', 8, 'Hospital Central de La Guaira', 'Pediatría, Cama 12', '+58 414 8765432', 'carlos.mendoza@email.com', 'si', 'desaparecido'),
('Miranda', 'Ana', 'María', 'Torres', 'Reyes', '98765432', 72, 'Hospital Regional de Los Teques', 'UCI, Box 5', '+58 416 5678123', 'ana.torres@email.com', 'averiada', 'encontrado'),
('Zulia', 'Luis', 'Fernando', 'Díaz', 'Pérez', '54321678', 35, 'Hospital General del Sur', 'Emergencias, Cama 8', '+58 424 2345678', 'luis.diaz@email.com', 'no', 'desaparecido'),
('Carabobo', 'Isabel', 'Cristina', 'Ramírez', 'García', '87651234', 28, 'Hospital Central de Valencia', 'Maternidad, Piso 3', '+58 412 9876543', 'isabel.ramirez@email.com', 'averiada', 'desaparecido'),
('Lara', 'Pedro', 'Antonio', 'Sánchez', 'Fernández', '65432187', 60, 'Hospital Universitario de Barquisimeto', 'Cardiología, Box 2', '+58 414 3456789', 'pedro.sanchez@email.com', 'si', 'encontrado');

-- 4. Verificar que los datos se insertaron correctamente
SELECT * FROM personas;

-- 5. Mostrar estructura de la tabla
DESCRIBE personas;