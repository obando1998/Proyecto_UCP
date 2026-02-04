<?php
require_once("conexion.php");
require_once 'login/session_timeout.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $login_url = 'login/login.html';
    header('Location: ' . $login_url);
    exit;
}

// Obtener todas las devoluciones para el historial
$todasDevoluciones = [];
try {
    $connection = Conexion::Conectar();
    $stmt = $connection->prepare("SELECT * FROM devoluciones ORDER BY fecha_creacion DESC LIMIT 100");
    $stmt->execute();
    $todasDevoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener historial: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta - DevolutionSync</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Incluir html2pdf desde CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* Estilos específicos para el PDF */
        .pdf-container {
            background: white;
            color: black;
            padding: 20px;
        }
        .pdf-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .pdf-section {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .pdf-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin: 2px;
        }
        .badge-pendiente { background-color: #ffeb3b; color: #000; }
        .badge-aprobado { background-color: #4caf50; color: white; }
        .badge-rechazado { background-color: #f44336; color: white; }
        .badge-procesado { background-color: #2196f3; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DevolutionSync - Consulta de Historial</h1>
            <div class="user-info">
                <span>Bienvenido, <?php echo $_SESSION['nombre']; ?></span>
                <?php if ($_SESSION['grado'] == 1): ?>
                    <a href="menu.php" class="btn">Menu Principal</a>
                <?php endif; ?>
                <a href="panel_administrador.php" class="btn btn-secondary">Panel Administrador</a>
            </div>
        </div>

        <div class="card">
            <h2>📚 Historial de Devoluciones (<?php echo count($todasDevoluciones); ?>)</h2>
            <?php if (empty($todasDevoluciones)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    No hay devoluciones registradas
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Código Producto</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Revisado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todasDevoluciones as $devolucion): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($devolucion['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($devolucion['nombre_cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($devolucion['codigo_producto']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($devolucion['motivo'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($devolucion['estado']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($devolucion['estado'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($devolucion['fecha_creacion'])); ?></td>
                                    <td><?php echo htmlspecialchars($devolucion['usuario_revisor'] ?: 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-secondary" onclick="verDetalles(<?php echo $devolucion['id']; ?>)">
                                            👁️ Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para ver detalles -->
    <div id="detallesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Detalles de la Devolución</h2>
                <span class="close" onclick="cerrarModal('detallesModal')">&times;</span>
            </div>
            <div class="modal-body" id="detallesContenido">
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer" id="modalFooter" style="display: none; padding: 15px; border-top: 1px solid #eee; text-align: right;">
                <button class="btn btn-primary" onclick="generarPDF()">📄 Generar PDF</button>
                <button class="btn btn-secondary" onclick="cerrarModal('detallesModal')">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        let currentDevolucionId = null;

        function verDetalles(id) {
            currentDevolucionId = id;
            document.getElementById('detallesModal').style.display = 'block';
            document.getElementById('modalFooter').style.display = 'none';
            
            document.getElementById('detallesContenido').innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div class="loader"></div>
                    <p style="margin-top: 15px; color: #666;">Cargando detalles...</p>
                </div>
            `;

            fetch('obtener_detalles.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('detallesContenido').innerHTML = data.html;
                    document.getElementById('modalFooter').style.display = 'block';
                    document.querySelector('#detallesModal .modal-body').scrollTop = 0;
                } else {
                    document.getElementById('detallesContenido').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #c62828; font-size: 18px;">❌ Error al cargar los detalles</p>
                            <button class="btn" onclick="cerrarModal('detallesModal')" style="margin-top: 20px;">Cerrar</button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

function generarPDF() {
    // 1. Clonar el contenido del modal completo (incluyendo el div.detalles-container con su CSS inline)
    const modalContent = document.getElementById('detallesContenido').cloneNode(true);
    
    // 2. Crear contenedor principal para el PDF
    const pdfContainer = document.createElement('div');
    pdfContainer.className = 'pdf-container';
    
    // Agregar header del PDF
    const header = document.createElement('div');
    header.className = 'pdf-header';
    header.innerHTML = `
        <h1>DevolutionSync - Detalles de Devolución</h1>
        <p>ID: #${currentDevolucionId} | Generado: ${new Date().toLocaleString()}</p>
    `;
    pdfContainer.appendChild(header);
    
    // 3. Obtener el contenedor interno que tiene las restricciones de altura y scroll
    const content = modalContent.cloneNode(true);
    const detallesContainer = content.querySelector('.detalles-container');

    // *** FIX CLAVE: ELIMINAR RESTRICCIONES DE ALTURA Y SCROLL PARA QUE html2canvas CAPTURE TODO ***
    if (detallesContainer) {
        detallesContainer.style.maxHeight = 'none';
        detallesContainer.style.overflowY = 'visible'; // O 'hidden'
        detallesContainer.style.paddingRight = '0'; // Quitar el padding extra de la scrollbar
    }
    
    // Aplicar estilos específicos para PDF (esto ya lo tenías, y está bien)
    const badges = content.querySelectorAll('.badge');
    badges.forEach(badge => {
        // Aseguramos que se mantengan las clases de color
        badge.classList.forEach(className => {
            if (className.startsWith('badge-')) {
                badge.classList.add('pdf-' + className);
            }
        });
        badge.classList.add('pdf-badge');
    });
    
    const sections = content.querySelectorAll('.detalle-item, .seccion-detalles'); // Asegurar .seccion-detalles
    sections.forEach(section => {
        section.classList.add('pdf-section');
    });
    
    pdfContainer.appendChild(content);
    
    // 4. Configuración de html2pdf
    const opt = {
        margin: 10,
        filename: `devolucion_${currentDevolucionId}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2,
            useCORS: true, // Crucial para imágenes de rutas relativas como 'uploads/'
            logging: true
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    
    // Generar PDF
    html2pdf().set(opt).from(pdfContainer).save();
}
        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function abrirImagenCompleta(rutaImagen) {
            window.open(rutaImagen, '_blank');
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>