<!DOCTYPE html>
<html lang="es">
<head>
    <title>Login - DevolutionSync</title>
    <link rel="icon" type="image/png" href="assets/img/icono.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Sebastian Obando">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap'); 

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-image: linear-gradient(to right, #e2e2e2, #ffe5c9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
        }

        .contenedor {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }

        .contenedor p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
        }

        .contenedor span {
            font-size: 12px;
        }

        .contenedor a {
            color: #333;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
        }

        .contenedor button {
            background-color: #ff7b00;
            color: #fff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
        }

        .contenedor button.hidden {
            background-color: transparent;
            border-color: #fff;
        }

        .contenedor button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .contenedor form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .contenedor input {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        /* ── Alerta ── */
        .alert {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
            display: none;
        }

        .alert-error   { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .contenedor.active .sign-in {
            transform: translateX(100%);
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .contenedor.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move {
            0%, 49.99% { opacity: 0; z-index: 1; }
            50%, 100%  { opacity: 1; z-index: 5; }
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .contenedor.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle {
            background: linear-gradient(to bottom, #ff7b00, #ff9a3c);
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .contenedor.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .contenedor.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .contenedor.active .toggle-right {
            transform: translateX(200%);
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }

        .copyright {
            display: inline-block;
            padding: 10px 15px;
            background-color: #fafafa;
            color: black;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    
    <div class="contenedor" id="contenedor">
        <div class="form-container sign-in">
            <form id="loginForm">
                <h1>Iniciar Sesion</h1>
                <span>Ingresa tu usuario y contraseña para iniciar sesión</span>

                <!-- ✅ Este div faltaba — causaba el error en consola -->
                <div id="alertMessage" class="alert"></div>

                <input type="text" id="username" name="username" placeholder="Ingresa tu usuario" required autocomplete="username">
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                <button type="submit" id="loginButton">Iniciar Sesión</button>
            </form>
        </div>
        
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-right">
                    <h1>Hola!</h1>
                    <p>Bienvenidos A DevolutionSync Avicampo.</p>
                </div>
            </div>
        </div>
    </div>

    <br>
    <div>
        <div class="copyright">
            &#169; Avicampo <?php echo date('Y'); ?>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loginButton  = document.getElementById('loginButton');
            const formData     = new FormData(this);
            
            loginButton.disabled    = true;
            loginButton.textContent = 'Verificando...';
            
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
                    showAlert(data.message || 'Credenciales incorrectas', 'error');
                    loginButton.disabled    = false;
                    loginButton.textContent = 'Iniciar Sesión';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error técnico en el servidor', 'error');
                loginButton.disabled    = false;
                loginButton.textContent = 'Iniciar Sesión';
            });
        });

        function showAlert(message, type) {
            const alertMessage = document.getElementById('alertMessage');
            alertMessage.textContent = message;
            alertMessage.className   = `alert alert-${type}`;
            alertMessage.style.display = 'block';
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('timeout') === '1') {
                showAlert('Su sesión ha expirado por inactividad.', 'warning');
            }
        });
    </script>	
</body>
</html>