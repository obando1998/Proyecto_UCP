<?php 
$titulo = "Panel Administrador - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<div class="admin-container">
    <!-- Header del Panel -->
    <div class="admin-header">
        <div class="admin-header-content">
            <h1>🔍 Panel de Administración</h1>
            <p class="subtitle">Revisa y gestiona las devoluciones pendientes</p>
        </div>
        <div class="admin-stats">
            <div class="stat-badge stat-pending">
                <span class="stat-number"><?php echo count($pendientes); ?></span>
                <span class="stat-label">Pendientes</span>
            </div>
            <div class="stat-badge stat-recent">
                <span class="stat-number"><?php echo count($historial); ?></span>
                <span class="stat-label">Recientes</span>
            </div>
        </div>
    </div>

    <!-- Mensajes de Alerta -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?php echo ($_GET['msg'] == 'success') ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo ($_GET['msg'] == 'success') ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <strong><?php echo ($_GET['msg'] == 'success') ? '✅ Éxito:' : '❌ Error:'; ?></strong>
            <?php echo ($_GET['msg'] == 'success') ? 'Revisión procesada correctamente' : 'Error al procesar la revisión'; ?>
        </div>
    <?php endif; ?>

    <!-- Sección: Pendientes de Revisión -->
    <div class="admin-card pending-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-clock"></i>
                <h2>Pendientes de Revisión</h2>
                <span class="badge badge-warning"><?php echo count($pendientes); ?></span>
            </div>
            <?php if (!empty($pendientes)): ?>
                <div class="card-actions">
                    <button class="btn-filter" onclick="filtrarTabla('pendientes')">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <?php if (empty($pendientes)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>¡Todo al día!</h3>
                    <p>No hay devoluciones pendientes por revisar</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table" id="tablaPendientes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Motivo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Creado por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendientes as $p): ?>
                            <tr data-id="<?php echo $p['id']; ?>">
                                <td>
                                    <strong class="id-badge">#<?php echo $p['id']; ?></strong>
                                </td>
                                <td>
                                    <div class="cliente-info">
                                        <strong><?php echo htmlspecialchars($p['nombre_cliente']); ?></strong>
                                        <small>NIT: <?php echo htmlspecialchars($p['nit'] ?? 'N/A'); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="producto-info">
                                        <strong><?php echo htmlspecialchars($p['item_producto'] ?? $p['codigo_producto'] ?? 'N/A'); ?></strong>
                                        <small><?php echo htmlspecialchars($p['descripcion_producto'] ?? ''); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="motivo-badge motivo-<?php echo strtolower($p['motivo']); ?>">
                                        <?php 
                                        $iconos = [
                                            'devolucion' => '🔄',
                                            'faltante' => '❌',
                                            'sobrante' => '➕'
                                        ];
                                        echo ($iconos[strtolower($p['motivo'])] ?? '📦') . ' ' . ucfirst($p['motivo']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="cantidad-info">
                                        <span><?php echo $p['cantidad_und'] ?? 0; ?> UND</span>
                                        <small><?php echo number_format($p['cantidad_kg'] ?? 0, 2); ?> KG</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fecha-info">
                                        <strong><?php echo date('d/m/Y', strtotime($p['fecha_creacion'])); ?></strong>
                                        <small><?php echo date('H:i', strtotime($p['fecha_creacion'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="usuario-badge">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($p['usuario_creador']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-review" onclick="abrirRevision(<?php echo $p['id']; ?>)">
                                        <i class="fas fa-search"></i> Revisar
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

    <!-- Sección: Historial Reciente -->
    <div class="admin-card history-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-history"></i>
                <h2>Historial Reciente</h2>
                <span class="badge badge-info"><?php echo count($historial); ?></span>
            </div>
            <div class="card-actions">
                <button class="btn-export" onclick="exportarHistorial()">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>

        <div class="card-body">
            <?php if (empty($historial)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No hay historial</h3>
                    <p>Aún no hay devoluciones revisadas</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table" id="tablaHistorial">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Producto</th>
                                <th>Fecha Revisión</th>
                                <th>Revisado por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                            <tr>
                                <td><strong class="id-badge">#<?php echo $h['id']; ?></strong></td>
                                <td>
                                    <span class="estado-badge estado-<?php echo strtolower($h['estado']); ?>">
                                        <?php 
                                        $iconosEstado = [
                                            'aprobado' => '✅',
                                            'rechazado' => '❌',
                                            'pendiente' => '⏳'
                                        ];
                                        echo ($iconosEstado[strtolower($h['estado'])] ?? '📋') . ' ' . ucfirst($h['estado']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($h['nombre_cliente']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($h['item_producto'] ?? $h['codigo_producto'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($h['fecha_revision'])) {
                                        echo date('d/m/Y H:i', strtotime($h['fecha_revision']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="usuario-badge">
                                        <?php echo $h['usuario_revisor'] ? htmlspecialchars($h['usuario_revisor']) : '-'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-view" onclick="verSoloDetalles(<?php echo $h['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver
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
</div>

<!-- Modal: Revisar Devolución -->
<div id="revisionModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-search-plus"></i> Revisar Devolución</h2>
                <button class="modal-close" onclick="cerrarModal('revisionModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="infoDevolucion" class="loading-state">
                    <div class="loader"></div>
                    <p>Cargando información...</p>
                </div>

                <hr class="modal-divider">
                
                <div class="revision-form">
                    <h3><i class="fas fa-edit"></i> Formulario de Revisión</h3>
                    <form action="index.php?url=admin/revisar" method="POST" id="formRevision">
                        <input type="hidden" name="id_devolucion" id="idDevolucionInput">
                        
                        <div class="form-group">
                            <label for="accion">
                                <i class="fas fa-check-double"></i> Decisión *
                            </label>
                            <select name="accion" id="accion" class="form-control" required>
                                <option value="">-- Seleccione una acción --</option>
                                <option value="aprobado">✅ APROBAR Devolución</option>
                                <option value="rechazado">❌ RECHAZAR Devolución</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="codigo_admin">
                                <i class="fas fa-key"></i> Código de Autorización *
                            </label>
                            <input type="text" 
                                   id="codigo_admin" 
                                   name="codigo_admin" 
                                   class="form-control" 
                                   placeholder="Ingrese el código de autorización"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacion_admin">
                                <i class="fas fa-comment-alt"></i> Observaciones del Administrador *
                            </label>
                            <textarea id="observacion_admin" 
                                      name="observacion_admin" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Ingrese sus observaciones sobre esta devolución"
                                      required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Enviar Revisión
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="cerrarModal('revisionModal')">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver Detalles (Solo lectura) -->
<div id="detallesModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Detalles de la Devolución</h2>
                <button class="modal-close" onclick="cerrarModal('detallesModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div id="detallesContenido" class="loading-state">
                    <div class="loader"></div>
                    <p>Cargando detalles...</p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModal('detallesModal')">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para abrir modal de revisión
function abrirRevision(id) {
    const modal = document.getElementById('revisionModal');
    modal.style.display = 'flex';
    document.getElementById('idDevolucionInput').value = id;
    document.getElementById('infoDevolucion').innerHTML = `
        <div class="loading-state">
            <div class="loader"></div>
            <p>Cargando información...</p>
        </div>
    `;

    // Cargar detalles de la devolución
    fetch(`index.php?url=consulta/detalles&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('infoDevolucion').innerHTML = data.html;
            } else {
                document.getElementById('infoDevolucion').innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar los detalles</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('infoDevolucion').innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Error de conexión</p>
                </div>
            `;
        });
}

// Función para ver detalles (solo lectura)
function verSoloDetalles(id) {
    const modal = document.getElementById('detallesModal');
    modal.style.display = 'flex';
    document.getElementById('detallesContenido').innerHTML = `
        <div class="loading-state">
            <div class="loader"></div>
            <p>Cargando detalles...</p>
        </div>
    `;

    fetch(`index.php?url=consulta/detalles&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('detallesContenido').innerHTML = data.html;
            } else {
                document.getElementById('detallesContenido').innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Error al cargar los detalles</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Función para cerrar modales
function cerrarModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    
    // Limpiar formulario si es el modal de revisión
    if (modalId === 'revisionModal') {
        document.getElementById('formRevision').reset();
    }
}

// Validar formulario antes de enviar
document.getElementById('formRevision').addEventListener('submit', function(e) {
    const accion = document.getElementById('accion').value;
    const codigo = document.getElementById('codigo_admin').value;
    const obs = document.getElementById('observacion_admin').value;
    
    if (!accion || !codigo || !obs) {
        e.preventDefault();
        alert('⚠️ Por favor complete todos los campos obligatorios');
        return false;
    }
    
    const confirmar = confirm(
        `¿Está seguro de ${accion === 'aprobado' ? 'APROBAR' : 'RECHAZAR'} esta devolución?\n\n` +
        `Esta acción no se puede deshacer.`
    );
    
    if (!confirmar) {
        e.preventDefault();
        return false;
    }
});

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Función para filtrar tabla (implementación básica)
function filtrarTabla(tabla) {
    alert('Función de filtrado en desarrollo');
}

// Función para exportar historial
function exportarHistorial() {
    alert('Función de exportación en desarrollo');
}
</script>

<?php include 'Views/layouts/footer.php'; ?>