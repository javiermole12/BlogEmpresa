<?php
// 1. SEGURIDAD Y CONEXIÓN
require_once 'includes/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_mensaje = "";

// 2. PROCESAR EL FORMULARIO (UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recoger datos (limpios)
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $cargo = mysqli_real_escape_string($conn, $_POST['cargo']);
    
    // -- LÓGICA DE SUBIDA DE IMAGEN (AVATAR) --
    $avatar_nombre = $_SESSION['avatar']; // Por defecto mantenemos el actual
    
    if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['name'])) {
        $archivo = $_FILES['avatar'];
        $nombre_archivo = $archivo['name'];
        $tipo = $archivo['type'];
        $tmp_name = $archivo['tmp_name'];

        // Validar que sea una imagen
        if ($tipo == "image/jpg" || $tipo == "image/jpeg" || $tipo == "image/png" || $tipo == "image/gif") {
            
            // Verificar si existe la carpeta, si no, crearla
            if (!is_dir('assets/img/avatars')) {
                mkdir('assets/img/avatars', 0777, true);
            }

            // Generar nombre único para evitar sobrescribir
            $avatar_nombre = "avatar_" . $id_usuario . "_" . time() . ".jpg";
            
            // Mover el archivo de la carpeta temporal a la nuestra
            move_uploaded_file($tmp_name, 'assets/img/avatars/' . $avatar_nombre);
            
        } else {
            $mensaje = "Formato de imagen no válido. Usa JPG o PNG.";
            $tipo_mensaje = "danger";
        }
    }

    // -- LÓGICA DE ACTUALIZACIÓN EN BD --
    if (empty($mensaje)) {
        
        // 1. Actualizar datos básicos
        $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', cargo='$cargo', avatar='$avatar_nombre' WHERE id = $id_usuario";
        $guardar = mysqli_query($conn, $sql);

        // 2. Actualizar contraseña (SOLO si el usuario escribió algo)
        if (!empty($_POST['password'])) {
            $password = mysqli_real_escape_string($conn, $_POST['password']);
            $password_segura = password_hash($password, PASSWORD_BCRYPT);
            
            $sql_pass = "UPDATE usuarios SET password='$password_segura' WHERE id = $id_usuario";
            mysqli_query($conn, $sql_pass);
        }

        if ($guardar) {
            // Actualizar la sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['avatar'] = $avatar_nombre;
            
            $mensaje = "Perfil actualizado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar: " . mysqli_error($conn);
            $tipo_mensaje = "danger";
        }
    }
}

// 3. OBTENER DATOS ACTUALES DEL USUARIO
$sql_user = "SELECT * FROM usuarios WHERE id = $id_usuario";
$res_user = mysqli_query($conn, $sql_user);
$usuario = mysqli_fetch_assoc($res_user);
?>

<?php include 'includes/header.php'; ?>

<div class="bg-light pb-5 pt-4 mb-5 border-bottom">
    <div class="container">
        <h2 class="fw-bold text-dark mb-0">Configuración de Perfil</h2>
        <p class="text-muted">Gestiona tu información personal y credenciales de acceso.</p>
    </div>
</div>

<div class="container" style="margin-top: -3rem;">
    
    <?php if(!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
            <?php echo $tipo_mensaje == 'success' ? '✅' : '⚠️'; ?> <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden position-relative">
                
                <div class="bg-primary" style="height: 100px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);"></div>
                
                <div class="card-body text-center position-relative pt-0">
                    
                    <?php 
                        $ruta_avatar = 'assets/img/avatars/' . $usuario['avatar'];
                        if (!file_exists($ruta_avatar) || empty($usuario['avatar'])) {
                            // Uso de UI Avatars como fallback elegante si no hay foto
                            $ruta_avatar = "https://ui-avatars.com/api/?name=".urlencode($usuario['nombre'])."&background=e9ecef&color=0d6efd&size=200";
                        }
                    ?>
                    
                    <div class="d-inline-block position-relative" style="margin-top: -50px;">
                        <img src="<?php echo $ruta_avatar; ?>" alt="Avatar de <?php echo htmlspecialchars($usuario['nombre']); ?>" 
                             class="rounded-circle bg-white p-1 shadow-sm" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    
                    <h4 class="fw-bold mt-3 mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h4>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill mb-3">
                        <?php echo htmlspecialchars($usuario['cargo']); ?>
                    </span>
                    
                    <ul class="list-group list-group-flush text-start mt-4 border-top pt-3">
                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">✉️ Email Corporativo</span>
                            <span class="small fw-medium"><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </li>
                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">🔐 Nivel de Acceso</span>
                            <?php if($usuario['rol'] == 'admin'): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Administrador</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Empleado</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted small">📅 Fecha de Ingreso</span>
                            <span class="small"><?php echo date("d/m/Y", strtotime($usuario['fecha_registro'] ?? 'now')); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    
                    <h5 class="fw-bold mb-4 border-bottom pb-3">Actualizar Datos</h5>

                    <form action="perfil.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control bg-light" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control bg-light" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-bold">Cargo / Departamento</label>
                                <input type="text" name="cargo" class="form-control bg-light" value="<?php echo htmlspecialchars($usuario['cargo']); ?>">
                            </div>
                        </div>

                        <div class="mb-4 bg-light p-3 rounded-3 border">
                            <label class="form-label text-muted small fw-bold d-block">Cambiar Fotografía</label>
                            <div class="input-group">
                                <input type="file" name="avatar" class="form-control" id="avatarInput" accept="image/png, image/jpeg, image/jpg">
                            </div>
                            <div class="form-text mt-2">Recomendado: Imagen cuadrada (1:1). Formatos: JPG, PNG. Máx 2MB.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-danger">Seguridad de la Cuenta</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light border-end-0">🔑</span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Nueva contraseña (dejar en blanco para no cambiar)">
                            </div>
                            <div class="form-text">Si no deseas cambiar tu contraseña actual, ignora este campo.</div>
                        </div>

                        <div class="d-flex justify-content-end mt-5 border-top pt-4">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm">
                                Guardar Cambios
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>