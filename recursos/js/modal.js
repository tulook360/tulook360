// recursos/js/modal.js

const modal = document.getElementById('modalConfirm');
const modalTitle = document.getElementById('modalTitle');
const modalText  = document.getElementById('modalText');
const btnConfirmar = document.getElementById('btnModalConfirmar');
const btnCancelar = document.getElementById('btnModalCancelar');
const iconBox = document.getElementById('modalIconBox');
const iconI   = document.getElementById('modalIconI');

/**
 * MODO 1: PREGUNTAR (Para eliminar o acciones peligrosas)
 * Muestra dos botones: Cancelar y Confirmar
 */
function preguntar(url, titulo, mensaje, textoBoton, tipo = 'danger') {
    if(!modal) return;

    // Restaurar visibilidad del botón Cancelar
    if(btnCancelar) btnCancelar.style.display = 'inline-flex';
    
    // Configurar enlace real
    btnConfirmar.setAttribute('href', url);
    btnConfirmar.onclick = null; // Quitar eventos previos

    modalTitle.textContent = titulo;
    modalText.innerHTML    = mensaje; 
    btnConfirmar.textContent = textoBoton;

    resetEstilos();
    aplicarEstilo(tipo);

    modal.classList.add('active');
}

/**
 * MODO 2: NOTIFICAR (Éxito o Info)
 * Muestra solo un botón: Aceptar (Cierra el modal)
 */
function mostrarNotificacion(titulo, mensaje, tipo = 'success') {
    if(!modal) return;

    // Ocultar botón Cancelar
    if(btnCancelar) btnCancelar.style.display = 'none';

    // El botón confirmar ahora solo cierra el modal
    btnConfirmar.setAttribute('href', 'javascript:void(0)');
    btnConfirmar.textContent = "Aceptar";
    
    // Al hacer clic, cerramos
    btnConfirmar.onclick = function() {
        cerrarModal();
    };

    modalTitle.textContent = titulo;
    modalText.innerHTML    = mensaje;

    resetEstilos();
    aplicarEstilo(tipo);

    modal.classList.add('active');
}


// --- Funciones Auxiliares ---

function resetEstilos() {
    iconBox.classList.remove('danger', 'success');
    btnConfirmar.classList.remove('btn-success');
}

function aplicarEstilo(tipo) {
    if (tipo === 'success') {
        iconBox.classList.add('success');
        iconI.className = 'fa-solid fa-check';
        btnConfirmar.classList.add('btn-success');
    } else {
        iconBox.classList.add('danger');
        iconI.className = 'fa-solid fa-triangle-exclamation';
    }
}

function cerrarModal() {
    if(modal) modal.classList.remove('active');
}

// Eventos Globales
if(btnCancelar) {
    btnCancelar.addEventListener('click', (e) => {
        e.preventDefault();
        cerrarModal();
    });
}
if(modal) {
    modal.addEventListener('click', (e) => {
        if(e.target === modal) cerrarModal();
    });
}


/**
 * MODO 3: CONFIRMACIÓN JS (Callback)
 * Para acciones AJAX que no requieren cambiar de página.
 */
function confirmarAccionJS(titulo, mensaje, callback) {
    if(!modal) return;

    // Restaurar visibilidad del botón Cancelar
    if(btnCancelar) btnCancelar.style.display = 'inline-flex';
    
    // Quitar href para que no navegue
    btnConfirmar.removeAttribute('href');
    
    // Asignar el evento onclick personalizado
    btnConfirmar.onclick = function(e) {
        e.preventDefault();
        callback(); // Ejecutar la función que pasamos
        cerrarModal(); // Cerrar el modal
    };

    modalTitle.textContent = titulo;
    modalText.innerHTML    = mensaje; 
    btnConfirmar.textContent = "Sí, Confirmar";

    resetEstilos();
    aplicarEstilo('danger'); // Por defecto peligroso (borrar)

    modal.classList.add('active');
}