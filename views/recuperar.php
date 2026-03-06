<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Cuenta | TuLook360</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Kalam:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= asset('recursos/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/login.css') ?>">
    
    <style>
        /* ========================================= */
        /* DISEÑO PREMIUM PARA EL CÓDIGO OTP (6 CAJAS)*/
        /* ========================================= */
        .otp-container { gap: 10px; }
        .otp-box {
            width: 55px;
            height: 65px;
            font-size: 2rem;
            font-weight: 800;
            text-align: center;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            color: var(--dark);
            background-color: #f8f9fc;
            transition: all 0.2s ease-in-out;
            padding: 0;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .otp-box:focus {
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 51, 102, 0.15);
            outline: none;
            color: var(--primary);
            transform: translateY(-2px);
        }
        .otp-box.filled {
            border-color: var(--primary);
            background-color: #ffffff;
        }

        /* Ajuste para los botones del ojito de contraseña */
        .input-group .btn-outline-secondary { border-color: #dee2e6; color: #6c757d; background: transparent; }
        .input-group .btn-outline-secondary:hover { background: #f8f9fa; color: var(--primary); }
        .input-group .form-control:focus + .btn-outline-secondary { border-color: var(--primary); box-shadow: none; }
        
        /* Lista de requisitos de contraseña */
        .req-list { font-size: 0.85rem; margin-top: 10px; padding: 12px; background: #f8f9fc; border-radius: 8px; border: 1px dashed #e2e8f0; }
        .req-item { margin-bottom: 4px; transition: all 0.3s ease; color: #6c757d; }
        .req-item i { width: 16px; text-align: center; }
        .req-item.valid { color: #10b981; font-weight: 600; } 
        .req-item.invalid { color: #ef4444; } 
    </style>
</head>
<body>

<div class="split-container">
    <div class="left-panel">
        <div class="brand-logo animate-in" style="animation-delay: 0.1s;">TuLook<span>360</span></div>
        <p class="slogan animate-in" style="animation-delay: 0.2s;">
            Recupera el acceso a tu cuenta de forma rápida y segura.
        </p>
        <div class="feature-ads animate-in" style="animation-delay: 0.3s;">
            <div class="ad-item">
                <div class="ad-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="ad-text">
                    <strong>Seguridad Garantizada</strong>
                    Te enviaremos un código único de 6 dígitos a tu correo para validar tu identidad de forma privada.
                </div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <a href="<?= ruta_vista('login.php', [], false) ?>" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Login</a>

        <div class="form-section">
            <div class="form-container animate-in" style="animation-delay: 0.2s;">
                
                <div id="alertBox" class="alert d-none custom-alert" role="alert"></div>

                <div id="step1">
                    <div class="text-center mb-4">
                        <h2 class="fw-800 text-dark">¿Olvidaste tu contraseña?</h2>
                        <p class="text-muted small">Ingresa tu correo registrado y te enviaremos un código de seguridad de 6 dígitos.</p>
                    </div>
                    <form id="formPaso1">
                        <div class="mb-4 touched-field">
                            <label class="form-label text-uppercase opacity-75 small fw-bold">Correo Electrónico</label>
                            <div class="input-group shadow-sm-custom">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" id="inpEmail" class="form-control" placeholder="ejemplo@correo.com" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-main btn-next" id="btnPaso1">
                            ENVIAR CÓDIGO <i class="fa-solid fa-paper-plane ms-2"></i>
                        </button>
                    </form>
                </div>

                <div id="step2" class="d-none animate-in">
                    <div class="text-center mb-4">
                        <div class="icon-circle mx-auto mb-3" style="width: 70px; height: 70px; background: rgba(255, 51, 102, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary);">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                        <h2 class="fw-800 text-dark">Ingresa tu código</h2>
                        <p class="text-muted small">Hemos enviado un código a <br><strong id="showEmail" class="text-primary"></strong><br> Revisa tu bandeja de entrada o Spam.</p>
                    </div>
                    
                    <form id="formPaso2">
                        <div class="otp-container d-flex justify-content-center mb-4">
                            <input type="text" class="otp-box" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-box" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                        </div>
                        
                        <input type="hidden" name="codigo" id="inpCodigoReal">

                        <button type="submit" class="btn-main btn-next" id="btnPaso2">
                            VERIFICAR CÓDIGO <i class="fa-solid fa-shield-check ms-2"></i>
                        </button>
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-link text-muted small fw-bold text-decoration-none" onclick="volverPaso1()">
                                <i class="fa-solid fa-rotate-left me-1"></i> Escribí mal mi correo
                            </button>
                        </div>
                    </form>
                </div>

                <div id="step3" class="d-none animate-in">
                    <div class="text-center mb-4">
                        <div style="font-size: 3rem; color: #10b981; margin-bottom: 10px;">
                            <i class="fa-solid fa-unlock-keyhole"></i>
                        </div>
                        <h2 class="fw-800 text-dark">Nueva Contraseña</h2>
                        <p class="text-muted small">Hola <strong id="showName" class="text-dark"></strong>, crea tu nueva contraseña segura.</p>
                    </div>
                    <form id="formPaso3">
                        
                        <div class="mb-3 touched-field">
                            <label class="form-label text-uppercase opacity-75 small fw-bold">Contraseña</label>
                            <div class="input-group shadow-sm-custom">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="pass1" id="inpPass1" class="form-control border-end-0" placeholder="Escribe tu contraseña" required>
                                <button class="btn btn-outline-secondary border-start-0 toggle-pwd" type="button" tabindex="-1">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </button>
                            </div>
                            
                            <div class="req-list">
                                <div id="req-len" class="req-item invalid"><i class="fa-solid fa-circle-xmark"></i> Mínimo 8 caracteres</div>
                                <div id="req-up" class="req-item invalid"><i class="fa-solid fa-circle-xmark"></i> Al menos 1 MAYÚSCULA</div>
                                <div id="req-low" class="req-item invalid"><i class="fa-solid fa-circle-xmark"></i> Al menos 1 minúscula</div>
                                <div id="req-num" class="req-item invalid"><i class="fa-solid fa-circle-xmark"></i> Al menos 1 número</div>
                                <div id="req-spc" class="req-item invalid"><i class="fa-solid fa-circle-xmark"></i> 1 carácter especial (@, ., -, _, etc.)</div>
                            </div>
                        </div>

                        <div class="mb-4 touched-field">
                            <label class="form-label text-uppercase opacity-75 small fw-bold">Confirmar Contraseña</label>
                            <div class="input-group shadow-sm-custom">
                                <span class="input-group-text"><i class="fa-solid fa-shield"></i></span>
                                <input type="password" name="pass2" id="inpPass2" class="form-control border-end-0" placeholder="Repite la contraseña" required disabled>
                                <button class="btn btn-outline-secondary border-start-0 toggle-pwd" type="button" tabindex="-1">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </button>
                            </div>
                            <div id="match-msg" class="mt-2 small fw-bold d-none"></div>
                        </div>

                        <button type="submit" class="btn-main btn-next opacity-50" id="btnPaso3" disabled>
                            GUARDAR Y ENTRAR <i class="fa-solid fa-check-double ms-2"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    let codigoGuardado = '';

    // --- FUNCIONES DE ALERTA ---
    const showAlert = (msg, isSuccess) => {
        const alertBox = document.getElementById('alertBox');
        alertBox.className = `alert custom-alert d-flex align-items-center mb-4 animate-in ${isSuccess ? 'alert-success' : 'alert-danger'}`;
        alertBox.innerHTML = `<i class="fa-solid ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'} me-2 fs-5"></i><div>${msg}</div>`;
    };
    const hideAlert = () => document.getElementById('alertBox').classList.add('d-none');

    // ==========================================
    // LÓGICA DE LAS 6 CAJAS DE CÓDIGO (OTP)
    // ==========================================
    const otpBoxes = document.querySelectorAll('.otp-box');
    const inpCodigoReal = document.getElementById('inpCodigoReal');

    function actualizarCodigoOculto() {
        let codigo = '';
        otpBoxes.forEach(b => codigo += b.value);
        inpCodigoReal.value = codigo;
    }

    otpBoxes.forEach((box, index) => {
        // Evento al Escribir
        box.addEventListener('input', (e) => {
            box.value = box.value.replace(/[^0-9]/g, ''); // Solo números
            if (box.value !== '') {
                box.classList.add('filled');
                if (index < otpBoxes.length - 1) otpBoxes[index + 1].focus();
            } else {
                box.classList.remove('filled');
            }
            actualizarCodigoOculto();
        });

        // Evento al borrar (Backspace)
        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && box.value === '') {
                if (index > 0) {
                    otpBoxes[index - 1].focus();
                    otpBoxes[index - 1].value = '';
                    otpBoxes[index - 1].classList.remove('filled');
                }
                actualizarCodigoOculto();
            }
        });

        // Evento al Pegar (Paste) un código de 6 dígitos
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            let pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            if (pasteData) {
                for (let i = 0; i < otpBoxes.length; i++) {
                    if (i >= index && pasteData[i - index]) {
                        otpBoxes[i].value = pasteData[i - index];
                        otpBoxes[i].classList.add('filled');
                        if (i < otpBoxes.length - 1) otpBoxes[i + 1].focus();
                        else otpBoxes[i].focus();
                    }
                }
                actualizarCodigoOculto();
            }
        });
    });

    window.volverPaso1 = () => {
        hideAlert();
        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step1').classList.remove('d-none');
        // Limpiar cajas
        otpBoxes.forEach(b => { b.value = ''; b.classList.remove('filled'); });
        inpCodigoReal.value = '';
    };

    // --- MOSTRAR/OCULTAR CONTRASEÑA (OJITOS) ---
    document.querySelectorAll('.toggle-pwd').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling; 
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
                icon.classList.replace('text-muted', 'text-primary');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
                icon.classList.replace('text-primary', 'text-muted');
            }
        });
    });

    // --- VALIDACIÓN DE CONTRASEÑA EN TIEMPO REAL ---
    const pass1 = document.getElementById('inpPass1');
    const pass2 = document.getElementById('inpPass2');
    const btnSubmit = document.getElementById('btnPaso3');
    const matchMsg = document.getElementById('match-msg');

    const rules = [
        { id: 'req-len', regex: /.{8,}/ },                 // Mínimo 8
        { id: 'req-up', regex: /[A-Z]/ },                  // Mayúscula
        { id: 'req-low', regex: /[a-z]/ },                 // Minúscula
        { id: 'req-num', regex: /[0-9]/ },                 // Número
        { id: 'req-spc', regex: /[^A-Za-z0-9]/ }           // Especial (incluye punto)
    ];

    function checkPassword() {
        let val1 = pass1.value;
        let val2 = pass2.value;
        let allRulesMet = true;

        // Evaluar reglas
        rules.forEach(rule => {
            const el = document.getElementById(rule.id);
            const icon = el.querySelector('i');
            
            if (rule.regex.test(val1)) {
                el.classList.replace('invalid', 'valid');
                icon.className = 'fa-solid fa-circle-check text-success';
            } else {
                el.classList.replace('valid', 'invalid');
                icon.className = 'fa-solid fa-circle-xmark text-danger';
                allRulesMet = false;
            }
        });

        pass2.disabled = !allRulesMet;
        if (!allRulesMet) { pass2.value = ''; val2 = ''; }

        let isMatch = false;
        if (val2.length > 0) {
            matchMsg.classList.remove('d-none');
            if (val1 === val2) {
                matchMsg.innerHTML = '<span class="text-success"><i class="fa-solid fa-check-double me-1"></i> Las contraseñas coinciden perfectamente</span>';
                isMatch = true;
            } else {
                matchMsg.innerHTML = '<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Las contraseñas no coinciden</span>';
            }
        } else {
            matchMsg.classList.add('d-none');
        }

        // Habilitar botón Final
        if (allRulesMet && isMatch) {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('opacity-50');
        } else {
            btnSubmit.disabled = true;
            btnSubmit.classList.add('opacity-50');
        }
    }

    pass1.addEventListener('input', checkPassword);
    pass2.addEventListener('input', checkPassword);

    // ==========================================
    // PETICIONES AJAX
    // ==========================================

    // PASO 1: ENVIAR CORREO
    document.getElementById('formPaso1').addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();
        const btn = document.getElementById('btnPaso1');
        btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> PROCESANDO...';

        fetch('<?= ruta_accion("auth", "solicitarRecuperacionAjax", [], false) ?>', {
            method: 'POST', body: new FormData(this)
        }).then(r => r.json()).then(data => {
            if(data.success) {
                document.getElementById('showEmail').innerText = document.getElementById('inpEmail').value;
                document.getElementById('step1').classList.add('d-none');
                document.getElementById('step2').classList.remove('d-none');
                setTimeout(() => otpBoxes[0].focus(), 500); // Foco en la 1ra caja
            } else {
                showAlert(data.msg || data.message, false);
            }
        }).catch(() => showAlert("Error de conexión.", false))
        .finally(() => { btn.disabled = false; btn.innerHTML = 'ENVIAR CÓDIGO <i class="fa-solid fa-paper-plane ms-2"></i>'; });
    });

    // PASO 2: VERIFICAR CÓDIGO
    document.getElementById('formPaso2').addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();
        
        if(inpCodigoReal.value.length < 6) {
            showAlert("Por favor, ingresa los 6 dígitos del código.", false);
            return;
        }

        const btn = document.getElementById('btnPaso2');
        btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> VERIFICANDO...';

        fetch('<?= ruta_accion("auth", "verificarCodigoRecuperacionAjax", [], false) ?>', {
            method: 'POST', body: new FormData(this)
        }).then(r => r.json()).then(data => {
            if(data.success) {
                codigoGuardado = inpCodigoReal.value;
                document.getElementById('showName').innerText = data.nombre;
                document.getElementById('step2').classList.add('d-none');
                document.getElementById('step3').classList.remove('d-none');
                setTimeout(() => document.getElementById('inpPass1').focus(), 500);
            } else {
                showAlert(data.msg || data.message, false);
            }
        }).catch(() => showAlert("Error de conexión.", false))
        .finally(() => { btn.disabled = false; btn.innerHTML = 'VERIFICAR CÓDIGO <i class="fa-solid fa-shield-check ms-2"></i>'; });
    });

    // PASO 3: GUARDAR NUEVA CONTRASEÑA
    document.getElementById('formPaso3').addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();
        const btn = document.getElementById('btnPaso3');
        btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> ENCRIPTANDO...';

        const formData = new FormData(this);
        formData.append('codigo', codigoGuardado);

        fetch('<?= ruta_accion("auth", "guardarNuevaPasswordAjax", [], false) ?>', {
            method: 'POST', body: formData
        }).then(r => r.json()).then(data => {
            if(data.success) {
                showAlert("¡ÉXITO! Tu cuenta ha sido asegurada y desbloqueada.", true);
                btn.className = "btn-main btn-next bg-success border-success text-white";
                btn.innerHTML = '<i class="fa-solid fa-unlock text-white me-2"></i> REDIRIGIENDO AL LOGIN...';
                setTimeout(() => window.location.href = data.redirect, 2500);
            } else {
                showAlert(data.msg || data.message, false);
                btn.disabled = false; btn.innerHTML = 'GUARDAR Y ENTRAR <i class="fa-solid fa-check-double ms-2"></i>';
            }
        }).catch(() => {
            showAlert("Error del servidor.", false);
            btn.disabled = false; btn.innerHTML = 'GUARDAR Y ENTRAR <i class="fa-solid fa-check-double ms-2"></i>';
        });
    });
});
</script>

</body>
</html>