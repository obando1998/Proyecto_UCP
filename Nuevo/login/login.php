<?php
require_once '../conexion.php';

header('Content-Type: application/json');

// Configuración de seguridad
$max_attempts = 3;
$lockout_time = 180;

// Función para limpiar y validar entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
        exit;
    }
    
    try {
        // Usar la conexión PDO
        $connection = Conexion::Conectar();
        
        // Consulta preparada para prevenir SQL injection - INCLUIR NOMBRE
        $sql = "SELECT USR, PAS, GRADO, NOMBRE FROM usuarios WHERE USR = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $username, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar la contraseña (comparación directa según tu estructura)
            if ($password === $user['PAS']) {
                // IMPORTANTE: Iniciar sesión ANTES de configurar variables
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Configurar variables de sesión
                $_SESSION['user'] = $user['USR'];
                $_SESSION['nombre'] = $user['NOMBRE'];  // AGREGAR NOMBRE
                $_SESSION['logged_in'] = true;
                $_SESSION['grado'] = $user['GRADO'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // Regenerar ID de sesión para seguridad
                session_regenerate_id(true);
                
                // Determinar la redirección basada en el GRADO
                $redirect_url = '';
                if ($user['GRADO'] == 1) {
                    $redirect_url = '../menu.php';  // Admin - va a la raíz
                } elseif ($user['GRADO'] == 2) {
                    $redirect_url = '../panel_auxiliar.php';  // Auxiliar
                } elseif ($user['GRADO'] == 3) {
                    $redirect_url = '../consulta.php';  // Consulta
                } else {
                    $redirect_url = 'login.html';  // Por defecto vuelve al login
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login exitoso',
                    'redirect' => $redirect_url,
                    'grado' => $user['GRADO']
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Contraseña incorrecta'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Usuario no encontrado'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error en login: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error de conexión con la base de datos'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>