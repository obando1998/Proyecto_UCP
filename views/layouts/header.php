<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sebastian Obando">
    <title><?php echo isset($titulo) ? $titulo : 'DevolutionSync'; ?></title>
    <link rel="icon" type="image/png" href="assets/img/icono.png">
    <style>
        /* Estilos Globales extraídos de crearUsuario.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-image: linear-gradient(to right, #e2e2e2, #ffe5c9); 
            min-height: 100vh; 
            padding: 20px; 
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { 
            background: white; padding: 25px; border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); margin-bottom: 20px; 
            display: flex; justify-content: space-between; align-items: center; 
            flex-wrap: wrap; gap: 15px; 
        }
        .header h1 { color: #ff8c00; font-size: 28px; }
        .user-info { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .btn { 
            background: #ff8c00; color: white; padding: 10px 20px; 
            text-decoration: none; border-radius: 8px; border: none; 
            cursor: pointer; font-size: 14px; font-weight: 600; 
            transition: all 0.3s ease; display: inline-block; 
        }
        .btn:hover { background: #e67e00; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; }
        .card { 
            background: white; padding: 30px; border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); margin-bottom: 20px; 
        }
        /* Estilos de tablas y alertas comunes */
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table thead { background: #ff8c00; color: white; }
        .table th, .table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 <?php echo isset($titulo) ? $titulo : 'DevolutionSync'; ?></h1>
            <div class="user-info">
                <?php if(isset($_SESSION['nombre'])): ?>
                    <span>👤 <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                <?php endif; ?>
                <a href="index.php?url=home/index" class="btn">📊 Menú Principal</a>
                <a href="index.php?url=auth/logout" class="btn btn-danger">🚪 Cerrar Sesión</a>
            </div>
        </div>