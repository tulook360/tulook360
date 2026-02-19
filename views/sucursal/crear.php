<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* --- ESTILOS GENERALES (PREMIUM) --- */
    .main-wrapper { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; display: flex; flex-direction: column; min-height: 650px; }
    .stepper-container { background: #fdfdfd; padding: 25px 40px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; position: relative; }
    
    /* Pista del stepper ajustada para 4 pasos */
    .stepper-track { position: absolute; top: 40px; left: 50px; right: 50px; height: 3px; background: #eee; z-index: 1; }
    
    .step-item { z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: default; transition: 0.3s; flex: 1; }
    .step-circle { width: 35px; height: 35px; border-radius: 50%; background: #fff; border: 2px solid #ddd; color: #bbb; display: flex; align-items: center; justify-content: center; transition: 0.3s; font-size: 0.9rem; }
    .step-label { font-size: 0.75rem; font-weight: 600; color: #ccc; text-transform: uppercase; letter-spacing: 0.5px; }
    .step-item.active .step-circle { background: var(--color-primario); border-color: var(--color-primario); color: white; box-shadow: 0 0 0 5px rgba(253, 121, 168, 0.15); transform: scale(1.1); }
    .step-item.active .step-label { color: var(--color-primario); }
    .step-item.completed .step-circle { background: #00b894; border-color: #00b894; color: white; }

    .form-content-box { padding: 30px; flex: 1; position: relative; }
    .step-panel { display: none; animation: slideFade 0.4s cubic-bezier(0.2, 0.8, 0.2, 1); }
    .step-panel.active-panel { display: block; }
    @keyframes slideFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .panel-header { text-align: center; margin-bottom: 30px; }
    .panel-header h3 { margin: 0 0 5px; font-size: 1.6rem; color: #2d3436; font-weight: 800; }
    .panel-header p { margin: 0; color: #b2bec3; }

    /* Inputs Modernos */
    .input-group-modern { margin-bottom: 20px; }
    .input-group-modern label { display: block; font-weight: 700; color: #636e72; margin-bottom: 8px; font-size: 0.9rem; }
    .input-group-modern input, .input-group-modern textarea { width: 100%; padding: 12px 15px; border: 2px solid #f1f2f6; border-radius: 10px; font-size: 1rem; outline: none; transition: 0.2s; color: #2d3436; }
    .input-group-modern input:focus { border-color: var(--color-primario); background: #fff; }

    /* --- FOTO UPLOAD (CORREGIDO Y CENTRADO) --- */
    .photo-upload-zone { 
        position: relative; width: 100%; height: 250px; border-radius: 16px; overflow: hidden; border: 2px dashed #dfe6e9; cursor: pointer; transition: 0.3s; background: #f8f9fa; 
        display: flex; flex-direction: column; align-items: center; justify-content: center; /* CENTRADO TOTAL */
        color: #b2bec3; margin: 0 auto; max-width: 400px; padding: 0;
    }
    .photo-upload-zone:hover { border-color: var(--color-primario); background: #fff0f6; color: var(--color-primario); }
    .photo-upload-zone img { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; display: none; z-index: 5; }
    .photo-upload-zone.has-image .upload-placeholder { display: none; }
    .photo-upload-zone.has-image img { display: block; }
    
    .upload-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; text-align: center; z-index: 1; }
    .icon-box-circle { background: #fff; width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); transition: 0.3s; }

    /* --- MAPA --- */
    #mapa { width: 100%; height: 350px; border-radius: 16px; border: 2px solid #f0f0f0; z-index: 1; }
    .geo-btn { background: #2d3436; color: white; border: none; padding: 10px 20px; border-radius: 50px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px; transition: 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .geo-btn:hover { background: #000; transform: translateY(-2px); }

    /* --- NUEVO: ESTILOS DE HORARIOS --- */
    .horario-row { display: grid; grid-template-columns: 100px 1fr 1fr 80px; gap: 15px; align-items: center; padding: 12px; border: 1px solid #f1f2f6; margin-bottom: 10px; border-radius: 12px; background: #fff; transition: 0.2s; }
    .horario-row:hover { border-color: #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
    .day-label { font-weight: 700; color: #2d3436; font-size: 0.95rem; }
    .time-input { border: 1px solid #e0e0e0; border-radius: 8px; padding: 8px; width: 100%; outline: none; color: #636e72; font-family: inherit; }
    .time-input:disabled { background: #f5f5f5; color: #ccc; cursor: not-allowed; }
    
    /* Toggle Switch Personalizado */
    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e0e0e0; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #ff4757; } /* Rojo para cerrado/descanso */
    input:checked + .slider:before { transform: translateX(20px); }
    .switch-label { font-size: 0.75rem; color: #b2bec3; text-align: center; display: block; margin-top: 5px; }

    .btn-replicar { background: #e3f2fd; color: #0984e3; border: none; padding: 8px 15px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; margin-bottom: 20px; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; }
    .btn-replicar:hover { background: #bbdefb; }

    /* Footer Navigation */
    .form-footer { padding: 20px 40px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff; border-radius: 0 0 20px 20px; }
    .btn-nav { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
    .prev { background: #fff; color: #636e72; border: 1px solid #ddd; } .prev:disabled { opacity: 0.5; }
    .next { background: var(--color-primario); color: white; box-shadow: 0 4px 15px rgba(253, 121, 168, 0.4); }
    .finish { background: #00b894; color: white; }

    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
        .stepper-container { padding: 20px 10px; } .step-label { display: none; } .stepper-track { left: 20px; right: 20px; }
        .form-footer { padding: 15px; gap: 10px; } .paso-texto { display: none; } 
        .btn-nav { flex: 1; justify-content: center; padding: 12px 10px; font-size: 0.9rem; }
        
        /* Ajuste de Horarios en Móvil */
        .horario-row { grid-template-columns: 1fr 1fr 60px; gap: 10px; }
        .day-label { grid-column: 1 / -1; margin-bottom: 5px; border-bottom: 1px solid #f9f9f9; padding-bottom: 5px; }
        .btn-replicar { width: 100%; justify-content: center; }
    }
</style>

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nueva Sucursal</h1>
        <p class="section-subtitle">Define la identidad, ubicación y horarios de tu local.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('sucursal', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="main-wrapper">
    
    <div class="stepper-container">
        <div class="stepper-track"></div>
        
        <div class="step-item active" id="stepIndicator1">
            <div class="step-circle"><i class="fa-solid fa-store"></i></div>
            <span class="step-label">Identidad</span>
        </div>
        <div class="step-item" id="stepIndicator2">
            <div class="step-circle"><i class="fa-solid fa-address-book"></i></div>
            <span class="step-label">Contacto</span>
        </div>
        <div class="step-item" id="stepIndicator3">
            <div class="step-circle"><i class="fa-solid fa-map-location-dot"></i></div>
            <span class="step-label">Ubicación</span>
        </div>
        <div class="step-item" id="stepIndicator4">
            <div class="step-circle"><i class="fa-regular fa-clock"></i></div>
            <span class="step-label">Horarios</span>
        </div>
    </div>

    <form action="<?= ruta_accion('sucursal', 'guardar') ?>" method="POST" enctype="multipart/form-data" autocomplete="off" id="formSucursal" class="form-content-box">
        
        <div class="step-panel active-panel" id="step1">
            <div class="panel-header"><h3>¿Cómo se ve tu local?</h3><p>Define la imagen y el nombre principal.</p></div>
            
            <div class="input-group-modern" style="text-align:center;">
                <label>Foto de Fachada</label>
                <label class="photo-upload-zone" id="photoZone">
                    <input type="file" name="foto" id="inputFoto" accept="image/*" style="display:none;">
                    <img id="imgPreview" src="" alt="Preview">
                    <div class="upload-placeholder">
                        <br>
                        <br>
                        <div class="icon-box-circle">
                            <i class="fa-solid fa-camera" style="font-size: 1.6rem; color:var(--color-primario);"></i>
                        </div>
                        <div style="font-weight:700; color:#2d3436; font-size:1.1rem; margin-bottom:5px;">Sube una foto</div>
                        <small style="font-size: 0.85rem; color:#b2bec3;">Haz que tu negocio destaque</small>
                    </div>
                </label>
            </div>

            <div class="input-group-modern">
                <label>Nombre de la Sucursal <span style="color:red">*</span></label>
                <input type="text" name="nombre" id="txtNombre" placeholder="Ej: Matriz - Centro Histórico">
            </div>
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header"><h3>Datos de Contacto</h3><p>¿Cómo te encuentran tus clientes?</p></div>
            
            <div class="input-group-modern">
                <label>Teléfono / WhatsApp</label>
                <input type="text" name="telefono" id="txtTelefono" placeholder="Ej: 0991234567">
            </div>

            <div class="input-group-modern">
                <label>Correo Electrónico (Opcional)</label>
                <input type="email" name="correo" placeholder="sucursal@tulook.com">
            </div>

            <div style="text-align:center; margin-top:40px;">
                <img src="https://cdn-icons-png.flaticon.com/512/3059/3059561.png" style="width:100px; opacity:0.5;" alt="Contact Icon">
            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header"><h3>Ubicación Geográfica</h3><p>Punto exacto en el mapa.</p></div>
            
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <label style="font-weight:700; color:#636e72;">Mapa</label>
                <button type="button" id="btnGeo" class="geo-btn">
                    <i class="fa-solid fa-location-crosshairs"></i> Usar mi ubicación
                </button>
            </div>

            <div id="mapa"></div>
            <div style="font-size:0.75rem; text-align:center; color:#b2bec3; margin-top:5px;">Arrastra el marcador azul</div>

            <input type="hidden" name="latitud" id="latitud">
            <input type="hidden" name="longitud" id="longitud">

            <div class="row-inputs" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:20px;">
                <div class="input-group-modern">
                    <label>Dirección <span style="color:red">*</span></label>
                    <input type="text" name="direccion" id="txtDireccion" placeholder="Ej: Av. Amazonas y NNUU">
                </div>
                <div class="input-group-modern">
                    <label>Referencia</label>
                    <input type="text" name="referencia" placeholder="Frente al parque...">
                </div>
            </div>
        </div>

        <div class="step-panel" id="step4">
            <div class="panel-header"><h3>Horarios de Atención</h3><p>Define cuándo está abierto tu local.</p></div>

            <div style="text-align:right;">
                <button type="button" class="btn-replicar" onclick="replicarHorario()">
                    <i class="fa-solid fa-copy"></i> Copiar Lunes a toda la semana
                </button>
            </div>

            <div class="horarios-wrapper">
                <?php 
                // MAPEO PARA MOSTRAR TEXTO, PERO USAREMOS EL ÍNDICE $i COMO VALOR (1=Lunes... 7=Domingo)
                $nombresDias = [
                    1 => 'Lunes', 
                    2 => 'Martes', 
                    3 => 'Miércoles', 
                    4 => 'Jueves', 
                    5 => 'Viernes', 
                    6 => 'Sábado', 
                    7 => 'Domingo'
                ];

                // BUCLE DEL 1 AL 7
                for ($i = 1; $i <= 7; $i++): 
                    $diaNombre = $nombresDias[$i];
                ?>
                <div class="horario-row" id="row_<?= $i ?>">
                    <div class="day-label"><?= $diaNombre ?></div>
                    
                    <input type="hidden" name="horarios[<?= $i ?>][dia]" value="<?= $i ?>">
                    
                    <input type="time" class="time-input inp-apertura" name="horarios[<?= $i ?>][apertura]" value="09:00">
                    
                    <input type="time" class="time-input inp-cierre" name="horarios[<?= $i ?>][cierre]" value="18:00">
                    
                    <div style="text-align:center;">
                        <label class="switch">
                            <input type="checkbox" class="chk-descanso" name="horarios[<?= $i ?>][es_descanso]" value="1" onchange="toggleDescanso(<?= $i ?>)">
                            <span class="slider"></span>
                        </label>
                        <span class="switch-label">Cerrado</span>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-nav prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled><i class="fa-solid fa-arrow-left"></i> Anterior</button>
            <div class="paso-texto" style="color:#b2bec3; font-size:0.85rem; font-weight:600;">Paso <span id="lblPaso">1</span> de 4</div>
            <button type="button" class="btn-nav next" id="btnNext" onclick="cambiarPaso(1)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
            <button type="submit" class="btn-nav finish" id="btnFinish" style="display:none;"><i class="fa-solid fa-check"></i> Finalizar</button>
        </div>

    </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    // --- LÓGICA DE PASOS (4 PASOS) ---
    let currentStep = 1;
    const totalSteps = 4;

    function cambiarPaso(dir) {
        // VALIDACIONES
        if (dir === 1) {
            if (currentStep === 1) {
                const nombre = document.getElementById('txtNombre').value.trim();
                if (!nombre) { mostrarNotificacion("Falta información", "Escribe el <b>nombre</b> de la sucursal.", "danger"); return; }
            }
            if (currentStep === 3) {
                 const direccion = document.getElementById('txtDireccion').value.trim();
                 if (!direccion) { mostrarNotificacion("Falta dirección", "Escribe una <b>dirección</b> válida.", "danger"); return; }
            }
        }

        // Animación
        document.getElementById('step' + currentStep).classList.remove('active-panel');
        document.getElementById('stepIndicator' + currentStep).classList.remove('active');
        
        if(dir === 1) document.getElementById('stepIndicator' + currentStep).classList.add('completed');
        else document.getElementById('stepIndicator' + (currentStep)).classList.remove('completed');

        currentStep += dir;
        
        document.getElementById('step' + currentStep).classList.add('active-panel');
        document.getElementById('stepIndicator' + currentStep).classList.add('active');
        
        actualizarBotones();

        if (currentStep === 3) {
            setTimeout(() => { map.invalidateSize(); }, 200);
        }
    }

    function actualizarBotones() {
        document.getElementById('lblPaso').innerText = currentStep;
        document.getElementById('btnPrev').disabled = (currentStep === 1);
        
        if (currentStep === totalSteps) {
            document.getElementById('btnNext').style.display = 'none';
            document.getElementById('btnFinish').style.display = 'inline-flex';
        } else {
            document.getElementById('btnNext').style.display = 'inline-flex';
            document.getElementById('btnFinish').style.display = 'none';
        }
    }

    // --- LÓGICA DE HORARIOS ---
    function toggleDescanso(index) {
        const row = document.getElementById('row_' + index);
        if(!row) return;

        const checkbox = row.querySelector('.chk-descanso');
        const inputs = row.querySelectorAll('.time-input');
        
        inputs.forEach(input => {
            input.disabled = checkbox.checked;
            if(checkbox.checked) {
                // Guardamos el valor previo si quieres restaurarlo o simplemente limpiamos visualmente
                // input.value = ''; 
            } else {
                if(!input.value) input.value = input.classList.contains('inp-apertura') ? '09:00' : '18:00';
            }
        });
        
        if(checkbox.checked) row.style.opacity = '0.6';
        else row.style.opacity = '1';
    }

    function replicarHorario() {
        // Lunes ahora es el ID 1
        const rowLunes = document.getElementById('row_1');
        const apLunes = rowLunes.querySelector('.inp-apertura').value;
        const ciLunes = rowLunes.querySelector('.inp-cierre').value;
        const descLunes = rowLunes.querySelector('.chk-descanso').checked;

        // Bucle del 2 (Martes) al 7 (Domingo)
        for(let i = 2; i <= 7; i++) {
            const row = document.getElementById('row_' + i);
            if(row) {
                row.querySelector('.inp-apertura').value = apLunes;
                row.querySelector('.inp-cierre').value = ciLunes;
                
                const chk = row.querySelector('.chk-descanso');
                chk.checked = descLunes;
                
                toggleDescanso(i);
            }
        }
        mostrarNotificacion("Horario Copiado", "Se ha aplicado el horario del Lunes a toda la semana.", "success");
    }

    // Inicializar estados al cargar
    document.addEventListener('DOMContentLoaded', () => {
        // Bucle del 1 al 7
        for(let i = 1; i <= 7; i++) {
            toggleDescanso(i);
        }
    });

    // --- PREVIEW FOTO ---
    const inputFoto = document.getElementById('inputFoto');
    const photoZone = document.getElementById('photoZone');
    const imgPreview = document.getElementById('imgPreview');

    inputFoto.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imgPreview.src = e.target.result;
                photoZone.classList.add('has-image');
            }
            reader.readAsDataURL(file);
        }
    });

    // --- MAPA ---
    let latDefault = -0.180653; 
    let lngDefault = -78.467834;
    const map = L.map('mapa').setView([latDefault, lngDefault], 14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { attribution: '&copy; CARTO', subdomains: 'abcd', maxZoom: 20 }).addTo(map);
    let marker = L.marker([latDefault, lngDefault], {draggable: true}).addTo(map);

    function actualizarCoordenadas(lat, lng) {
        document.getElementById('latitud').value = lat;
        document.getElementById('longitud').value = lng;
    }
    actualizarCoordenadas(latDefault, lngDefault);

    marker.on('dragend', function(e) {
        const pos = marker.getLatLng();
        actualizarCoordenadas(pos.lat, pos.lng);
        map.panTo(pos);
    });
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        actualizarCoordenadas(e.latlng.lat, e.latlng.lng);
        map.panTo(e.latlng);
    });

    document.getElementById('btnGeo').addEventListener('click', () => {
        const btn = document.getElementById('btnGeo');
        if (!navigator.geolocation) { mostrarNotificacion('Error', 'Sin soporte GPS.', 'danger'); return; }
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Buscando...';
        btn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                const newPos = new L.LatLng(latitude, longitude);
                marker.setLatLng(newPos);
                map.setView(newPos, 16);
                actualizarCoordenadas(latitude, longitude);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                mostrarNotificacion('Éxito', 'Ubicación encontrada.', 'success');
            },
            () => {
                mostrarNotificacion('Error', 'No se pudo obtener ubicación.', 'danger');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        );
    });
</script>