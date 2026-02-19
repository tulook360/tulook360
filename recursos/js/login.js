document.addEventListener('DOMContentLoaded', function () {

    // ============================
    // Mostrar / ocultar contraseña
    // ============================
    const passInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');

    if (passInput && toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = passInput.type === 'password';
            passInput.type = isHidden ? 'text' : 'password';

            toggleBtn.classList.toggle('fa-eye');
            toggleBtn.classList.toggle('fa-eye-slash');
        });
    }

    // ============================
    // Desvanecer alerta de error
    // ============================
    const alerta = document.querySelector('.alerta-error');
    if (alerta) {
        setTimeout(() => {
            alerta.classList.add('alerta-oculta');
            setTimeout(() => alerta.remove(), 600);
        }, 3000);
    }

    // ============================
    // Validación del formulario
    // ============================
    const form = document.getElementById('formLogin');
    if (!form) return;

    form.addEventListener('submit', function (e) {

        const emailInput = document.getElementById('email');
        const passInput  = document.getElementById('password');

        const emailError = emailInput.parentElement.querySelector('.msg-error');
        const passError  = passInput.parentElement.querySelector('.msg-error');

        // Reiniciar mensajes
        emailError.textContent = '';
        passError.textContent = '';
        emailError.classList.remove('mostrar');
        passError.classList.remove('mostrar');

        let hayErrores = false;

        if (emailInput.value.trim() === '') {
            emailError.textContent = 'El correo es obligatorio';
            emailError.classList.add('mostrar');
            hayErrores = true;
        }

        if (passInput.value.trim() === '') {
            passError.textContent = 'La contraseña es obligatoria';
            passError.classList.add('mostrar');
            hayErrores = true;
        }

        if (hayErrores) {
            e.preventDefault();
        }
    });
});
