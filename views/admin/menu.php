<?php 
$titulo = "Panel de Control - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<style>
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .indicator-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #ff8c00; transition: 0.3s; }
    .indicator-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
    .indicator-card h3 { font-size: 14px; color: #666; text-transform: uppercase; margin-bottom: 10px; }
    .indicator-card .value { font-size: 28px; font-weight: bold; color: #333; }
    .filter-section { background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
</style>

<div class="filter-section">
    <label><strong>Filtrar actividad por fecha:</strong></label>
    <form action="index.php" method="GET" id="fechaForm">
        <input type="hidden" name="url" value="home/index">
        <select name="fecha" class="form-control" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
            <?php foreach($fechas as $f): ?>
                <option value="<?php echo $f; ?>" <?php echo ($f == $fechaFiltro) ? 'selected' : ''; ?>>
                    <?php echo date('d/m/Y', strtotime($f)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<h2 style="margin-bottom: 15px; color: #444;">📅 Resumen del Día (<?php echo date('d/m/Y', strtotime($fechaFiltro)); ?>)</h2>
<div class="dashboard-grid">
    <div class="indicator-card">
        <h3>Devoluciones</h3>
        <div class="value"><?php echo $statsHoy['total_dev']; ?></div>
    </div>
    <div class="indicator-card" style="border-color: #28a745;">
        <h3>Total KG</h3>
        <div class="value"><?php echo number_format($statsHoy['total_kg'], 2); ?></div>
    </div>
    <div class="indicator-card" style="border-color: #17a2b8;">
        <h3>Total Unidades</h3>
        <div class="value"><?php echo number_format($statsHoy['total_und'], 0); ?></div>
    </div>
    <div class="indicator-card" style="border-color: #dc3545;">
        <h3>Pendientes</h3>
        <div class="value"><?php echo $statsHoy['pendientes']; ?></div>
    </div>
</div>

<h2 style="margin-bottom: 15px; color: #444;">📊 Estadísticas Generales (Acumulado)</h2>
<div class="dashboard-grid">
    <div class="indicator-card" style="border-color: #6c757d;">
        <h3>Total Registros</h3>
        <div class="value"><?php echo $statsGeneral['total_dev']; ?></div>
    </div>
    <div class="indicator-card" style="border-color: #6c757d;">
        <h3>KG Histórico</h3>
        <div class="value"><?php echo number_format($statsGeneral['total_kg'], 2); ?></div>
    </div>
    <div class="indicator-card" style="border-color: #6c757d;">
        <h3>Unidades Histórico</h3>
        <div class="value"><?php echo number_format($statsGeneral['total_und'], 0); ?></div>
    </div>
</div>

<div class="card" style="text-align: center; padding: 40px;">
    <h3>Bienvenido al Sistema de Gestión de Devoluciones</h3>
    <p style="color: #777; margin-top: 10px;">Utilice el menú superior para navegar entre las opciones de registro y consulta.</p>
</div>

<?php include 'Views/layouts/footer.php'; ?>