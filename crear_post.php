<?php
// 1. SEGURIDAD Y CONEXIÓN
require_once 'includes/conexion.php';

// Verificar si el usuario está logueado
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
    $autor_id = $_SESSION['usuario_id'];

    // Validaciones
    if (empty($titulo)) { $errores[] = "El título es obligatorio."; }
    if (empty($contenido)) { $errores[] = "El contenido no puede estar vacío."; }

    // -- LOGICA DE IMAGEN --
    $nombre_imagen = null; // Por defecto null (si no suben nada)

    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {
        $archivo = $_FILES['imagen'];
        $tmp = $archivo['tmp_name'];

        // MEJORA DE SEGURIDAD (Auditoría):
        // No confiamos en $_FILES['type'], usamos mime_content_type para ver el archivo real
        $mime_real = mime_content_type($tmp);
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($mime_real, $permitidos)) {
            
            // Crear carpeta si no existe
            if (!is_dir('assets/img/posts')) {
                mkdir('assets/img/posts', 0777, true);
            }

            // Generar extensión correcta basada en el archivo real
            // Esto evita que suban un .php renombrado a .jpg
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            
            // Renombrar archivo único
            $nombre_imagen = "post_" . $autor_id . "_" . time() . "." . $extension;
            
            // Mover archivo
            if (!move_uploaded_file($tmp, 'assets/img/posts/' . $nombre_imagen)) {
                $errores[] = "Error al mover la imagen a la carpeta.";
            }

        } else {
            $errores[] = "El archivo no es una imagen válida (Detectado: $mime_real).";
        }
    }

    /// 3. INSERTAR EN BASE DE DATOS
    if (empty($errores)) {
        // NOTA: Recuerda cambiar 'posts_falso' por 'posts' cuando termines de hacer las capturas
        $sql = "INSERT INTO posts (titulo, contenido, imagen, autor_id, fecha_publicacion) VALUES (?, ?, ?, ?, NOW())";

        try {
            $stmt = mysqli_prepare($conn, $sql);
            // sssi: string, string, string (puede ser null), integer
            mysqli_stmt_bind_param($stmt, "sssi", $titulo, $contenido, $nombre_imagen, $autor_id);
            mysqli_stmt_execute($stmt);

            $exito = true;
            header("refresh:2;url=index.php"); 
            mysqli_stmt_close($stmt);

        } catch (mysqli_sql_exception $e) {
            // ¡Atrapamos la explosión antes de que la vea el usuario!
            
            // 1. Guardamos el error real en el log del servidor
            error_log("Fallo SQL al crear post: " . $e->getMessage());
            
            // 2. Le damos el mensaje genérico y seguro al usuario
            $errores[] = "No se ha podido publicar el artículo debido a un error técnico.";
        }
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