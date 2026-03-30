<?php
// 1. SEGURIDAD Y CONEXIÓN
require_once '../includes/conexion.php';

// Verificación de Admin (Vital)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// ==========================================
// 1. LÓGICA DE PROCESAMIENTO (PROTEGIDA)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 🛡️ ESCUDO GLOBAL CSRF PARA CUALQUIER ACCIÓN POST
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $mensaje = "🛑 Bloqueo de seguridad: Token CSRF inválido o caducado.";
        $tipo_mensaje = "danger";
    } else {
        
        // --- ACCIÓN A: CAMBIAR ROL ---
        if (isset($_POST['accion']) && $_POST['accion'] == 'cambiar_rol') {
            $id_usuario_editar = $_POST['user_id'];
            $nuevo_rol = $_POST['nuevo_rol'];

            if ($id_usuario_editar == $_SESSION['usuario_id']) {
                $mensaje = "❌ No puedes cambiar tu propio rol. Pide a otro admin que lo haga.";
                $tipo_mensaje = "danger";
            } else {
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

        // --- ACCIÓN B: BORRAR USUARIO (Ahora seguro por POST) ---
        if (isset($_POST['accion']) && $_POST['accion'] == 'borrar_usuario' && isset($_POST['id_borrar'])) {
            $id_borrar = $_POST['id_borrar'];

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
    }
}


// 2. OBTENER LISTADO DE USUARIOS
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
                                    <form id="form-borrar-<?php echo $user['id']; ?>" action="usuarios.php" method="POST" class="m-0 d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="accion" value="borrar_usuario">
                                        <input type="hidden" name="id_borrar" value="<?php echo $user['id']; ?>">   
                                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" data-bs-toggle="modal" 
                                        data-bs-target="#modalConfirmarBorrado"data-form-id="form-borrar-<?php echo $user['id']; ?>">
                                            🗑️ Borrar
                                        </button>
                                    </form>
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
<div class="modal fade" id="modalConfirmarBorrado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h5 class="modal-title fw-bold text-danger">⚠️ Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-4 px-4 fs-5 text-center text-dark">
        ¿Estás seguro de que deseas borrar a este usuario?<br>
        <small class="text-muted fs-6">Esta acción borrará también todos sus posts y no se puede deshacer.</small>
      </div>
      <div class="modal-footer border-top-0 justify-content-center pt-0 pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnConfirmarBorrar" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Sí, borrar usuario</button>
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalBorrado = document.getElementById('modalConfirmarBorrado');
        var formIdToSubmit = null; // Variable para guardar qué formulario queremos enviar
        
        if(modalBorrado) {
            // Cuando se abre el modal, guardamos el ID del formulario correspondiente
            modalBorrado.addEventListener('show.bs.modal', function (event) {
                var boton = event.relatedTarget;
                formIdToSubmit = boton.getAttribute('data-form-id');
            });

            // Cuando hacen clic en el botón rojo de "Sí, borrar" dentro del modal...
            document.getElementById('btnConfirmarBorrar').addEventListener('click', function() {
                if(formIdToSubmit) {
                    // Buscamos el formulario oculto y lo enviamos
                    document.getElementById(formIdToSubmit).submit();
                }
            });
        }
    });
</script>
<?php include '../includes/footer.php'; ?>