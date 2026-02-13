<?php 
// CORRECCIÓN IMPORTANTE:
// Usamos __DIR__ para que PHP busque conexion.php DENTRO de la carpeta 'includes',
// sin importar desde dónde se esté llamando a este header.
include_once __DIR__ . '/conexion.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Corporativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">🏢 Blog Empresa</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Inicio</a>
        </li>

        <?php if (isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>crear_post.php">➕ Crear Post</a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>perfil.php">👤 Mi Perfil</a>
            </li>

            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="<?php echo BASE_URL; ?>admin/index.php">⚙️ Panel Admin</a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link btn btn-danger text-white btn-sm ms-2" href="<?php echo BASE_URL; ?>logout.php">Cerrar Sesión</a>
            </li>

        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Iniciar Sesión</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>registro.php">Registrarse</a>
            </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container main-content">
    
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['mensaje']); 
        unset($_SESSION['tipo_mensaje']);
        ?>
    <?php endif; ?>