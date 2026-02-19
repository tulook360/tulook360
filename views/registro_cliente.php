<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Crear Cuenta | TuLook360' ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Kalam:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { --primary: #ff3366; --dark: #1e272e; --success: #00b894; --error: #dc3545; }
        body { font-family: 'Outfit', sans-serif; background-color: #ffffff; min-height: 100vh; overflow-x: hidden; overflow-y: auto; }

        /* --- ANIMACIONES --- */
        @keyframes slideInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .animate-in { animation: slideInRight 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards; }
        .step-exit { animation: slideInLeft 0.4s ease-in reverse forwards; }

        .split-container { display: flex; min-height: 100vh; }

        /* --- PANEL IZQUIERDO (CON VIDA) --- */
        .left-panel {
            flex: 0.8;
            background: linear-gradient(135deg, rgba(30,39,46,0.95) 0%, rgba(30,39,46,0.8) 100%), 
                        url('https://images.unsplash.com/photo-1560066984-138dadb4c035?q=80&w=1974&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: white; padding: 40px; position: relative;
        }
        
        /* Elementos decorativos para que no se vea seco */
        .left-panel::before {
            content: ''; position: absolute; top: -10%; right: -10%; width: 300px; height: 300px;
            background: var(--primary); filter: blur(150px); opacity: 0.2; border-radius: 50%;
        }

        .brand-logo { font-family: 'Kalam'; font-size: 3rem; margin-bottom: 10px; z-index: 1; }
        .brand-logo span { color: var(--primary); }
        .left-panel p.slogan { opacity: 0.9; font-size: 1.1rem; text-align: center; max-width: 350px; font-weight: 300; z-index: 1; margin-bottom: 30px; }

        .user-preview-card {
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2); border-radius: 30px; padding: 30px;
            width: 100%; max-width: 300px; color: white; text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3); z-index: 1;
        }
        .preview-avatar {
            width: 100px; height: 100px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;
            border: 4px solid var(--primary); color: #ccc; font-size: 3rem; overflow: hidden;
        }
        .preview-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .preview-name { font-weight: 800; font-size: 1.4rem; margin-bottom: 5px; }

        /* --- PANEL DERECHO (70% Form / 30% Social) --- */
        .right-panel { flex: 1.2; display: flex; flex-direction: column; background: #fff; min-height: 100vh; position: relative; }

        .btn-back { position: absolute; top: 25px; left: 40px; color: #aaa; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.3s; z-index: 10; }
        .btn-back:hover { color: var(--primary); transform: translateX(-5px); }

        /* Sección Formulario (70%) */
        .form-section { flex: 0.7; display: flex; flex-direction: column; justify-content: center; padding: 20px 80px; }
        .form-container { width: 100%; max-width: 550px; margin: 0 auto; }
        
        /* Steps Indicator */
        .steps-indicator { display: flex; align-items: center; justify-content: center; margin-bottom: 35px; position: relative; }
        .step-line { position: absolute; top: 18px; width: 80px; height: 2px; background: #eee; z-index: 0; }
        .step-item { z-index: 1; display: flex; flex-direction: column; align-items: center; margin: 0 40px; }
        .step-dot { width: 38px; height: 38px; border-radius: 50%; background: #fff; border: 2px solid #eee; color: #aaa; display: flex; align-items: center; justify-content: center; font-weight: 700; transition: 0.4s; margin-bottom: 5px; }
        .step-item.active .step-dot { border-color: var(--primary); background: var(--primary); color: white; transform: scale(1.1); box-shadow: 0 5px 15px rgba(255, 51, 102, 0.2); }
        .step-item.completed .step-dot { border-color: var(--success); background: var(--success); color: white; }
        .step-label { font-size: 0.7rem; font-weight: 800; color: #bbb; text-transform: uppercase; letter-spacing: 1px; }
        .step-item.active .step-label { color: var(--primary); }

        /* Estilos Inputs */
        .form-label { font-weight: 700; font-size: 0.8rem; color: var(--dark); margin-bottom: 6px; }
        .input-group-text { background: #fbfbfb; border: 2px solid #eee; border-right: none; color: #ccc; border-radius: 12px 0 0 12px; }
        .form-control { padding: 12px; border-radius: 0 12px 12px 0; border: 2px solid #eee; border-left: none; background: #fbfbfb; font-weight: 500; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); background: #fff; box-shadow: none; }
        .input-group:focus-within .input-group-text { border-color: var(--primary); color: var(--primary); }

        /* Validación */
        .touched .is-invalid ~ .invalid-feedback { display: block; font-weight: 600; font-size: 0.75rem; }
        .touched .input-group.is-invalid .input-group-text, .touched .input-group.is-invalid .form-control { border-color: var(--error); background: #fff8f8; }

        /* --- DISEÑO DE ZONA DE CARGA DE FOTO --- */
        /* Diseño COMPACTO de zona de carga */
        .photo-upload-label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 12px; border: 2px dashed #e0e0e0; border-radius: 18px; 
            cursor: pointer; transition: all 0.3s ease; background: #fafafa;
            min-height: 100px; /* Bajamos de 150px a 100px */
            width: 100%; margin-bottom: 10px;
        }
        .photo-upload-label:hover { 
            border-color: var(--primary); background: rgba(255, 51, 102, 0.03); 
        }
        .upload-icon-circle {
            width: 40px; height: 40px; /* Bajamos de 55px a 40px */
            background: rgba(255, 51, 102, 0.1);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin-bottom: 5px; color: var(--primary); font-size: 1.2rem; transition: 0.3s;
        }
        .photo-upload-label:hover .upload-icon-circle {
            background: var(--primary); color: white; transform: scale(1.1);
        }

        /* Sección Social (30%) */
        .social-section { flex: 0.3; background: #fcfcfc; border-top: 1px solid #f0f0f0; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; }
        .social-btns { display: flex; gap: 15px; margin-top: 15px; }
        .social-btn { width: 50px; height: 50px; border-radius: 50%; border: 1px solid #eee; background: white; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; transition: 0.3s; color: #555; }
        .social-btn:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); color: var(--primary); border-color: var(--primary); }

        .btn-main { width: 100%; padding: 15px; border-radius: 12px; font-weight: 800; border: none; transition: 0.3s; margin-top: 10px; }
        .btn-next { background: var(--primary); color: white; box-shadow: 0 10px 20px rgba(255, 51, 102, 0.2); }
        .btn-next:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }
        .btn-next:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(255, 51, 102, 0.3); }

        @media (max-width: 992px) {
            /* Evitamos cualquier desborde global */
            body, html { overflow-x: hidden; position: relative; width: 100%; }
            
            .split-container { flex-direction: column; min-height: 100vh; width: 100%; }
            .left-panel { flex: none; padding: 40px 20px; width: 100%; }
            
            /* Ajuste del panel derecho para que no empuje hacia los lados */
            .right-panel { 
                flex: none; 
                width: 100%;
                height: auto; 
                min-height: auto; 
                padding: 20px 0; 
                overflow-x: hidden; /* Candado de seguridad lateral */
            }

            .form-section { padding: 20px 15px; width: 100%; }
            .form-container { width: 100%; max-width: 100%; }

            /* --- EL ERROR ESTABA AQUÍ: Reducimos márgenes de los pasos --- */
            .step-item { margin: 0 15px; } /* Bajamos de 40px a 15px para que quepan */
            .step-line { width: 50px; } /* Acortamos la línea de unión */

            .user-preview-card { display: none; }
            .btn-back { top: 15px; left: 15px; }
            
            /* Ajustamos el padding de las filas para que no desborden */
            .row { margin-left: 0; margin-right: 0; }
            .row > * { padding-left: 5px; padding-right: 5px; }
        }

        /* --- DISEÑO DEL MODAL PREMIUM --- */
        .modal-content { border: none; border-radius: 30px; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.2); }
        .modal-header-custom { 
            background: linear-gradient(45deg, var(--primary), #ff6b81); 
            padding: 40px 20px; text-align: center; color: white; position: relative;
        }
        .modal-header-custom .brand-name { font-family: 'Kalam'; font-size: 2.2rem; }
        .modal-icon-box {
            width: 80px; height: 80px; background: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: -40px auto 15px; position: relative; z-index: 2;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1); color: var(--primary); font-size: 2.5rem;
        }
        .modal-body { padding: 30px 40px; text-align: center; }
        .modal-body h3 { font-weight: 800; color: var(--dark); margin-bottom: 10px; }
        .modal-body p { color: #666; font-size: 0.95rem; }
        .btn-modal-confirm {
            background: var(--dark); color: white; border-radius: 15px;
            padding: 12px 30px; font-weight: 700; border: none; width: 100%;
            transition: 0.3s; margin-top: 15px;
        }
        .btn-modal-confirm:hover { background: #000; transform: scale(1.02); }

        /* Fuerza al modal a mostrarse manualmente sin el JS de Bootstrap */
        .modal.manual-show { 
            display: block !important; 
            background: rgba(0,0,0,0.7); 
            backdrop-filter: blur(5px);
            opacity: 1 !important;
        }
        .modal.manual-show .modal-dialog {
            transform: none !important;
        }
    </style>
</head>
<body>

<div class="split-container">
    <div class="left-panel">
        <div class="brand-logo">TuLook<span>360</span></div>
        <p class="slogan">Gestiona tu belleza, agenda tus citas y luce espectacular con un solo clic.</p>
        
        <div class="user-preview-card animate-in">
            <div class="preview-avatar" id="avatarPreview"><i class="fa-solid fa-user"></i></div>
            <div class="preview-name" id="namePreview">Tu Nombre</div>
            <div class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold" style="font-size:0.7rem">CLIENTE VIP</div>
            <div class="mt-4 d-flex justify-content-around opacity-75 small">
                <span><i class="fa-solid fa-calendar"></i><br>Citas</span>
                <span><i class="fa-solid fa-star"></i><br>Puntos</span>
                <span><i class="fa-solid fa-heart"></i><br>Favoritos</span>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <a href="index.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Inicio</a>

        <div class="form-section">
            <div class="form-container">
                <div class="text-center mb-4">
                    <h2 class="fw-800">Crear Cuenta</h2>
                    <p class="text-muted small">Únete a la red de belleza más grande del país.</p>
                </div>

                <div class="steps-indicator">
                    <div class="step-line"></div>
                    <div class="step-item active" id="dot1">
                        <div class="step-dot">1</div>
                        <div class="step-label">Identidad</div>
                    </div>
                    <div class="step-item" id="dot2">
                        <div class="step-dot">2</div>
                        <div class="step-label">Acceso</div>
                    </div>
                </div>

                <form id="wizardForm" enctype="multipart/form-data">
                    <div id="step1Content" class="animate-in">
                        <div class="mb-3">
                            <label class="form-label text-uppercase opacity-75 small" style="font-size: 0.7rem;">Foto de Perfil</label>
                            <label for="inputFoto" class="photo-upload-label" id="photoLabel">
                                <div class="upload-icon-circle">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <div class="text-center">
                                    <span class="d-block fw-bold text-dark" style="font-size: 0.8rem;" id="photoText">Subir imagen</span>
                                </div>
                            </label>
                            <input type="file" name="foto_perfil" id="inputFoto" accept="image/jpeg, image/png" hidden>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 touched-field" id="f_nom">
                                <label class="form-label">NOMBRES *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" name="nombres" id="iNom" class="form-control" placeholder="Juan Carlos">
                                </div>
                                <div class="invalid-feedback">Ingresa al menos 3 letras.</div>
                            </div>
                            <div class="col-md-6 touched-field" id="f_ape">
                                <label class="form-label">APELLIDOS *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-signature"></i></span>
                                    <input type="text" name="apellidos" id="iApe" class="form-control" placeholder="Pérez">
                                </div>
                                <div class="invalid-feedback">Ingresa tus apellidos.</div>
                            </div>
                        </div>

                        <div class="mt-3 touched-field" id="f_ced">
                            <label class="form-label">CÉDULA ECUATORIANA *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" name="cedula" id="iCed" class="form-control" placeholder="0999999999" maxlength="10">
                            </div>
                            <div class="invalid-feedback">La cédula no es válida para Ecuador.</div>
                        </div>

                        <button type="button" class="btn-main btn-next" id="btnGoStep2" disabled>CONTINUAR <i class="fa-solid fa-chevron-right ms-2"></i></button>
                    </div>

                    <div id="step2Content" style="display: none;">
                        <div class="mb-3 touched-field" id="f_em">
                            <label class="form-label">CORREO ELECTRÓNICO *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" id="iEmail" class="form-control" placeholder="tu@correo.com">
                            </div>
                            <div class="invalid-feedback" id="emMsg">Correo inválido o ya en uso.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 touched-field" id="f_p1">
                                <label class="form-label">CONTRASEÑA *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                    <input type="password" name="password" id="iP1" class="form-control" placeholder="******">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePass('iP1', this)" style="background:#fbfbfb; border-color:#eee; border-radius:0 12px 12px 0;"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6 touched-field" id="f_p2">
                                <label class="form-label">CONFIRMAR *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-shield"></i></span>
                                    <input type="password" id="iP2" class="form-control" placeholder="******">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePass('iP2', this)" style="background:#fbfbfb; border-color:#eee; border-radius:0 12px 12px 0;"><i class="fa-solid fa-eye"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-main btn-next" id="btnSubmit" disabled>REGISTRARME AHORA <i class="fa-solid fa-check-double ms-2"></i></button>
                        <button type="button" class="btn-main btn-prev" onclick="goToStep(1)" style="color:#aaa; font-size:0.9rem">Volver al paso anterior</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="social-section">
            <!-- <span class="text-muted small fw-bold">O ÚNETE RÁPIDAMENTE CON</span>
            <div class="social-btns">
                <a href="#" class="social-btn"><i class="fa-brands fa-google"></i></a>
                <a href="#" class="social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="social-btn"><i class="fa-brands fa-apple"></i></a>
            </div> -->
            <p class="mt-3 small text-muted">¿Ya eres miembro? <a href="<?= ruta_vista('login.php', [], false) ?>" class="text-primary fw-bold text-decoration-none">Inicia Sesión</a></p>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom">
                <div class="brand-name">TuLook<span>360</span></div>
            </div>
            <div class="modal-icon-box" id="modalIcon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="modal-body">
                <h3 id="modalTitle">¡Bienvenido a bordo!</h3>
                <p id="modalMessage">Tu cuenta ha sido creada con éxito. Prepárate para vivir una nueva experiencia en belleza.</p>
                <button type="button" class="btn-modal-confirm" id="btnCerrarModal">COMENZAR AHORA</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- VALIDACIÓN DE CÉDULA ECUATORIANA (ALGORITMO REAL) ---
    function validarCedulaEcuatoriana(cedula) {
        if (cedula.length !== 10) return false;
        const provincia = parseInt(cedula.substring(0, 2));
        if (provincia < 1 || provincia > 24) return false;
        const tercerDigito = parseInt(cedula[2]);
        if (tercerDigito > 5) return false;

        const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        const verificador = parseInt(cedula[9]);
        let suma = 0;

        for (let i = 0; i < 9; i++) {
            let valor = parseInt(cedula[i]) * coeficientes[i];
            if (valor > 9) valor -= 9;
            suma += valor;
        }

        const total = (Math.ceil(suma / 10) * 10);
        let digitoValidado = total - suma;
        if (digitoValidado === 10) digitoValidado = 0;

        return digitoValidado === verificador;
    }

    // --- MANEJO DE PASOS ---
    function goToStep(s) {
        const s1 = document.getElementById('step1Content'), s2 = document.getElementById('step2Content');
        const d1 = document.getElementById('dot1'), d2 = document.getElementById('dot2');
        if(s === 2) {
            s1.style.display = 'none'; s2.style.display = 'block'; s2.classList.add('animate-in');
            d1.classList.add('completed'); d2.classList.add('active');
        } else {
            s2.style.display = 'none'; s1.style.display = 'block'; s1.classList.add('animate-in');
            d1.classList.remove('completed'); d2.classList.remove('active');
        }
    }

    document.getElementById('btnGoStep2').onclick = () => goToStep(2);

    // --- VALIDACIÓN DINÁMICA ---
    const iNom = document.getElementById('iNom'), iApe = document.getElementById('iApe'), iCed = document.getElementById('iCed');
    const btn2 = document.getElementById('btnGoStep2');

    function checkStep1() {
        const vNom = iNom.value.trim().length >= 3;
        const vApe = iApe.value.trim().length >= 3;
        const vCed = validarCedulaEcuatoriana(iCed.value);

        // UI Feedback (Solo si hay contenido o perdió foco)
        if(iNom.value) applyClass('f_nom', vNom);
        if(iApe.value) applyClass('f_ape', vApe);
        if(iCed.value.length === 10) applyClass('f_ced', vCed);

        btn2.disabled = !(vNom && vApe && vCed);
        document.getElementById('namePreview').innerText = (iNom.value + ' ' + iApe.value).trim() || 'Tu Nombre';
    }

    function applyClass(id, valid) {
        const el = document.getElementById(id);
        el.classList.add('touched');
        const group = el.querySelector('.input-group');
        group.classList.toggle('is-invalid', !valid);
        group.classList.toggle('is-valid', valid);
    }

    iNom.oninput = checkStep1; iApe.oninput = checkStep1; 
    iCed.oninput = function() { this.value = this.value.replace(/\D/g,''); checkStep1(); };

    // --- PASO 2 ---
    const iEm = document.getElementById('iEmail'), iP1 = document.getElementById('iP1'), iP2 = document.getElementById('iP2');
    const btnSub = document.getElementById('btnSubmit');
    let emailOk = false;

    function checkStep2() {
        const vP1 = iP1.value.length >= 6;
        const vP2 = iP2.value === iP1.value && vP1;
        
        if(iP1.value) applyClass('f_p1', vP1);
        if(iP2.value) applyClass('f_p2', vP2);
        
        btnSub.disabled = !(emailOk && vP1 && vP2);
    }

    iP1.oninput = checkStep2; iP2.oninput = checkStep2;
    iEm.onblur = function() {
        const email = this.value.trim();
        if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailOk = false; applyClass('f_em', false); checkStep2(); return;
        }
        fetch(`<?= ruta_accion("auth", "verificar_correo_ajax", [], false) ?>&email=${email}`)
        .then(r => r.json()).then(data => {
            emailOk = !data.existe;
            applyClass('f_em', emailOk);
            if(data.existe) document.getElementById('emMsg').innerText = "Este correo ya está en uso.";
            checkStep2();
        });
    };

    function togglePass(id, btn) {
        const i = document.getElementById(id);
        i.type = i.type === "password" ? "text" : "password";
        btn.querySelector('i').classList.toggle('fa-eye');
        btn.querySelector('i').classList.toggle('fa-eye-slash');
    }

    // Foto Preview
    document.getElementById('inputFoto').onchange = (e) => {
        const file = e.target.files[0];
        if(file) {
            // Recortar nombre si es muy largo
            const fileName = file.name.length > 25 ? file.name.substring(0, 22) + "..." : file.name;
            document.getElementById('photoText').innerHTML = `<i class="fa-solid fa-check-circle text-success me-1"></i> ${fileName}`;
            document.getElementById('photoText').style.color = "var(--primary)";
            
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('avatarPreview').innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
            }
            reader.readAsDataURL(file);
        }
    };

    // Submit
    // --- ENVÍO CON MODAL PREMIUM ---
    // --- MANEJO MANUAL DEL MODAL (SIN LIBRERÍAS EXTERNAS) ---
    const modalEl = document.getElementById('modalConfirmacion');
    const btnCerrar = document.getElementById('btnCerrarModal');

    function mostrarMiModal(exito, titulo, mensaje) {
        // 1. Cambiar Icono
        const iconDiv = document.getElementById('modalIcon');
        if (exito) {
            iconDiv.innerHTML = '<i class="fa-solid fa-circle-check" style="color: var(--success)"></i>';
        } else {
            iconDiv.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: var(--error)"></i>';
        }

        // 2. Cambiar Textos
        document.getElementById('modalTitle').innerText = titulo;
        document.getElementById('modalMessage').innerText = mensaje;

        // 3. Mostrar Modal (Clase manual)
        modalEl.classList.add('manual-show');
    }

    // Cerrar modal
    btnCerrar.onclick = function() {
        modalEl.classList.remove('manual-show');
    };

    // --- ENVÍO DEL FORMULARIO ---
    document.getElementById('wizardForm').onsubmit = function(e) {
        e.preventDefault(); // AHORA SÍ va a funcionar y no te mandará al index
        
        const btnReg = document.getElementById('btnSubmit');
        btnReg.disabled = true;
        btnReg.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Procesando...';

        fetch('<?= ruta_accion("auth", "guardarCliente", [], false) ?>', { 
            method: 'POST', 
            body: new FormData(this) 
        })
        .then(r => {
            if (!r.ok) throw new Error("Error en el servidor");
            return r.json();
        })
        .then(d => {
            if(d.success) {
                // Configurar botón para ir al panel
                btnCerrar.innerText = "COMENZAR AHORA";
                btnCerrar.onclick = () => { window.location.href = d.redirect; };
                
                mostrarMiModal(true, '¡Registro Exitoso!', 'Tu cuenta ha sido creada. ¡Bienvenido!');
            } else {
                // Mostrar el error que viene del Controlador
                btnReg.disabled = false;
                btnReg.innerHTML = 'REGISTRARME AHORA <i class="fa-solid fa-check-double ms-2"></i>';
                
                btnCerrar.innerText = "CORREGIR DATOS";
                mostrarMiModal(false, 'Algo salió mal', d.msg || 'Revisa la información.');
            }
        })
        .catch(err => {
            console.error(err);
            btnReg.disabled = false;
            btnReg.innerHTML = 'REGISTRARME AHORA <i class="fa-solid fa-check-double ms-2"></i>';
            mostrarMiModal(false, 'Error Crítico', 'No hay conexión con el servidor.');
        });
    };
</script>
</body>
</html>