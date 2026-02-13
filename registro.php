<?php
// --- ACTIVAR ERRORES (Solo para desarrollo) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Incluir la configuración
// Asegúrate de que la carpeta se llama 'includes' y no 'Includes'
require_once 'includes/conexion.php'; 

// Si ya está logueado, fuera
if (isset($_SESSION['usuario'])) {
    header("Location: index.php"); // Usa ruta relativa simple si BASE_URL falla
    exit();
}

$errores = [];
$exito = false;

// 2. Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $cargo = trim($_POST['cargo']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validaciones
    if (empty($nombre)) { $errores['nombre'] = "El nombre es obligatorio."; }
    if (empty($email)) { $errores['email'] = "El email es obligatorio."; }
    if (empty($password)) { $errores['password'] = "La contraseña es obligatoria."; }
    if ($password !== $password_confirm) { $errores['password'] = "Las contraseñas no coinciden."; }

    // Comprobar email
    if (empty($errores)) {
        $sql_check = "SELECT id FROM usuarios WHERE email = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $errores['email'] = "Este correo ya está registrado.";
        }
        mysqli_stmt_close($stmt_check);
    }

    // Insertar
    if (empty($errores)) {
        $password_segura = password_hash($password, PASSWORD_BCRYPT);
        $sql_insert = "INSERT INTO usuarios (nombre, email, password, cargo, rol, avatar, fecha_registro) VALUES (?, ?, ?, ?, 'empleado', 'default.png', NOW())";
        
        $stmt = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $password_segura, $cargo);
        
        if (mysqli_stmt_execute($stmt)) {
            $exito = true;
        } else {
            $errores['general'] = "Error al registrar: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// 3. INCLUIR EL HEADER (Aquí empieza la visual)
include 'includes/header.php'; 
?>

<style>
    /* Usamos un contenedor centrado específico si el header no lo provee */
    .registro-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh; /* Altura visual */
        padding: 40px 0;
    }
    .login-container { 
        background: white; 
        padding: 2rem; 
        border-radius: 10px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        width: 100%; 
        max-width: 450px; 
    }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; color: #666; font-weight: 500; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
    .btn-submit { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    .btn-submit:hover { background-color: #218838; }
    .error-msg { background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; font-size: 14px; border: 1px solid #ffcdd2; margin-top: 5px;}
    .success-msg { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; }
    .register-link { text-align: center; margin-top: 15px; }
</style>

<div class="registro-wrapper">
    <div class="login-container">
        
        <?php if ($exito): ?>
            <div class="success-msg">
                <h3>¡Cuenta creada!</h3>
                <p>Ya puedes iniciar sesión.</p>
                <a href="login.php" style="display:block; margin-top:10px; font-weight:bold; color:#155724;">Ir al Login &rarr;</a>
            </div>
        <?php else: ?>
            
            <div style="text-align:center; margin-bottom:20px;">
                <h2>Crear Cuenta</h2>
                <p>Únete al portal del empleado</p>
            </div>

            <?php if(isset($errores['general'])): ?>
                <div class="error-msg"><?= $errores['general']; ?></div>
            <?php endif; ?>

            <form action="registro.php" method="POST" id="formRegistro">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" name="nombre" id="nombre" required value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                    <?php if(isset($errores['nombre'])): ?><div class="error-msg"><?= $errores['nombre'] ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="cargo">Cargo / Puesto</label>
                    <input type="text" name="cargo" id="cargo" value="<?= isset($_POST['cargo']) ? htmlspecialchars($_POST['cargo']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Corporativo</label>
                    <input type="email" name="email" id="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <?php if(isset($errores['email'])): ?><div class="error-msg"><?= $errores['email'] ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <input type="password" name="password_confirm" id="password_confirm" required>
                    <?php if(isset($errores['password'])): ?><div class="error-msg"><?= $errores['password'] ?></div><?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">Registrarse</button>
            </form>

            <div class="register-link">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>

        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById('formRegistro').addEventListener('submit', function(e) {
        var pass1 = document.getElementById('password').value;
        var pass2 = document.getElementById('password_confirm').value;
        
        if (pass1 !== pass2) {
            e.preventDefault();
            alert("Las contraseñas no coinciden.");
        } else if (pass1.length < 4) {
            e.preventDefault();
            alert("La contraseña debe tener al menos 4 caracteres.");
        }
    });
</script>

<?php 
// 4. INCLUIR EL FOOTER
include 'includes/footer.php'; 
?>