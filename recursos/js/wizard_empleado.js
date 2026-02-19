/* recursos/js/wizard_empleado.js */

let currentStep = 1;
const totalSteps = 5;
let esEspecialista = false;
let rolGlobal = false;
let validCedula = false;
// variables globales esperadas: ordenDias, horariosSucursales, isEditMode

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    actualizarProgreso();
    initWizard(); // Auto-configuración
});

function initWizard() {
    // Si estamos editando, las validaciones iniciales deben pasar
    if (typeof isEditMode !== 'undefined' && isEditMode) {
        validCedula = true; // Asumimos cédula válida si ya existe
        correoValido = true; // Asumimos correo válido
        
        // Disparar lógica de rol para mostrar/ocultar campos correctos
        const rolInput = document.getElementById('inputRolId');
        if(rolInput && rolInput.value) {
            // Buscamos la tarjeta seleccionada para obtener el nombre
            const card = document.querySelector(`.role-card[onclick*="${rolInput.value}"]`);
            if(card) {
                // Simulamos click para activar lógica pero sin resetear valores
                const onclickText = card.getAttribute('onclick'); 
                // Extraer el nombre del rol del string onclick (un poco hacky pero funciona sin cambiar html)
                // O mejor, ejecutamos la lógica manual basada en clases existentes
                const nombreRol = card.innerText.toLowerCase(); 
                
                esEspecialista = (nombreRol.includes('especialista') || nombreRol.includes('barbero') || nombreRol.includes('estilista'));
                rolGlobal = (rolInput.value == 2 || nombreRol.includes('dueño') || nombreRol.includes('negocio'));
                
                configurarVisibilidadRol();
            }
        }
        
        // Renderizar días si hay sucursal
        const sucInput = document.getElementById('inputSucursalId');
        if(sucInput && sucInput.value) {
            renderizarDias(sucInput.value);
        } else if (rolGlobal) {
            renderizarDias(null);
        }

        // Habilitar botón next
        document.getElementById('btnNext').disabled = false;
    }
}

function actualizarProgreso() {
    const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
    const bar = document.getElementById('progressBar');
    if(bar) bar.style.width = percent + '%';
    
    for(let i=1; i<=totalSteps; i++) {
        const stepEl = document.getElementById('stepIndicator' + i);
        if(stepEl) {
            if(i < currentStep) stepEl.classList.add('completed');
            else stepEl.classList.remove('completed');
            if(i === currentStep) stepEl.classList.add('active');
            else stepEl.classList.remove('active');
        }
    }
}

// --- PASO 1: ROLES ---
function seleccionarRol(card, id, nombreRol) {
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('inputRolId').value = id;

    esEspecialista = (nombreRol.includes('especialista') || nombreRol.includes('barbero') || nombreRol.includes('estilista'));
    rolGlobal = (id == 2 || nombreRol.includes('dueño') || nombreRol.includes('negocio')); 

    configurarVisibilidadRol();

    // Lógica Días
    if (rolGlobal) {
        renderizarDias(null); 
    } else {
        const grid = document.getElementById('gridDiasLaborables');
        const msg = document.getElementById('msgSinHorario');
        if(grid) grid.innerHTML = '';
        if(msg) {
            msg.style.display = 'block';
            msg.innerHTML = '<i class="fa-solid fa-store"></i> Primero selecciona una sede en el Paso 3.';
        }
        document.getElementById('inputSucursalId').value = ""; // Limpiar sucursal si cambia a no-global
    }

    document.getElementById('btnNext').disabled = false;
}

function configurarVisibilidadRol() {
    const bloquesEspecialista = document.querySelectorAll('.especialista-only');
    const bloqueNoEsp = document.getElementById('bloqueNoEspecialista');
    
    if(esEspecialista) {
        bloquesEspecialista.forEach(el => el.style.display = ''); 
        if(bloqueNoEsp) bloqueNoEsp.style.display = 'none';
    } else {
        bloquesEspecialista.forEach(el => el.style.display = 'none'); 
        if(bloqueNoEsp) bloqueNoEsp.style.display = 'block'; 
    }
}

// --- PASO 2: VALIDACIONES ---
let correoValido = false; 

function verificarCorreoEnVivo() {
    const input = document.getElementById('txtCorreo');
    const wrapper = document.getElementById('wrapperCorreo');
    const msg = document.getElementById('msgCorreo');
    const loader = document.getElementById('loaderCorreo');
    const correo = input.value.trim();

    msg.style.display = 'none';
    wrapper.style.borderColor = '#f1f2f6';
    correoValido = false;

    if (!correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        validarPaso2(); return;
    }

    // Si estamos editando y el correo es el mismo que el original, es válido
    if (typeof originalEmail !== 'undefined' && correo === originalEmail) {
        correoValido = true;
        validarPaso2();
        return;
    }

    loader.style.display = 'block';

    fetch(`index.php?c=auth&a=verificar_correo_ajax&email=${correo}`)
    .then(r => r.json())
    .then(data => {
        loader.style.display = 'none';
        msg.style.display = 'block';

        if (data.existe) {
            msg.innerText = "Este correo ya está registrado.";
            msg.style.color = "#e74c3c";
            wrapper.style.borderColor = "#e74c3c";
            correoValido = false;
        } else {
            msg.innerText = "Correo disponible.";
            msg.style.color = "#00b894";
            wrapper.style.borderColor = "#00b894";
            correoValido = true;
        }
        validarPaso2();
    })
    .catch(err => {
        console.error(err);
        loader.style.display = 'none';
        correoValido = true; // Fallback
        validarPaso2();
    });
}

function validarPaso2() {
    const nom = document.getElementById('txtNombres').value.trim();
    const ape = document.getElementById('txtApellidos').value.trim();
    const ced = document.getElementById('txtCedula').value.trim();
    const mail = document.getElementById('txtCorreo').value.trim();
    const pass = document.getElementById('txtPass').value.trim();
    
    const formatoCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail);
    
    // En modo edición, la contraseña es opcional
    const passRequerida = (typeof isEditMode !== 'undefined' && isEditMode) ? true : (pass.length >= 8);

    // Permitimos avanzar si validCedula y correoValido son true (o formato correcto si aún no ajax)
    const isValid = (nom && ape && ced && mail && passRequerida && validCedula && (correoValido || formatoCorreo));
    
    document.getElementById('btnNext').disabled = !isValid;
}

function validarCedulaEnVivo(input) {
    const cedula = input.value;
    const msg = document.getElementById('msgCedula');
    
    if (cedula.length !== 10 || isNaN(cedula)) {
        msg.innerText = "Debe tener 10 dígitos.";
        msg.style.color = "#e74c3c";
        validCedula = false;
    } else if (!esCedulaEcuatoriana(cedula)) {
        msg.innerText = "Cédula inválida.";
        msg.style.color = "#e74c3c";
        validCedula = false;
    } else {
        msg.innerText = "Cédula correcta";
        msg.style.color = "#00b894";
        validCedula = true;
    }
    validarPaso2();
}

function esCedulaEcuatoriana(cedula) {
    if (cedula.length !== 10) return false;
    const digitoRegion = parseInt(cedula.substring(0, 2));
    if (digitoRegion < 1 || digitoRegion > 24) return false;
    const ultimoDigito = parseInt(cedula.substring(9, 10));
    let pares = 0; let impares = 0; let suma = 0;
    for (let i = 0; i < 9; i++) {
        let num = parseInt(cedula.charAt(i));
        if (i % 2 === 0) {
            num = num * 2;
            if (num > 9) num -= 9;
            impares += num;
        } else {
            pares += num;
        }
    }
    suma = pares + impares;
    let digitoVerificador = 10 - (suma % 10);
    if (digitoVerificador === 10) digitoVerificador = 0;
    return digitoVerificador === ultimoDigito;
}

// --- PASO 3: SUCURSAL ---
function seleccionarSucursal(card, id) {
    if(rolGlobal) return; 
    document.querySelectorAll('.suc-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('inputSucursalId').value = id;
    renderizarDias(id);
    document.getElementById('btnNext').disabled = false;
}

// --- PASO 5: DÍAS ---
function renderizarDias(sucursalId) {
    const container = document.getElementById('gridDiasLaborables');
    const msg = document.getElementById('msgSinHorario');
    if(!container) return;
    
    container.innerHTML = ''; 

    // MAPA PARA TRADUCIR NÚMEROS A NOMBRES (Si no está definido globalmente)
    const mapaNombres = { 1:'Lunes', 2:'Martes', 3:'Miércoles', 4:'Jueves', 5:'Viernes', 6:'Sábado', 7:'Domingo' };
    
    // ORDEN NUMÉRICO OBLIGATORIO
    const ordenNumerico = [1, 2, 3, 4, 5, 6, 7];

    let diasDisponibles = []; // Array de NÚMEROS

    if (rolGlobal) {
        // Si es global, trabaja todos los días (1 al 7)
        diasDisponibles = ordenNumerico;
    } else if (sucursalId && typeof horariosSucursales !== 'undefined' && horariosSucursales[sucursalId]) {
        // Filtramos solo los números que la sucursal tiene abiertos
        // horariosSucursales[id] ahora trae [1, 2, 3...]
        diasDisponibles = ordenNumerico.filter(diaNum => {
            // Convertimos a string para comparar seguro, o int
            return horariosSucursales[sucursalId].includes(diaNum) || horariosSucursales[sucursalId].includes(String(diaNum));
        });
    }

    if (diasDisponibles.length === 0) {
        if(msg) {
            msg.style.display = 'block';
            if(!rolGlobal) msg.innerText = "Esta sucursal no tiene horarios configurados.";
        }
        return;
    }

    if(msg) msg.style.display = 'none';

    // Recuperar días guardados si estamos editando (definidos en la vista como [1, 5, 6...])
    const diasGuardados = (typeof misDiasGuardados !== 'undefined') ? misDiasGuardados : [];

    diasDisponibles.forEach(diaNum => {
        // En modo crear: todos activos. En editar: solo los que coinciden.
        // Convertimos a int para asegurar comparación
        const diaInt = parseInt(diaNum);
        const isChecked = (typeof isEditMode !== 'undefined' && isEditMode) ? diasGuardados.includes(diaInt) || diasGuardados.includes(String(diaInt)) : true;
        
        const activeClass = isChecked ? 'active' : '';
        const textStatus = isChecked ? 'Laborable' : 'Descanso';
        const colorStyle = isChecked ? 'color:var(--color-primario)' : 'color:#ccc';
        const nombreDia = mapaNombres[diaInt];

        const label = document.createElement('label');
        label.className = `day-card ${activeClass}`;
        label.innerHTML = `
            <div class="day-check">
                <input type="checkbox" name="dias_trabajo[]" value="${diaInt}" ${isChecked ? 'checked' : ''} onchange="toggleDay(this)">
                <div class="day-circle"><i class="fa-solid fa-check"></i></div>
            </div>
            <span class="day-name">${nombreDia}</span>
            <span class="day-status" style="${colorStyle}">${textStatus}</span>
        `;
        container.appendChild(label);
    });
}

// --- NAVEGACIÓN ---
function cambiarPaso(dir) {
    if (dir === 1) {
        if (currentStep === 1 && !document.getElementById('inputRolId').value) return;
        if (currentStep === 2 && !validCedula) return;
        if (currentStep === 3 && !rolGlobal && !document.getElementById('inputSucursalId').value) return;
    }

    document.getElementById('step' + currentStep).classList.remove('active-panel');
    currentStep += dir;
    
    if (rolGlobal && currentStep === 3) {
        currentStep = (dir === 1) ? 4 : 2;
    }

    document.getElementById('step' + currentStep).classList.add('active-panel');
    actualizarProgreso();
    actualizarBotones();
}

function actualizarBotones() {
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnFinish = document.getElementById('btnFinish');

    btnPrev.disabled = (currentStep === 1);
    
    if(currentStep === totalSteps) {
        btnNext.style.display = 'none';
        btnFinish.style.display = 'inline-flex';
    } else {
        btnNext.style.display = 'inline-flex';
        btnFinish.style.display = 'none';
        
        let pasoCompleto = false;
        if (currentStep === 1 && document.getElementById('inputRolId').value) pasoCompleto = true;
        // En paso 2 (datos), al volver, asumimos válido si tiene datos
        if (currentStep === 2 && document.getElementById('txtNombres').value) pasoCompleto = true; 
        if (currentStep === 3 && (rolGlobal || document.getElementById('inputSucursalId').value)) pasoCompleto = true;
        if (currentStep === 4) pasoCompleto = true; 

        btnNext.disabled = !pasoCompleto;
    }
}

function togglePassword(id, icon) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text"; icon.classList.replace("fa-eye-slash", "fa-eye");
    } else {
        input.type = "password"; icon.classList.replace("fa-eye", "fa-eye-slash");
    }
}

function toggleDay(chk) {
    const card = chk.closest('.day-card');
    const status = card.querySelector('.day-status');
    if(chk.checked) {
        card.classList.add('active');
        status.innerText = "Laborable";
        status.style.color = "var(--color-primario)";
    } else {
        card.classList.remove('active');
        status.innerText = "Descanso";
        status.style.color = "#ccc";
    }
}