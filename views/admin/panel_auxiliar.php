<?php 
$titulo = "Registro de Devolución - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<div class="panel-container">
    <div class="panel-header">
        <h1>📦 Gestión de Devoluciones</h1>
        <p class="subtitle">Registra nuevas devoluciones, faltantes o sobrantes de productos</p>
    </div>

    <?php if(isset($_SESSION['alerta'])): ?>
        <div class="alert alert-<?php echo $_SESSION['alerta']['tipo']; ?>">
            <strong><?php echo ($_SESSION['alerta']['tipo'] == 'success') ? '✅ Éxito:' : '❌ Error:'; ?></strong>
            <?php echo $_SESSION['alerta']['msg']; unset($_SESSION['alerta']); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h2>📝 Nueva Devolución</h2>
        </div>
        
        <form action="index.php?url=panel/registrar" method="POST" enctype="multipart/form-data" id="formDevolucion">
            
            <!-- Sección: Información del Cliente -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user"></i> Información del Cliente
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nit">
                            <i class="fas fa-id-card"></i> NIT Cliente *
                        </label>
                        <input type="text" 
                               id="nit" 
                               name="nit" 
                               class="form-control" 
                               placeholder="Ej: 900123456-7" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre_cliente">
                            <i class="fas fa-building"></i> Nombre Cliente *
                        </label>
                        <input type="text" 
                               id="nombre_cliente" 
                               name="nombre_cliente" 
                               class="form-control" 
                               placeholder="Nombre completo del cliente"
                               required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="direccion">
                            <i class="fas fa-map-marker-alt"></i> Dirección *
                        </label>
                        <input type="text" 
                               id="direccion" 
                               name="direccion" 
                               class="form-control" 
                               placeholder="Dirección completa de entrega"
                               required>
                    </div>
                </div>
            </div>

            <!-- Sección: Información del Producto -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-box"></i> Información del Producto
                </div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="producto">
                            <i class="fas fa-barcode"></i> Producto *
                        </label>
                        <select id="producto" 
                                name="producto" 
                                class="form-control" 
                                required 
                                onchange="cargarInfoProducto()">
                            <option value="">-- Seleccione un producto --</option>
                            <?php foreach($productos as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['item']); ?>" 
                                        data-descripcion="<?php echo htmlspecialchars($p['descripcion']); ?>"
                                        data-unidad="<?php echo htmlspecialchars($p['unidad'] ?? 'UND'); ?>"
                                        data-kg="<?php echo htmlspecialchars($p['kg'] ?? '0'); ?>">
                                    <?php echo htmlspecialchars($p['item']) . " - " . htmlspecialchars($p['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="item_producto">
                            <i class="fas fa-tag"></i> Item Producto
                        </label>
                        <input type="text" 
                               id="item_producto" 
                               name="item_producto" 
                               class="form-control" 
                               readonly>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_producto">
                            <i class="fas fa-align-left"></i> Descripción
                        </label>
                        <input type="text" 
                               id="descripcion_producto" 
                               name="descripcion_producto" 
                               class="form-control" 
                               readonly>
                    </div>

                    <div class="form-group">
                        <label for="unidad">
                            <i class="fas fa-cubes"></i> Unidad
                        </label>
                        <input type="text" 
                               id="unidad" 
                               name="unidad" 
                               class="form-control" 
                               readonly>
                    </div>

                    <div class="form-group">
                        <label for="kg">
                            <i class="fas fa-weight"></i> KG por Unidad
                        </label>
                        <input type="text" 
                               id="kg" 
                               name="kg" 
                               class="form-control" 
                               readonly>
                    </div>
                </div>
            </div>

            <!-- Sección: Detalles de la Devolución -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-exclamation-triangle"></i> Detalles de la Devolución
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="motivo">
                            <i class="fas fa-question-circle"></i> Motivo *
                        </label>
                        <select id="motivo" name="motivo" class="form-control" required>
                            <option value="">-- Seleccione un motivo --</option>
                            <option value="devolucion">🔄 Devolución</option>
                            <option value="faltante">❌ Faltante</option>
                            <option value="sobrante">➕ Sobrante</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cantidad_und">
                            <i class="fas fa-sort-numeric-up"></i> Cantidad (Unidades) *
                        </label>
                        <input type="number" 
                               id="cantidad_und" 
                               name="cantidad_und" 
                               class="form-control" 
                               placeholder="0" 
                               min="0"
                               step="1"
                               onchange="calcularKgTotal()"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="cantidad_kg">
                            <i class="fas fa-weight-hanging"></i> Cantidad (Kilogramos) *
                        </label>
                        <input type="number" 
                               id="cantidad_kg" 
                               name="cantidad_kg" 
                               class="form-control" 
                               placeholder="0.00" 
                               min="0"
                               step="0.01"
                               required>
                        <small class="form-text">Se calculará automáticamente según las unidades</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="observacion">
                            <i class="fas fa-comment-alt"></i> Observaciones *
                        </label>
                        <textarea id="observacion" 
                                  name="observacion" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Describe los detalles de la devolución, estado del producto, etc."
                                  required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="evidencia">
                            <i class="fas fa-camera"></i> Evidencia Fotográfica (Opcional)
                        </label>
                        <input type="file" 
                               id="evidencia" 
                               name="evidencia" 
                               class="form-control-file"
                               accept="image/*">
                        <small class="form-text">Formatos permitidos: JPG, PNG, GIF (Máx. 5MB)</small>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Devolución
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-eraser"></i> Limpiar Formulario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Cargar información del producto seleccionado
function cargarInfoProducto() {
    const select = document.getElementById('producto');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('item_producto').value = option.value;
        document.getElementById('descripcion_producto').value = option.dataset.descripcion || '';
        document.getElementById('unidad').value = option.dataset.unidad || 'UND';
        document.getElementById('kg').value = option.dataset.kg || '0';
    } else {
        document.getElementById('item_producto').value = '';
        document.getElementById('descripcion_producto').value = '';
        document.getElementById('unidad').value = '';
        document.getElementById('kg').value = '';
    }
}

// Calcular kilogramos totales basado en unidades
function calcularKgTotal() {
    const cantidad_und = parseFloat(document.getElementById('cantidad_und').value) || 0;
    const kg_unitario = parseFloat(document.getElementById('kg').value) || 0;
    const total_kg = cantidad_und * kg_unitario;
    
    document.getElementById('cantidad_kg').value = total_kg.toFixed(2);
}

// Validar formulario antes de enviar
document.getElementById('formDevolucion').addEventListener('submit', function(e) {
    const producto = document.getElementById('producto').value;
    const motivo = document.getElementById('motivo').value;
    const cantidad_und = document.getElementById('cantidad_und').value;
    
    if (!producto || !motivo || !cantidad_und || cantidad_und <= 0) {
        e.preventDefault();
        alert('⚠️ Por favor complete todos los campos obligatorios correctamente');
        return false;
    }
});
</script>

<?php include 'Views/layouts/footer.php'; ?>