<?php 
require_once 'includes/conexion.php';
include 'includes/header.php'; 
?>

<div class="p-5 mb-5 rounded-4 shadow-lg position-relative overflow-hidden" 
     style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
    
    <div class="position-absolute top-0 end-0 p-5 opacity-10">
        <h1 class="display-1 fw-bold">🏢</h1>
    </div>

    <div class="container-fluid py-3 position-relative z-index-1">
        <h1 class="display-4 fw-bold">Hub Corporativo</h1>
        
        <?php if(!isset($_SESSION['usuario_id'])): ?>
            <p class="col-md-8 fs-5 mt-3 text-light opacity-75">
                Tu espacio central para normativas, noticias y comunicación interna. 
                Identifícate para acceder a los recursos de la empresa.
            </p>
            <div class="d-flex gap-3 mt-4">
                <a href="login.php" class="btn btn-light btn-lg fw-bold text-primary shadow-sm px-4">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </a>
                <a href="registro.php" class="btn btn-outline-light btn-lg px-4">
                    Nuevo Empleado
                </a>
            </div>
        <?php else: ?>
            <p class="col-md-8 fs-4 mt-2">
                ¡Hola de nuevo, <span class="fw-bold text-warning"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>! 👋
            </p>
            <p class="fs-6 opacity-75 mb-4">Revisa las últimas actualizaciones de tu equipo.</p>
            
            <a href="crear_post.php" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm px-4">
                ➕ Nueva Publicación
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($_SESSION['usuario_id'])): ?>
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-2">
            <h3 class="fw-bold text-dark mb-0">📰 Feed de Noticias</h3>
            <span class="badge bg-primary rounded-pill px-3 py-2">Últimas actualizaciones</span>
        </div>

        <div class="row g-4"> 
            <?php
            // CONSULTA A LA BASE DE DATOS
            $sql = "SELECT posts.*, usuarios.nombre AS autor_nombre, usuarios.avatar AS autor_avatar, usuarios.cargo AS autor_cargo 
                    FROM posts 
                    LEFT JOIN usuarios ON posts.autor_id = usuarios.id 
                    ORDER BY posts.fecha_publicacion DESC";
            
            $posts = mysqli_query($conn, $sql);

            if (mysqli_num_rows($posts) > 0):
                while($post = mysqli_fetch_assoc($posts)): 
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="transition: transform 0.2s, box-shadow 0.2s;">
                        
                        <?php 
                        $ruta_imagen = 'assets/img/posts/default.png';
                        if (!empty($post['imagen']) && file_exists('assets/img/posts/' . $post['imagen'])) {
                            $ruta_imagen = 'assets/img/posts/' . $post['imagen'];
                        }
                        ?>
                        <div class="position-relative">
                            <img src="<?php echo $ruta_imagen; ?>" class="w-100" alt="Imagen del post" style="height: 220px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-dark bg-opacity-75 p-2 rounded-3 shadow-sm">
                                    📅 <?php echo date("d M Y", strtotime($post['fecha_publicacion'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            
                            <h5 class="card-title fw-bold mb-3">
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="text-decoration-none text-dark stretched-link">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-secondary mb-4 flex-grow-1" style="font-size: 0.95rem;">
                                <?php echo substr(htmlspecialchars($post['contenido']), 0, 110) . '...'; ?>
                            </p>

                            <div class="d-flex align-items-center mt-auto border-top pt-3 position-relative" style="z-index: 2;">
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                   👤
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold fs-6"><?php echo htmlspecialchars($post['autor_nombre']); ?></h6>
                                    <small class="text-muted" style="font-size: 0.8rem;">
                                        <?php echo isset($post['autor_cargo']) ? htmlspecialchars($post['autor_cargo']) : 'Empleado'; ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <?php 
                        $es_dueno = ($_SESSION['usuario_id'] == $post['autor_id']);
                        $es_admin = ($_SESSION['rol'] == 'admin');

                        if ($es_dueno || $es_admin): 
                        ?>
                            <div class="card-footer bg-light border-0 px-4 py-3 d-flex justify-content-between position-relative" style="z-index: 2;">
                                <span class="text-muted small align-self-center">Tus acciones:</span>
                                <div>
                                    <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Editar">
                                        ✏️ Editar
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-1" 
                                            title="Borrar" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalConfirmarBorrado" 
                                            data-post-id="<?php echo $post['id']; ?>">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="col-12 mt-4">
                    <div class="p-5 text-center bg-light rounded-4 border border-dashed border-2 text-muted">
                        <div class="mb-3" style="font-size: 3rem;">📭</div>
                        <h4 class="fw-bold text-dark">Tablón limpio</h4>
                        <p class="mb-4">No hay avisos ni normativas publicadas en este momento.</p>
                        <a href="crear_post.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                            Redactar el primer aviso
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="modalConfirmarBorrado" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h5 class="modal-title fw-bold text-danger" id="modalLabel">
            ⚠️ Confirmar Eliminación
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-4 px-4 fs-5 text-center text-dark">
        ¿Estás seguro de que deseas borrar esta publicación?<br>
        <small class="text-muted fs-6">Esta acción es permanente y no se puede deshacer.</small>
      </div>
      <div class="modal-footer border-top-0 justify-content-center pt-0 pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
        <a href="#" id="btnConfirmarBorrar" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Sí, borrar publicación</a>
      </div>
    </div>
  </div>
</div>

<style>
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .border-dashed {
        border-style: dashed !important;
        border-color: #dee2e6 !important;
    }
    .stretched-link::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: "";
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalBorrado = document.getElementById('modalConfirmarBorrado');
        
        // Cuando el modal está a punto de mostrarse...
        modalBorrado.addEventListener('show.bs.modal', function (event) {
            // 1. Detectar qué botón específico activó el modal
            var boton = event.relatedTarget;
            
            // 2. Extraer el ID del post desde el atributo 'data-post-id'
            var postId = boton.getAttribute('data-post-id');
            
            // 3. Buscar el botón rojo de confirmación dentro del modal
            var btnConfirmar = modalBorrado.querySelector('#btnConfirmarBorrar');
            
            // 4. Cambiar el href para que apunte al archivo PHP correcto
            btnConfirmar.href = 'borrar_post.php?id=' + postId;
        });
    });
</script>

<?php include 'includes/footer.php'; ?>