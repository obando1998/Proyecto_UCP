<?php
// Incluir el archivo de conexión una sola vez
require_once("conexion.php");
require_once 'login/session_timeout.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Usar ruta relativa desde la raíz del servidor web
    $login_url = 'login/login.html';
    header('Location: ' . $login_url);
    exit;
}

// Inicializar variables
$fechasDisponibles = [];
$totalDevoluciones = 0;
$devolucionesHoy = 0;
$totalDevolucion = 0;
$totalFaltante = 0;
$totalSobrante = 0;
$totalKg = 0;
$totalUnd = 0;
$devolucionesPendientes = 0;
$devolucionesAprobadas = 0;
$devolucionesRechazadas = 0;
$errorMessage = '';

// Obtener fechas disponibles de la tabla devoluciones
try {
    $connection = Conexion::Conectar();
    
    // Obtener lista de fechas disponibles (sin duplicados)
    $stmt = $connection->prepare("
        SELECT DISTINCT DATE(fecha_creacion) as fecha 
        FROM devoluciones 
        WHERE fecha_creacion IS NOT NULL
        ORDER BY fecha DESC
    ");
    $stmt->execute();
    $fechasDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: verificar si hay fechas
    if (empty($fechasDisponibles)) {
        $errorMessage = "No se encontraron devoluciones en la base de datos";
    }
    
} catch (Exception $e) {
    $fechasDisponibles = [];
    $errorMessage = "Error al obtener fechas: " . $e->getMessage();
    error_log("Error al obtener fechas: " . $e->getMessage());
}

// Determinar la fecha a mostrar
$fechaSeleccionada = $_POST['fecha'] ?? date('Y-m-d');

// Obtener estadísticas de devoluciones para la fecha seleccionada
try {
    $connection = Conexion::Conectar();
    
    // Total de devoluciones para la fecha seleccionada
    $stmt = $connection->prepare("SELECT COUNT(*) as total FROM devoluciones WHERE DATE(fecha_creacion) = ?");
    $stmt->execute([$fechaSeleccionada]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalDevoluciones = $result['total'] ?? 0;
    
    // Devoluciones del día actual para comparar
    $stmt = $connection->prepare("SELECT COUNT(*) as hoy FROM devoluciones WHERE DATE(fecha_creacion) = CURDATE()");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $devolucionesHoy = $result['hoy'] ?? 0;
    
    // Total por tipo de motivo
    $stmt = $connection->prepare("
        SELECT 
            motivo,
            COUNT(*) as cantidad,
            SUM(COALESCE(cantidad_kg, 0)) as total_kg,
            SUM(COALESCE(cantidad_und, 0)) as total_und
        FROM devoluciones 
        WHERE DATE(fecha_creacion) = ?
        GROUP BY motivo
    ");
    $stmt->execute([$fechaSeleccionada]);
    $resultadosMotivo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inicializar contadores
    $totalDevolucion = 0;
    $totalFaltante = 0;
    $totalSobrante = 0;
    $totalKg = 0;
    $totalUnd = 0;
    
    // Procesar resultados por motivo
    foreach ($resultadosMotivo as $row) {
        switch ($row['motivo']) {
            case 'devolucion':
                $totalDevolucion = $row['cantidad'];
                break;
            case 'faltante':
                $totalFaltante = $row['cantidad'];
                break;
            case 'sobrante':
                $totalSobrante = $row['cantidad'];
                break;
        }
        $totalKg += $row['total_kg'];
        $totalUnd += $row['total_und'];
    }
    
    // Devoluciones por estado
    $stmt = $connection->prepare("
        SELECT 
            estado,
            COUNT(*) as cantidad
        FROM devoluciones 
        WHERE DATE(fecha_creacion) = ?
        GROUP BY estado
    ");
    $stmt->execute([$fechaSeleccionada]);
    $resultadosEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inicializar contadores de estado
    $devolucionesPendientes = 0;
    $devolucionesAprobadas = 0;
    $devolucionesRechazadas = 0;
    
    foreach ($resultadosEstado as $row) {
        switch ($row['estado']) {
            case 'pendiente':
                $devolucionesPendientes = $row['cantidad'];
                break;
            case 'aprobado':
                $devolucionesAprobadas = $row['cantidad'];
                break;
            case 'rechazado':
                $devolucionesRechazadas = $row['cantidad'];
                break;
        }
    }
    
    // Total general de devoluciones (todas las fechas)
    $stmt = $connection->prepare("SELECT COUNT(*) as total_general FROM devoluciones");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalGeneral = $result['total_general'] ?? 0;
    
    // Total de devoluciones aprobadas (todas las fechas)
    $stmt = $connection->prepare("SELECT COUNT(*) as aprobadas FROM devoluciones WHERE estado = 'aprobado'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalAprobadas = $result['aprobadas'] ?? 0;
    
    // Total de devoluciones rechazadas (todas las fechas)
    $stmt = $connection->prepare("SELECT COUNT(*) as rechazadas FROM devoluciones WHERE estado = 'rechazado'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalRechazadas = $result['rechazadas'] ?? 0;
    
    // Total KG y UND (todas las fechas)
    $stmt = $connection->prepare("
        SELECT 
            SUM(COALESCE(cantidad_kg, 0)) as total_kg_general,
            SUM(COALESCE(cantidad_und, 0)) as total_und_general
        FROM devoluciones
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalKgGeneral = $result['total_kg_general'] ?? 0;
    $totalUndGeneral = $result['total_und_general'] ?? 0;
    
} catch (Exception $e) {
    // Los valores ya están inicializados a 0 por defecto
    error_log("Error al obtener estadísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <meta name="copyright" content="Sebastian Obando">
    <title>Dashboard - DevolutionSync</title>
    <link rel="icon" type="image/png" href="img/icono.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-image: linear-gradient(to right, #e2e2e2, #ffe5c9);
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar con botones */
        .sidebar {
            width: 220px;
            background: linear-gradient(to bottom, #ff8c00, #ff6b00);
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .logo-sidebar {
            text-align: center;
            padding: 0 15px 20px 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
        }

        .logo-sidebar img {
            max-width: 180px;
            height: auto;
        }

        .menu-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 0 15px;
        }

        .menu-button {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .menu-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .button-icon {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            flex-shrink: 0;
        }

        .button-text {
            font-weight: bold;
            font-size: 13px;
        }

        .user-section {
            margin-top: auto;
            padding: 15px;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-info {
            color: white;
            text-align: center;
            margin-bottom: 15px;
        }

        .logout-btn {
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Contenido principal */
        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
        }

        .dashboard-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .dashboard-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .date-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .date-selector label {
            font-weight: bold;
            color: #555;
        }

        .date-selector select {
            padding: 8px 12px;
            border: 2px solid #ff8c00;
            border-radius: 5px;
            background: white;
            font-size: 14px;
            min-width: 200px;
            cursor: pointer;
        }

        .date-selector button {
            background: #ff8c00;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }

        .date-selector button:hover {
            background: #e67e00;
        }

        .current-date {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
            padding: 10px;
            background: #fff8f0;
            border-radius: 5px;
            border-left: 4px solid #ff8c00;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #c62828;
        }

        .debug-info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #1565c0;
            font-size: 12px;
        }

        /* Grid de indicadores */
        .indicators-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .indicator-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
            border-left: 5px solid #ff8c00;
            position: relative;
        }

        .indicator-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 140, 0, 0.2);
        }

        .indicator-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #ff8c00;
        }

        .indicator-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .indicator-label {
            color: #666;
            font-size: 14px;
            font-weight: bold;
        }

        .indicator-trend {
            margin-top: 10px;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .trend-positive {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .trend-negative {
            background: #ffebee;
            color: #c62828;
        }

        .trend-neutral {
            background: #e3f2fd;
            color: #1565c0;
        }

        .date-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff8c00;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        /* Sección de resumen general */
        .general-stats {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .general-stats h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #ff8c00;
            padding-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ff8c00;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 12px;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 20px;
        }
        
        .copyright {
            display: inline-block;
            padding: 10px 25px;
            background-color: white;
            border: 1px solid #000;
            border-radius: 8px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            font-weight: 900;
            font-style: italic;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar .button-text {
                display: none;
            }
            
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
            
            .logo-sidebar img {
                max-width: 40px;
            }
            
            .user-info {
                font-size: 12px;
            }
            
            .date-selector {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .indicators-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 60px;
            }
            
            .main-content {
                margin-left: 60px;
                width: calc(100% - 60px);
                padding: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .indicator-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .indicator-card:nth-child(1) { animation-delay: 0.1s; }
        .indicator-card:nth-child(2) { animation-delay: 0.2s; }
        .indicator-card:nth-child(3) { animation-delay: 0.3s; }
        .indicator-card:nth-child(4) { animation-delay: 0.4s; }
        .indicator-card:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>

<!-- Sidebar con menú -->
<div class="sidebar">
    <div class="logo-sidebar">
        <img src="img/avi.png" alt="Logo Avicampo">
    </div>
    
    <div class="menu-buttons">
        <?php
        $buttons = [
            ['text' => 'GESTIÓN DEVOLUCIONES', 'link' => 'panel_administrador.php', 'image' => 'img/entrada.png', 'icon' => '📊'],
            ['text' => 'CONSULTAR HISTORIAL', 'link' => 'consualAdmin.php', 'image' => 'img/registros.png', 'icon' => '📋'],
            ['text' => 'CREAR USUARIO', 'link' => 'crearUsuario.php', 'image' => 'img/agregar.png', 'icon' => '➕']
        ];
        
        foreach ($buttons as $button) {
            echo '<a href="' . htmlspecialchars($button['link']) . '" class="menu-button">';
            
            // Verificar si la imagen existe, si no usar emoji
            if (file_exists($button['image'])) {
                echo '<div class="button-icon" style="background-image: url(\'' . htmlspecialchars($button['image']) . '\');"></div>';
            } else {
                echo '<div class="button-icon" style="filter: none; font-size: 24px;">' . $button['icon'] . '</div>';
            }
            
            echo '<span class="button-text">' . htmlspecialchars($button['text']) . '</span>';
            echo '</a>';
        }
        ?>
    </div>
    <br>
    <div class="user-section">
        <div class="user-info">
            <strong><?php echo htmlspecialchars($_SESSION['user'] ?? 'Usuario'); ?></strong>
        </div>
        <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
    </div>
</div>

<!-- Contenido principal -->
<div class="main-content">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Dashboard - DevolutionSync</h1>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <strong>⚠️ Atención:</strong> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($fechasDisponibles)): ?>
            <div class="debug-info">
                <strong>ℹ️ Información:</strong> 
                No hay devoluciones registradas. Esto puede significar que:
                <ul style="margin: 10px 0 0 20px;">
                    <li>No se han registrado devoluciones aún</li>
                    <li>Hay un problema de conexión con la base de datos</li>
                </ul>
                <p style="margin-top: 10px;"><strong>Total de registros en devoluciones:</strong> <?php echo $totalGeneral; ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Selector de fecha -->
        <form method="POST" action="" class="date-selector">
            <label for="fecha">Seleccionar Fecha:</label>
            <select name="fecha" id="fecha">
                <option value="<?php echo date('Y-m-d'); ?>">Hoy - <?php echo date('d/m/Y'); ?></option>
                <?php if (!empty($fechasDisponibles)): ?>
                    <?php foreach ($fechasDisponibles as $fecha): ?>
                        <?php if ($fecha['fecha'] != date('Y-m-d')): ?>
                            <option value="<?php echo htmlspecialchars($fecha['fecha']); ?>" 
                                    <?php echo $fechaSeleccionada == $fecha['fecha'] ? 'selected' : ''; ?>>
                                <?php echo date('d/m/Y', strtotime($fecha['fecha'])); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No hay fechas disponibles</option>
                <?php endif; ?>
            </select>
            <button type="submit">Actualizar</button>
        </form>
        
        <div class="current-date">
            <strong>Mostrando datos para:</strong> 
            <?php 
            if ($fechaSeleccionada == date('Y-m-d')) {
                echo "<strong>HOY - " . date('d/m/Y') . "</strong>";
            } else {
                echo date('d/m/Y', strtotime($fechaSeleccionada));
            }
            ?>
        </div>
    </div>

    <!-- Grid de indicadores para la fecha seleccionada -->
    <div class="indicators-grid">
        <div class="indicator-card">
            <div class="date-badge">FECHA SELECCIONADA</div>
            <div class="indicator-icon">📊</div>
            <div class="indicator-value"><?php echo $totalDevoluciones; ?></div>
            <div class="indicator-label">Total Devoluciones</div>
            <?php if ($fechaSeleccionada == date('Y-m-d')): ?>
                <div class="indicator-trend trend-positive">En tiempo real</div>
            <?php else: ?>
                <div class="indicator-trend trend-neutral">Datos históricos</div>
            <?php endif; ?>
        </div>

        <div class="indicator-card">
            <div class="indicator-icon">📦</div>
            <div class="indicator-value"><?php echo $totalDevolucion; ?></div>
            <div class="indicator-label">Devoluciones</div>
            <div class="indicator-trend trend-neutral">
                Motivo: Devolución
            </div>
        </div>

        <div class="indicator-card">
            <div class="indicator-icon">❌</div>
            <div class="indicator-value"><?php echo $totalFaltante; ?></div>
            <div class="indicator-label">Faltantes</div>
            <div class="indicator-trend trend-negative">
                Motivo: Faltante
            </div>
        </div>

        <div class="indicator-card">
            <div class="indicator-icon">➕</div>
            <div class="indicator-value"><?php echo $totalSobrante; ?></div>
            <div class="indicator-label">Sobrantes</div>
            <div class="indicator-trend trend-positive">
                Motivo: Sobrante
            </div>
        </div>

        <div class="indicator-card">
            <div class="indicator-icon">⚖️</div>
            <div class="indicator-value"><?php echo number_format($totalKg, 2); ?></div>
            <div class="indicator-label">Total KG</div>
            <div class="indicator-trend trend-neutral">
                Peso total
            </div>
        </div>

        <div class="indicator-card">
            <div class="indicator-icon">📦</div>
            <div class="indicator-value"><?php echo $totalUnd; ?></div>
            <div class="indicator-label">Total UND</div>
            <div class="indicator-trend trend-neutral">
                Unidades totales
            </div>
        </div>
    </div>

    <!-- Estado de las devoluciones -->
    <div class="general-stats">
        <h3>📈 Estado de Devoluciones - <?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo $devolucionesPendientes; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $devolucionesAprobadas; ?></div>
                <div class="stat-label">Aprobadas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $devolucionesRechazadas; ?></div>
                <div class="stat-label">Rechazadas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    <?php 
                    $totalRevisadas = $devolucionesAprobadas + $devolucionesRechazadas;
                    $porcentajeRevisadas = $totalDevoluciones > 0 ? round(($totalRevisadas / $totalDevoluciones) * 100, 1) : 0;
                    echo $porcentajeRevisadas . '%';
                    ?>
                </div>
                <div class="stat-label">Revisadas</div>
            </div>
        </div>
    </div>

    <!-- Resumen general (todas las fechas) -->
    <div class="general-stats">
        <h3>📊 Resumen General (Todas las Fechas)</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalGeneral; ?></div>
                <div class="stat-label">Total Devoluciones</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalAprobadas; ?></div>
                <div class="stat-label">Total Aprobadas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalRechazadas; ?></div>
                <div class="stat-label">Total Rechazadas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($totalKgGeneral, 2); ?></div>
                <div class="stat-label">Total KG General</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalUndGeneral; ?></div>
                <div class="stat-label">Total UND General</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo count($fechasDisponibles); ?></div>
                <div class="stat-label">Días con Actividad</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="copyright">
            &#169; DevolutionSync <?php echo date('Y'); ?>
        </div>
    </div>
</div>

<script>
    // Función para cerrar sesión
    function logout() {
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    }

    // Auto-submit del formulario cuando cambia la fecha
    document.getElementById('fecha').addEventListener('change', function() {
        this.form.submit();
    });

    // Efectos de animación al cargar
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.indicator-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
        });
    });
</script>

</body>
</html>