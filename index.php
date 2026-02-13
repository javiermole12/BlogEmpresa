<?php 
// 1. Incluimos la conexión y el header
// Nota: header.php ya incluye la conexión, pero por seguridad y claridad lo declaramos.
require_once 'includes/conexion.php';
include 'includes/header.php'; 
?>

<div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
    <div class="container-fluid py-2">
        <h1 class="display-5 fw-bold text-primary">🏢 Blog Corporativo</h1>
        
        <?php if(!isset($_SESSION['usuario_id'])): ?>
            <p class="col-md-8 fs-4">Bienvenido al portal del empleado. Inicia sesión para ver las últimas noticias, normativas y eventos de la empresa.</p>
            <a href="login.php" class="btn btn-primary btn-lg mt-3">🔐 Iniciar Sesión</a>
            <a href="registro.php" class="btn btn-outline-secondary btn-lg mt-3 ms-2">Crear cuenta</a>
        <?php else: ?>
            <p class="col-md-8 fs-4">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>. Aquí tienes las últimas novedades.</p>
            <a href="crear_post.php" class="btn btn-success btn-lg mt-3">✍️ Publicar Noticia</a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($_SESSION['usuario_id'])): ?>

    <div class="container">
        <div class="row">
            <div class="col-12 mb-4">
                <h3 class="border-bottom pb-2">📰 Últimas Publicaciones</h3>
            </div>

            <?php
            // 2. CONSULTA A LA BASE DE DATOS
            // Usamos LEFT JOIN para traer el nombre del autor desde la tabla 'usuarios'
            $sql = "SELECT posts.*, usuarios.nombre AS autor_nombre, usuarios.avatar AS autor_avatar 
                    FROM posts 
                    LEFT JOIN usuarios ON posts.autor_id = usuarios.id 
                    ORDER BY posts.fecha_publicacion DESC";
            
            $posts = mysqli_query($conn, $sql);

            // Comprobamos si hay posts
            if (mysqli_num_rows($posts) > 0):
                // BUCLE: Recorremos cada post
                while($post = mysqli_fetch_assoc($posts)): 
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow transition">
                        
                        <?php 
    // 1. Definimos la imagen por defecto
    $ruta_imagen = 'assets/img/posts/default.png';

    // 2. Comprobamos si el post tiene imagen Y si el archivo existe físicamente
    if (!empty($post['imagen']) && file_exists('assets/img/posts/' . $post['imagen'])) {
        $ruta_imagen = 'assets/img/posts/' . $post['imagen'];
    }
?>

<img src="<?php echo $ruta_imagen; ?>" class="card-img-top" alt="Imagen del post" style="height: 200px; object-fit: cover;">

                        <div class="card-body">
                            <h5 class="card-title fw-bold">
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted small mb-2">
                                <span class="me-2">👤 <?php echo htmlspecialchars($post['autor_nombre']); ?></span>
                                <span>📅 <?php echo date("d/m/Y", strtotime($post['fecha_publicacion'])); ?></span>
                            </p>

                            <p class="card-text">
                                <?php echo substr(htmlspecialchars($post['contenido']), 0, 100) . '...'; ?>
                            </p>
                        </div>

                        <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pb-3">
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">Leer más</a>

                            <?php 
                            $es_dueno = ($_SESSION['usuario_id'] == $post['autor_id']);
                            $es_admin = ($_SESSION['rol'] == 'admin');

                            if ($es_dueno || $es_admin): 
                            ?>
                                <div>
                                    <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning text-white" title="Editar">
                                        ✏️
                                    </a>
                                    <a href="borrar_post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       title="Borrar"
                                       onclick="return confirm('¿Estás seguro de que quieres borrar este post? No se puede deshacer.');">
                                        🗑️
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php 
                endwhile; // Fin del bucle
            else: 
            ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>📭 Todavía no hay noticias</h4>
                        <p>Sé el primero en publicar algo interesante para la empresa.</p>
                        <a href="crear_post.php" class="btn btn-primary">Crear primera publicación</a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

<?php endif; // Fin del if(isset session) ?>

<?php include 'includes/footer.php'; ?>