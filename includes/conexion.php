<?php
// includes/conexion.php

// Iniciar sesión (si no está iniciada ya)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la Base de Datos
$host = 'localhost';
$usuario = 'root';
$password = ''; // XAMPP por defecto viene vacío
$base_datos = 'blog_empresa_db';

// Crear conexión
$conn = mysqli_connect($host, $usuario, $password, $base_datos);

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer codificación de caracteres (evita problemas con tildes)
mysqli_set_charset($conn, "utf8");

// Definir la ruta raíz del proyecto (AJUSTA ESTO SI TU CARPETA SE LLAMA DISTINTO)
// Esto es vital para que los enlaces funcionen desde la carpeta /admin/ también
define('BASE_URL', 'http://localhost/blog_empresa/');
?>