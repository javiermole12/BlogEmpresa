-- ==========================================
-- SCRIPT DE INSTALACIÓN: BLOG CORPORATIVO v2
-- ==========================================

-- 1. PREPARACIÓN DE LA BASE DE DATOS
DROP DATABASE IF EXISTS blog_empresa_db;
CREATE DATABASE blog_empresa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_empresa_db;

-- 2. TABLA DE USUARIOS
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') DEFAULT 'empleado',
    cargo VARCHAR(100) DEFAULT 'Nuevo empleado',
    avatar VARCHAR(255) DEFAULT 'default.png', -- Imagen genérica por defecto
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABLA DE POSTS
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT 'default.png', -- Nombre del archivo de imagen
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    autor_id INT,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 3. TABLA DE COMENTARIOS EN LOS POSTS
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    autor_id INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. USUARIOS DE PRUEBA
-- Contraseña para todos: "1234"
-- NOTA: Usamos 'default.png' para evitar errores visuales si no tienes fotos reales preparadas.

INSERT INTO usuarios (nombre, email, password, rol, cargo, avatar) VALUES 
('Admin Jefe', 'admin@empresa.com', '$2y$10$ErZRqlssHUC8nWBehkHcc.qRFaC5jJH4LoHjkW8YeNQOts0gPHovC', 'admin', 'SysAdmin', 'default.png'),
('Usuario A', 'userA@empresa.com', '$2y$10$ErZRqlssHUC8nWBehkHcc.qRFaC5jJH4LoHjkW8YeNQOts0gPHovC', 'empleado', 'Redactor Jefe', 'default.png'),
('Usuario B', 'userB@empresa.com', '$2y$10$ErZRqlssHUC8nWBehkHcc.qRFaC5jJH4LoHjkW8YeNQOts0gPHovC', 'empleado', 'Editor Junior', 'default.png');

-- 5. POSTS DE PRUEBA (Con imágenes simuladas)
-- Hemos añadido la columna 'imagen' al insert

INSERT INTO posts (titulo, contenido, imagen, autor_id, fecha_publicacion) VALUES 
('Bienvenida al nuevo Portal', 'Estamos muy contentos de inaugurar este espacio para todos los trabajadores.', 'oficina.jpg', 1, '2023-10-01 09:00:00'),
('Normativa de vacaciones 2024', 'Recordad que debéis solicitar las vacaciones antes del 30 de marzo.', 'calendario.jpg', 2, '2023-10-02 10:30:00'),
('Menú de la cafetería', 'Esta semana tenemos menú especial por el aniversario de la empresa.', 'comida.jpg', 3, '2023-10-03 12:15:00');