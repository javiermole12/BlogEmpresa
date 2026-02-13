<?php
// 1. SEGURIDAD Y CONEXIÓN
require_once 'includes/conexion.php';

// Verificar si el usuario está logueado (Si no, patada al login)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$errores = [];
$exito = false;

// 2. PROCESAR EL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recoger y limpiar datos
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $autor_id = $_SESSION['usuario_id']; // EL AUTOR ES EL QUE ESTÁ LOGUEADO

    // Validaciones
    if (empty($titulo)) { $errores[] = "El título es obligatorio."; }
    if (empty($contenido)) { $errores[] = "El contenido no puede estar vacío."; }

    // -- LOGICA DE IMAGEN (Opcional) --
    $nombre_imagen = null; // Por defecto null

    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {
        $archivo = $_FILES['imagen'];
        $tipo = $archivo['type'];
        $tmp = $archivo['tmp_name'];

        // Validar tipo de archivo (Seguridad: Solo imágenes)
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($tipo, $permitidos)) {
            // Crear carpeta si no existe
            if (!is_dir('assets/img/posts')) {
                mkdir('assets/img/posts', 0777, true);
            }

            // Renombrar archivo para evitar duplicados y caracteres raros
            // Ejemplo: post_5_16978322.jpg
            $nombre_imagen = "post_" . $autor_id . "_" . time() . ".jpg";
            
            // Mover archivo
            move_uploaded_file($tmp, 'assets/img/posts/' . $nombre_imagen);
        } else {
            $errores[] = "El archivo no es una imagen válida (solo JPG, PNG, GIF).";
        }
    }

    // 3. INSERTAR EN BASE DE DATOS
    if (empty($errores)) {
        // Usamos Sentencias Preparadas (Evita inyección SQL en el contenido)
        $sql = "INSERT INTO posts (titulo, contenido, imagen, autor_id, fecha_publicacion) VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        // "ssss" -> String, String, String, Integer (pero autor_id pasa como int, cuidado con el tipo)
        // Corrección: el ID es int, así que "sssi"
        mysqli_stmt_bind_param($stmt, "sssi", $titulo, $contenido, $nombre_imagen, $autor_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $exito = true;
            // Redirigir al index después de 1 segundo para ver el post
            header("refresh:2;url=index.php"); 
        } else {
            $errores[] = "Error al guardar el post: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary">✍️ Crear Nueva Publicación</h2>
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>

            <?php if ($exito): ?>
                <div class="alert alert-success text-center shadow">
                    <h4>¡Publicado con éxito!</h4>
                    <p>Tu post ya es visible en el blog. Redirigiendo...</p>
                </div>
            <?php else: ?>

                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">

                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $e): ?>
                                        <li><?php echo $e; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="crear_post.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-4">
                                <label for="titulo" class="form-label fw-bold">Título del Artículo</label>
                                <input type="text" name="titulo" id="titulo" class="form-control form-control-lg" placeholder="Ej: Resultados del Q3..." value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="imagen" class="form-label fw-bold">Imagen Destacada (Opcional)</label>
                                <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
                                <div class="form-text">Se ve mejor con imágenes horizontales.</div>
                            </div>

                            <div class="mb-4">
                                <label for="contenido" class="form-label fw-bold">Contenido</label>
                                <textarea name="contenido" id="contenido" rows="8" class="form-control" placeholder="Escribe aquí tu noticia..." required><?php echo isset($_POST['contenido']) ? htmlspecialchars($_POST['contenido']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">🚀 Publicar Ahora</button>
                            </div>

                        </form>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>