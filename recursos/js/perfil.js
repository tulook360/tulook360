// recursos/js/perfil.js

document.addEventListener('DOMContentLoaded', function() {
    initFotoPerfil();
});

// =======================================================
// UTILIDADES GLOBALES
// =======================================================

/**
 * [CORRECCIÓN] Nombre único para evitar conflictos con modal.js
 * Uso en HTML: onclick="cerrarModalPropio('miModal')"
 */
window.cerrarModalPropio = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
    // Limpieza específica para el modal de contraseña
    if (modalId === 'modalPassword') {
        limpiarErroresPass();
    }
};


// =======================================================
// 1. EDICIÓN DE DATOS SIMPLES
// =======================================================

window.abrirModalEditar = function(campo, etiqueta, valorActual, tipoInput = 'text') {
    const modal = document.getElementById('modalEditarDato');
    const inputCampo = document.getElementById('editCampo');
    const inputValor = document.getElementById('editValor');
    const tituloModal = document.getElementById('editModalTitle');

    if(modal && inputCampo && inputValor) {
        inputCampo.value = campo;
        inputValor.value = valorActual;
        inputValor.type = tipoInput;
        tituloModal.textContent = 'Editar ' + etiqueta;
        
        modal.classList.add('active');
        setTimeout(() => inputValor.focus(), 100);
    }
};

window.guardarDato = function(e) {
    e.preventDefault();
    
    // Usamos la nueva función con nombre único
    window.cerrarModalPropio('modalEditarDato');
    
    // Referencias para el envío
    const inputCampo = document.getElementById('editCampo');
    const inputValor = document.getElementById('editValor');
    
    mostrarEstadoCarga();

    const formData = new FormData();
    formData.append('campo', inputCampo.value);
    formData.append('valor', inputValor.value);

    fetch(urlGuardarDato, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(handleResponse)
    .catch(handleError);
};


// =======================================================
// 2. CAMBIO DE CONTRASEÑA
// =======================================================

window.abrirModalPassword = function() {
    const modal = document.getElementById('modalPassword');
    const passNew = document.getElementById('passNew');
    const passConfirm = document.getElementById('passConfirm');
    
    if(passNew) passNew.value = '';
    if(passConfirm) passConfirm.value = '';
    
    limpiarErroresPass();
    
    if(modal) {
        modal.classList.add('active');
        setTimeout(() => passNew.focus(), 100);
    }
};

function limpiarErroresPass() {
    const err1 = document.getElementById('errorPassNew');
    const err2 = document.getElementById('errorPassConfirm');
    const p1 = document.getElementById('passNew');
    const p2 = document.getElementById('passConfirm');

    if(err1) err1.innerText = ''; 
    if(err2) err2.innerText = '';
    if(p1) p1.classList.remove('input-error');
    if(p2) p2.classList.remove('input-error');
}

window.guardarContrasena = function(e) {
    e.preventDefault();
    
    const p1 = document.getElementById('passNew');
    const p2 = document.getElementById('passConfirm');
    const err1 = document.getElementById('errorPassNew');
    const err2 = document.getElementById('errorPassConfirm');
    
    limpiarErroresPass();
    let hayError = false;

    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!regex.test(p1.value)) {
        err1.innerText = 'Mínimo 8 caracteres, mayúscula, número y símbolo.';
        p1.classList.add('input-error');
        hayError = true;
    }

    if (p1.value !== p2.value) {
        err2.innerText = 'Las contraseñas no coinciden.';
        p2.classList.add('input-error');
        hayError = true;
    }

    if (hayError) return; 

    window.cerrarModalPropio('modalPassword');
    mostrarEstadoCarga();

    const formData = new FormData();
    formData.append('pass1', p1.value);
    formData.append('pass2', p2.value);

    fetch(urlCambiarPass, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(handleResponse)
    .catch(handleError);
};


// =======================================================
// 3. FOTO DE PERFIL
// =======================================================

function initFotoPerfil() {
    const inputFoto = document.getElementById('inputFotoPerfil');
    const btnEditAvatar = document.querySelector('.btn-edit-avatar');
    
    if(!inputFoto || !btnEditAvatar) return;

    let originalBtnContent = btnEditAvatar.innerHTML;

    inputFoto.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                alert('Solo JPG o PNG.'); this.value = ''; return;
            }
            
            mostrarModalConfirmacionJS(
                '¿Cambiar foto?', 'Se reemplazará tu foto actual.',
                () => ejecutarSubida(file, btnEditAvatar, originalBtnContent, inputFoto),
                () => inputFoto.value = ''
            );
        }
    });
}

function ejecutarSubida(file, btn, original, input) {
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.style.pointerEvents = 'none';
    mostrarEstadoCarga();

    const formData = new FormData();
    formData.append('fotoPerfil', file);

    fetch(urlActualizarFoto, { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) mostrarExito(data.message);
        else { 
            mostrarError(data.message); 
            btn.innerHTML = original; 
            btn.style.pointerEvents = 'auto'; 
            input.value = '';
        }
    })
    .catch(err => {
        mostrarError('Error de conexión.');
        btn.innerHTML = original;
        btn.style.pointerEvents = 'auto';
        input.value = '';
    });
}


// =======================================================
// HELPERS MODAL GLOBAL
// =======================================================

function handleResponse(data) {
    if (data.success) mostrarExito(data.message);
    else mostrarError(data.message);
}

function handleError(err) {
    console.error(err);
    mostrarError('Error de conexión.');
}

function mostrarEstadoCarga() {
    const modal = document.getElementById('modalConfirm');
    if(!modal) return;
    
    document.getElementById('modalTitle').textContent = "Procesando...";
    document.getElementById('modalText').textContent = "Por favor espera...";
    document.getElementById('modalIconBox').className = 'modal-icon';
    document.getElementById('modalIconI').className = 'fa-solid fa-spinner fa-spin';
    
    document.getElementById('btnModalConfirmar').style.display = 'none';
    document.getElementById('btnModalCancelar').style.display = 'none';
    
    modal.classList.add('active');
}

function mostrarExito(msg) {
    const iconBox = document.getElementById('modalIconBox');
    iconBox.className = 'modal-icon success';
    document.getElementById('modalIconI').className = 'fa-solid fa-check';
    document.getElementById('modalTitle').textContent = "¡Éxito!";
    document.getElementById('modalText').textContent = msg;
    
    setTimeout(() => window.location.reload(), 1500);
}

function mostrarError(msg) {
    const modal = document.getElementById('modalConfirm');
    const btnConfirm = document.getElementById('btnModalConfirmar');
    
    btnConfirm.style.display = 'inline-flex';
    btnConfirm.textContent = "Aceptar";
    btnConfirm.className = 'btn-modal btn-confirm';
    btnConfirm.onclick = function(e) { 
        e.preventDefault();
        modal.classList.remove('active');
    };

    document.getElementById('modalTitle').textContent = "Error";
    document.getElementById('modalText').textContent = msg;
    document.getElementById('modalIconBox').className = 'modal-icon danger';
    document.getElementById('modalIconI').className = 'fa-solid fa-xmark';
    
    const btnCancel = document.getElementById('btnModalCancelar');
    if(btnCancel) btnCancel.style.display = 'none';

    modal.classList.add('active');
}

function mostrarModalConfirmacionJS(titulo, mensaje, cbSi, cbNo) {
    const modal = document.getElementById('modalConfirm');
    document.getElementById('modalTitle').textContent = titulo;
    document.getElementById('modalText').innerHTML = mensaje;
    
    const btnSi = document.getElementById('btnModalConfirmar');
    const btnNo = document.getElementById('btnModalCancelar');
    
    document.getElementById('modalIconBox').className = 'modal-icon';
    document.getElementById('modalIconI').className = 'fa-solid fa-question';
    
    btnSi.style.display = 'inline-flex';
    btnNo.style.display = 'inline-flex';
    btnSi.textContent = "Sí, Confirmar";
    btnSi.className = 'btn-modal btn-confirm';
    btnSi.removeAttribute('href');

    const newSi = btnSi.cloneNode(true);
    btnSi.parentNode.replaceChild(newSi, btnSi);
    const newNo = btnNo.cloneNode(true);
    btnNo.parentNode.replaceChild(newNo, btnNo);

    newSi.onclick = (e) => { e.preventDefault(); if(cbSi) cbSi(); };
    newNo.onclick = (e) => { e.preventDefault(); modal.classList.remove('active'); if(cbNo) cbNo(); };

    modal.classList.add('active');
}