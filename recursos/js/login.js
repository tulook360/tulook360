document.addEventListener('DOMContentLoaded', () => {

    // 1. Mostrar/Ocultar Contraseña
    const btnTogglePass = document.getElementById('btnTogglePass');
    const inputPass = document.getElementById('iPass');

    if (btnTogglePass && inputPass) {
        btnTogglePass.addEventListener('click', () => {
            const type = inputPass.getAttribute('type') === 'password' ? 'text' : 'password';
            inputPass.setAttribute('type', type);
            
            // Cambiar el ícono
            const icon = btnTogglePass.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // 2. Validación en tiempo real para habilitar botón
    const iEmail = document.getElementById('iEmail');
    const btnSubmit = document.getElementById('btnSubmit');

    const checkFields = () => {
        // Validación básica de correo (mínimo 6 chars y arroba) y clave no vacía
        const emailValid = iEmail.value.trim().length > 5 && iEmail.value.includes('@');
        const passValid = inputPass.value.trim().length > 0;
        
        // Si ambos campos tienen datos lógicos, habilita el botón rosa
        btnSubmit.disabled = !(emailValid && passValid);
    };

    if (iEmail && inputPass) {
        iEmail.addEventListener('input', checkFields);
        inputPass.addEventListener('input', checkFields);
    }

    // 3. Efecto visual al enviar (Spinner)
    const formLogin = document.getElementById('formLogin');
    if (formLogin) {
        formLogin.addEventListener('submit', function() {
            if (!btnSubmit.disabled) {
                btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin me-2"></i> Entrando...';
                btnSubmit.disabled = true; // Evitar doble click
            }
        });
    }
});