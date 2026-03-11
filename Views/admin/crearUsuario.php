<?php 
$titulo = "Crear Usuario - DevolutionSync";
include 'Views/layouts/header.php'; 
?>

<div class="admin-container">

    <!-- Header -->
    <div class="admin-header">
        <div class="admin-header-content">
            <h1><i class="fas fa-user-cog"></i> Gestión de Usuarios</h1>
            <p class="subtitle">Crea y administra los usuarios del sistema</p>
        </div>
        <div class="admin-stats">
            <div class="stat-badge stat-recent">
                <span class="stat-number"><?php echo count($usuarios); ?></span>
                <span class="stat-label">Usuarios Activos</span>
            </div>
        </div>
    </div>

    <!-- Alerta de resultado -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipoMensaje === 'success' ? 'success' : 'error'; ?>" 
             style="display:flex; align-items:center; gap:10px; margin-bottom:25px;">
            <?php echo ($tipoMensaje === 'success') ? '✅' : '⚠️'; ?>
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 25px; align-items: start;">

        <!-- ── Formulario Crear Usuario ── -->
        <div class="admin-card" style="overflow:hidden;">
            <div class="card-header pending-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-title">
                    <i class="fas fa-user-plus"></i>
                    <h2>Nuevo Usuario</h2>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?url=usuario/crear" id="formCrearUsuario">
                    
                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="font-size:14px; font-weight:600; color:#495057; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-id-badge" style="color:#667eea;"></i> Usuario (USR)
                        </label>
                        <input type="text" 
                               name="usr" 
                               class="form-control" 
                               placeholder="Ej: JPEREZ"
                               maxlength="20"
                               required
                               style="text-transform:uppercase;">
                        <small style="color:#6c757d; font-size:12px; margin-top:4px; display:block;">Se guardará en mayúsculas automáticamente</small>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="font-size:14px; font-weight:600; color:#495057; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-lock" style="color:#667eea;"></i> Contraseña
                        </label>
                        <div style="position:relative;">
                            <input type="password" 
                                   name="pas" 
                                   id="inputPassword"
                                   class="form-control" 
                                   placeholder="Mínimo 6 caracteres"
                                   minlength="6"
                                   required
                                   style="padding-right:45px;">
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#667eea; font-size:16px;">
                                <i class="fas fa-eye" id="iconoOjo"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:20px;">
                        <label style="font-size:14px; font-weight:600; color:#495057; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-user" style="color:#667eea;"></i> Nombre Completo
                        </label>
                        <input type="text" 
                               name="nombre" 
                               class="form-control" 
                               placeholder="Ej: JUAN PEREZ"
                               maxlength="100"
                               required
                               style="text-transform:uppercase;">
                    </div>

                    <div class="form-group" style="margin-bottom:25px;">
                        <label style="font-size:14px; font-weight:600; color:#495057; margin-bottom:8px; display:flex; align-items:center; gap:8px;">
                            <i class="fas fa-shield-alt" style="color:#667eea;"></i> Grado / Rol
                        </label>
                        <select name="grado" class="form-control" required>
                            <option value="">-- Selecciona un rol --</option>
                            <option value="1">🔴 Grado 1 - Administrador</option>
                            <option value="2">🟡 Grado 2 - Auxiliar</option>
                            <option value="3">🟢 Grado 3 - Consulta</option>
                        </select>
                        <small style="color:#6c757d; font-size:12px; margin-top:4px; display:block;">
                            Admin: acceso total · Auxiliar: registra devoluciones · Consulta: solo lectura
                        </small>
                    </div>

                    <button type="submit" 
                            name="crear_usuario" 
                            class="btn btn-review" 
                            style="width:100%; justify-content:center; padding:14px;">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>

                </form>
            </div>
        </div>

        <!-- ── Tabla de Usuarios Registrados ── -->
        <div class="admin-card history-card" style="overflow:hidden;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-users"></i>
                    <h2>Usuarios Registrados</h2>
                </div>
                <div class="card-actions">
                    <span class="badge badge-info" style="font-size:14px; padding:8px 16px;">
                        Total: <?php echo count($usuarios); ?>
                    </span>
                </div>
            </div>

            <!-- Buscador -->
            <div style="padding: 15px 25px; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
                <input type="text" 
                       id="buscadorUsuarios" 
                       class="form-control" 
                       placeholder="🔍 Buscar usuario por nombre o usuario..."
                       oninput="buscarUsuario()"
                       style="margin:0;">
            </div>

            <div class="card-body" style="padding: 20px;">
                <div class="table-responsive">
                    <table class="admin-table" id="tablaUsuarios">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Grado</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoUsuarios">
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="empty-state">
                                            <i class="fas fa-users-slash"></i>
                                            <h3>Sin usuarios</h3>
                                            <p>No hay usuarios registrados aún.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                <?php
                                    $grado = intval($u['GRADO']);
                                    $rolTexto = match($grado) {
                                        1 => 'Administrador',
                                        2 => 'Auxiliar',
                                        3 => 'Consulta',
                                        default => 'Desconocido'
                                    };
                                    $rolClase = match($grado) {
                                        1 => 'background:#f8d7da; color:#721c24;',
                                        2 => 'background:#fff3cd; color:#856404;',
                                        3 => 'background:#d1e7dd; color:#0f5132;',
                                        default => 'background:#e9ecef; color:#495057;'
                                    };
                                    $rolIcono = match($grado) {
                                        1 => '🔴',
                                        2 => '🟡',
                                        3 => '🟢',
                                        default => '⚪'
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <span class="usuario-badge">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($u['USR']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="cliente-info">
                                            <strong><?php echo htmlspecialchars($u['NOMBRE']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="estado-badge" style="<?php echo $rolClase; ?>">
                                            <?php echo $rolIcono . ' ' . $rolTexto; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="id-badge"><?php echo $grado; ?></span>
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
</div>

<script>
// Mostrar/ocultar contraseña
function togglePassword() {
    const input = document.getElementById('inputPassword');
    const icono = document.getElementById('iconoOjo');
    if (input.type === 'password') {
        input.type = 'text';
        icono.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icono.className = 'fas fa-eye';
    }
}

// Convertir a mayúsculas en tiempo real
document.querySelectorAll('input[style*="uppercase"]').forEach(input => {
    input.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});

// Buscar en tabla de usuarios
function buscarUsuario() {
    const texto = document.getElementById('buscadorUsuarios').value.toLowerCase();
    const filas = document.querySelectorAll('#cuerpoUsuarios tr');
    filas.forEach(fila => {
        fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
    });
}
</script>

<?php include 'Views/layouts/footer.php'; ?>