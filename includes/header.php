<?php 
// Usamos __DIR__ para que PHP busque conexion.php DENTRO de la carpeta 'includes'
include_once __DIR__ . '/conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Corporativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="bg-light"> 

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top py-2" style="background: linear-gradient(135deg, #0d6efd 0%, #084298 100%); border-bottom: 3px solid #ffc107;">
  <div class="container">
    
    <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>index.php">
        <div class="bg-white text-primary rounded d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
            <i class="bi bi-buildings-fill fs-5"></i>
        </div>
        <span class="text-white">Hub<span class="text-warning">Corp</span></span>
    </a>
    
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        
        <li class="nav-item me-3 mb-2 mb-lg-0">
            <a class="nav-link fw-medium text-white d-flex align-items-center opacity-75 hover-opacity-100" href="<?php echo BASE_URL; ?>index.php">
                <i class="bi bi-house-door-fill me-1"></i> Inicio
            </a>
        </li>

        <?php if (isset($_SESSION['usuario_id'])): ?>
            
            <li class="nav-item me-3 mb-3 mb-lg-0">
                <a class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm text-dark d-flex align-items-center" href="<?php echo BASE_URL; ?>crear_post.php">
                    <i class="bi bi-pencil-square me-2 fs-6"></i> Nueva Publicación
                </a>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-0 text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php 
                        // Lógica para mostrar el avatar
                        $nav_avatar = "https://ui-avatars.com/api/?name=".urlencode($_SESSION['nombre'])."&background=ffc107&color=000";
                        if(isset($_SESSION['avatar']) && $_SESSION['avatar'] !== 'default.png') {
                            $nav_avatar = BASE_URL . 'assets/img/avatars/' . $_SESSION['avatar'];
                        }
                    ?>
                    <img src="<?php echo $nav_avatar; ?>" alt="Usuario" class="rounded-circle border border-2 border-white shadow-sm" width="38" height="38" style="object-fit:cover;">
                    <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center" href="<?php echo BASE_URL; ?>perfil.php">
                            <i class="bi bi-person-badge text-primary me-2 fs-5"></i> Mi Perfil
                        </a>
                    </li>
                    
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center fw-bold text-dark bg-warning bg-opacity-25" href="<?php echo BASE_URL; ?>admin/index.php">
                                <i class="bi bi-shield-lock-fill text-warning me-2 fs-5"></i> Panel Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <li>
                        <a class="dropdown-item py-2 text-danger d-flex align-items-center fw-medium" href="<?php echo BASE_URL; ?>logout.php">
                            <i class="bi bi-box-arrow-right me-2 fs-5"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </li>

        <?php else: ?>
            
            <li class="nav-item me-2 mb-2 mb-lg-0">
                <a class="btn btn-outline-light rounded-pill px-4 fw-bold" href="<?php echo BASE_URL; ?>login.php">Iniciar Sesión</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark" href="<?php echo BASE_URL; ?>registro.php">Registrarse</a>
            </li>
            
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container main-content mt-4 mb-5">
    
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <?php 
                $icono = $_SESSION['tipo_mensaje'] == 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'; 
                $icono = $_SESSION['tipo_mensaje'] == 'info' ? 'bi-info-circle-fill text-info' : $icono;
            ?>
            <i class="bi <?= $icono ?> fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Aviso del sistema</h6>
                <p class="mb-0"><?= $_SESSION['mensaje']; ?></p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['mensaje']); 
        unset($_SESSION['tipo_mensaje']);
        ?>
    <?php endif; ?>