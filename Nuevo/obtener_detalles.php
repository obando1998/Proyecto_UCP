<?php
require_once("conexion.php");
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

try {
    $connection = Conexion::Conectar();
    
    // Obtener todos los detalles de la devolución
    $stmt = $connection->prepare("
        SELECT * FROM devoluciones WHERE id = ?
    ");
    $stmt->execute([$id]);
    $devolucion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$devolucion) {
        echo json_encode(['success' => false, 'message' => 'Devolución no encontrada']);
        exit;
    }
    
    // Construir HTML para el modal
    $html = '<div class="detalles-container">';
    
    // Información del Cliente
    $html .= '<div class="seccion-detalles">';
    $html .= '<h3 class="titulo-seccion">👤 Información del Cliente</h3>';
    $html .= '<div class="info-grid">';
    $html .= '<div class="info-item"><strong>NIT:</strong> <span>' . htmlspecialchars($devolucion['nit']) . '</span></div>';
    $html .= '<div class="info-item"><strong>Cliente:</strong> <span>' . htmlspecialchars($devolucion['nombre_cliente']) . '</span></div>';
    $html .= '<div class="info-item full-width"><strong>Dirección:</strong> <span>' . htmlspecialchars($devolucion['direccion'] ?: 'N/A') . '</span></div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Información del Producto
    $html .= '<div class="seccion-detalles">';
    $html .= '<h3 class="titulo-seccion">📦 Información del Producto</h3>';
    $html .= '<div class="info-grid">';
    $html .= '<div class="info-item"><strong>Código:</strong> <span>' . htmlspecialchars($devolucion['codigo_producto']) . '</span></div>';
    $html .= '<div class="info-item"><strong>Descripción:</strong> <span>' . htmlspecialchars($devolucion['descripcion_producto']) . '</span></div>';
    $html .= '<div class="info-item"><strong>Unidad:</strong> <span>' . htmlspecialchars($devolucion['unidad'] ?: 'N/A') . '</span></div>';
    $html .= '<div class="info-item"><strong>KG:</strong> <span>' . htmlspecialchars($devolucion['kg'] ?: '0') . '</span></div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Información de la Devolución
    $html .= '<div class="seccion-detalles">';
    $html .= '<h3 class="titulo-seccion">🔄 Detalles de la Devolución</h3>';
    $html .= '<div class="info-grid">';
    
    // Badge del motivo con colores
    $motivoClass = '';
    $motivoIcon = '';
    switch($devolucion['motivo']) {
        case 'devolucion':
            $motivoClass = 'badge-devolucion';
            $motivoIcon = '📦';
            break;
        case 'faltante':
            $motivoClass = 'badge-faltante';
            $motivoIcon = '❌';
            break;
        case 'sobrante':
            $motivoClass = 'badge-sobrante';
            $motivoIcon = '➕';
            break;
    }
    
    $html .= '<div class="info-item"><strong>Motivo:</strong> <span class="badge ' . $motivoClass . '">' . $motivoIcon . ' ' . ucfirst(htmlspecialchars($devolucion['motivo'])) . '</span></div>';
    
    // Badge del estado
    $estadoClass = 'badge-' . $devolucion['estado'];
    $estadoIcon = $devolucion['estado'] == 'pendiente' ? '⏳' : ($devolucion['estado'] == 'aprobado' ? '✅' : '❌');
    $html .= '<div class="info-item"><strong>Estado:</strong> <span class="badge ' . $estadoClass . '">' . $estadoIcon . ' ' . ucfirst(htmlspecialchars($devolucion['estado'])) . '</span></div>';
    
    $html .= '<div class="info-item"><strong>Cantidad UND:</strong> <span>' . htmlspecialchars($devolucion['cantidad_und'] ?: '0') . '</span></div>';
    $html .= '<div class="info-item"><strong>Cantidad KG:</strong> <span>' . htmlspecialchars($devolucion['cantidad_kg'] ?: '0') . '</span></div>';
    $html .= '<div class="info-item"><strong>Fecha Creación:</strong> <span>' . date('d/m/Y H:i', strtotime($devolucion['fecha_creacion'])) . '</span></div>';
    $html .= '<div class="info-item"><strong>Creado por:</strong> <span>' . htmlspecialchars($devolucion['usuario_creador']) . '</span></div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Observación del Auxiliar
    if (!empty($devolucion['observacion'])) {
        $html .= '<div class="seccion-detalles">';
        $html .= '<h3 class="titulo-seccion">📝 Observación del Auxiliar</h3>';
        $html .= '<div class="observacion-box">' . nl2br(htmlspecialchars($devolucion['observacion'])) . '</div>';
        $html .= '</div>';
    }
    
    // Evidencia (Imagen/Video)
    if (!empty($devolucion['evidencia'])) {
        $html .= '<div class="seccion-detalles">';
        $html .= '<h3 class="titulo-seccion">📷 Evidencia</h3>';
        
        $rutaArchivo = 'uploads/' . $devolucion['evidencia'];
        $extension = strtolower(pathinfo($devolucion['evidencia'], PATHINFO_EXTENSION));
        
        // Verificar si el archivo existe
        if (file_exists($rutaArchivo)) {
            // Si es imagen
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $html .= '<div class="evidencia-container">';
                $html .= '<img src="' . $rutaArchivo . '" alt="Evidencia" class="evidencia-imagen" onclick="abrirImagenCompleta(\'' . $rutaArchivo . '\')">';
                $html .= '<p class="evidencia-hint">💡 Haz clic en la imagen para verla en tamaño completo</p>';
                $html .= '</div>';
            }
            // Si es video
            elseif (in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'avi'])) {
                $html .= '<div class="evidencia-container">';
                $html .= '<video controls class="evidencia-video">';
                $html .= '<source src="' . $rutaArchivo . '" type="video/' . ($extension == 'mov' ? 'quicktime' : $extension) . '">';
                $html .= 'Tu navegador no soporta la reproducción de video.';
                $html .= '</video>';
                $html .= '</div>';
            }
            // Otro tipo de archivo
            else {
                $html .= '<div class="evidencia-container">';
                $html .= '<p>📎 Archivo adjunto: <a href="' . $rutaArchivo . '" target="_blank" class="enlace-descarga">' . htmlspecialchars($devolucion['evidencia']) . '</a></p>';
                $html .= '</div>';
            }
        } else {
            $html .= '<div class="evidencia-container">';
            $html .= '<p class="texto-error">⚠️ Archivo no encontrado: ' . htmlspecialchars($devolucion['evidencia']) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    // Información de Revisión (si existe)
    if ($devolucion['estado'] != 'pendiente' && !empty($devolucion['usuario_revisor'])) {
        $html .= '<div class="seccion-detalles revision-section">';
        $html .= '<h3 class="titulo-seccion">🔍 Información de Revisión</h3>';
        $html .= '<div class="info-grid">';
        $html .= '<div class="info-item"><strong>Revisado por:</strong> <span>' . htmlspecialchars($devolucion['usuario_revisor']) . '</span></div>';
        $html .= '<div class="info-item"><strong>Fecha Revisión:</strong> <span>' . date('d/m/Y H:i', strtotime($devolucion['fecha_revision'])) . '</span></div>';
        $html .= '<div class="info-item"><strong>Código Admin:</strong> <span>' . htmlspecialchars($devolucion['codigo_admin'] ?: 'N/A') . '</span></div>';
        $html .= '</div>';
        
        if (!empty($devolucion['observacion_admin'])) {
            $html .= '<div class="info-item full-width" style="margin-top: 15px;">';
            $html .= '<strong>Observación del Administrador:</strong>';
            $html .= '<div class="observacion-box observacion-admin">' . nl2br(htmlspecialchars($devolucion['observacion_admin'])) . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>'; // Cierre detalles-container
    
    // CSS inline para el modal
    $html .= '
    <style>
        .detalles-container {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .seccion-detalles {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ff8c00;
        }
        
        .revision-section {
            border-left-color: #28a745;
            background: #e8f5e9;
        }
        
        .titulo-seccion {
            color: #ff8c00;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
            border-bottom: 2px solid #ff8c00;
            padding-bottom: 8px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-item.full-width {
            grid-column: 1 / -1;
        }
        
        .info-item strong {
            color: #555;
            font-size: 14px;
            font-weight: 600;
        }
        
        .info-item span {
            color: #333;
            font-size: 15px;
            padding: 8px;
            background: white;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        
        .badge {
            display: inline-block;
            padding: 8px 15px !important;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            border: none !important;
        }
        
        .badge-devolucion {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .badge-faltante {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-sobrante {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .observacion-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            color: #333;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .observacion-admin {
            background: #fff8e1;
            border-color: #ffd54f;
        }
        
        .evidencia-container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 2px dashed #e0e0e0;
        }
        
        .evidencia-imagen {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .evidencia-imagen:hover {
            transform: scale(1.02);
        }
        
        .evidencia-video {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .evidencia-hint {
            margin-top: 10px;
            color: #666;
            font-size: 13px;
            font-style: italic;
        }
        
        .enlace-descarga {
            color: #ff8c00;
            text-decoration: none;
            font-weight: 600;
        }
        
        .enlace-descarga:hover {
            text-decoration: underline;
        }
        
        .texto-error {
            color: #c62828;
            font-weight: 500;
        }
        
        /* Scrollbar personalizado */
        .detalles-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .detalles-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .detalles-container::-webkit-scrollbar-thumb {
            background: #ff8c00;
            border-radius: 10px;
        }
        
        .detalles-container::-webkit-scrollbar-thumb:hover {
            background: #e67e00;
        }
    </style>';
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    error_log("Error al obtener detalles: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los detalles: ' . $e->getMessage()
    ]);
}
?>