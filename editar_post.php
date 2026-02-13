<?php
// 1. CONEXIÓN Y SEGURIDAD
require_once 'includes/conexion.php';

// Si no hay sesión, fuera
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_post = $_GET['id'];
$errores = [];
$exito = false;

// 2. OBTENER DATOS ACTUALES DEL POST (Para verificar permisos y rellenar form)
// Usamos prepared statements por seguridad
$sql_check = "SELECT * FROM posts WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt, "i", $id_post);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$post_actual = mysqli_fetch_assoc($resultado);

// Verificar si existe el post
if (!$post_actual) {
    header("Location: index.php");
    exit();
}

// 3. VERIFICACIÓN CRÍTICA DE PERMISOS (AUDITORÍA)
// Solo el Autor O el Admin pueden editar
$es_autor = ($post_actual['autor_id'] == $_SESSION['usuario_id']);
$es_admin = ($_SESSION['rol'] == 'admin');

if (!$es_autor && !$es_admin) {
    // Si intenta entrar un usuario listo cambiando la URL, lo echamos
    die("❌ ACCESO DENEGADO: No tienes permisos para editar este contenido.");
}

// 4. PROCESAR EL FORMULARIO (UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recoger datos nuevos
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    
    // Validaciones
    if (empty($titulo)) $errores[] = "El título es obligatorio.";
    if (empty($contenido)) $errores[] = "El contenido es obligatorio.";

    // -- Lógica de Imagen --
    $nombre_imagen = $post_actual['imagen']; // Por defecto, mantenemos la vieja
    
    // Si suben una nueva imagen...
    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {
        $archivo = $_FILES['imagen'];
        $tipo = $archivo['type'];
        $tmp = $archivo['tmp_name'];
        
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($tipo, $permitidos)) {
            // Generar nuevo nombre
            $nombre_nuevo = "post_" . $_SESSION['usuario_id'] . "_" . time() . ".jpg";
            
            // Subir la nueva
            if (move_uploaded_file($tmp, 'assets/img/posts/' . $nombre_nuevo)) {
                $nombre_imagen = $nombre_nuevo;
                
                // OPCIONAL: Borrar la imagen vieja del servidor para no acumular basura
                // if (!empty($post_actual['imagen']) && file_exists('assets/img/posts/'.$post_actual['imagen'])) {
                //     unlink('assets/img/posts/'.$post_actual['imagen']);
                // }
            }
        } else {
            $errores[] = "Formato de imagen no válido.";
        }
    }

    // -- SQL UPDATE --
    if (empty($errores)) {
        $sql_update = "UPDATE posts SET titulo = ?, contenido = ?, imagen = ? WHERE id = ?";
        
        $stmt_up = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_up, "sssi", $titulo, $contenido, $nombre_imagen, $id_post);
        
        if (mysqli_stmt_execute($stmt_up)) {
            $exito = true;
            // Actualizamos la variable $post_actual para que el formulario muestre los cambios al instante
            $post_actual['titulo'] = $titulo;
            $post_actual['contenido'] = $contenido;
            $post_actual['imagen'] = $nombre_imagen;
            
            header("refresh:2;url=post.php?id=$id_post"); // Volver al post tras 2 seg
        } else {
            $errores[] = "Error al actualizar: " . mysqli_error($conn);
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary">✏️ Editar Publicación</h2>
                <a href="post.php?id=<?php echo $id_post; ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>

            <?php if ($exito): ?>
                <div class="alert alert-success shadow text-center">
                    <h4>¡Cambios Guardados!</h4>
                    <p>Redirigiendo al artículo...</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $e) echo "<li>$e</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    
                    <form action="editar_post.php?id=<?php echo $id_post; ?>" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-bold">Título</label>
                            <input type="text" name="titulo" class="form-control form-control-lg" 
                                   value="<?php echo htmlspecialchars($post_actual['titulo']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Imagen Destacada</label>
                            
                            <?php if (!empty($post_actual['imagen'])): ?>
                                <div class="mb-2 p-2 border rounded bg-light text-center">
                                    <p class="small text-muted mb-1">Imagen Actual:</p>
                                    <img src="assets/img/posts/<?php echo $post_actual['imagen']; ?>" 
                                         alt="Actual" style="max-height: 150px; border-radius: 5px;">
                                </div>
                            <?php endif; ?>

                            <input type="file" name="imagen" class="form-control" accept="image/*">
                            <div class="form-text">Sube una nueva solo si quieres cambiar la actual.</div>
                        </div>

                        <div class="mb-4">
                            <label for="contenido" class="form-label fw-bold">Contenido</label>
                            <textarea name="contenido" rows="10" class="form-control" required><?php echo htmlspecialchars($post_actual['contenido']); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg text-white">💾 Guardar Cambios</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>