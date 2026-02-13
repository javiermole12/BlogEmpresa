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
        $tmp_name = $archivo['tmp_name']; // Ruta temporal donde PHP guarda el archivo

        // Validar que sea una imagen
        if ($tipo == "image/jpg" || $tipo == "image/jpeg" || $tipo == "image/png" || $tipo == "image/gif") {
            
            // Verificar si existe la carpeta, si no, crearla
            if (!is_dir('assets/img/avatars')) {
                mkdir('assets/img/avatars', 0777, true);
            }

            // Generar nombre único para evitar sobrescribir (ej: avatar_15_time.jpg)
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
            // Actualizar la sesión para que los cambios se vean al momento en el header
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

// 3. OBTENER DATOS ACTUALES DEL USUARIO (Para rellenar el formulario)
$sql_user = "SELECT * FROM usuarios WHERE id = $id_usuario";
$res_user = mysqli_query($conn, $sql_user);
$usuario = mysqli_fetch_assoc($res_user);
?>

<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <?php 
                        $ruta_avatar = 'assets/img/avatars/' . $usuario['avatar'];
                        if (!file_exists($ruta_avatar) || empty($usuario['avatar'])) {
                            $ruta_avatar = 'assets/img/avatars/default.png'; // Imagen por defecto si falla
                            // Truco: Puedes usar una url externa si no tienes imagen local aún
                            // $ruta_avatar = "https://ui-avatars.com/api/?name=".$usuario['nombre'];
                        }
                    ?>
                    <img src="<?php echo $ruta_avatar; ?>" alt="Avatar" class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #eee;">
                    
                    <h5 class="my-3"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($usuario['cargo']); ?></p>
                    <p class="text-muted mb-4 font-size-sm">Rol: <strong><?php echo strtoupper($usuario['rol']); ?></strong></p>
                    
                    <?php if($usuario['rol'] == 'admin'): ?>
                        <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                            👑 Tienes privilegios de Administrador
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-primary">✏️ Editar Información</h5>
                </div>
                <div class="card-body">
                    
                    <?php if(!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                            <?= $mensaje ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="perfil.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0 mt-2">Nombre Completo</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0 mt-2">Email</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0 mt-2">Cargo / Puesto</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="text" name="cargo" class="form-control" value="<?php echo htmlspecialchars($usuario['cargo']); ?>">
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0 mt-2">Cambiar Avatar</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                <small class="text-muted">Formatos: JPG, PNG. Máx 2MB.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0 mt-2">Nueva Contraseña</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>