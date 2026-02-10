<?php include 'includes/header.php'; ?>

<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Bienvenido al Blog Corporativo</h1>
        <p class="col-md-8 fs-4">Esta es la página de inicio. Si ves la barra negra arriba, la conexión y los estilos están funcionando perfectamente.</p>
        
        <?php if(!isset($_SESSION['usuario_id'])): ?>
            <a href="login.php" class="btn btn-primary btn-lg">Identifícate para participar</a>
        <?php else: ?>
            <button class="btn btn-success btn-lg">Hola, <?php echo $_SESSION['nombre']; ?></button>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>