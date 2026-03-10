<?php 
$titulo = "Registro de Devolución - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Adaptar Select2 al estilo del proyecto */
    .select2-container--default .select2-selection--single {
        height: 46px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    .select2-container--default .select2-selection--single:hover {
        border-color: #ff8c00;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #ff8c00;
        box-shadow: 0 0 0 3px rgba(255,140,0,0.1);
        background: white;
        outline: none;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 15px;
        color: #333;
        font-size: 14px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
        right: 10px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #ff8c00;
    }
    .select2-dropdown {
        border: 2px solid #ff8c00;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 14px;
    }
    .select2-container { width: 100% !important; }
</style>

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
                        <input type="text" id="nit" name="nit" class="form-control" 
                               placeholder="Ej: 900123456-7" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre_cliente">
                            <i class="fas fa-building"></i> Nombre Cliente *
                        </label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" 
                               placeholder="Nombre completo del cliente" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="direccion">
                            <i class="fas fa-map-marker-alt"></i> Dirección *
                        </label>
                        <input type="text" id="direccion" name="direccion" class="form-control" 
                               placeholder="Dirección completa de entrega" required>
                    </div>

                    <!-- Campo Correo -->
                    <div class="form-group full-width">
                        <label for="correo_solicitante">
                            <i class="fas fa-envelope"></i> Correo Electrónico
                            <small style="font-weight:400; color:#6c757d;">(para recibir notificaciones del estado)</small>
                        </label>
                        <input type="email" 
                               id="correo_solicitante" 
                               name="correo_solicitante" 
                               class="form-control" 
                               placeholder="ejemplo@correo.com">
                        <small class="form-text">Si ingresa un correo, recibirá una notificación cuando la devolución sea revisada.</small>
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
                        <!-- Select2 reemplaza al select normal -->
                        <select id="producto" name="producto" class="form-control select2-producto" required>
                            <option value="">-- Busque o seleccione un producto --</option>
                            <?php foreach($productos as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['item']); ?>" 
                                        data-descripcion="<?php echo htmlspecialchars($p['descripcion']); ?>"
                                        data-unidad="<?php echo htmlspecialchars($p['unidad'] ?? 'UND'); ?>"
                                        data-kg="<?php echo htmlspecialchars($p['kg'] ?? '0'); ?>">
                                    <?php echo htmlspecialchars($p['item']) . ' - ' . htmlspecialchars($p['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="item_producto">
                            <i class="fas fa-tag"></i> Item Producto
                        </label>
                        <input type="text" id="item_producto" name="item_producto" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_producto">
                            <i class="fas fa-align-left"></i> Descripción
                        </label>
                        <input type="text" id="descripcion_producto" name="descripcion_producto" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="unidad">
                            <i class="fas fa-cubes"></i> Unidad
                        </label>
                        <input type="text" id="unidad" name="unidad" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="kg">
                            <i class="fas fa-weight"></i> KG por Unidad
                        </label>
                        <input type="text" id="kg" name="kg" class="form-control" readonly>
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
                        <input type="number" id="cantidad_und" name="cantidad_und" class="form-control" 
                               placeholder="0" min="0" step="1" onchange="calcularKgTotal()" required>
                    </div>

                    <div class="form-group">
                        <label for="cantidad_kg">
                            <i class="fas fa-weight-hanging"></i> Cantidad (Kilogramos) *
                        </label>
                        <input type="number" id="cantidad_kg" name="cantidad_kg" class="form-control" 
                               placeholder="0.00" min="0" step="0.01" required>
                        <small class="form-text">Se calculará automáticamente según las unidades</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="observacion">
                            <i class="fas fa-comment-alt"></i> Observaciones *
                        </label>
                        <textarea id="observacion" name="observacion" class="form-control" rows="4" 
                                  placeholder="Describe los detalles de la devolución, estado del producto, etc."
                                  required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="evidencia">
                            <i class="fas fa-camera"></i> Evidencia Fotográfica (Opcional)
                        </label>
                        <input type="file" id="evidencia" name="evidencia" 
                               class="form-control-file" accept="image/*">
                        <small class="form-text">Formatos permitidos: JPG, PNG, GIF (Máx. 5MB)</small>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Devolución
                </button>
                <button type="reset" class="btn btn-secondary" onclick="limpiarSelect2()">
                    <i class="fas fa-eraser"></i> Limpiar Formulario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery + Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2-producto').select2({
        placeholder: '🔍 Busque o seleccione un producto...',
        allowClear: true,
        language: {
            noResults: function() { return "No se encontraron productos"; },
            searching: function() { return "Buscando..."; }
        }
    });

    // Evento de cambio en Select2
    $('.select2-producto').on('change', function() {
        cargarInfoProducto();
    });
});

function cargarInfoProducto() {
    const select = document.getElementById('producto');
    const option = select.options[select.selectedIndex];

    if (option && option.value) {
        document.getElementById('item_producto').value      = option.value;
        document.getElementById('descripcion_producto').value = option.dataset.descripcion || '';
        document.getElementById('unidad').value             = option.dataset.unidad || 'UND';
        document.getElementById('kg').value                 = option.dataset.kg || '0';
        calcularKgTotal();
    } else {
        document.getElementById('item_producto').value      = '';
        document.getElementById('descripcion_producto').value = '';
        document.getElementById('unidad').value             = '';
        document.getElementById('kg').value                 = '';
        document.getElementById('cantidad_kg').value        = '';
    }
}

function calcularKgTotal() {
    const cantidad_und = parseFloat(document.getElementById('cantidad_und').value) || 0;
    const kg_unitario  = parseFloat(document.getElementById('kg').value) || 0;
    document.getElementById('cantidad_kg').value = (cantidad_und * kg_unitario).toFixed(2);
}

function limpiarSelect2() {
    // Limpiar Select2 al resetear el formulario
    setTimeout(() => {
        $('.select2-producto').val(null).trigger('change');
        document.getElementById('item_producto').value      = '';
        document.getElementById('descripcion_producto').value = '';
        document.getElementById('unidad').value             = '';
        document.getElementById('kg').value                 = '';
        document.getElementById('cantidad_kg').value        = '';
    }, 10);
}

// Validar antes de enviar
document.getElementById('formDevolucion').addEventListener('submit', function(e) {
    const producto    = document.getElementById('producto').value;
    const motivo      = document.getElementById('motivo').value;
    const cantidad    = document.getElementById('cantidad_und').value;
    const correo      = document.getElementById('correo_solicitante').value;

    if (!producto || !motivo || !cantidad || cantidad <= 0) {
        e.preventDefault();
        alert('⚠️ Por favor complete todos los campos obligatorios correctamente');
        return false;
    }

    // Validar formato de correo si fue ingresado
    if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        e.preventDefault();
        alert('⚠️ El formato del correo electrónico no es válido');
        return false;
    }
});
</script>

<?php include 'Views/layouts/footer.php'; ?>