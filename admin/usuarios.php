<?php
// 1. SEGURIDAD Y CONEXIÓN
require_once '../includes/conexion.php';

// Verificación de Admin (Vital)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// 2. LÓGICA: CAMBIAR ROL (Si se envía el formulario)
$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'cambiar_rol') {
    $id_usuario_editar = $_POST['user_id'];
    $nuevo_rol = $_POST['nuevo_rol'];

    // Evitar que el admin se quite el rol a sí mismo (Seguridad)
    if ($id_usuario_editar == $_SESSION['usuario_id']) {
        $mensaje = "❌ No puedes cambiar tu propio rol. Pide a otro admin que lo haga.";
        $tipo_mensaje = "danger";
    } else {
        // Actualizamos en la BD
        $sql_update = "UPDATE usuarios SET rol = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt, "si", $nuevo_rol, $id_usuario_editar);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "✅ Rol actualizado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al cambiar rol.";
            $tipo_mensaje = "danger";
        }
    }
}

// 3. LÓGICA: BORRAR USUARIO (Si viene por GET action=borrar)
// Normalmente esto se hace en un archivo aparte, pero para ahorrar archivos lo incluyo aquí protegido.
if (isset($_GET['action']) && $_GET['action'] == 'borrar' && isset($_GET['id'])) {
    $id_borrar = $_GET['id'];

    if ($id_borrar == $_SESSION['usuario_id']) {
        $mensaje = "❌ No puedes borrar tu propia cuenta.";
        $tipo_mensaje = "danger";
    } else {
        $sql_del = "DELETE FROM usuarios WHERE id = ?";
        $stmt_del = mysqli_prepare($conn, $sql_del);
        mysqli_stmt_bind_param($stmt_del, "i", $id_borrar);
        if (mysqli_stmt_execute($stmt_del)) {
            $mensaje = "🗑️ Usuario eliminado correctamente.";
            $tipo_mensaje = "warning";
        }
    }
}

// 4. OBTENER LISTADO DE USUARIOS
$sql_lista = "SELECT * FROM usuarios ORDER BY id DESC";
$usuarios = mysqli_query($conn, $sql_lista);
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">👥 Gestión de Usuarios</h2>
        <a href="index.php" class="btn btn-outline-secondary">&larr; Volver al Panel</a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Usuario</th>
                            <th>Email</th>
                            <th>Fecha Registro</th>
                            <th>Rol Actual</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($usuarios)): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="../assets/img/avatars/<?php echo !empty($user['avatar']) ? $user['avatar'] : 'default.png'; ?>" 
                                         class="rounded-circle me-3 border" width="40" height="40" style="object-fit:cover;">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($user['nombre']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($user['cargo']); ?></div>
                                    </div>
                                </div>
                            </td>
                            
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            
                            <td><?php echo date("d/m/Y", strtotime($user['fecha_registro'])); ?></td>

                            <td>
                                <form action="usuarios.php" method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="accion" value="cambiar_rol">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    
                                    <select name="nuevo_rol" class="form-select form-select-sm me-2" style="width: 110px;" 
                                            <?php echo ($user['id'] == $_SESSION['usuario_id']) ? 'disabled' : ''; ?>>
                                        <option value="empleado" <?php echo ($user['rol'] == 'empleado') ? 'selected' : ''; ?>>Empleado</option>
                                        <option value="admin" <?php echo ($user['rol'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    
                                    <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Guardar Rol">💾</button>
                                    <?php endif; ?>
                                </form>
                            </td>

                            <td class="text-end pe-4">
                                <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                    <a href="usuarios.php?action=borrar&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('⚠️ ¡CUIDADO! ⚠️\n\nEstás a punto de borrar al usuario: <?php echo $user['nombre']; ?>.\n\nEsto también borrará TODOS sus posts.\n¿Estás seguro?');">
                                        🗑️ Borrar
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-success">TÚ</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>