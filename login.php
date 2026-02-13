<?php
// 1. LÓGICA DE PROGRAMACIÓN
// Incluimos la conexión primero para poder usar la base de datos
require 'includes/conexion.php';

// Si el usuario ya está logueado, lo mandamos al inicio (no tiene sentido que se loguee otra vez)
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$errores = '';

// Verificamos si el formulario ha sido enviado (Botón "Ingresar")
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recogemos los datos y los "limpiamos" para evitar inyecciones básicas
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Validamos que no estén vacíos
    if (empty($email) || empty($password)) {
        $errores = "Por favor, rellena todos los campos.";
    } else {
        // Consultamos a la base de datos
        $sql = "SELECT * FROM usuarios WHERE email = '$email' LIMIT 1";
        $resultado = mysqli_query($conn, $sql);

        if (mysqli_num_rows($resultado) == 1) {
            // El usuario existe, ahora verificamos la contraseña
            $usuario = mysqli_fetch_assoc($resultado);
            
            // password_verify compara la contraseña escrita con el HASH de la BD
            // Recuerda: tus usuarios de prueba tienen la contraseña "1234"
            if (password_verify($password, $usuario['password'])) {
                
                // ¡LOGIN CORRECTO! - Guardamos datos en la Sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['avatar'] = $usuario['avatar'];

                // Redirigimos según el rol
                if ($usuario['rol'] == 'admin') {
                    header("Location: admin/index.php"); // Al panel de control
                } else {
                    header("Location: index.php"); // Al blog normal
                }
                exit();

            } else {
                $errores = "La contraseña es incorrecta.";
            }
        } else {
            $errores = "No existe ninguna cuenta con ese email.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3>Iniciar Sesión</h3>
            </div>
            <div class="card-body p-4">
                
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <?php echo $errores; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="ejemplo@empresa.com" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" id="password" placeholder="********" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Ingresar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></small>
            </div>
        </div>
        
        <div class="alert alert-info mt-3 text-center">
            <strong>Credenciales de prueba:</strong><br>
            admin@empresa.com / 1234<br>
            userA@empresa.com / 1234
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>