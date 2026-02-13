<?php
// 1. SEGURIDAD Y CONEXIÓN
require_once '../includes/conexion.php';

// Verificación de Admin (Sin esto, cualquiera entra)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 2. LÓGICA DE BÚSQUEDA (Filtro)
$where_clause = "";
$busqueda = "";

if (isset($_GET['busqueda'])) {
    $busqueda = mysqli_real_escape_string($conn, $_GET['busqueda']);
    // Buscamos en el título del post O en el nombre del autor
    $where_clause = "WHERE posts.titulo LIKE '%$busqueda%' OR usuarios.nombre LIKE '%$busqueda%'";
}

// 3. CONSULTA PRINCIPAL
// Traemos posts + datos del autor
$sql = "SELECT posts.*, usuarios.nombre AS autor_nombre, usuarios.email AS autor_email 
        FROM posts 
        LEFT JOIN usuarios ON posts.autor_id = usuarios.id 
        $where_clause
        ORDER BY posts.fecha_publicacion DESC";

$posts = mysqli_query($conn, $sql);
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary fw-bold">📰 Gestión de Publicaciones</h2>
            <p class="text-muted">Administra todas las noticias del blog corporativo.</p>
        </div>
        <div>
            <a href="../crear_post.php" class="btn btn-success">
                ➕ Nueva Publicación
            </a>
            <a href="index.php" class="btn btn-outline-secondary ms-2">
                &larr; Volver al Panel
            </a>
        </div>
    </div>

    <div class="card mb-4 bg-light border-0">
        <div class="card-body">
            <form action="posts.php" method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-bold">Filtrar:</label>
                </div>
                <div class="col-md-6">
                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar por título o autor..." value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if(!empty($busqueda)): ?>
                        <a href="posts.php" class="btn btn-outline-danger">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Imagen</th>
                            <th style="width: 30%;">Título</th>
                            <th>Autor</th>
                            <th>Publicado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($posts) > 0):
                            while ($post = mysqli_fetch_assoc($posts)): 
                        ?>
                            <tr>
                                <td class="ps-3 fw-bold">#<?php echo $post['id']; ?></td>
                                
                                <td>
                                    <?php if (!empty($post['imagen']) && file_exists('../assets/img/posts/' . $post['imagen'])): ?>
                                        <img src="../assets/img/posts/<?php echo $post['imagen']; ?>" 
                                             class="rounded shadow-sm" style="width: 50px; height: 35px; object-fit: cover;">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Sin img</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank" class="fw-bold text-decoration-none">
                                        <?php echo htmlspecialchars(substr($post['titulo'], 0, 50)) . (strlen($post['titulo']) > 50 ? '...' : ''); ?>
                                    </a>
                                </td>

                                <td>
                                    <div class="small fw-bold"><?php echo htmlspecialchars($post['autor_nombre']); ?></div>
                                    <div class="small text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($post['autor_email']); ?></div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo date("d/m/y H:i", strtotime($post['fecha_publicacion'])); ?>
                                    </span>
                                </td>

                                <td class="text-end pe-3">
                                    <div class="btn-group" role="group">
                                        <a href="../editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            ✏️
                                        </a>
                                        
                                        <a href="../borrar_post.php?id=<?php echo $post['id']; ?>&from=admin" 
                                           class="btn btn-sm btn-danger" 
                                           title="Eliminar permanentemente"
                                           onclick="return confirm('⚠️ ¿Estás seguro de borrar este post?\n\nTítulo: <?php echo htmlspecialchars($post['titulo']); ?>');">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No se encontraron publicaciones.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            Total de registros: <?php echo mysqli_num_rows($posts); ?>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>