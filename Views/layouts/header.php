<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <meta name="copyright" content="Sebastian Obando">
    <title><?php echo $titulo ?? 'DevolutionSync'; ?></title>
    <link rel="icon" type="image/png" href="assets/img/icono.png">

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- CSS del proyecto -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/panel.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<!-- ══════════════════════════════════════
     SIDEBAR
══════════════════════════════════════ -->
<div class="sidebar">

    <!-- Logo -->
    <div class="logo-sidebar">
        <img src="assets/img/logo.png" alt="DevolutionSync Logo">
    </div>

    <div class="sidebar-menu">

        <!-- Dashboard (solo Admin - Grado 1) -->
        <?php if (isset($_SESSION['grado']) && $_SESSION['grado'] == 1): ?>
        <a href="index.php?url=home/index" class="menu-button <?php echo (strpos($_SERVER['QUERY_STRING'] ?? '', 'home/index') !== false) ? 'active' : ''; ?>">
            <div class="menu-icon"><i class="fas fa-chart-line"></i></div>
            <div class="menu-text">
                <strong>DASHBOARD</strong><br>Principal
            </div>
        </a>
        <?php endif; ?>

        <!-- Gestión de Devoluciones (Admin y Auxiliar) -->
        <?php if (isset($_SESSION['grado']) && ($_SESSION['grado'] == 1 || $_SESSION['grado'] == 2)): ?>
        <a href="index.php?url=panel/auxiliar" class="menu-button <?php echo (strpos($_SERVER['QUERY_STRING'] ?? '', 'panel/auxiliar') !== false) ? 'active' : ''; ?>">
            <div class="menu-icon"><i class="fas fa-boxes"></i></div>
            <div class="menu-text">
                <strong>GESTIÓN</strong><br>DEVOLUCIONES
            </div>
        </a>
        <?php endif; ?>

        <!-- Panel Administrador (solo Admin - Grado 1) -->
        <?php if (isset($_SESSION['grado']) && $_SESSION['grado'] == 1): ?>
        <a href="index.php?url=admin/index" class="menu-button <?php echo (strpos($_SERVER['QUERY_STRING'] ?? '', 'admin/index') !== false) ? 'active' : ''; ?>">
            <div class="menu-icon"><i class="fas fa-tasks"></i></div>
            <div class="menu-text">
                <strong>PANEL</strong><br>ADMINISTRADOR
            </div>
        </a>
        <?php endif; ?>

        <!-- Consultar Historial (todos los usuarios) -->
        <a href="index.php?url=consulta/index" class="menu-button <?php echo (strpos($_SERVER['QUERY_STRING'] ?? '', 'consulta/') !== false) ? 'active' : ''; ?>">
            <div class="menu-icon"><i class="fas fa-history"></i></div>
            <div class="menu-text">
                <strong>CONSULTAR</strong><br>HISTORIAL
            </div>
        </a>

        <!-- Crear Usuario (solo Admin - Grado 1) -->
        <?php if (isset($_SESSION['grado']) && $_SESSION['grado'] == 1): ?>
        <a href="index.php?url=usuario/crear" class="menu-button <?php echo (strpos($_SERVER['QUERY_STRING'] ?? '', 'usuario/crear') !== false) ? 'active' : ''; ?>">
            <div class="menu-icon"><i class="fas fa-user-plus"></i></div>
            <div class="menu-text">
                <strong>CREAR</strong><br>USUARIO
            </div>
        </a>
        <?php endif; ?>

        <!-- Info del usuario logueado -->
        <div class="user-info">
            <div class="user-label">
                <?php 
                if (isset($_SESSION['grado'])) {
                    switch ($_SESSION['grado']) {
                        case 1: echo '<i class="fas fa-crown"></i> ADMINISTRADOR'; break;
                        case 2: echo '<i class="fas fa-tools"></i> AUXILIAR';       break;
                        case 3: echo '<i class="fas fa-eye"></i> CONSULTA';         break;
                        default: echo 'USUARIO';
                    }
                }
                ?>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></div>
        </div>

        <!-- Cerrar sesión -->
        <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>

    </div>
</div>

<!-- ══════════════════════════════════════
     CONTENIDO PRINCIPAL
══════════════════════════════════════ -->
<div class="main-content">

<script>
function logout() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = 'index.php?url=auth/logout';
    }
}
</script>

<style>
/* Botón activo en el sidebar */
.menu-button.active {
    background: rgba(255, 255, 255, 1) !important;
    border-left: 4px solid #ff6b00;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.menu-button.active .menu-text strong {
    color: #ff6b00;
}
</style>
