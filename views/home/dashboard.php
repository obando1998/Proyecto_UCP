<?php 
$titulo = "Dashboard - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<div class="dashboard-header">
    <h1 class="dashboard-title">Dashboard - DevolutionSync</h1>
    
    <!-- Selector de fecha -->
    <form method="GET" action="index.php" class="date-selector">
        <input type="hidden" name="url" value="home/index">
        <label for="fecha">Seleccionar Fecha:</label>
        <select name="fecha" id="fecha" onchange="this.form.submit()">
            <?php foreach ($fechas as $f): ?>
                <option value="<?php echo $f; ?>" <?php echo ($fechaFiltro == $f) ? 'selected' : ''; ?>>
                    <?php echo date('d/m/Y', strtotime($f)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Actualizar</button>
    </form>
    
    <div class="current-date">
        <strong>Mostrando datos para:</strong> 
        <?php 
        if ($fechaFiltro == date('Y-m-d')) {
            echo "<strong>HOY - " . date('d/m/Y') . "</strong>";
        } else {
            echo date('d/m/Y', strtotime($fechaFiltro));
        }
        ?>
    </div>
</div>

<!-- Grid de indicadores principales -->
<div class="indicators-grid">
    <div class="indicator-card border-blue">
        <div class="date-badge">FECHA SELECCIONADA</div>
        <div class="indicator-icon">??</div>
        <div class="indicator-value"><?php echo $statsHoy['total_dev'] ?? 0; ?></div>
        <div class="indicator-label">Total Devoluciones</div>
        <?php if ($fechaFiltro == date('Y-m-d')): ?>
            <div class="indicator-trend trend-positive">En tiempo real</div>
        <?php else: ?>
            <div class="indicator-trend trend-neutral">Datos hist車ricos</div>
        <?php endif; ?>
    </div>

    <div class="indicator-card border-teal">
        <div class="indicator-icon">??</div>
        <div class="indicator-value"><?php echo $statsHoy['motivo_dev'] ?? 0; ?></div>
        <div class="indicator-label">Devoluciones</div>
        <div class="indicator-trend trend-neutral">Motivo: Devoluci車n</div>
    </div>

    <div class="indicator-card border-red">
        <div class="indicator-icon">?</div>
        <div class="indicator-value"><?php echo $statsHoy['motivo_fal'] ?? 0; ?></div>
        <div class="indicator-label">Faltantes</div>
        <div class="indicator-trend trend-negative">Motivo: Faltante</div>
    </div>

    <div class="indicator-card border-purple">
        <div class="indicator-icon">?</div>
        <div class="indicator-value"><?php echo $statsHoy['motivo_sob'] ?? 0; ?></div>
        <div class="indicator-label">Sobrantes</div>
        <div class="indicator-trend trend-positive">Motivo: Sobrante</div>
    </div>

    <div class="indicator-card border-green">
        <div class="indicator-icon">??</div>
        <div class="indicator-value"><?php echo number_format($statsHoy['total_kg'] ?? 0, 2); ?></div>
        <div class="indicator-label">Total KG</div>
        <div class="indicator-trend trend-neutral">Peso total</div>
    </div>

    <div class="indicator-card border-orange">
        <div class="indicator-icon">??</div>
        <div class="indicator-value"><?php echo number_format($statsHoy['total_und'] ?? 0, 0); ?></div>
        <div class="indicator-label">Total UND</div>
        <div class="indicator-trend trend-neutral">Unidades totales</div>
    </div>
</div>

<!-- Estado de las devoluciones -->
<div class="general-stats">
    <h3>?? Estado de Devoluciones - <?php echo date('d/m/Y', strtotime($fechaFiltro)); ?></h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsHoy['pendientes'] ?? 0; ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsHoy['aprobadas'] ?? 0; ?></div>
            <div class="stat-label">Aprobadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsHoy['rechazadas'] ?? 0; ?></div>
            <div class="stat-label">Rechazadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">
                <?php 
                $totalRevisadas = ($statsHoy['aprobadas'] ?? 0) + ($statsHoy['rechazadas'] ?? 0);
                $totalDev = $statsHoy['total_dev'] ?? 0;
                $porcentaje = $totalDev > 0 ? round(($totalRevisadas / $totalDev) * 100, 1) : 0;
                echo $porcentaje . '%';
                ?>
            </div>
            <div class="stat-label">Revisadas</div>
        </div>
    </div>
</div>

<!-- Resumen General (todas las fechas) -->
<div class="general-stats">
    <h3>?? Resumen General (Todas las Fechas)</h3>
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['total_dev'] ?? 0; ?></div>
            <div class="stat-label">Total Devoluciones</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['aprobadas'] ?? 0; ?></div>
            <div class="stat-label">Total Aprobadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo $statsGeneral['rechazadas'] ?? 0; ?></div>
            <div class="stat-label">Total Rechazadas</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo number_format($statsGeneral['total_kg'] ?? 0, 2); ?></div>
            <div class="stat-label">Total KG General</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo number_format($statsGeneral['total_und'] ?? 0, 0); ?></div>
            <div class="stat-label">Total UND General</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?php echo count($fechas); ?></div>
            <div class="stat-label">D赤as con Actividad</div>
        </div>
    </div>
</div>

<script>
    // Funci車n para cerrar sesi車n
    function logout() {
        if (confirm('?Est芍s seguro de que deseas cerrar sesi車n?')) {
            window.location.href = 'index.php?url=auth/logout';
        }
    }

    // Efectos de animaci車n al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.indicator-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>

<?php include 'Views/layouts/footer.php'; ?>