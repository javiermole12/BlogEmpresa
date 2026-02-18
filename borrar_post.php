<?php
// borrar_post.php

// 1. CONEXIÓN Y SEGURIDAD INICIAL
require_once 'includes/conexion.php';

// Si no estás logueado, fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Si no pasas un ID válido por la URL (ej: borrar_post.php?id=5), fuera
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_post = $_GET['id'];
$usuario_actual = $_SESSION['usuario_id'];
$rol_actual = $_SESSION['rol'];

// 2. OBTENER INFORMACIÓN DEL POST
// Necesitamos saber quién es el autor (para permisos) y el nombre de la imagen (para borrarla)
$sql_check = "SELECT autor_id, imagen FROM posts WHERE id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $id_post);
mysqli_stmt_execute($stmt_check);
$resultado = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($resultado) > 0) {
    $post = mysqli_fetch_assoc($resultado);

    // ==========================================
    // 3. LÓGICA DE AUTORIZACIÓN (VITAL PARA AUDITORÍA)
    // ==========================================
    // ¿El usuario que intenta borrar es el DUEÑO del post O es un ADMIN?
    if ($usuario_actual == $post['autor_id'] || $rol_actual === 'admin') {
        
        // A. Borrar la imagen del servidor (para no ocupar espacio a lo tonto)
        if (!empty($post['imagen']) && $post['imagen'] !== 'default.png') {
            $ruta_imagen = 'assets/img/posts/' . $post['imagen'];
            // Si el archivo físico existe, lo eliminamos con unlink()
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen); 
            }
        }

        // B. Borrar el registro de la base de datos
        $sql_delete = "DELETE FROM posts WHERE id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id_post);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            $_SESSION['mensaje'] = "La publicación y sus comentarios han sido eliminados.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Hubo un error al eliminar la publicación en la BD.";
            $_SESSION['tipo_mensaje'] = "danger";
        }

    } else {
        // Intento de "Hackeo" (Alguien intentó poner el ID de otro en la URL)
        $_SESSION['mensaje'] = "ACCESO DENEGADO: No tienes permisos para borrar contenido ajeno.";
        $_SESSION['tipo_mensaje'] = "danger";
    }

} else {
    $_SESSION['mensaje'] = "La publicación que intentas borrar ya no existe.";
    $_SESSION['tipo_mensaje'] = "warning";
}

// 4. REDIRECCIÓN FINAL
// Pase lo que pase, devolvemos al usuario al inicio para que vea el mensaje
header("Location: index.php");
exit();
?>