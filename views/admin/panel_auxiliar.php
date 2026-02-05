<?php 
include 'Views/layouts/header.php'; 
?>

<div class="card">
    <h2>📝 Nueva Devolución</h2>
    <hr><br>

    <?php if(isset($_SESSION['alerta'])): ?>
        <div class="alert alert-<?php echo $_SESSION['alerta']['tipo']; ?>">
            <?php echo $_SESSION['alerta']['msg']; unset($_SESSION['alerta']); ?>
        </div>
    <?php endif; ?>

    <form action="index.php?url=panel/registrar" method="POST" enctype="multipart/form-data">
        <div class="form-grid">
            <div class="form-group">
                <label>NIT Cliente</label>
                <input type="text" name="nit" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Nombre Cliente</label>
                <input type="text" name="nombre_cliente" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Producto</label>
                <select name="producto" class="form-control" required>
                    <option value="">Seleccione un producto...</option>
                    <?php foreach($productos as $p): ?>
                        <option value="<?php echo $p['item']; ?>">
                            <?php echo $p['item'] . " - " . $p['descripcion']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Motivo</label>
                <select name="motivo" class="form-control" required>
                    <option value="MAL ESTADO">MAL ESTADO</option>
                    <option value="VENCIMIENTO">VENCIMIENTO</option>
                    <option value="ERROR PEDIDO">ERROR PEDIDO</option>
                </select>
            </div>
        </div>

        <div style="margin-top:20px;">
            <button type="submit" class="btn">🚀 Registrar Devolución</button>
        </div>
    </form>
</div>

<?php include 'Views/layouts/footer.php'; ?>