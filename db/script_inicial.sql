-- ==========================================
-- SCRIPT DE INSTALACIÓN: BLOG CORPORATIVO
-- ==========================================

-- 1. PREPARACIÓN DE LA BASE DE DATOS
-- Borramos la BD si existe para empezar de cero (útil para auditorías)
DROP DATABASE IF EXISTS blog_empresa_db;

-- Creamos la base de datos
CREATE DATABASE blog_empresa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_empresa_db;

-- 2. TABLA DE USUARIOS (Con roles y perfil)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Aquí va el hash encriptado
    rol ENUM('admin', 'empleado') DEFAULT 'empleado',
    cargo VARCHAR(100) DEFAULT 'Nuevo empleado',
    avatar VARCHAR(255) DEFAULT 'default.png',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABLA DE POSTS (Recurso principal)
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    autor_id INT,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. DATOS DE PRUEBA (OBLIGATORIOS PARA LA ENTREGA)
-- La contraseña para todos estos usuarios es: "1234"
-- El hash generado abajo ($2y$10$...) es la versión encriptada de "1234"

INSERT INTO usuarios (nombre, email, password, rol, cargo, avatar) VALUES 
('Admin Jefe', 'admin@empresa.com', '$2y$10$ErZRqlssHUC8nWBehkHcc.qRFaC5jJH4LoHjkW8YeNQOts0gPHovC', 'admin', 'SysAdmin', 'admin.png'),
('Usuario A', 'userA@empresa.com', '$2y$10$ErZRqlssHUC8nWBehkHcc.qRFaC5jJH4LoHjkW8YeNQOts0gPHovC', 'empleado', 'Redactor Jefe', 'userA.png'),
('Usuario B', 'userB@empresa.com', '$2y$10$ErZRqlssHUC8nWBehkHcc.qRFaC5jJH4LoHjkW8YeNQOts0gPHovC', 'empleado', 'Editor Junior', 'userB.png');

-- 5. DATOS DE PRUEBA PARA POSTS
INSERT INTO posts (titulo, contenido, autor_id, fecha_publicacion) VALUES 
('Bienvenida al nuevo Portal del Empleado', 'Estamos muy contentos de inaugurar este espacio para todos los trabajadores. Aquí podréis encontrar noticias y actualizaciones.', 1, '2023-10-01 09:00:00'),
('Normativa de vacaciones 2024', 'Recordad que debéis solicitar las vacaciones antes del 30 de marzo para poder cuadrar los turnos correctamente.', 2, '2023-10-02 10:30:00'),
('Menú de la cafetería', 'Esta semana tenemos menú especial por el aniversario de la empresa. ¡No os lo perdáis!', 3, '2023-10-03 12:15:00');