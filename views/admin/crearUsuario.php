<?php 
$titulo = "Crear Usuario - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?>">
        <?php echo ($tipoMensaje === 'success') ? '✅' : '⚠️'; ?> <?php echo htmlspecialchars($mensaje); ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2>➕ Crear Nuevo Usuario</h2>
    </div>

<div class="card">
    <h2>👥 Usuarios Registrados (<?php echo count($usuarios); ?>)</h2>
    </div>

<?php include 'Views/layouts/footer.php'; ?>