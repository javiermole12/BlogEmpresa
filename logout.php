<?php
// logout.php

// 1. Incluimos la conexión para poder usar la constante BASE_URL
require 'includes/conexion.php';

// 2. Destruir todas las variables de sesión
$_SESSION = array();

// 3. Borrar la cookie de sesión del navegador
// Esto es importante para la auditoría de seguridad: invalida el token del lado del cliente.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. Redirigir al usuario al Login
header("Location: " . BASE_URL . "login.php");
exit();
?>