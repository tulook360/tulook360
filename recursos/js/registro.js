document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('formRegistro');
    const btnSubmit = document.getElementById('btnSubmit');
    
    // --- Live Preview ---
    const liveCard = document.getElementById('liveCard');
    const lcName = document.getElementById('lcName');
    const lcImg = document.getElementById('lcImg');
    const lcIcon = document.getElementById('lcIcon');
    const nameInput = document.getElementById('negocio_nombre');
    const logoInput = document.getElementById('uploadLogo');
    const fileLabel = document.getElementById('fileLabel');
    const typeInput = document.getElementById('inputTipoNegocio');

    function checkVisibility() {
        const hasName = nameInput.value.trim().length > 0;
        const hasLogo = lcImg.style.display === 'block';
        // Mostramos la tarjeta si hay nombre o logo
        if (hasName || hasLogo) {
            liveCard.classList.add('visible');
        } else {
            liveCard.classList.remove('visible');
        }
    }

    nameInput.addEventListener('input', function() {
        const val = this.value.trim();
        lcName.textContent = val || "Nombre del Negocio";
        checkVisibility();
    });

    logoInput.addEventListener('change', function(e) {
        const file = this.files[0];
        // Referencia al contenedor del label
        const labelBox = document.getElementById('uploadLabelBox'); 

        if (file) {
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                alert('Formato inválido'); return;
            }
            
            // --- CAMBIO VISUAL: Poner verde y sólido ---
            fileLabel.innerHTML = '<i class="fa-solid fa-check"></i> ' + file.name;
            labelBox.classList.add('uploaded'); // Agrega la clase CSS verde
            
            // ... (resto del código de FileReader igual) ...
            const reader = new FileReader();
            reader.onload = function(e) {
                lcImg.src = e.target.result;
                lcImg.style.display = 'block';
                lcIcon.style.display = 'none';
                checkVisibility();
            }
            reader.readAsDataURL(file);
        } else {
            // Si cancela, volver al estado normal
            labelBox.classList.remove('uploaded');
            fileLabel.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Elegir imagen';
        }
    });

    // --- Selección de Tipo ---
    window.seleccionarTipo = function(element, id) {
        typeInput.value = id;
        document.querySelectorAll('.type-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        validarCampo('tneg_id');
    };

    // --- Validaciones ---
    const fields = {
        tneg_id:          { element: typeInput, validate: valRequired },
        negocio_nombre:   { element: nameInput, validate: valRequired },
        admin_nombres:    { element: document.getElementById('admin_nombres'), validate: valSingleName },
        admin_apellidos:  { element: document.getElementById('admin_apellidos'), validate: valSingleName },
        admin_correo:     { element: document.getElementById('admin_correo'), validate: valEmail },
        admin_pass:       { element: document.getElementById('admin_pass'), validate: valSecurePass },
        confirm_pass:     { element: document.getElementById('confirm_pass'), validate: valMatchPass },
        admin_cedula: { element: document.getElementById('admin_cedula'), validate: valCedulaEc }
    };

    // Event Listeners para validar en tiempo real
    for (const key in fields) {
        const field = fields[key];
        if(field.element) {
            field.element.addEventListener('input', () => validarCampo(key));
            field.element.addEventListener('blur', () => validarCampo(key));
        }
    }

    function validarCampo(fieldName) {
        const field = fields[fieldName];
        const input = field.element;
        const errorSmall = document.getElementById('error-' + fieldName);
        
        if(!input || !errorSmall) return false;
        const errorMessage = field.validate(input.value);

        if (errorMessage) {
            input.classList.add('input-error');
            errorSmall.innerText = errorMessage;
            return false;
        } else {
            input.classList.remove('input-error');
            errorSmall.innerText = '';
            return true;
        }
    }

    // Validadores
    function valRequired(value) { return value.trim() === '' ? 'Campo obligatorio.' : null; }
    function valSingleName(value) {
        if (value.trim() === '') return 'Campo obligatorio.';
        const regex = /^[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/;
        return !regex.test(value) ? 'Solo letras, sin espacios.' : null;
    }
    function valEmail(value) {
        if (value.trim() === '') return 'Campo obligatorio.';
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return !regex.test(value) ? 'Correo inválido.' : null;
    }
    function valSecurePass(value) {
        if (value === '') return 'Campo obligatorio.';
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        return !regex.test(value) ? 'Mín. 8 caracteres, mayus, num y símbolo.' : null;
    }
    function valMatchPass(value) {
        const pass = document.getElementById('admin_pass').value;
        return value !== pass ? 'Las contraseñas no coinciden.' : null;
    }

    function valCedulaEc(value) {
        if (value.trim() === '') return 'La cédula es obligatoria.';
        if (!validarCedulaEcuador(value)) {
            return 'Cédula incorrecta o no válida.';
        }
        return null;
    }

    // Submit
    form.addEventListener('submit', function(e) {
        let isValid = true;
        for (const key in fields) {
            if (!validarCampo(key)) isValid = false;
        }
        if (!isValid) {
            e.preventDefault();
            const firstError = document.querySelector('.error-msg:not(:empty)');
            if(firstError) firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Creando...';
            btnSubmit.style.opacity = '0.8';
        }
    });

});

// --- ALGORITMO DE VALIDACIÓN DE CÉDULA ECUATORIANA ---
function validarCedulaEcuador(cedula) {
    // 1. Longitud exacta
    if (cedula.length !== 10) return false;

    // 2. Solo números
    const digits = cedula.split('').map(Number);
    if (digits.some(isNaN)) return false;

    // 3. Código de provincia (dos primeros dígitos, 01-24)
    const provincia = Number(cedula.substring(0, 2));
    if (provincia < 1 || provincia > 24) return false;

    // 4. Tercer dígito (debe ser menor a 6 para personas naturales)
    if (digits[2] >= 6) return false;

    // 5. Algoritmo Módulo 10
    let suma = 0;
    const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];

    for (let i = 0; i < 9; i++) {
        let valor = digits[i] * coeficientes[i];
        if (valor >= 10) valor -= 9;
        suma += valor;
    }

    const digitoVerificador = digits[9];
    const decenaSuperior = Math.ceil(suma / 10) * 10;
    let resultado = decenaSuperior - suma;
    
    if (resultado === 10) resultado = 0;

    return resultado === digitoVerificador;
}

// Toggle Pass (Global)
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        icon.style.color = 'var(--color-primario)';
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        icon.style.color = '#b2bec3';
    }
}