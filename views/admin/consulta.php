<?php 
$titulo = "Historial de Devoluciones - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<style>
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(3px); }
    .modal-content { background: white; margin: 5% auto; width: 80%; max-width: 900px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); overflow: hidden; animation: slideIn 0.3s ease; }
    .modal-header { background: #ff8c00; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 20px; max-height: 75vh; overflow-y: auto; }
    .close { color: white; font-size: 28px; font-weight: bold; cursor: pointer; }
    
    @keyframes slideIn {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .badge-estado { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
    .estado-pendiente { background: #fff3cd; color: #856404; }
    .estado-completado { background: #d4edda; color: #155724; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>📊 Historial de Devoluciones</h2>
        <span class="badge" style="background: #6c757d; color: white;">Total: <?php echo count($devoluciones); ?></span>
    </div>

    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Factura</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($devoluciones)): ?>
                    <tr><td colspan="6" style="text-align: center;">No hay registros encontrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($devoluciones as $d): ?>
                    <tr>
                        <td><strong>#<?php echo $d['id']; ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($d['fecha_creacion'])); ?></td>
                        <td><?php echo htmlspecialchars($d['cliente']); ?></td>
                        <td><?php echo htmlspecialchars($d['factura'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge-estado estado-<?php echo strtolower($d['estado'] ?? 'pendiente'); ?>">
                                <?php echo $d['estado'] ?? 'Pendiente'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn" onclick="verDetalles(<?php echo $d['id']; ?>)">👁️ Ver Detalles</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="detallesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Detalles de la Devolución</h3>
            <span class="close" onclick="cerrarModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="contenidoDetalle">
                <div style="text-align: center; padding: 20px;">
                    <p>Cargando información...</p>
                </div>
            </div>
            <div id="modalFooter" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; display: none; justify-content: flex-end; gap: 10px;">
                <button class="btn" style="background: #28a745;" onclick="generarPDF()">📥 Descargar PDF</button>
                <button class="btn" style="background: #6c757d;" onclick="cerrarModal()">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentId = null;

function verDetalles(id) {
    currentId = id;
    const modal = document.getElementById('detallesModal');
    const contenido = document.getElementById('contenidoDetalle');
    const footer = document.getElementById('modalFooter');
    
    modal.style.display = 'block';
    contenido.innerHTML = '<div style="text-align:center"><p>⌛ Cargando detalles del registro #' + id + '...</p></div>';
    footer.style.display = 'none';

    // Llamada al método detalles() del ConsultaController
    fetch(`index.php?url=consulta/detalles&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                contenido.innerHTML = data.html;
                footer.style.display = 'flex';
                document.getElementById('modalTitulo').innerText = `Detalles de Devolución #${id}`;
            } else {
                contenido.innerHTML = `<div class="alert alert-error">❌ ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            contenido.innerHTML = '<div class="alert alert-error">❌ Error técnico al recuperar los datos.</div>';
        });
}

function cerrarModal() {
    document.getElementById('detallesModal').style.display = 'none';
}

function generarPDF() {
    const element = document.getElementById('contenidoDetalle');
    const opt = {
        margin: 10,
        filename: `Devolucion_${currentId}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}

// Cerrar si hace clic fuera del contenido blanco
window.onclick = function(event) {
    const modal = document.getElementById('detallesModal');
    if (event.target == modal) cerrarModal();
}
</script>

<?php include 'Views/layouts/footer.php'; ?>