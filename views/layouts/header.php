<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <meta name="copyright" content="Sebastian Obando">
    <title><?php echo $titulo ?? 'DevolutionSync'; ?></title>
    <link rel="icon" type="image/png" href="img/icono.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/panel.css">
</head>
<body>

<!-- Sidebar con logo y botones -->
<div class="sidebar">
    <div class="logo-sidebar">
        <img src="img/logo.png" alt="AviCampo Logo">
    </div>
    
    <div class="sidebar-menu">
        <!-- Bot車n: Dashboard Principal (solo para Admin - Grado 1) -->
        <?php if (isset($_SESSION['grado']) && $_SESSION['grado'] == 1): ?>
        <a href="index.php?url=home/index" class="menu-button">
            <div class="menu-icon">??</div>
            <div class="menu-text">
                <strong>DASHBOARD</strong><br>Principal
            </div>
        </a>
        <?php endif; ?>
        
        <!-- Bot車n: Gesti車n de Devoluciones (Auxiliar - Grado 2, o Admin) -->
        <?php if (isset($_SESSION['grado']) && ($_SESSION['grado'] == 1 || $_SESSION['grado'] == 2)): ?>
        <a href="index.php?url=panel/auxiliar" class="menu-button">
            <div class="menu-icon">??</div>
            <div class="menu-text">
                <strong>GESTI車N</strong><br>DEVOLUCIONES
            </div>
        </a>
        <?php endif; ?>
        
        <!-- Bot車n: Panel Administrador (solo para Admin - Grado 1) -->
        <?php if (isset($_SESSION['grado']) && $_SESSION['grado'] == 1): ?>
        <a href="index.php?url=admin/index" class="menu-button">
            <div class="menu-icon">??</div>
            <div class="menu-text">
                <strong>PANEL</strong><br>ADMINISTRADOR
            </div>
        </a>
        <?php endif; ?>
        
        <!-- Bot車n: Consultar Historial (Todos los usuarios) -->
        <a href="index.php?url=consulta/index" class="menu-button">
            <div class="menu-icon">??</div>
            <div class="menu-text">
                <strong>CONSULTAR</strong><br>HISTORIAL
            </div>
        </a>
        
        <!-- Bot車n: Crear Usuario (solo para Admin - Grado 1) -->
        <?php if (isset($_SESSION['grado']) && $_SESSION['grado'] == 1): ?>
        <a href="index.php?url=usuario/crear" class="menu-button">
            <div class="menu-icon">?</div>
            <div class="menu-text">
                <strong>CREAR USUARIO</strong>
            </div>
        </a>
        <?php endif; ?>
        
        <!-- Info de Usuario -->
        <div class="user-info">
            <div class="user-label">
                <?php 
                if (isset($_SESSION['grado'])) {
                    switch($_SESSION['grado']) {
                        case 1: echo 'ADMINISTRADOR'; break;
                        case 2: echo 'AUXILIAR'; break;
                        case 3: echo 'CONSULTA'; break;
                        default: echo 'USUARIO';
                    }
                }
                ?>
            </div>
            <div class="user-name"><?php echo $_SESSION['nombre'] ?? 'Usuario'; ?></div>
        </div>
        <button class="logout-btn" onclick="logout()">Cerrar Sesi車n</button>
    </div>
</div>

<!-- Contenido principal -->
<div class="main-content">