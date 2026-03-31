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
    // El detalle técnico va al log del servidor
    error_log("Error crítico de conexión a BD: " . mysqli_connect_error());
    // El usuario solo ve un mensaje genérico
    die("Error interno del servidor. Por favor, inténtelo de nuevo más tarde.");
}

// Establecer codificación de caracteres (evita problemas con tildes)
mysqli_set_charset($conn, "utf8");

// Definir la ruta raíz del proyecto (AJUSTA ESTO SI TU CARPETA SE LLAMA DISTINTO)
// Esto es vital para que los enlaces funcionen desde la carpeta /admin/ también
define('BASE_URL', 'http://localhost/BlogEmpresa/');
?>