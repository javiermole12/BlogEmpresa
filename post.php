<?php
// 1. CONEXIÓN Y VALIDACIÓN
require_once 'includes/conexion.php';

// Verificar si nos pasan un ID y si es numérico (Seguridad básica)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_post = $_GET['id'];

// 2. OBTENER DATOS DEL POST Y DEL AUTOR
// Usamos sentencias preparadas para evitar Inyección SQL a través de la URL
$sql = "SELECT p.*, u.nombre AS autor_nombre, u.avatar AS autor_avatar, u.rol AS autor_rol 
        FROM posts p
        INNER JOIN usuarios u ON p.autor_id = u.id
        WHERE p.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_post);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si el post existe
if (mysqli_num_rows($resultado) == 0) {
    // Si pones un ID que no existe (ej: post.php?id=9999), te manda al inicio
    header("Location: index.php");
    exit();
}

$post = mysqli_fetch_assoc($resultado);

// Título de la página dinámico
$page_title = $post['titulo'];
include 'includes/header.php'; 
?>

<div class="container py-5">
    
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($post['titulo']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            
            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($post['titulo']); ?></h1>
            
            <div class="d-flex align-items-center mb-4 text-muted">
                <img src="assets/img/avatars/<?php echo !empty($post['autor_avatar']) ? $post['autor_avatar'] : 'default.png'; ?>" 
                     alt="Avatar" class="rounded-circle me-2" width="40" height="40" style="object-fit:cover;">
                
                <div>
                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($post['autor_nombre']); ?></span>
                    <span class="mx-2">•</span>
                    <small>Publicado el <?php echo date("d M, Y", strtotime($post['fecha_publicacion'])); ?></small>
                </div>
            </div>

            <?php if (!empty($post['imagen']) && file_exists('assets/img/posts/' . $post['imagen'])): ?>
                <div class="mb-4">
                    <img src="assets/img/posts/<?php echo $post['imagen']; ?>" class="img-fluid rounded shadow-sm w-100" alt="Imagen del artículo">
                </div>
            <?php endif; ?>

            <div class="post-content fs-5 lh-lg text-break">
                <?php 
                    // nl2br convierte los saltos de línea (\n) en etiquetas <br> de HTML
                    // htmlspecialchars evita que se ejecute código HTML malicioso
                    echo nl2br(htmlspecialchars($post['contenido'])); 
                ?>
            </div>

            <hr class="my-5">

            <a href="index.php" class="btn btn-outline-primary">&larr; Volver al listado</a>

        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title">Opciones</h5>
                    
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        
                        <?php 
                        // LÓGICA DE PERMISOS (Igual que en index.php)
                        // ¿Soy el dueño O soy admin?
                        $es_autor = ($_SESSION['usuario_id'] == $post['autor_id']);
                        $es_admin = ($_SESSION['rol'] == 'admin');

                        if ($es_autor || $es_admin): 
                        ?>
                            <div class="d-grid gap-2">
                                <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning">
                                    ✏️ Editar Publicación
                                </a>
                                <a href="borrar_post.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('ATENCIÓN: ¿Estás seguro de eliminar este artículo permanentemente?');">
                                    🗑️ Eliminar Publicación
                                </a>
                            </div>
                            <p class="text-muted small mt-3 text-center">
                                * Tienes permisos de gestión sobre este contenido.
                            </p>
                        <?php else: ?>
                            <p class="text-muted">No tienes permisos para editar este artículo.</p>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>Debes iniciar sesión para realizar acciones.</p>
                        <a href="login.php" class="btn btn-primary w-100">Iniciar Sesión</a>
                    <?php endif; ?>

                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body text-center">
                    <h6 class="text-uppercase text-muted mb-3">Sobre el Autor</h6>
                    <img src="assets/img/avatars/<?php echo !empty($post['autor_avatar']) ? $post['autor_avatar'] : 'default.png'; ?>" 
                         class="rounded-circle mb-3" width="80" height="80" style="object-fit:cover;">
                    <h5 class="card-title"><?php echo htmlspecialchars($post['autor_nombre']); ?></h5>
                    <span class="badge bg-secondary"><?php echo strtoupper($post['autor_rol']); ?></span>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>