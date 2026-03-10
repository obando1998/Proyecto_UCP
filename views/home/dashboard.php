<?php 
$titulo = "Dashboard - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<div class="dashboard-header">
    <h1 class="dashboard-title">📊 Dashboard - DevolutionSync</h1>
    
    <!-- Selector de fecha -->
    <div class="date-selector">
        <form method="GET" action="index.php" style="display:flex; align-items:center; gap:15px; flex-wrap:wrap; width:100%;">
            <input type="hidden" name="url" value="home/index">
            <label for="fecha"><i class="fas fa-calendar-alt"></i> Seleccionar Fecha:</label>
            <select name="fecha" id="fecha" onchange="this.form.submit()">
                <?php foreach ($fechas as $f): ?>
                    <option value="<?php echo $f; ?>" <?php echo ($fechaFiltro == $f) ? 'selected' : ''; ?>>
                        <?php echo date('d/m/Y', strtotime($f)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fas fa-sync-alt"></i> Actualizar</button>
        </form>
    </div>

    <div class="current-date">
        <strong>📅 Mostrando datos para:</strong>
        <?php if ($fechaFiltro == date('Y-m-d')): ?>
            <strong>HOY — <?php echo date('d/m/Y'); ?></strong>
        <?php else: ?>
            <?php echo date('d/m/Y', strtotime($fechaFiltro)); ?>
        <?php endif; ?>
    </div>
</div>

<!-- ── Indicadores del día seleccionado ── -->
<div class="indicators-grid">

    <div class="indicator-card border-blue">
        <div class="date-badge">FECHA SELECCIONADA</div>
        <div class="indicator-icon">📦</div>
        <div class="indicator-value"><?php echo $statsHoy['total'] ?? 0; ?></div>
        <div class="indicator-label">Total Devoluciones</div>
        <?php if ($fechaFiltro == date('Y-m-d')): ?>
            <div class="indicator-trend trend-positive">En tiempo real</div>
        <?php else: ?>
            <div class="indicator-trend trend-neutral">Datos históricos</div>
        <?php endif; ?>
    </div>

    <div class="indicator-card border-teal">
        <div class="indicator-icon">🔄</div>
        <div class="indicator-value"><?php echo $statsHoy['motivo_dev'] ?? 0; ?></div>
        <div class="indicator-label">Devoluciones</div>
        <div class="indicator-trend trend-neutral">Motivo: Devolución</div>
    </div>

    <div class="indicator-card border-red">
        <div class="indicator-icon">❌</div>
        <div class="indicator-value"><?php echo $statsHoy['motivo_fal'] ?? 0; ?></div>
        <div class="indicator-label">Faltantes</div>
        <div class="indicator-trend trend-negative">Motivo: Faltante</div>
    </div>

    <div class="indicator-card border-purple">
        <div class="indicator-icon">➕</div>
        <div class="indicator-value"><?php echo $statsHoy['motivo_sob'] ?? 0; ?></div>
        <div class="indicator-label">Sobrantes</div>
        <div class="indicator-trend trend-positive">Motivo: Sobrante</div>
    </div>

    <div class="indicator-card border-green">
        <div class="indicator-icon">⚖️</div>
        <div class="indicator-value"><?php echo number_format($statsHoy['total_kg'] ?? 0, 2); ?></div>
        <div class="indicator-label">Total KG</div>
        <div class="indicator-trend trend-neutral">Peso total</div>
    </div>

    <div class="indicator-card border-orange">
        <div class="indicator-icon">🔢</div>
        <div class="indicator-value"><?php echo number_format($statsHoy['total_und'] ?? 0, 0); ?></div>
        <div class="indicator-label">Total UND</div>
        <div class="indicator-trend trend-neutral">Unidades totales</div>
    </div>

</div>

<!-- ── Estado de devoluciones del día ── -->
<div class="general-stats">
    <h3>📋 Estado de Devoluciones — <?php echo date('d/m/Y', strtotime($fechaFiltro)); ?></h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsHoy['pendientes'] ?? 0; ?></div>
            <div class="stat-label">⏳ Pendientes</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsHoy['aprobados'] ?? 0; ?></div>
            <div class="stat-label">✅ Aprobadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsHoy['rechazados'] ?? 0; ?></div>
            <div class="stat-label">❌ Rechazadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">
                <?php 
                $totalRevisadas = ($statsHoy['aprobados'] ?? 0) + ($statsHoy['rechazados'] ?? 0);
                $totalDev       = $statsHoy['total'] ?? 0;
                $porcentaje     = $totalDev > 0 ? round(($totalRevisadas / $totalDev) * 100, 1) : 0;
                echo $porcentaje . '%';
                ?>
            </div>
            <div class="stat-label">📊 Revisadas</div>
        </div>
    </div>
</div>

<!-- ── Resumen general (todas las fechas) ── -->
<div class="general-stats">
    <h3>🌐 Resumen General (Todas las Fechas)</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['total'] ?? 0; ?></div>
            <div class="stat-label">📦 Total Devoluciones</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['aprobados'] ?? 0; ?></div>
            <div class="stat-label">✅ Total Aprobadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['rechazados'] ?? 0; ?></div>
            <div class="stat-label">❌ Total Rechazadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['pendientes'] ?? 0; ?></div>
            <div class="stat-label">⏳ Total Pendientes</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo number_format($statsGeneral['total_kg'] ?? 0, 2); ?></div>
            <div class="stat-label">⚖️ Total KG General</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo number_format($statsGeneral['total_und'] ?? 0, 0); ?></div>
            <div class="stat-label">🔢 Total UND General</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo count($fechas); ?></div>
            <div class="stat-label">📅 Días con Actividad</div>
        </div>
    </div>
</div>

<script>
// Animación de entrada para las tarjetas
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.indicator-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 80);
    });
});
</script>

<?php include 'Views/layouts/footer.php'; ?>
