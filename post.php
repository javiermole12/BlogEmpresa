<?php
// 1. CONEXIÓN Y VALIDACIÓN
require_once 'includes/conexion.php';

// Verificar si nos pasan un ID y si es numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_post = $_GET['id'];

// ==========================================
// NUEVO: PROCESAR GUARDADO DEL COMENTARIO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_comentar'])) {
    if (isset($_SESSION['usuario_id'])) {
        $contenido_comentario = mysqli_real_escape_string($conn, trim($_POST['comentario']));
        $autor_id_comentario = $_SESSION['usuario_id'];
        
        if (!empty($contenido_comentario)) {
            $sql_insert = "INSERT INTO comentarios (post_id, autor_id, contenido) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iis", $id_post, $autor_id_comentario, $contenido_comentario);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                // Recargamos la página limpiamente para ver el comentario
                header("Location: post.php?id=$id_post&exito=1");
                exit();
            }
        }
    }
}

// 2. OBTENER DATOS DEL POST Y DEL AUTOR
$sql = "SELECT p.*, u.nombre AS autor_nombre, u.avatar AS autor_avatar, u.rol AS autor_rol, u.cargo AS autor_cargo 
        FROM posts p
        INNER JOIN usuarios u ON p.autor_id = u.id
        WHERE p.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_post);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) == 0) {
    header("Location: index.php");
    exit();
}

$post = mysqli_fetch_assoc($resultado);
$page_title = $post['titulo'];

// Lógica del Avatar del Autor
$ruta_avatar = 'assets/img/avatars/' . $post['autor_avatar'];
if (!file_exists($ruta_avatar) || empty($post['autor_avatar'])) {
    $ruta_avatar = "https://ui-avatars.com/api/?name=".urlencode($post['autor_nombre'])."&background=e9ecef&color=0d6efd";
}

// Lógica de la imagen del post
$ruta_imagen_post = 'assets/img/posts/' . $post['imagen'];
if (!file_exists($ruta_imagen_post)) {
    $ruta_imagen_post = 'assets/img/posts/default.png';
}

include 'includes/header.php'; 
?>

<div class="container py-4">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light p-3 rounded-3 shadow-sm">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">🏠 Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Noticias</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo substr(htmlspecialchars($post['titulo']), 0, 30); ?>...</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-8">
            
            <article class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-light">
                
                <h1 class="fw-bold mb-4" style="font-size: 2.5rem; line-height: 1.2; color: #1a1a1a;">
                    <?php echo htmlspecialchars($post['titulo']); ?>
                </h1>
                
                <div class="d-flex align-items-center mb-4 pb-4 border-bottom text-muted">
                    <img src="<?php echo $ruta_avatar; ?>" alt="Avatar" class="rounded-circle me-3 border" width="45" height="45" style="object-fit:cover;">
                    <div>
                        <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($post['autor_nombre']); ?></span>
                        <small>
                            <i class="bi bi-calendar3"></i> Publicado el <?php echo date("d de F, Y", strtotime($post['fecha_publicacion'])); ?>
                        </small>
                    </div>
                </div>

                <div class="mb-5">
                    <img src="<?php echo $ruta_imagen_post; ?>" class="img-fluid rounded-4 shadow-sm w-100" alt="Imagen del artículo" style="max-height: 450px; object-fit: cover;">
                </div>

                <div class="post-content fs-5 text-break" style="line-height: 1.8; color: #4a4a4a;">
                    <?php echo nl2br(htmlspecialchars($post['contenido'])); ?>
                </div>

            </article>

            <section class="bg-light p-4 p-md-5 rounded-4 border border-light shadow-sm mt-5">
                <h4 class="fw-bold mb-4 border-bottom pb-2">💬 Comentarios</h4>

                <?php if (isset($_GET['exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-pill" role="alert">
                        ✅ Tu comentario ha sido publicado.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="d-flex mb-5">
                        <div class="me-3 d-none d-sm-block">
                            <?php 
                                $mi_avatar = 'assets/img/avatars/' . $_SESSION['avatar'];
                                if (!file_exists($mi_avatar) || empty($_SESSION['avatar'])) {
                                    $mi_avatar = "https://ui-avatars.com/api/?name=".urlencode($_SESSION['nombre'])."&background=e9ecef&color=0d6efd";
                                }
                            ?>
                            <img src="<?php echo $mi_avatar; ?>" class="rounded-circle border shadow-sm" width="50" height="50" style="object-fit:cover;">
                        </div>
                        <div class="flex-grow-1">
                            <form action="post.php?id=<?php echo $id_post; ?>" method="POST">
                                <div class="mb-2">
                                    <textarea name="comentario" class="form-control rounded-4 bg-white" rows="3" placeholder="Escribe tu opinión o pregunta aquí..." required></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" name="btn_comentar" class="btn btn-primary rounded-pill px-4 fw-bold">Enviar comentario</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary text-center rounded-4 mb-5">
                        <i class="bi bi-lock-fill"></i> Debes <a href="login.php" class="fw-bold text-decoration-none">iniciar sesión</a> para participar.
                    </div>
                <?php endif; ?>

                <div class="comentarios-lista">
                    <?php
                    $sql_com = "SELECT c.*, u.nombre, u.avatar, u.cargo 
                                FROM comentarios c 
                                INNER JOIN usuarios u ON c.autor_id = u.id 
                                WHERE c.post_id = ? 
                                ORDER BY c.fecha_creacion DESC";
                    $stmt_com = mysqli_prepare($conn, $sql_com);
                    mysqli_stmt_bind_param($stmt_com, "i", $id_post);
                    mysqli_stmt_execute($stmt_com);
                    $res_com = mysqli_stmt_get_result($stmt_com);

                    if (mysqli_num_rows($res_com) > 0):
                        while ($com = mysqli_fetch_assoc($res_com)):
                            $avatar_com = 'assets/img/avatars/' . $com['avatar'];
                            if (!file_exists($avatar_com) || empty($com['avatar'])) {
                                $avatar_com = "https://ui-avatars.com/api/?name=".urlencode($com['nombre'])."&background=6c757d&color=fff";
                            }
                    ?>
                        <div class="d-flex mb-4">
                            <div class="me-3">
                                <img src="<?php echo $avatar_com; ?>" class="rounded-circle shadow-sm" width="45" height="45" style="object-fit:cover;">
                            </div>
                            <div class="flex-grow-1 bg-white p-3 rounded-4 border shadow-sm position-relative">
                                <div class="position-absolute" style="left: -8px; top: 15px; width: 0; height: 0; border-top: 8px solid transparent; border-bottom: 8px solid transparent; border-right: 8px solid #dee2e6;"></div>
                                <div class="position-absolute" style="left: -7px; top: 15px; width: 0; height: 0; border-top: 8px solid transparent; border-bottom: 8px solid transparent; border-right: 8px solid white;"></div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-primary">
                                        <?php echo htmlspecialchars($com['nombre']); ?>
                                        <span class="badge bg-light text-secondary ms-1 fw-normal border"><?php echo htmlspecialchars($com['cargo'] ?? 'Empleado'); ?></span>
                                    </h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">
                                        🕒 <?php echo date("d/m/Y H:i", strtotime($com['fecha_creacion'])); ?>
                                    </small>
                                </div>
                                <p class="mb-0 text-dark" style="font-size: 0.95rem;">
                                    <?php echo nl2br(htmlspecialchars($com['contenido'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <div class="text-center text-muted p-4">
                            <h1 class="opacity-50">🍃</h1>
                            <p>No hay comentarios todavía. ¡Sé el primero en aportar algo!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <div class="mt-4">
                <a href="index.php" class="btn btn-light border shadow-sm px-4 rounded-pill">
                    &larr; Volver al tablón
                </a>
            </div>

        </div>

        <div class="col-lg-4">
            
            <div class="sticky-top" style="top: 2rem;">
                
                <div class="card shadow-sm border-0 rounded-4 mb-4 text-center overflow-hidden">
                    <div class="bg-light py-4 border-bottom">
                        <img src="<?php echo $ruta_avatar; ?>" class="rounded-circle shadow-sm border border-3 border-white mb-3" width="90" height="90" style="object-fit:cover;">
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($post['autor_nombre']); ?></h5>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($post['autor_cargo'] ?? 'Empleado'); ?></p>
                        
                        <?php if(strtolower($post['autor_rol']) == 'admin'): ?>
                            <span class="badge bg-primary rounded-pill"><i class="bi bi-star-fill"></i> Administración</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill text-white">Redactor</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body bg-white py-3">
                        <small class="text-muted">Contacto interno</small><br>
                        <a href="#" class="btn btn-sm btn-outline-secondary rounded-pill mt-2 px-3">Envíar mensaje</a>
                    </div>
                </div>

                <?php 
                if (isset($_SESSION['usuario_id'])) {
                    $es_autor = ($_SESSION['usuario_id'] == $post['autor_id']);
                    $es_admin = ($_SESSION['rol'] == 'admin');

                    if ($es_autor || $es_admin): 
                ?>
                    <div class="card shadow-sm border-0 rounded-4 border-top border-warning border-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">⚙️ Gestión de Noticia</h5>
                            <p class="text-muted small mb-4">
                                Tienes permisos de moderación sobre este artículo. Puedes actualizar su contenido o retirarlo del portal.
                            </p>
                            
                            <div class="d-grid gap-2">
                                <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning fw-bold text-dark rounded-pill shadow-sm">
                                    ✏️ Editar Publicación
                                </a>
                                <a href="borrar_post.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-danger fw-bold rounded-pill shadow-sm mt-2"
                                   onclick="return confirm('ATENCIÓN: ¿Estás seguro de eliminar este artículo permanentemente? Esta acción no se puede deshacer.');">
                                    🗑️ Eliminar Publicación
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                    endif; 
                } 
                ?>

            </div> 
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>