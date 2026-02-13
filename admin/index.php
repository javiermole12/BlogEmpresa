<?php
// 1. CONEXIÓN Y SEGURIDAD (CRÍTICO)
// "Salimos" de la carpeta admin (../) para buscar la conexión
require_once '../includes/conexion.php';

// VERIFICACIÓN DE ROL: Esto es lo más importante de la auditoría.
// Si no hay sesión O el rol no es 'admin', lo echamos fuera inmediatamente.
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 2. OBTENER ESTADÍSTICAS (KPIs)
// Contar usuarios
$sql_users_count = "SELECT COUNT(*) as total FROM usuarios";
$res_users_count = mysqli_query($conn, $sql_users_count);
$total_usuarios = mysqli_fetch_assoc($res_users_count)['total'];

// Contar posts
$sql_posts_count = "SELECT COUNT(*) as total FROM posts";
$res_posts_count = mysqli_query($conn, $sql_posts_count);
$total_posts = mysqli_fetch_assoc($res_posts_count)['total'];

// 3. OBTENER LISTAS DE DATOS
// Lista de todos los usuarios
$usuarios = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY id DESC");

// Lista de todos los posts (con nombre del autor)
$sql_posts = "SELECT posts.*, usuarios.nombre AS autor_nombre 
              FROM posts 
              LEFT JOIN usuarios ON posts.autor_id = usuarios.id 
              ORDER BY posts.id DESC";
$posts = mysqli_query($conn, $sql_posts);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        
        <div class="col-md-2">
            <div class="list-group shadow-sm">
                <a href="#" class="list-group-item list-group-item-action active fw-bold">
                    ⚙️ Dashboard
                </a>
                <a href="usuarios.php" class="list-group-item list-group-item-action">👥 Gestionar Usuarios</a>
                <a href="posts.php" class="list-group-item list-group-item-action">📰 Gestionar Noticias</a>
                <a href="../index.php" class="list-group-item list-group-item-action text-primary">🏠 Volver al Blog</a>
                <a href="../logout.php" class="list-group-item list-group-item-action text-danger">🚪 Salir</a>
            </div>
        </div>

        <div class="col-md-10">
            
            <h2 class="mb-4">Panel de Administración</h2>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase">Usuarios</h6>
                                    <h2 class="display-4 fw-bold"><?php echo $total_usuarios; ?></h2>
                                </div>
                                <div class="fs-1">👥</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase">Publicaciones</h6>
                                    <h2 class="display-4 fw-bold"><?php echo $total_posts; ?></h2>
                                </div>
                                <div class="fs-1">📰</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-dark bg-warning shadow h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase">Tu Rol</h6>
                                    <h3 class="fw-bold">ADMIN</h3>
                                </div>
                                <div class="fs-1">👑</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-5" id="usuarios">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">👥 Listado de Usuarios</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = mysqli_fetch_assoc($usuarios)): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td>
                                        <img src="../assets/img/avatars/<?php echo !empty($user['avatar']) ? $user['avatar'] : 'default.png'; ?>" 
                                             class="rounded-circle me-2" width="30" height="30">
                                        <?php echo htmlspecialchars($user['nombre']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if($user['rol'] == 'admin'): ?>
                                            <span class="badge bg-warning text-dark">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Empleado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($user['id'] != $_SESSION['usuario_id']): ?>
                                            <a href="borrar_usuario.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('¿Estás seguro de ELIMINAR a este usuario? Se borrarán todos sus posts.');">
                                                Eliminar
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Tu cuenta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow" id="posts">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">📰 Moderación de Noticias</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Autor</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($post = mysqli_fetch_assoc($posts)): ?>
                                <tr>
                                    <td>#<?php echo $post['id']; ?></td>
                                    <td>
                                        <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars(substr($post['titulo'], 0, 40)) . '...'; ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['autor_nombre']); ?></td>
                                    <td><?php echo date("d/m/y", strtotime($post['fecha_publicacion'])); ?></td>
                                    <td>
                                        <a href="../editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <a href="../borrar_post.php?id=<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('¿Borrar este post permanentemente?');">
                                            Borrar
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>