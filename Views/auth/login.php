<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DevolutionSync</title>
    <link rel="icon" type="image/png" href="assets/img/icono.png">
    <style>
        /* Mantenemos tu diseño original con leves ajustes para MVC */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            background-image: linear-gradient(to right, #e2e2e2, #ffe5c9);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }

        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h1 { color: #ff7b00; font-size: 28px; margin-bottom: 8px; }
        .login-header p { color: #666; font-size: 14px; }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #ff7b00;
            box-shadow: 0 0 0 2px rgba(255, 123, 0, 0.2);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: #ff7b00;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover:not(:disabled) { background-color: #e66a00; transform: translateY(-2px); }
        .btn:disabled { background-color: #cccccc; cursor: not-allowed; opacity: 0.6; }

        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; display: none; }
        .alert-error { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }

        .logo { text-align: center; margin-bottom: 20px; }
        .security-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            border-left: 4px solid #ff7b00;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="assets/img/icono.png" alt="Logo" width="80" height="auto" style="display: block; margin: 0 auto;" />
        </div>
        
        <div class="login-header">
            <h1>Iniciar Sesión</h1>
            <p>Ingresa tus credenciales para acceder a DevolutionSync</p>
        </div>
        
        <div id="alertMessage" class="alert"></div>
        
        <form id="loginForm">
            <div class="input-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" placeholder="Ingresa tu usuario" required autocomplete="username">
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn" id="loginButton">Iniciar Sesión</button>
        </form>

        <div class="security-info">
            <h4>Medidas de Seguridad</h4>
            <ul>
                <li>La sesión expira tras 3 minutos de inactividad.</li>
                <li>Conexión segura a base de datos centralizada.</li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 25px; color: #777; font-size: 14px;">
            <p>Gestion De Devoluciones</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loginButton = document.getElementById('loginButton');
            const alertMessage = document.getElementById('alertMessage');
            const formData = new FormData(this);
            
            // Bloquear botón mientras procesa
            loginButton.disabled = true;
            loginButton.textContent = 'Verificando...';
            
            // FETCH a la ruta del controlador MVC
            fetch('index.php?url=auth/login', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Acceso concedido. Redirigiendo...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showAlert(data.message || 'Error al iniciar sesión', 'error');
                    loginButton.disabled = false;
                    loginButton.textContent = 'Iniciar Sesión';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error técnico en el servidor', 'error');
                loginButton.disabled = false;
                loginButton.textContent = 'Iniciar Sesión';
            });
        });

        function showAlert(message, type) {
            const alertMessage = document.getElementById('alertMessage');
            alertMessage.textContent = message;
            alertMessage.className = `alert alert-${type}`;
            alertMessage.style.display = 'block';
        }

        // Detectar si venimos de un timeout de sesión
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('timeout') === '1') {
                showAlert('Su sesión ha expirado por inactividad.', 'warning');
            }
        });
    </script>
</body>
</html>