document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('formEmpleado');
    const btnSubmit = document.getElementById('btnSubmit');

    // Si no hay formulario (por seguridad), no hacemos nada
    if (!form) return;

    // 1. CONFIGURACIÓN DE CAMPOS
    // Definimos qué ID tiene cada campo y qué función lo valida
    const fieldsConfig = {
        'nombres':   valTextoSimple,
        'apellidos': valTextoSimple,
        'cedula':    valCedulaEc,      // Se validará solo si existe el input
        'correo':    valEmail,
        'telefono':  valTelefono,      // Se validará solo si existe el input
        'rol_id':    valRequerido,
        'password':  valPasswordSmart  // Lógica inteligente (obligatorio en crear, opcional en editar)
    };

    // 2. AGREGAR LISTENERS (SOLO A LOS QUE EXISTEN)
    // Recorremos la configuración y buscamos los inputs en el HTML
    for (const [id, validatorFunc] of Object.entries(fieldsConfig)) {
        const input = document.getElementById(id);
        
        if (input) {
            // Solo si el input existe en esta vista, le agregamos eventos
            input.addEventListener('input', () => validarCampo(id, validatorFunc));
            input.addEventListener('blur', () => validarCampo(id, validatorFunc));
        }
    }

    // 3. FUNCIÓN CENTRAL DE VALIDACIÓN
    function validarCampo(id, validatorFunc) {
        const input = document.getElementById(id);
        const errorTag = document.getElementById('error-' + id);

        // Si el elemento no existe en esta vista (ej: cédula en crear), retornamos TRUE (Válido)
        if (!input) return true; 

        // Ejecutar la validación específica
        const error = validatorFunc(input);

        // Mostrar u ocultar errores visuales
        if (error) {
            input.style.borderColor = '#d63031';
            input.style.backgroundColor = '#fff5f5';
            if(errorTag) {
                errorTag.innerText = error;
                errorTag.style.display = 'block';
            }
            return false;
        } else {
            input.style.borderColor = ''; // Restaurar original
            input.style.backgroundColor = '';
            if(errorTag) {
                errorTag.innerText = '';
                errorTag.style.display = 'none';
            }
            return true;
        }
    }

    // --- REGLAS DE VALIDACIÓN ---

    function valRequerido(input) {
        return input.value.trim() === '' ? 'Este campo es obligatorio.' : null;
    }

    function valTextoSimple(input) {
        if (input.value.trim() === '') return 'Campo obligatorio.';
        const regex = /^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/;
        return !regex.test(input.value) ? 'Solo se permiten letras.' : null;
    }

    function valEmail(input) {
        if (input.value.trim() === '') return 'El correo es obligatorio.';
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return !regex.test(input.value) ? 'Formato de correo inválido.' : null;
    }

    function valTelefono(input) {
        if (input.value.trim() === '') return null; // Opcional si está vacío
        const regex = /^[0-9]{10}$/;
        return !regex.test(input.value) ? 'Debe tener 10 dígitos.' : null;
    }

    function valPasswordSmart(input) {
        const valor = input.value;
        
        // CASO 1: Campo vacío
        if (valor === '') {
            // Si tiene el atributo 'required' (Vista Crear), es error.
            // Si no lo tiene (Vista Editar), es válido dejarlo vacío.
            if (input.hasAttribute('required')) {
                return 'La contraseña es obligatoria.';
            } else {
                return null; // Válido (no se cambia)
            }
        }

        // CASO 2: Escribió algo -> Validar seguridad
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        if (!regex.test(valor)) {
            return 'Insegura: Mín 8 carac, Mayús, Num, Simb.';
        }
        return null;
    }

    // Algoritmo Cédula Ecuador
    function valCedulaEc(input) {
        const cedula = input.value.trim();
        if (cedula === '') return 'La cédula es obligatoria.';
        if (cedula.length !== 10) return 'Debe tener 10 dígitos.';
        
        const digits = cedula.split('').map(Number);
        if (digits.some(isNaN)) return 'Solo números.';
        
        const provincia = Number(cedula.substring(0, 2));
        if (provincia < 1 || provincia > 24) return 'Provincia inválida.';
        if (digits[2] >= 6) return 'Tercer dígito inválido.';

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

        return resultado === digitoVerificador ? null : 'Cédula incorrecta.';
    }

    // 4. CONTROL DEL ENVÍO (SUBMIT)
    form.addEventListener('submit', function(e) {
        let isValid = true;
        let firstErrorField = null;

        // Recorremos todos los campos configurados
        for (const [id, validatorFunc] of Object.entries(fieldsConfig)) {
            // Validamos (la función validarCampo ya sabe ignorar si no existe)
            if (!validarCampo(id, validatorFunc)) {
                isValid = false;
                // Guardamos el primero para hacer focus
                if (!firstErrorField) firstErrorField = document.getElementById(id);
            }
        }

        if (!isValid) {
            e.preventDefault(); // Detener envío
            if (firstErrorField) {
                firstErrorField.focus();
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            // Éxito visual
            if(btnSubmit) {
                btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
                btnSubmit.style.opacity = '0.7';
                btnSubmit.disabled = true;
            }
        }
    });

});

// --- FUNCIÓN GLOBAL PARA VER CONTRASEÑA ---
// Debe estar fuera del DOMContentLoaded para que el onclick del HTML la encuentre
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
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