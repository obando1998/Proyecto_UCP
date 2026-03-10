<?php 
$titulo = "Historial de Devoluciones - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="admin-container">

    <!-- Header -->
    <div class="admin-header">
        <div class="admin-header-content">
            <h1><i class="fas fa-history"></i> Historial de Devoluciones</h1>
            <p class="subtitle">Consulta y seguimiento de todas las devoluciones registradas</p>
        </div>
        <div class="admin-stats">
            <div class="stat-badge stat-recent">
                <span class="stat-number"><?php echo count($devoluciones); ?></span>
                <span class="stat-label">Total Registros</span>
            </div>
            <div class="stat-badge stat-pending">
                <span class="stat-number">
                    <?php echo count(array_filter($devoluciones, fn($d) => strtolower($d['estado']) === 'pendiente')); ?>
                </span>
                <span class="stat-label">Pendientes</span>
            </div>
        </div>
    </div>

    <!-- Tabla principal -->
    <div class="admin-card history-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-table"></i>
                <h2>Registros de Devoluciones</h2>
            </div>
            <div class="card-actions">
                <button class="btn-filter" onclick="filtrarTabla()">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <button class="btn-export" onclick="exportarCSV()">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </button>
            </div>
        </div>

        <!-- Barra de búsqueda rápida -->
        <div style="padding: 20px 30px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" 
                           id="buscador" 
                           class="form-control" 
                           placeholder="🔍 Buscar por cliente, NIT, motivo..."
                           oninput="buscarEnTabla()"
                           style="margin:0;">
                </div>
                <div>
                    <select id="filtroEstado" class="form-control" onchange="buscarEnTabla()" style="margin:0; min-width:150px;">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="admin-table" id="tablaHistorial">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>NIT</th>
                            <th>Cliente</th>
                            <th>Motivo</th>
                            <th>Cant. UND</th>
                            <th>Cant. KG</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        <?php if(empty($devoluciones)): ?>
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>Sin registros</h3>
                                        <p>No hay devoluciones registradas aún.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($devoluciones as $d): ?>
                            <tr>
                                <td>
                                    <span class="id-badge">#<?php echo $d['id']; ?></span>
                                </td>
                                <td>
                                    <div class="fecha-info">
                                        <strong><?php echo date('d/m/Y', strtotime($d['fecha_creacion'])); ?></strong>
                                        <small><?php echo date('H:i', strtotime($d['fecha_creacion'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <small style="color:#495057; font-weight:600;"><?php echo htmlspecialchars($d['nit'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <div class="cliente-info">
                                        <strong><?php echo htmlspecialchars($d['nombre_cliente'] ?? 'N/A'); ?></strong>
                                        <small><?php echo htmlspecialchars($d['direccion'] ?? ''); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $motivo = strtolower($d['motivo'] ?? '');
                                    $motivoClase = match($motivo) {
                                        'devolucion' => 'motivo-devolucion',
                                        'faltante'   => 'motivo-faltante',
                                        'sobrante'   => 'motivo-sobrante',
                                        default      => 'motivo-devolucion'
                                    };
                                    $motivoIcono = match($motivo) {
                                        'devolucion' => '🔄',
                                        'faltante'   => '❌',
                                        'sobrante'   => '➕',
                                        default      => '📦'
                                    };
                                    ?>
                                    <span class="motivo-badge <?php echo $motivoClase; ?>">
                                        <?php echo $motivoIcono . ' ' . ucfirst($d['motivo'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="cantidad-info">
                                        <strong><?php echo number_format($d['cantidad_und'] ?? 0, 0); ?></strong>
                                        <small>unidades</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="cantidad-info">
                                        <strong><?php echo number_format($d['cantidad_kg'] ?? 0, 2); ?></strong>
                                        <small>kg</small>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $estado = strtolower($d['estado'] ?? 'pendiente');
                                    $estadoIcono = match($estado) {
                                        'aprobado'  => '✅',
                                        'rechazado' => '❌',
                                        default     => '⏳'
                                    };
                                    ?>
                                    <span class="estado-badge estado-<?php echo $estado; ?>">
                                        <?php echo $estadoIcono . ' ' . ucfirst($d['estado'] ?? 'Pendiente'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="usuario-badge">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($d['usuario_creador'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-view" onclick="verDetalles(<?php echo $d['id']; ?>)">
                                        <i class="fas fa-eye"></i> Detalles
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div id="detallesModal" class="modal" style="display:none; align-items:center; justify-content:center;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-file-alt"></i> <span id="modalTitulo">Detalles de la Devolución</span></h2>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <div class="modal-body" id="contenidoDetalle">
                <div class="loading-state">
                    <div class="loader"></div>
                    <p>Cargando información...</p>
                </div>
            </div>
            <div class="modal-footer" id="modalFooter" style="display:none;">
                <button class="btn btn-success" onclick="generarPDF()">
                    <i class="fas fa-file-pdf"></i> Descargar PDF
                </button>
                <button class="btn btn-secondary" onclick="cerrarModal()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentId = null;

// ── Buscar / filtrar en tabla ──────────────────────────────
function buscarEnTabla() {
    const texto  = document.getElementById('buscador').value.toLowerCase();
    const estado = document.getElementById('filtroEstado').value.toLowerCase();
    const filas  = document.querySelectorAll('#cuerpoTabla tr');

    filas.forEach(fila => {
        const contenido = fila.textContent.toLowerCase();
        const coincideTexto  = texto  === '' || contenido.includes(texto);
        const coincideEstado = estado === '' || contenido.includes(estado);
        fila.style.display = (coincideTexto && coincideEstado) ? '' : 'none';
    });
}

function filtrarTabla() {
    document.getElementById('buscador').focus();
}

// ── Exportar CSV ───────────────────────────────────────────
function exportarCSV() {
    const filas   = document.querySelectorAll('#tablaHistorial tr');
    let csvContent = '';

    filas.forEach(fila => {
        if (fila.style.display === 'none') return;
        const celdas = Array.from(fila.querySelectorAll('th, td'));
        const fila_csv = celdas.map(c => `"${c.textContent.trim().replace(/\s+/g,' ')}"`).join(',');
        csvContent += fila_csv + '\n';
    });

    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `historial_devoluciones_${new Date().toISOString().slice(0,10)}.csv`);
    link.click();
}

// ── Modal de detalles ──────────────────────────────────────
function verDetalles(id) {
    currentId = id;
    const modal     = document.getElementById('detallesModal');
    const contenido = document.getElementById('contenidoDetalle');
    const footer    = document.getElementById('modalFooter');

    modal.style.display = 'flex';
    contenido.innerHTML = `
        <div class="loading-state">
            <div class="loader"></div>
            <p>Cargando detalles del registro #${id}...</p>
        </div>`;
    footer.style.display = 'none';

    fetch(`index.php?url=consulta/detalles&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                contenido.innerHTML = data.html;
                footer.style.display = 'flex';
                document.getElementById('modalTitulo').innerText = `Devolución #${id}`;
            } else {
                contenido.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>${data.message}</p>
                    </div>`;
            }
        })
        .catch(() => {
            contenido.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error técnico al recuperar los datos.</p>
                </div>`;
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

// Cerrar modal al hacer clic fuera
window.onclick = function(e) {
    const modal = document.getElementById('detallesModal');
    if (e.target === modal) cerrarModal();
};
</script>

<?php include 'Views/layouts/footer.php'; ?>