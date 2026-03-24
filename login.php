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
// --- CONFIGURACIÓN DE SEGURIDAD ---
$max_intentos = 3;          // Número de intentos antes de bloquear
$minutos_bloqueo = 15;      // Tiempo de castigo en minutos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recogemos datos
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errores = "Por favor, rellena todos los campos.";
    } else {
        
        // 1. Buscar al usuario por email (Usamos sentencias preparadas por seguridad)
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultado) == 1) {
            $usuario = mysqli_fetch_assoc($resultado);

            // 2. Comprobar si la cuenta está BLOQUEADA
            if ($usuario['bloqueado_hasta'] != NULL && strtotime($usuario['bloqueado_hasta']) > time()) {
                
                // Calcular cuántos minutos le quedan de bloqueo
                $tiempo_restante = ceil((strtotime($usuario['bloqueado_hasta']) - time()) / 60);
                $errores = "🔒 Cuenta bloqueada por seguridad tras varios intentos fallidos. Inténtalo de nuevo en $tiempo_restante minutos.";
                
            } else {
                
                // 3. La cuenta NO está bloqueada. Verificamos la contraseña.
                if (password_verify($password, $usuario['password'])) {
                    
                    // LOGIN CORRECTO -> Reseteamos los fallos a 0
                    $sql_reset = "UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?";
                    $stmt_reset = mysqli_prepare($conn, $sql_reset);
                    mysqli_stmt_bind_param($stmt_reset, "i", $usuario['id']);
                    mysqli_stmt_execute($stmt_reset);

                    // Guardamos la sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['rol'] = $usuario['rol'];
                    $_SESSION['avatar'] = $usuario['avatar'];

                    // Redirigimos según el rol
                    if ($usuario['rol'] == 'admin') {
                        header("Location: admin/index.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();

                } else {
                    // LOGIN FALLIDO (Contraseña incorrecta)
                    $intentos_actuales = $usuario['intentos_fallidos'] + 1;
                    $fecha_bloqueo = NULL;

                    if ($intentos_actuales >= $max_intentos) {
                        // Ha llegado al límite: Calculamos la fecha/hora de desbloqueo
                        $fecha_bloqueo = date('Y-m-d H:i:s', strtotime("+$minutos_bloqueo minutes"));
                        $errores = "🔒 Has superado el límite de intentos. Cuenta bloqueada por $minutos_bloqueo minutos.";
                    } else {
                        // Aún le quedan intentos
                        $intentos_restantes = $max_intentos - $intentos_actuales;
                        $errores = "Credenciales incorrectas. Te quedan $intentos_restantes intentos.";
                    }

                    // Actualizamos la base de datos con el nuevo fallo
                    $sql_fallo = "UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?";
                    $stmt_fallo = mysqli_prepare($conn, $sql_fallo);
                    mysqli_stmt_bind_param($stmt_fallo, "isi", $intentos_actuales, $fecha_bloqueo, $usuario['id']);
                    mysqli_stmt_execute($stmt_fallo);
                }
            }
        } else {
            // MITIGACIÓN DE ENUMERACIÓN DE USUARIOS:
            // Si el email no existe, damos el mismo error genérico para no darle pistas al hacker.
            $errores = "Credenciales incorrectas.";
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