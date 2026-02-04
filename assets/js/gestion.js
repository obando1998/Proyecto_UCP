// assets/js/gestion.js

let faseActual = 'fase1';

// Cambiar entre fases
function cambiarFase(fase, event) {
    faseActual = fase;
    
    // Actualizar tabs
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Mostrar contenido de la fase
    document.querySelectorAll('.phase-section').forEach(section => section.classList.remove('active'));
    document.getElementById(fase).classList.add('active');
    
    // Cargar datos de la fase
    cargarDatosFase(fase);
}

// Cargar datos según la fase
async function cargarDatosFase(fase) {
    try {
        const response = await fetch(`index.php?controller=candidato&action=listarPorFase&fase=${fase}`);
        const result = await response.json();
        
        if(result.success) {
            if(fase === 'fase1') {
                mostrarCandidatosFase1(result.data);
            } else if(fase === 'fase2') {
                mostrarCandidatosFase2(result.data);
            } else if(fase === 'fase3') {
                mostrarCandidatosFase3(result.data);
            }
        }
    } catch(error) {
        console.error('Error al cargar datos:', error);
    }
}

// Mostrar candidatos Fase 1
function mostrarCandidatosFase1(candidatos) {
    const tbody = document.querySelector('#tablaFase1 tbody');
    
    if(candidatos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#999; font-style:italic;">No hay candidatos en esta fase</td></tr>';
        return;
    }
    
    let html = '';
    candidatos.forEach(c => {
        const badgeAntecedentes = {
            'Aprobado': 'badge-success',
            'Rechazado': 'badge-danger',
            'Pendiente': 'badge-warning'
        }[c.antecedentes] || 'badge-warning';
        
        const alertaVeces = c.veces_proceso > 1 ? 
            `<span class="badge badge-danger" title="Ya ha estado en proceso ${c.veces_proceso} veces">⚠️ ${c.veces_proceso}x</span>` : 
            '<span style="color:#999;">-</span>';
        
        html += `
            <tr>
                <td><strong>${c.cedula}</strong></td>
                <td>${c.nombres_apellidos}</td>
                <td><small>${c.requisicion_codigo || ''}</small><br>${c.nombre_cargo || ''}</td>
                <td><span class="badge ${badgeAntecedentes}">${c.antecedentes}</span></td>
                <td>${c.celular}</td>
                <td>${alertaVeces}</td>
                <td>
                    <button class="btn btn-primary" onclick="pasarAFase2(${c.id})" style="background: linear-gradient(135deg, #198754, #146c43);">
                        ➡️ Fase 2
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Mostrar candidatos Fase 2
function mostrarCandidatosFase2(candidatos) {
    const tbody = document.querySelector('#tablaFase2 tbody');
    
    if(candidatos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px; color:#999; font-style:italic;">No hay candidatos en esta fase</td></tr>';
        return;
    }
    
    let html = '';
    candidatos.forEach(c => {
        html += `
            <tr>
                <td><strong>${c.cedula}</strong></td>
                <td>${c.nombres_apellidos}</td>
                <td>${c.nombre_cargo || '-'}</td>
                <td>-</td>
                <td>-</td>
                <td>
                    <button class="btn btn-primary" onclick="abrirModalEntrevista(${c.id})">
                        📝 Registrar
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Mostrar candidatos Fase 3
function mostrarCandidatosFase3(candidatos) {
    const tbody = document.querySelector('#tablaFase3 tbody');
    
    if(candidatos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#999; font-style:italic;">No hay candidatos en esta fase</td></tr>';
        return;
    }
    
    let html = '';
    candidatos.forEach(c => {
        html += `
            <tr>
                <td><strong>${c.cedula}</strong></td>
                <td>${c.nombres_apellidos}</td>
                <td>${c.nombre_cargo || '-'}</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>
                    <button class="btn btn-primary" onclick="abrirModalExamen(${c.id})">
                        🩺 Registrar
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Cargar requisiciones para el select
async function cargarRequisiciones() {
    try {
        const response = await fetch('index.php?controller=requisicion&action=listar&estado=Aprobada');
        const result = await response.json();
        
        if(result.success) {
            const select = document.getElementById('selectRequisicion');
            let html = '<option value="">Seleccione una requisición...</option>';
            
            result.data.forEach(req => {
                html += `<option value="${req.id}">${req.codigo} - ${req.nombre_cargo}</option>`;
            });
            
            select.innerHTML = html;
        }
    } catch(error) {
        console.error('Error al cargar requisiciones:', error);
    }
}

// Verificar si el candidato ya existe
async function verificarCandidato(cedula) {
    if(!cedula) return;
    
    try {
        const response = await fetch(`index.php?controller=candidato&action=obtenerAlertas&cedula=${cedula}`);
        const result = await response.json();
        
        const alertaDiv = document.getElementById('alertaCandidato');
        
        if(result.success && result.total > 0) {
            alertaDiv.innerHTML = `
                <div class="alert alert-warning">
                    <strong>⚠️ ALERTA:</strong> Este candidato ya ha estado en proceso ${result.total} vez(es).
                    <ul style="margin-top:12px; margin-left:20px;">
                        ${result.data.map(a => `<li>${a.codigo} - ${a.nombre_cargo} (${a.fecha_alerta})</li>`).join('')}
                    </ul>
                </div>
            `;
        } else {
            alertaDiv.innerHTML = '';
        }
    } catch(error) {
        console.error('Error al verificar candidato:', error);
    }
}

// Pasar candidato a Fase 2
async function pasarAFase2(candidatoId) {
    if(confirm('¿Desea pasar este candidato a la Fase 2 (Entrevista)?')) {
        abrirModalEntrevista(candidatoId);
    }
}

// Modal Entrevista
function abrirModalEntrevista(candidatoId) {
    document.getElementById('candidato_id_f2').value = candidatoId;
    document.getElementById('modalEntrevista').style.display = 'flex';
}

function cerrarModalEntrevista() {
    document.getElementById('modalEntrevista').style.display = 'none';
    document.getElementById('formFase2').reset();
}

// Modal Examen
function abrirModalExamen(candidatoId) {
    document.getElementById('candidato_id_f3').value = candidatoId;
    document.getElementById('modalExamen').style.display = 'flex';
}

function cerrarModalExamen() {
    document.getElementById('modalExamen').style.display = 'none';
    document.getElementById('formFase3').reset();
}

function toggleFechaContratacion(select) {
    const grupo = document.getElementById('grupoFechaContratacion');
    grupo.style.display = select.value === 'Si' ? 'block' : 'none';
}

// Formulario Fase 1
document.getElementById('formFase1').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('index.php?controller=candidato&action=crearFase1', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if(result.success) {
            alert('✅ ' + result.message + (result.alerta ? '\n\n⚠️ ' + result.alerta : ''));
            this.reset();
            document.getElementById('alertaCandidato').innerHTML = '';
            cargarDatosFase('fase1');
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch(error) {
        alert('❌ Error al registrar candidato');
        console.error(error);
    }
});

// Formulario Fase 2
document.getElementById('formFase2').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('index.php?controller=candidato&action=crearFase2', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if(result.success) {
            alert('✅ ' + result.message);
            cerrarModalEntrevista();
            cargarDatosFase('fase2');
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch(error) {
        alert('❌ Error al registrar entrevista');
        console.error(error);
    }
});

// Formulario Fase 3
document.getElementById('formFase3').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('index.php?controller=candidato&action=crearFase3', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if(result.success) {
            alert('✅ ' + result.message);
            cerrarModalExamen();
            cargarDatosFase('fase3');
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch(error) {
        alert('❌ Error al registrar exámenes');
        console.error(error);
    }
});

// Inicializar
cargarRequisiciones();
cargarDatosFase('fase1');