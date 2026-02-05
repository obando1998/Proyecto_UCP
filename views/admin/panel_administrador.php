<?php include 'Views/layouts/header.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?php echo ($_GET['msg'] == 'success') ? 'success' : 'error'; ?>">
        <?php echo ($_GET['msg'] == 'success') ? '✅ Revisión procesada correctamente' : '❌ Error al procesar'; ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2 style="border-bottom-color: #dc3545;">⏳ Pendientes de Revisión (<?php echo count($pendientes); ?>)</h2>
    <?php if (empty($pendientes)): ?>
        <p style="text-align: center; color: #666; padding: 20px;">✅ ¡Todo al día! No hay pendientes.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                        <th>Creador</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendientes as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($p['codigo_producto']); ?></td>
                        <td><span class="badge"><?php echo $p['motivo']; ?></span></td>
                        <td><?php echo date('d/m H:i', strtotime($p['fecha_creacion'])); ?></td>
                        <td><?php echo $p['usuario_creador']; ?></td>
                        <td>
                            <button class="btn" style="background: #28a745;" onclick="abrirRevision(<?php echo $p['id']; ?>)">
                                🔍 Revisar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>📚 Historial Reciente</h2>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Cliente</th>
                    <th>Revisor</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td>#<?php echo $h['id']; ?></td>
                    <td><span class="badge"><?php echo $h['estado']; ?></span></td>
                    <td><?php echo htmlspecialchars($h['nombre_cliente']); ?></td>
                    <td><?php echo $h['usuario_revisor'] ?: '-'; ?></td>
                    <td>
                        <button class="btn btn-secondary" onclick="verSoloDetalles(<?php echo $h['id']; ?>)">👁️ Ver</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="revisionModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div class="modal-content" style="background:white; width:90%; max-width:800px; margin:2% auto; padding:20px; border-radius:8px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h2 style="color:#ff8c00;">🔍 Revisar Devolución</h2>
            <span onclick="cerrarModal()" style="cursor:pointer; font-size:24px;">&times;</span>
        </div>
        
        <div id="infoDevolucion">Cargando...</div>

        <hr>
        
        <form action="index.php?url=admin/revisar" method="POST" style="background:#f9f9f9; padding:20px; border-radius:8px;">
            <input type="hidden" name="id_devolucion" id="idDevolucionInput">
            
            <div class="form-group">
                <label>Acción:</label>
                <select name="accion" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="aprobado">✅ APROBAR</option>
                    <option value="rechazado">❌ RECHAZAR</option>
                </select>
            </div>
            <div class="form-group">
                <label>Código Admin:</label>
                <input type="text" name="codigo_admin" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Observación:</label>
                <textarea name="observacion_admin" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn" style="width:100%;">Enviar Revisión</button>
        </form>
    </div>
</div>

<script>
function abrirRevision(id) {
    document.getElementById('revisionModal').style.display = 'block';
    document.getElementById('idDevolucionInput').value = id;
    document.getElementById('infoDevolucion').innerHTML = 'Cargando datos...';

    // REUTILIZAMOS la ruta de consulta que ya creamos para traer el HTML de detalles
    fetch(`index.php?url=consulta/detalles&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('infoDevolucion').innerHTML = data.html;
            } else {
                alert('Error al cargar datos');
            }
        });
}

function cerrarModal() {
    document.getElementById('revisionModal').style.display = 'none';
}
</script>

<?php include 'Views/layouts/footer.php'; ?>