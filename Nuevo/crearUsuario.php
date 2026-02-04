<?php
require_once("conexion.php");
require_once 'login/session_timeout.php';

// Verificar si el usuario está logueado y es administrador (GRADO 1)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login/login.html');
    exit;
}

if ($_SESSION['grado'] != 1) {
    header('Location: menu.php');
    exit;
}

$mensaje = '';
$tipoMensaje = '';

// Procesar el formulario de creación de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $usr = trim($_POST['usr']);
    $pas = trim($_POST['pas']);
    $nombre = trim($_POST['nombre']);
    $grado = intval($_POST['grado']);
    
    // Validaciones
    if (empty($usr) || empty($pas) || empty($nombre) || empty($grado)) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipoMensaje = 'error';
    } else {
        try {
            $connection = Conexion::Conectar();
            
            // Verificar si el usuario ya existe
            $stmt = $connection->prepare("SELECT USR FROM usuarios WHERE USR = ?");
            $stmt->execute([$usr]);
            
            if ($stmt->rowCount() > 0) {
                $mensaje = 'El usuario ya existe en el sistema';
                $tipoMensaje = 'error';
            } else {
                // Insertar nuevo usuario
                $sql = "INSERT INTO usuarios (USR, PAS, NOMBRE, GRADO) VALUES (?, ?, ?, ?)";
                $stmt = $connection->prepare($sql);
                $stmt->execute([$usr, $pas, $nombre, $grado]);
                
                $mensaje = 'Usuario creado exitosamente';
                $tipoMensaje = 'success';
                
                // Limpiar el formulario después de crear
                $_POST = array();
            }
        } catch (Exception $e) {
            $mensaje = 'Error al crear el usuario: ' . $e->getMessage();
            $tipoMensaje = 'error';
            error_log("Error al crear usuario: " . $e->getMessage());
        }
    }
}

// Obtener lista de usuarios existentes
$usuarios = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("SELECT USR, NOMBRE, GRADO FROM usuarios ORDER BY GRADO, NOMBRE");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
}

// Función para obtener el nombre del grado
function getNombreGrado($grado) {
    switch($grado) {
        case 1: return 'Administrador';
        case 2: return 'Auxiliar';
        case 3: return 'Consulta';
        default: return 'Desconocido';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <meta name="copyright" content="Sebastian Obando">
    <title>Crear Usuario - DevolutionSync</title>
    <link rel="icon" type="image/png" href="img/icono.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(to right, #e2e2e2, #ffe5c9);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #ff8c00;
            font-size: 28px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .user-info span {
            color: #666;
            font-weight: 500;
        }

        .btn {
            background: #ff8c00;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn:hover {
            background: #e67e00;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #ff8c00;
            font-size: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc3545;
            margin-left: 3px;
        }

        .form-control {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff8c00;
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
        }

        .form-control:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        select.form-control {
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table thead {
            background: linear-gradient(to right, #ff8c00, #ff6b00);
            color: white;
        }

        .table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .table tbody tr {
            transition: background 0.3s ease;
        }

        .table tbody tr:hover {
            background: #fff8f0;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .badge-admin {
            background: #ffd700;
            color: #8b6914;
        }

        .badge-auxiliar {
            background: #87ceeb;
            color: #1e5b7a;
        }

        .badge-consulta {
            background: #90ee90;
            color: #2d5b2d;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-box h3 {
            color: #1976d2;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info-box ul {
            margin-left: 20px;
            color: #555;
        }

        .info-box li {
            margin-bottom: 5px;
            font-size: 14px;
        }

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

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Crear Usuario - DevolutionSync</h1>
            <div class="user-info">
                <span>👤 <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                <a href="menu.php" class="btn">📊 Menú Principal</a>
                <a href="logout.php" class="btn btn-danger">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <?php if ($tipoMensaje === 'success'): ?>
                    ✅ <?php echo htmlspecialchars($mensaje); ?>
                <?php else: ?>
                    ⚠️ <?php echo htmlspecialchars($mensaje); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Información sobre los grados -->
        <div class="info-box">
            <h3>ℹ️ Información sobre Grados de Usuario</h3>
            <ul>
                <li><strong>Grado 1 - Administrador:</strong> Acceso completo al sistema, puede crear usuarios, aprobar/rechazar devoluciones</li>
                <li><strong>Grado 2 - Auxiliar:</strong> Puede registrar devoluciones y consultar su historial</li>
                <li><strong>Grado 3 - Consulta:</strong> Solo puede consultar el historial de devoluciones</li>
            </ul>
        </div>

        <!-- Formulario de creación -->
        <div class="card">
            <h2>➕ Crear Nuevo Usuario</h2>
            <form method="POST" action="" id="formCrearUsuario">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="usr">
                            Usuario (USR)
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="usr" 
                            name="usr" 
                            class="form-control" 
                            placeholder="Ej: JPEREZ"
                            required
                            maxlength="50"
                            value="<?php echo isset($_POST['usr']) ? htmlspecialchars($_POST['usr']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="pas">
                            Contraseña (PAS)
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="pas" 
                            name="pas" 
                            class="form-control" 
                            placeholder="Ingrese la contraseña"
                            required
                            maxlength="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="nombre">
                            Nombre Completo
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            class="form-control" 
                            placeholder="Ej: JUAN PEREZ"
                            required
                            maxlength="100"
                            value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="grado">
                            Grado de Usuario
                            <span class="required">*</span>
                        </label>
                        <select 
                            id="grado" 
                            name="grado" 
                            class="form-control" 
                            required
                        >
                            <option value="">-- Seleccione un grado --</option>
                            <option value="1" <?php echo (isset($_POST['grado']) && $_POST['grado'] == 1) ? 'selected' : ''; ?>>
                                1 - Administrador
                            </option>
                            <option value="2" <?php echo (isset($_POST['grado']) && $_POST['grado'] == 2) ? 'selected' : ''; ?>>
                                2 - Auxiliar
                            </option>
                            <option value="3" <?php echo (isset($_POST['grado']) && $_POST['grado'] == 3) ? 'selected' : ''; ?>>
                                3 - Consulta
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="crear_usuario" class="btn">
                        ✅ Crear Usuario
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        🔄 Limpiar Formulario
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de usuarios existentes -->
        <div class="card">
            <h2>👥 Usuarios Registrados (<?php echo count($usuarios); ?>)</h2>
            <?php if (empty($usuarios)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">
                    No hay usuarios registrados en el sistema
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Grado</th>
                                <th>Nivel de Acceso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($usuario['USR']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($usuario['NOMBRE']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['GRADO']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = '';
                                        switch($usuario['GRADO']) {
                                            case 1:
                                                $badgeClass = 'badge-admin';
                                                break;
                                            case 2:
                                                $badgeClass = 'badge-auxiliar';
                                                break;
                                            case 3:
                                                $badgeClass = 'badge-consulta';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo getNombreGrado($usuario['GRADO']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="copyright">
                &#169; DevolutionSync <?php echo date('Y'); ?>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
            const usr = document.getElementById('usr').value.trim();
            const pas = document.getElementById('pas').value.trim();
            const nombre = document.getElementById('nombre').value.trim();
            const grado = document.getElementById('grado').value;

            if (!usr || !pas || !nombre || !grado) {
                e.preventDefault();
                alert('⚠️ Todos los campos son obligatorios');
                return false;
            }

            if (usr.length < 3) {
                e.preventDefault();
                alert('⚠️ El usuario debe tener al menos 3 caracteres');
                return false;
            }

            if (pas.length < 4) {
                e.preventDefault();
                alert('⚠️ La contraseña debe tener al menos 4 caracteres');
                return false;
            }

            // Confirmar creación
            const gradoTexto = getNombreGrado(grado);
            if (!confirm(`¿Está seguro de crear el usuario "${usr}" con grado "${gradoTexto}"?`)) {
                e.preventDefault();
                return false;
            }
        });

        function getNombreGrado(grado) {
            switch(grado) {
                case '1': return 'Administrador';
                case '2': return 'Auxiliar';
                case '3': return 'Consulta';
                default: return 'Desconocido';
            }
        }

        // Convertir usuario a mayúsculas automáticamente
        document.getElementById('usr').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        document.getElementById('nombre').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>