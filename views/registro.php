<?php
/**
 * VISTA: REGISTRO DE NEGOCIO (MULTI-STEP / WIZARD)
 * Estilo: Original (Colores corporativos)
 * Lógica: Formulario dividido en 3 pasos secuenciales.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | TuLook360</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root { 
            --primary: #ff3366; 
            --dark: #1e272e; 
            --gray-light: #f5f6fa; 
            --text-gray: #636e72;
            --border: #dfe6e9;
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #fff; height: 100vh; overflow: hidden; display: flex; }

        /* --- 1.1 Layout Pantalla Dividida --- */
        .reg-container { display: flex; width: 100%; height: 100%; }
        
        /* Panel Izquierdo (Marketing) */
        .reg-side {
            width: 35%;
            background: var(--dark);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            background-image: radial-gradient(#2d3436 1px, transparent 1px);
            background-size: 20px 20px;
        }

        /* Tarjeta Live (Estilo Original) */
        .live-card {
            background: #fff; width: 280px; padding: 25px; border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3); text-align: center; color: var(--dark);
            transition: 0.3s; transform: scale(0.95); opacity: 0.8;
        }
        .live-card.active { transform: scale(1); opacity: 1; }
        .lc-logo { 
            width: 90px; height: 90px; margin: 0 auto 15px; border-radius: 50%; background: #f1f2f6; 
            display: flex; align-items: center; justify-content: center; overflow: hidden; border: 3px solid var(--primary);
        }
        .lc-logo img { width: 100%; height: 100%; object-fit: cover; }
        .lc-logo i { font-size: 2rem; color: #b2bec3; }

        /* Panel Derecho (Formulario) */
        .reg-form-area {
            width: 65%;
            background: #fff;
            display: flex;
            flex-direction: column;
            padding: 40px 80px;
            overflow-y: auto;
        }

        /* --- 1.2 Stepper (Barra de Progreso) MEJORADO --- */
        .stepper-wrapper {
            display: flex; justify-content: space-between; margin-bottom: 40px; position: relative;
        }
        /* Eliminamos el ::before antiguo que era una sola línea gris */
        
        .step-item {
            position: relative; z-index: 2; text-align: center; flex: 1;
        }

        /* LÍNEA CONECTORA: Cada item dibuja una línea a su derecha */
        .step-item:not(:last-child)::after {
            content: ''; position: absolute; top: 17.5px; left: 50%; width: 100%; height: 3px;
            background: #eee; z-index: -1; transition: all 0.5s ease; /* Transición suave */
        }

        /* CÍRCULO DEL PASO */
        .step-circle {
            width: 35px; height: 35px; background: #fff; border: 2px solid #ddd; border-radius: 50%;
            margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;
            font-weight: 600; color: #ddd; transition: all 0.5s ease; position: relative;
        }
        .step-text { 
            font-size: 0.8rem; color: #ccc; font-weight: 500; text-transform: uppercase; 
            transition: all 0.5s ease;
        }
        
        /* --- ESTADOS --- */

        /* Activo (Paso actual) */
        .step-item.active .step-circle { 
            border-color: var(--primary); background: var(--primary); color: #fff; 
            box-shadow: 0 0 0 4px rgba(255, 51, 102, 0.1); /* Pequeño resplandor */
        }
        .step-item.active .step-text { color: var(--primary); font-weight: 700; }

        /* Completado (Pasos anteriores) */
        .step-item.completed .step-circle { 
            background: var(--primary); border-color: var(--primary); color: #fff;
        }
        /* Ocultamos el número y mostramos el icono CHECK */
        .step-item.completed .step-circle span { display: none; }
        .step-item.completed .step-circle::before {
            content: '\f00c'; /* Icono de check de FontAwesome */
            font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 1rem;
        }
        .step-item.completed .step-text { color: var(--primary); }
        
        /* Coloreamos la línea que sale de un paso completado */
        .step-item.completed::after { background: var(--primary); }

        /* --- 1.3 Inputs Estilo Original (Con Iconos) --- */
        .input-group { margin-bottom: 20px; }
        .input-label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: var(--dark); }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #b2bec3; }
        .input-box {
            width: 100%; padding: 12px 15px 12px 45px; /* Padding izquierdo para el icono */
            border: 1px solid var(--border); border-radius: 50px; background: #fdfdfd;
            font-family: 'Poppins', sans-serif; font-size: 0.95rem; transition: 0.3s;
        }
        .input-box:focus { border-color: var(--primary); background: #fff; box-shadow: 0 5px 15px rgba(255, 51, 102, 0.1); outline: none; }
        /* --- Layout de 2 Columnas (Desktop) --- */
        .input-row { display: flex; gap: 20px; width: 100%; }
        .input-col { flex: 1; position: relative; } /* Flex 1 hace que ocupen 50% cada uno */

        /* Icono Ver Contraseña */
        .toggle-pass {
            position: absolute;
            right: 15px;
            top: 50%; /* Centrado vertical exacto */
            transform: translateY(-50%); 
            cursor: pointer;
            color: #b2bec3;
            z-index: 10;
            padding: 5px; /* Área de click más grande */
        }
        .toggle-pass:hover { color: var(--primary); }

        /* Mensajes de Error */
        .error-msg {
            color: #e74c3c; /* Rojo alerta */
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 5px;
            display: none; /* Oculto por defecto */
            animation: fadeIn 0.3s;
        }
        .error-msg.visible { display: block; }

        /* Estado de Input con Error */
        .input-box.input-error {
            border-color: #e74c3c;
            background: #fff5f5;
            color: #c0392b;
        }

        /* Responsive: En móvil vuelve a ser 1 columna */
        @media (max-width: 768px) {
            .input-row { flex-direction: column; gap: 0; }
            .input-col { margin-bottom: 0; }
        }


        /* --- Estilos para el Upload de Archivo Moderno --- */
        .upload-area { position: relative; }
        
        .upload-box {
            border: 2px dashed var(--border); /* Borde punteado elegante */
            background: #fdfdfd;
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-gray);
        }

        .upload-box:hover {
            border-color: var(--primary);
            background: #fff0f3; /* Fondo rosado muy suave al pasar mouse */
            color: var(--primary);
        }

        .upload-box.file-selected {
            border-style: solid;
            border-color: #00b894; /* Verde éxito */
            background: #e6fffa;
            color: #00b894;
        }

        .upload-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            transition: 0.3s;
        }


        /* Grid de Tipos (Ajustado para textos largos) */
        .type-grid { 
            display: grid; 
            /* Aumentamos a 135px el ancho mínimo para dar más aire */
            grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); 
            gap: 15px; 
        }
        
        .type-card {
            border: 1px solid var(--border); border-radius: 15px; 
            padding: 15px 5px; /* Reducimos padding lateral */
            text-align: center;
            cursor: pointer; transition: 0.3s;
            background: #fff;
            
            /* TRUCO DE ALINEACIÓN: */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 140px; /* Forzamos que todos sean cuadrados iguales */
        }
        
        .type-card:hover, .type-card.selected { 
            border-color: var(--primary); background: #fff0f3; 
            color: var(--primary); transform: translateY(-5px); 
        }

        /* Nuevo estilo para el texto dentro de la tarjeta */
        .type-text {
            font-weight: 600;
            font-size: 0.8rem; /* Letra un poco más pequeña para que entre */
            line-height: 1.2;
            margin-top: 10px;
            /* Reservamos espacio para 2 líneas para que todo se alinee */
            height: 32px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }

        /* Botones de Navegación */
        .nav-buttons { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn-nav {
            padding: 12px 30px; border-radius: 50px; border: none; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .btn-prev { background: #f1f2f6; color: var(--text-gray); }
        .btn-next { background: var(--primary); color: #fff; box-shadow: 0 10px 20px rgba(255, 51, 102, 0.2); }
        .btn-next:hover { transform: translateY(-3px); }

        /* Ocultar pasos no activos */
        .form-step { display: none; animation: fadeIn 0.5s; }
        .form-step.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Mobile */
        @media (max-width: 900px) {
            .reg-container { flex-direction: column; overflow-y: auto; }
            .reg-side { display: none; }
            .reg-form-area { width: 100%; padding: 20px; }
        }


        /* --- 1.4 MODAL NATIVO (Estilo TuLook360) --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(30, 39, 46, 0.6); /* Oscuro con transparencia */
            z-index: 9999;
            display: none; /* Oculto por defecto */
            align-items: center; justify-content: center;
            backdrop-filter: blur(8px); /* Efecto borroso moderno */
        }
        
        .modal-overlay.active { display: flex; animation: fadeIn 0.3s forwards; }
        
        .modal-box {
            background: #fff;
            width: 90%; max-width: 420px;
            padding: 40px 30px;
            border-radius: 30px; /* Bordes muy redondeados como tus inputs */
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            transform: scale(0.8); opacity: 0;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Efecto rebote */
        }
        
        .modal-overlay.active .modal-box { transform: scale(1); opacity: 1; }

        .modal-icon-box {
            width: 80px; height: 80px; margin: 0 auto 20px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem;
        }
        .icon-success { background: #e1ffe1; color: #00b894; }
        .icon-error   { background: #ffebee; color: #ff3366; }

        .modal-title { font-family: 'Kalam', cursive; font-size: 2rem; margin: 0 0 10px; color: var(--dark); }
        .modal-desc { color: var(--text-gray); font-size: 1rem; margin-bottom: 30px; line-height: 1.5; }

        .modal-btn {
            background: var(--dark); color: #fff; border: none;
            padding: 15px 40px; border-radius: 50px; font-weight: 700; font-size: 1rem;
            cursor: pointer; transition: 0.3s; width: 100%;
            box-shadow: 0 10px 20px rgba(30, 39, 46, 0.2);
        }
        .modal-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 51, 102, 0.4); background: var(--primary); }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

<div class="reg-container">
    
    <div class="reg-side">
        <h1 class="kalam" style="font-size: 2.5rem; margin-bottom: 10px;">
            <i class="fa-solid fa-scissors" style="color: var(--primary);"></i> TuLook360
        </h1>
        <p style="opacity: 0.8; margin-bottom: 40px; text-align: center;">Únete a la plataforma líder en gestión de belleza.</p>

        <div class="live-card active" id="liveCard">
            <div class="lc-logo">
                <img id="lcImg" src="" style="display:none;">
                <i id="lcIcon" class="fa-solid fa-store"></i>
            </div>
            <h3 id="lcName" style="margin: 10px 0 5px; font-weight: 700;">Tu Negocio</h3>
            <p id="lcCat" style="font-size: 0.8rem; color: #aaa; text-transform: uppercase; letter-spacing: 1px;">CATEGORÍA</p>
            <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                <span style="background: #e1ffe1; color: #00b894; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                    ● Abierto
                </span>
            </div>
        </div>
    </div>

    <div class="reg-form-area">
        
        <div style="margin-bottom: 30px;">
            <h2 style="margin: 0; color: var(--dark); font-size: 1.8rem;">Crear Cuenta</h2>
            <p style="color: var(--text-gray);">Configura tu negocio en 3 simples pasos.</p>
        </div>

        <div class="stepper-wrapper">
            <div class="step-item active" id="stepIndicator1">
                <div class="step-circle"><span>1</span></div> <div class="step-text">Categoría</div>
            </div>
            <div class="step-item" id="stepIndicator2">
                <div class="step-circle"><span>2</span></div> <div class="step-text">Negocio</div>
            </div>
            <div class="step-item" id="stepIndicator3">
                <div class="step-circle"><span>3</span></div> <div class="step-text">Acceso</div>
            </div>
        </div>

        <?php if (!empty($_GET['error'])): ?>
            <div style="background:#fff0f3; color:var(--primary); padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #ffccd5; display:flex; gap:10px; align-items:center;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= ruta_accion('auth', 'guardarRegistro') ?>" method="POST" enctype="multipart/form-data" id="wizardForm" autocomplete="off">
            
            <div class="form-step active" id="step1">
                <h4 style="color:var(--dark); margin-bottom:20px;">¿Qué tipo de negocio administras?</h4>
                <input type="hidden" name="tneg_id" id="inputTipoNegocio" required>
                
                <div class="type-grid">
                    <?php foreach ($tiposNegocio as $tipo): ?>
                        <div class="type-card" onclick="selectType(this, '<?= $tipo['tneg_id'] ?>', '<?= $tipo['tneg_icono'] ?>', '<?= $tipo['tneg_nombre'] ?>')">
                            <i class="fa-solid <?= $tipo['tneg_icono'] ?>" style="font-size: 2rem; margin-bottom: 10px; color: #b2bec3;"></i>
                            <div class="type-text"><?= $tipo['tneg_nombre'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small id="err-step1" style="color:red; display:none; margin-top:10px;">Selecciona una opción.</small>
                
                <div class="nav-buttons" style="justify-content: flex-end;">
                    <button type="button" class="btn-nav btn-next" onclick="nextStep(2)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <div class="form-step" id="step2">
                <h4 style="color:var(--dark); margin-bottom:20px;">Información del Establecimiento</h4>
                
                <div class="input-group">
                    <label class="input-label">Nombre Comercial</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-shop input-icon"></i>
                        <input type="text" name="negocio_nombre" id="negocio_nombre" class="input-box" placeholder="Ej: Barbería Los Reyes" required>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Fecha de Fundación</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-calendar-days input-icon"></i>
                        <input type="date" name="negocio_fundacion" class="input-box" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Logo del Negocio (Opcional)</label>
                    <div class="upload-area">
                        <input type="file" name="logo" id="uploadLogo" accept="image/*" hidden>
                        
                        <label for="uploadLogo" class="upload-box" id="uploadBoxLabel">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon" id="uploadIcon"></i>
                            <span id="uploadText" style="font-weight:500; font-size:0.9rem;">
                                Haz clic para subir tu logo
                            </span>
                        </label>
                    </div>
                </div>

                <div class="nav-buttons">
                    <button type="button" class="btn-nav btn-prev" onclick="prevStep(1)"><i class="fa-solid fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn-nav btn-next" onclick="nextStep(3)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <div class="form-step" id="step3">
                <h4 style="color:var(--dark); margin-bottom:25px;">Datos de Acceso y Seguridad</h4>
                
                <div class="input-row">
                    <div class="input-col input-group">
                        <label class="input-label">Nombres</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="admin_nombres" id="admin_nombres" class="input-box" placeholder="Ej: Juan Antonio" required>
                        </div>
                        <small class="error-msg" id="err-nombres">Ingresa tu nombre.</small>
                    </div>
                    <div class="input-col input-group">
                        <label class="input-label">Apellidos</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" name="admin_apellidos" id="admin_apellidos" class="input-box" placeholder="Ej: Pérez Loor" required>
                        </div>
                        <small class="error-msg" id="err-apellidos">Ingresa tu apellido.</small>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-col input-group">
                        <label class="input-label">Cédula de Identidad</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-id-card input-icon"></i>
                            <input type="text" name="admin_cedula" id="admin_cedula" class="input-box" placeholder="10 dígitos" maxlength="10" inputmode="numeric">
                        </div>
                        <small class="error-msg" id="err-cedula">Cédula incorrecta o no válida.</small>
                    </div>
                    <div class="input-col input-group">
                        <label class="input-label">Correo Electrónico</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope input-icon"></i>
                            <input type="email" name="admin_correo" id="admin_correo" class="input-box" placeholder="admin@negocio.com" required>
                        </div>
                        <small class="error-msg" id="err-correo">Correo inválido.</small>
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-col input-group">
                        <label class="input-label">Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" name="admin_pass" id="pass1" class="input-box" placeholder="Mín. 8 caracteres" required>
                            <i class="fa-solid fa-eye-slash toggle-pass" onclick="togglePass('pass1', this)"></i>
                        </div>
                        <small class="error-msg" id="err-pass1">Debe tener 8 caracteres, mayúscula y número.</small>
                    </div>
                    <div class="input-col input-group">
                        <label class="input-label">Confirmar Contraseña</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-check-double input-icon"></i>
                            <input type="password" name="confirm_pass" id="pass2" class="input-box" placeholder="Repite la contraseña" required>
                            <i class="fa-solid fa-eye-slash toggle-pass" onclick="togglePass('pass2', this)"></i>
                        </div>
                        <small class="error-msg" id="err-pass2">Las contraseñas no coinciden.</small>
                    </div>
                </div>

                <div class="nav-buttons">
                    <button type="button" class="btn-nav btn-prev" onclick="prevStep(2)"><i class="fa-solid fa-arrow-left"></i> Atrás</button>
                    <button type="button" class="btn-nav btn-next" onclick="validarYEnviar()" style="background:var(--dark);">
                        FINALIZAR <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<div class="modal-overlay" id="customModal">
    <div class="modal-box">
        <div class="modal-icon-box" id="mIconBox">
            <i class="fa-solid fa-check" id="mIcon"></i>
        </div>
        
        <h3 class="modal-title" id="mTitle">¡Éxito!</h3>
        <p class="modal-desc" id="mDesc">Tu operación se realizó correctamente.</p>
        
        <button class="modal-btn" id="mBtn">Entendido</button>
    </div>
</div>

<script>
    // Variables Globales
    let currentStep = 1;
    let emailEsValido = false;
    const inputName = document.getElementById('negocio_nombre');
    const inputType = document.getElementById('inputTipoNegocio');
    const lcName = document.getElementById('lcName');
    const lcCat = document.getElementById('lcCat');
    const lcIcon = document.getElementById('lcIcon');
    const lcImg = document.getElementById('lcImg');

    // 1. Selección de Tipo (Visual + Lógica)
    function selectType(card, id, icon, name) {
        // Visual
        document.querySelectorAll('.type-card').forEach(c => {
            c.classList.remove('selected');
            c.querySelector('i').style.color = '#b2bec3'; // Reset color gris
        });
        card.classList.add('selected');
        card.querySelector('i').style.color = 'var(--primary)'; // Color activo

        // Lógica Formulario
        inputType.value = id;

        // Actualizar Tarjeta
        if(lcImg.style.display === 'none') {
            lcIcon.className = 'fa-solid ' + icon;
        }
        lcCat.textContent = name;
    }

    // 2. Navegación Siguiente
    function nextStep(targetStep) {
        // Validaciones Simples antes de avanzar
        if (currentStep === 1 && inputType.value === '') {
            document.getElementById('err-step1').style.display = 'block';
            return;
        }
        if (currentStep === 2 && inputName.value.trim() === '') {
            inputName.focus();
            return; // Nombre obligatorio
        }

        showStep(targetStep);
    }

    // 3. Navegación Atrás
    function prevStep(targetStep) {
        showStep(targetStep);
    }

    // 4. Mostrar Paso
    function showStep(step) {
        // Ocultar todos
        document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
        // Mostrar target
        document.getElementById('step' + step).classList.add('active');
        
        // Actualizar Stepper (Bolitas)
        updateStepper(step);
        currentStep = step;
    }

    function updateStepper(step) {
        // Resetear todos
        for (let i = 1; i <= 3; i++) {
            const el = document.getElementById('stepIndicator' + i);
            el.classList.remove('active', 'completed');
            
            if (i < step) el.classList.add('completed'); // Pasos anteriores
            if (i === step) el.classList.add('active');  // Paso actual
        }
    }

    // 5. Live Preview: Nombre del Negocio
    inputName.addEventListener('input', function() {
        lcName.textContent = this.value || 'Tu Negocio';
    });

    // 6. Live Preview: Logo + Feedback Visual en el Botón
    document.getElementById('uploadLogo').addEventListener('change', function(e) {
        const file = this.files[0];
        const boxLabel = document.getElementById('uploadBoxLabel');
        const boxText = document.getElementById('uploadText');
        const boxIcon = document.getElementById('uploadIcon');

        if (file) {
            // A. Actualizar Tarjeta Izquierda (Live Preview)
            const reader = new FileReader();
            reader.onload = function(evt) {
                lcImg.src = evt.target.result;
                lcImg.style.display = 'block';
                lcIcon.style.display = 'none';
            }
            reader.readAsDataURL(file);

            // B. Actualizar la Caja del Input (Feedback visual)
            boxLabel.classList.add('file-selected'); // Pone borde verde
            boxText.textContent = file.name; // Muestra el nombre del archivo
            boxIcon.className = "fa-solid fa-check-circle upload-icon"; // Cambia icono a check
            
        } else {
            // Si cancela la subida, reseteamos
            lcImg.style.display = 'none';
            lcIcon.style.display = 'block';
            
            boxLabel.classList.remove('file-selected');
            boxText.textContent = "Haz clic para subir tu logo";
            boxIcon.className = "fa-solid fa-cloud-arrow-up upload-icon";
        }
    });

    // 7. Validación Passwords al enviar
    document.getElementById('wizardForm').addEventListener('submit', function(e) {
        const p1 = document.getElementById('pass1').value;
        const p2 = document.getElementById('pass2').value;
        if (p1 !== p2) {
            e.preventDefault();
            document.getElementById('passError').style.display = 'block';
        }
    });

    // ============================================================
    // NUEVO: VALIDACIÓN DE CORREO EN TIEMPO REAL (AJAX)
    // ============================================================
    const emailInput = document.getElementById('admin_correo');
    const emailErr = document.getElementById('err-correo');

    emailInput.addEventListener('blur', async function() {
        const correo = this.value.trim();
        
        // 1. Si está vacío, no hacemos nada aún
        if(correo === '') return;

        // 2. Validación de Formato (Regex local)
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regex.test(correo)) {
            mostrarErrorCorreo('Correo inválido.', true);
            emailEsValido = false;
            return;
        }

        // 3. Validación de Existencia (Preguntar al Servidor)
        try {
            // Feedback visual: "Pensando..."
            emailErr.style.color = '#b2bec3';
            emailErr.innerText = 'Verificando...';
            emailErr.classList.add('visible');

            // Llamada al controlador
            const response = await fetch(`index.php?c=auth&a=verificar_correo_ajax&email=${encodeURIComponent(correo)}`);
            const data = await response.json();

            if (data.existe) {
                // ERROR: Ya existe
                mostrarErrorCorreo('Este correo ya está registrado.', true);
                emailEsValido = false;
            } else {
                // ÉXITO: Está libre
                mostrarErrorCorreo('Correo disponible', false); // False = es color verde
                emailEsValido = true;
                // Quitar el mensaje verde después de 2 seg
                setTimeout(() => {
                    if(emailEsValido) emailErr.classList.remove('visible');
                }, 2000);
            }

        } catch (error) {
            console.error('Error AJAX:', error);
        }
    });

    function mostrarErrorCorreo(msg, esError) {
        if(esError) {
            emailInput.classList.add('input-error');
            emailErr.style.color = '#e74c3c'; // Rojo
        } else {
            emailInput.classList.remove('input-error');
            emailErr.style.color = '#00b894'; // Verde
        }
        emailErr.innerText = msg;
        emailErr.classList.add('visible');
    }
    // ============================================================

    /* --- A. FUNCIÓN PARA VER/OCULTAR CONTRASEÑA --- */
    function togglePass(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            icon.style.color = 'var(--primary)'; // Se pone rosado al ver
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            icon.style.color = '#b2bec3';
        }
    }

    /* --- B. ALGORITMO CÉDULA ECUADOR --- */
    function esCedulaValida(cedula) {
        if (cedula.length !== 10) return false;
        const digits = cedula.split('').map(Number);
        if (digits.some(isNaN)) return false; // Solo números
        
        const provincia = Number(cedula.substring(0, 2));
        if (provincia < 1 || provincia > 24) return false; // Provincia válida
        if (digits[2] >= 6) return false; // Tercer dígito persona natural

        // Algoritmo Módulo 10
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

    /* --- C. VALIDACIÓN FINAL ANTES DE ENVIAR (ACTUALIZADA) --- */
    function validarYEnviar() {
        let errores = 0;

        // 0. NUEVO: Validar Nombres y Apellidos (Obligatorios)
        const inputNombres = document.getElementById('admin_nombres');
        const errNombres = document.getElementById('err-nombres'); // Asegúrate que el <small> tenga este ID en el HTML
        if (inputNombres.value.trim() === '') {
            inputNombres.classList.add('input-error');
            if(errNombres) errNombres.classList.add('visible');
            errores++;
        }

        const inputApellidos = document.getElementById('admin_apellidos');
        const errApellidos = document.getElementById('err-apellidos'); // Asegúrate que el <small> tenga este ID
        if (inputApellidos.value.trim() === '') {
            inputApellidos.classList.add('input-error');
            if(errApellidos) errApellidos.classList.add('visible');
            errores++;
        }

        // 1. Validar Cédula
        const cedulaInput = document.getElementById('admin_cedula');
        const errCedula = document.getElementById('err-cedula');
        if (!esCedulaValida(cedulaInput.value)) {
            cedulaInput.classList.add('input-error');
            errCedula.classList.add('visible');
            errores++;
        } else {
            cedulaInput.classList.remove('input-error');
            errCedula.classList.remove('visible');
        }

        // 2. Validar Correo (USANDO LA VARIABLE GLOBAL DEL AJAX)
        const correoInput = document.getElementById('admin_correo');
        const errCorreo = document.getElementById('err-correo');
        
        if (correoInput.value.trim() === '') {
            mostrarErrorCorreo('Campo obligatorio', true);
            errores++;
        } else if (!emailEsValido) {
            // Si el AJAX dijo que no, o el formato está mal
            mostrarErrorCorreo(errCorreo.innerText || 'Correo inválido o registrado', true);
            errores++;
        }

        // 3. Validar Seguridad Contraseña
        const p1 = document.getElementById('pass1');
        const errP1 = document.getElementById('err-pass1');
        const regexPass = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/; 
        
        if (!regexPass.test(p1.value)) {
            p1.classList.add('input-error');
            errP1.classList.add('visible');
            errores++;
        } else {
            p1.classList.remove('input-error');
            errP1.classList.remove('visible');
        }

        // 4. Validar Coincidencia
        const p2 = document.getElementById('pass2');
        const errP2 = document.getElementById('err-pass2');
        if (p1.value !== p2.value || p2.value === '') {
            p2.classList.add('input-error');
            errP2.classList.add('visible');
            errores++;
        } else {
            p2.classList.remove('input-error');
            errP2.classList.remove('visible');
        }

        // --- RESULTADO ---
        if (errores === 0) {
            const btn = document.querySelector('.btn-nav.btn-next[onclick="validarYEnviar()"]');
            const originalText = btn.innerHTML;
            
            // 1. Feedback visual en el botón
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> CREANDO...';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            // 2. Preparar datos
            const form = document.getElementById('wizardForm');
            const formData = new FormData(form);

            // 3. Petición AJAX al Controlador
            fetch('index.php?c=auth&a=guardarRegistro', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ÉXITO: Mostramos Modal Verde
                    mostrarModal('¡Bienvenido!', data.msg, 'success', data.redirect);
                } else {
                    // ERROR SERVER: Mostramos Modal Rojo
                    mostrarModal('Algo salió mal', data.msg, 'error');
                    // Restauramos el botón
                    btn.innerHTML = originalText;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                mostrarModal('Error de Conexión', 'No pudimos conectar con el servidor.', 'error');
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.disabled = false;
            });
        }
    }
    
    // Limpiar errores al escribir
    document.querySelectorAll('.input-box').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('input-error');
            // Busca el small hermano y lo oculta
            const small = this.parentElement.nextElementSibling;
            if(small && small.classList.contains('error-msg')) {
                small.classList.remove('visible');
            }
        });
    });


    /* --- E. FUNCIÓN PARA MOSTRAR NUESTRO MODAL --- */
    let redirectUrl = null; // Variable para guardar a dónde ir

    function mostrarModal(titulo, mensaje, tipo, url = null) {
        const modal = document.getElementById('customModal');
        const mTitle = document.getElementById('mTitle');
        const mDesc = document.getElementById('mDesc');
        const mIconBox = document.getElementById('mIconBox');
        const mIcon = document.getElementById('mIcon');
        const mBtn = document.getElementById('mBtn');

        // Configurar Contenido
        mTitle.textContent = titulo;
        mDesc.textContent = mensaje;
        redirectUrl = url; // Guardamos la URL para cuando den click

        // Configurar Estilos según Tipo
        if (tipo === 'success') {
            mIconBox.className = 'modal-icon-box icon-success';
            mIcon.className = 'fa-solid fa-check';
            mBtn.textContent = '¡Comenzar Ahora!';
        } else {
            mIconBox.className = 'modal-icon-box icon-error';
            mIcon.className = 'fa-solid fa-xmark';
            mBtn.textContent = 'Intentar de nuevo';
        }

        // Mostrar
        modal.classList.add('active');
    }

    // Cerrar Modal al dar click al botón
    document.getElementById('mBtn').addEventListener('click', function() {
        if (redirectUrl) {
            window.location.href = redirectUrl; // Redirigir si es éxito
        } else {
            document.getElementById('customModal').classList.remove('active'); // Solo cerrar si es error
        }
    });
</script>

</body>
</html>