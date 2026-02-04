<?php
require_once 'conexion.php';
session_start();

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'No autorizado'
    ]);
    exit;
}

// Verificar que se envió el ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'ID no proporcionado'
    ]);
    exit;
}

$id = intval($_POST['id']);
$usuario = $_SESSION['user'];

try {
    $connection = Conexion::Conectar();
    
    // Actualizar la notificación como leída
    $sql = "UPDATE notificaciones 
            SET leida = TRUE 
            WHERE id = ? AND usuario_destino = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->execute([$id, $usuario]);
    
    // Verificar si se actualizó algún registro
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    } else {
        // Verificar si la notificación existe pero no pertenece al usuario
        $checkStmt = $connection->prepare("SELECT id, usuario_destino FROM notificaciones WHERE id = ?");
        $checkStmt->execute([$id]);
        $notif = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($notif) {
            if ($notif['usuario_destino'] != $usuario) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permiso para marcar esta notificación'
                ]);
            } else {
                // Ya estaba marcada como leída
                echo json_encode([
                    'success' => true,
                    'message' => 'La notificación ya estaba marcada como leída'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Notificación no encontrada'
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log("Error al marcar notificación: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al marcar la notificación: ' . $e->getMessage()
    ]);
}
?>