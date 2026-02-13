<?php
// registro.php
require 'includes/conexion.php';

// Si ya estás logueado, no deberías ver el registro
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$errores = [];
$nombre = '';
$email = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Limpiamos los datos de entrada
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // VALIDACIONES BÁSICAS
    if (empty($nombre) || empty($email) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Verificar si el email ya existe
    $check_email = "SELECT id FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conn, $check_email);
    if (mysqli_num_rows($resultado) > 0) {
        $errores[] = "Ese correo electrónico ya está registrado.";
    }

    // Si no hay errores, REGISTRAMOS
    if (empty($errores)) {
        // 1. Encriptamos la contraseña (VITAL PARA SEGURIDAD)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 2. Insertamos en la BD (Rol por defecto: empleado)
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, cargo, avatar) 
                VALUES ('$nombre', '$email', '$password_hash', 'empleado', 'Nuevo Usuario', 'default.png')";

        if (mysqli_query($conn, $sql)) {
            // ÉXITO
            $_SESSION['mensaje'] = "¡Cuenta creada correctamente! Ahora puedes iniciar sesión.";
            $_SESSION['tipo_mensaje'] = "success"; // Color verde en Bootstrap
            header("Location: login.php");
            exit();
        } else {
            $errores[] = "Error en la base de datos: " . mysqli_error($conn);
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h3>Crear Nueva Cuenta</h3>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errores as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="registro.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo $nombre; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" required minlength="4">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="4">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">Registrarse</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>