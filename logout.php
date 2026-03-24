<?php
require 'includes/conexion.php';

// 1. ESCUDO 1: BLOQUEAR MÉTODO GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = "⚠️ Acción no permitida. Usa el botón de Cerrar Sesión del menú.";
    $_SESSION['tipo_mensaje'] = "warning";
    header("Location: index.php");
    exit();
}

// 2. ESCUDO 2: VERIFICAR TOKEN CSRF
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['mensaje'] = "🛑 Bloqueo de seguridad: Petición CSRF inválida.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: index.php");
    exit();
}

// 3. Destruir sesión y cookie
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

header("Location: " . BASE_URL . "login.php");
exit();
?>