<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar Servicio</h1>
        <p class="section-subtitle">Modificando: <b><?= htmlspecialchars($servicio['serv_nombre']) ?></b></p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('servicio', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-xmark"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="main-wrapper">
    
    <div class="stepper-container">
        <div class="stepper-track"></div>
        <div class="step-item active" id="stepIndicator1"><div class="step-circle"><i class="fa-solid fa-layer-group"></i></div><span class="step-label">Categoría</span></div>
        <div class="step-item" id="stepIndicator2"><div class="step-circle"><i class="fa-solid fa-wand-magic-sparkles"></i></div><span class="step-label">Detalles</span></div>
        <div class="step-item" id="stepIndicator3"><div class="step-circle"><i class="fa-solid fa-tag"></i></div><span class="step-label">Precio</span></div>
        <div class="step-item" id="stepIndicator4"><div class="step-circle"><i class="fa-solid fa-flask"></i></div><span class="step-label">Receta</span></div>
    </div>

    <form action="<?= ruta_accion('servicio', 'actualizar') ?>" method="POST" enctype="multipart/form-data" autocomplete="off" id="formServicio" class="form-content-box">
        <input type="hidden" name="id" value="<?= $servicio['serv_id'] ?>">

        <div class="step-panel active-panel" id="step1">
            <div class="panel-header"><h3>Familia del Servicio</h3><p>Clasificación principal.</p></div>
            <input type="hidden" name="tser_id" id="inputCategoriaId" value="<?= $servicio['tser_id'] ?>" required>
            <div class="category-grid" id="gridCategorias">
                <?php foreach($listaCategorias as $cat): ?>
                    <?php $sel = ($cat['tser_id'] == $servicio['tser_id']) ? 'selected' : ''; ?>
                    <div class="cat-card <?= $sel ?>" onclick="seleccionarCategoria(this, <?= $cat['tser_id'] ?>)">
                        <div class="cat-icon-bg"><i class="fa-solid fa-scissors"></i></div>
                        <span class="cat-name"><?= htmlspecialchars($cat['tser_nombre']) ?></span>
                        <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                    </div>
                <?php endforeach; ?>
                <div class="cat-card add-new" onclick="abrirModalCategoria()">
                    <div class="cat-icon-bg"><i class="fa-solid fa-plus"></i></div><span class="cat-name">Crear Nueva</span>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header"><h3>Detalles</h3><p>Información visual y tiempos.</p></div>
            
            <div class="split-layout">
                <div class="upload-section">
                    <div style="margin-bottom: 2rem;">
                        <div id="galeriaMosaico" style="display:flex; gap:15px; flex-wrap:wrap; justify-content:center;">
                            
                            <?php 
                                $fotosGuardadas = $servicio['galeria'] ?? []; 
                                $conteo = count($fotosGuardadas);
                            ?>
                            
                            <?php foreach($fotosGuardadas as $foto): ?>
                                <div class="thumb-box" id="saved-<?= $foto['img_id'] ?>">
                                    <img src="<?= htmlspecialchars($foto['img_url']) ?>" class="thumb-img">
                                    <div class="btn-remove-img" onclick="borrarFotoServidor(<?= $foto['img_id'] ?>, '<?= $foto['img_url'] ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <label class="upload-card" id="btnUploadCard" for="inputFotos" style="<?= $conteo >= 3 ? 'display:none;' : 'display:flex;' ?>">
                                <input type="file" name="fotos[]" id="inputFotos" accept="image/*" multiple style="display:none;">
                                <div style="text-align:center; color:#636e72;">
                                    <i class="fa-solid fa-camera" style="font-size:1.5rem; color:var(--color-primario);"></i>
                                    <div style="font-size:0.75rem;">Agregar</div>
                                </div>
                            </label>
                        </div>
                        
                        <div style="text-align:center; margin-top:10px; font-size:0.85rem; color:#636e72;">
                            <span id="conteoTotal"><?= $conteo ?></span> / 3 Imágenes
                        </div>
                    </div>
                </div>

                <div class="inputs-section">
                    <div class="input-group-modern"><label>Nombre</label><input type="text" name="nombre" id="txtNombre" value="<?= htmlspecialchars($servicio['serv_nombre']) ?>" required></div>
                    <div class="row-inputs">
                        <div class="input-group-modern"><label>Duración (min)</label><input type="number" name="duracion" value="<?= $servicio['serv_duracion'] ?>" required></div>
                        <div class="input-group-modern"><label>Limpieza (min)</label><input type="number" name="espera" value="<?= $servicio['serv_espera'] ?>"></div>
                    </div>
                    <div class="input-group-modern"><label>Descripción</label><textarea name="descripcion" rows="4"><?= htmlspecialchars($servicio['serv_descripcion']) ?></textarea></div>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header"><h3>Precios</h3><p>Configuración de costos.</p></div>
            <div class="price-hero">
                <label>Precio Base</label>
                <div class="price-input-wrapper"><span class="currency-symbol">$</span><input type="number" step="0.01" name="precio" id="precioBase" value="<?= $servicio['serv_precio'] ?>" oninput="actualizarPlaceholders()"></div>
            </div>
            <div class="branches-container">
                <h4>Por Sucursal</h4>
                <?php $asig = $servicio['asignaciones'] ?? []; ?>
                <?php foreach($listaSucursales as $suc): ?>
                    <?php $has = array_key_exists($suc['suc_id'], $asig); $val = $has ? $asig[$suc['suc_id']] : ''; ?>
                    <div class="branch-row">
                        <div class="branch-toggle">
                            <input type="checkbox" name="sucursales[]" value="<?= $suc['suc_id'] ?>" id="suc_<?= $suc['suc_id'] ?>" <?= $has ? 'checked' : '' ?> onchange="toggleSucursal(this, '<?= $suc['suc_id'] ?>')">
                            <label for="suc_<?= $suc['suc_id'] ?>"><?= htmlspecialchars($suc['suc_nombre']) ?></label>
                        </div>
                        <div class="branch-input">
                            <input type="number" step="0.01" name="precio_sucursal[<?= $suc['suc_id'] ?>]" id="precio_<?= $suc['suc_id'] ?>" class="input-mini input-sucursal" placeholder="Igual" value="<?= $val ?>" <?= !$has ? 'disabled style="opacity:0.5"' : '' ?>>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step-panel" id="step4">
            <div class="panel-header"><h3>Receta</h3><p>Insumos a descontar.</p></div>
            <div class="recipe-box">
                <div class="recipe-controls">
                    <div class="input-group-modern grow">
                        <label>Producto</label>
                        <select id="selectInsumo" onchange="actualizarUnidadVisual()">
                            <option value="">-- Buscar --</option>
                            <?php foreach($listaInsumos as $ins): ?>
                                <option value="<?= $ins['pro_id'] ?>" 
                                    data-nombre="<?= htmlspecialchars($ins['pro_nombre']) ?>"
                                    data-unidad="<?= htmlspecialchars($ins['pro_unidad']) ?>" 
                                    data-contenido="<?= $ins['pro_contenido'] ?>"
                                    data-uconsumo="<?= htmlspecialchars($ins['pro_unidad_consumo']) ?>">
                                    <?= htmlspecialchars($ins['pro_nombre']) ?> (Stock: <?= htmlspecialchars($ins['pro_unidad']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group-modern small"><label>Cant (<span id="lblUnidadUso">...</span>)</label><input type="number" id="cantInsumo" placeholder="0"></div>
                    <button type="button" class="btn-add-recipe" onclick="agregarInsumo(null)"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div id="infoConversion" class="conversion-pill animate-pop" style="display:none;">
                    <i class="fa-solid fa-calculator"></i><span>Usas <b><span id="valUso">0</span> <span id="txtUso">ml</span></b>. Descuenta <b><span id="valDesc">0</span> <span id="txtBodega">Botella</span></b>.</span>
                </div>
                <div class="recipe-list-container" id="listaInsumos">
                    <div class="empty-state-recipe" id="emptyRecipeMsg"><i class="fa-solid fa-basket-shopping"></i><p>Sin insumos agregados.</p></div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-nav prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled><i class="fa-solid fa-arrow-left"></i> Anterior</button>
            <div class="steps-dots"><span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
            <button type="button" class="btn-nav next" id="btnNext" onclick="cambiarPaso(1)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
            <button type="submit" class="btn-nav finish" id="btnFinish" style="display:none;"><i class="fa-solid fa-check"></i> Guardar Cambios</button>
        </div>
    </form>
</div>

<div class="modal-overlay" id="modalNuevaCat">
    <div class="modal-container animate-pop">
        <div class="modal-top-bar"><span>Nueva Categoría</span><button class="btn-close-modal" onclick="cerrarModalCat()"><i class="fa-solid fa-xmark"></i></button></div>
        <div class="modal-content-inner"><div class="icon-float"><i class="fa-solid fa-tags"></i></div><h3>Organiza tus servicios</h3><div class="input-modal-wrap"><input type="text" id="txtNuevaCat" placeholder="Ej: Faciales" autocomplete="off"></div></div>
        <div class="modal-actions-row"><button class="btn-ghost" onclick="cerrarModalCat()">Cancelar</button><button class="btn-save-modal" onclick="guardarCategoriaExpress()">Guardar</button></div>
    </div>
</div>

<div class="modal-overlay" id="modalUniversal">
    <div class="modal-container animate-pop">
        <div class="modal-top-bar">
            <span id="uModalTitle">Aviso</span>
            <button type="button" class="btn-close-modal" onclick="cerrarModalU()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-content-inner">
            <div class="icon-float" style="background:#fff0f6; color:var(--color-primario);">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 id="uModalMessage" style="margin-top:15px; font-weight:600;">...</h3>
        </div>
        <div class="modal-actions-row">
            <button type="button" id="uBtnCancel" class="btn-ghost" onclick="cerrarModalU()">Cancelar</button>
            <button type="button" id="uBtnConfirm" class="btn-save-modal">Aceptar</button>
        </div>
    </div>
</div>

<style>
    /* --- ESTILOS PREMIUM + TUS ESTILOS DE GALERÍA --- */
    .main-wrapper { max-width: 850px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; display: flex; flex-direction: column; min-height: 600px; }
    .stepper-container { background: #fdfdfd; padding: 25px 40px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; position: relative; }
    .stepper-track { position: absolute; top: 40px; left: 60px; right: 60px; height: 3px; background: #eee; z-index: 1; }
    .step-item { z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: default; transition: 0.3s; }
    .step-circle { width: 35px; height: 35px; border-radius: 50%; background: #fff; border: 2px solid #ddd; color: #bbb; display: flex; align-items: center; justify-content: center; transition: 0.3s; font-size: 0.9rem; }
    .step-label { font-size: 0.75rem; font-weight: 600; color: #ccc; text-transform: uppercase; letter-spacing: 0.5px; }
    .step-item.active .step-circle { background: var(--color-primario); border-color: var(--color-primario); color: white; box-shadow: 0 0 0 5px rgba(253, 121, 168, 0.15); transform: scale(1.1); }
    .step-item.active .step-label { color: var(--color-primario); }
    .form-content-box { padding: 30px; flex: 1; position: relative; }
    .step-panel { display: none; animation: slideFade 0.4s cubic-bezier(0.2, 0.8, 0.2, 1); }
    .step-panel.active-panel { display: block; }
    @keyframes slideFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .panel-header { text-align: center; margin-bottom: 35px; }
    .panel-header h3 { margin: 0 0 5px; font-size: 1.6rem; color: #2d3436; font-weight: 800; }
    .panel-header p { margin: 0; color: #b2bec3; }
    .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
    .cat-card { background: white; border: 2px solid #f1f2f6; border-radius: 16px; padding: 25px 15px; display: flex; flex-direction: column; align-items: center; gap: 15px; cursor: pointer; transition: 0.2s; position: relative; overflow: hidden; }
    .cat-card:hover { transform: translateY(-5px); border-color: var(--color-primario); }
    .cat-card.selected { border-color: var(--color-primario); background: #fff5f8; }
    .cat-card.selected .check-mark { transform: scale(1); opacity: 1; }
    .cat-icon-bg { width: 50px; height: 50px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #636e72; transition: 0.2s; }
    .cat-card.selected .cat-icon-bg { background: var(--color-primario); color: white; }
    .cat-name { font-weight: 700; color: #2d3436; text-align: center; font-size: 0.95rem; }
    .check-mark { position: absolute; top: 10px; right: 10px; width: 24px; height: 24px; background: var(--color-primario); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transform: scale(0); opacity: 0; transition: 0.3s; }
    .add-new { border-style: dashed; border-color: #ccc; }
    .split-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; height: 100%; }

    /* ESTILOS DE TU GALERÍA QUE FUNCIONA */
    .thumb-box, .upload-card { width: 100px; height: 100px; border-radius: 10px; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.1); background:white; overflow:hidden; }
    .thumb-img { width: 100% !important; height: 100% !important; object-fit: cover; display: block !important; border:none; }
    .upload-card { border: 2px dashed #b2bec3; background: #f9fafb; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
    .upload-card:hover { background: #fff; border-color: var(--color-primario); }
    .btn-remove-img { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; background: rgba(255,118,117,0.9); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; cursor: pointer; z-index:10; }
    .btn-remove-img:hover { transform: scale(1.1); background: #d63031; }

    .input-group-modern { margin-bottom: 20px; }
    .input-group-modern label { display: block; font-weight: 700; color: #636e72; margin-bottom: 8px; font-size: 0.9rem; }
    .input-group-modern input, .input-group-modern textarea, .input-group-modern select { width: 100%; padding: 12px 15px; border: 2px solid #f1f2f6; border-radius: 10px; font-size: 1rem; outline: none; transition: 0.2s; color: #2d3436; }
    .input-group-modern input:focus { border-color: var(--color-primario); background: #fff; }
    .row-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .price-hero { text-align: center; margin-bottom: 30px; }
    .price-input-wrapper { position: relative; display: inline-block; width: 200px; }
    .currency-symbol { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-size: 1.5rem; font-weight: 700; color: #2d3436; }
    .price-hero input { width: 100%; padding: 15px 15px 15px 40px; font-size: 2rem; font-weight: 800; color: var(--color-primario); border: none; border-bottom: 3px solid #eee; text-align: center; outline: none; background: transparent; }
    .branches-container { background: #f9f9f9; padding: 20px; border-radius: 12px; }
    .branch-row { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #eee; }
    .input-mini { width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; text-align: right; }
    .recipe-box { border: 2px solid #f1f2f6; border-radius: 16px; padding: 20px; }
    .recipe-controls { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 15px; }
    .grow { flex: 1; } .small { width: 120px; }
    .btn-add-recipe { background: var(--color-primario); color: white; border: none; width: 48px; height: 48px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .conversion-pill { background: #e3f2fd; color: #0984e3; padding: 10px 15px; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .recipe-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f0f0f0; animation: popIn 0.3s ease; }
    .ri-trash { color: #ff7675; cursor: pointer; }
    .empty-state-recipe { text-align: center; padding: 30px; color: #dfe6e9; } .empty-state-recipe i { font-size: 2rem; display: block; margin-bottom: 10px; }
    .form-footer { padding: 20px 40px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff; border-radius: 0 0 20px 20px; }
    .steps-dots { display: flex; gap: 8px; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: #eee; transition: 0.3s; }
    .dot.active { background: var(--color-primario); transform: scale(1.2); }
    .btn-nav { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
    .prev { background: #fff; color: #636e72; border: 1px solid #ddd; } .prev:disabled { opacity: 0.5; }
    .next { background: var(--color-primario); color: white; box-shadow: 0 4px 15px rgba(253, 121, 168, 0.4); }
    .finish { background: #00b894; color: white; }
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(20, 20, 30, 0.6); backdrop-filter: blur(5px); z-index: 9999; display: none; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-container { background: white; width: 90%; max-width: 400px; border-radius: 20px; overflow: hidden; animation: popIn 0.3s ease; }
    .modal-top-bar { padding: 15px 20px; display: flex; justify-content: space-between; border-bottom: 1px solid #f5f5f5; color: #b2bec3; font-weight: 700; font-size: 0.85rem; }
    .btn-close-modal { background: transparent; border: none; color: #ccc; cursor: pointer; font-size: 1.1rem; }
    .modal-content-inner { padding: 30px; text-align: center; }
    .icon-float { width: 70px; height: 70px; background: #fff0f6; color: var(--color-primario); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 20px; }
    .input-modal-wrap input { width: 100%; padding: 15px; border-radius: 12px; border: 2px solid #f0f0f0; text-align: center; outline: none; font-size: 1.1rem; color: #2d3436; font-weight: 600; }
    .input-modal-wrap input:focus { border-color: var(--color-primario); }
    .modal-actions-row { padding: 20px 30px; background: #fbfbfb; display: flex; gap: 15px; }
    .btn-ghost { flex: 1; padding: 12px; background: transparent; border: none; font-weight: 600; color: #636e72; cursor: pointer; }
    .btn-save-modal { flex: 2; padding: 12px; background: var(--color-primario); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }
    @media (max-width: 768px) {
        .split-layout { grid-template-columns: 1fr; }
        .photo-uploader { min-height: 200px; margin-bottom: 20px; }
        .recipe-controls { flex-direction: column; align-items: stretch; }
        .small { width: 100%; } .btn-add-recipe { width: 100%; }
        .form-footer { padding: 15px 20px; }
        .steps-dots { display: none; }
        .btn-nav { font-size: 0.85rem; padding: 10px 18px; }
        .stepper-container { padding: 20px 10px; } .step-label { display: none; } .stepper-track { left: 30px; right: 30px; }
    }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.9); } 100% { opacity: 1; transform: scale(1); } }
</style>

<script>
    let currentStep = 1;
    const totalSteps = 4;

    // --- 1. LÓGICA DEL NUEVO MODAL DE ALERTAS/CONFIRMACIÓN ---
    const uModal = document.getElementById('modalUniversal');
    const uTitle = document.getElementById('uModalTitle');
    const uMsg   = document.getElementById('uModalMessage');
    const uBtnSi = document.getElementById('uBtnConfirm');
    const uBtnNo = document.getElementById('uBtnCancel');

    function cerrarModalU() {
        if(uModal) uModal.classList.remove('active');
    }

    function usarModalBonito(titulo, mensaje, esConfirmacion, callbackSi) {
        if(!uModal) { alert(mensaje); return; } // Fallback por seguridad

        uTitle.innerText = titulo;
        uMsg.innerText = mensaje;

        // Clonamos el botón para limpiar eventos previos
        const nuevoBtn = uBtnSi.cloneNode(true);
        uBtnSi.parentNode.replaceChild(nuevoBtn, uBtnSi);
        const btnLimpio = document.getElementById('uBtnConfirm');

        if(esConfirmacion) {
            uBtnNo.style.display = 'inline-block';
            btnLimpio.innerText = 'Sí, continuar';
            btnLimpio.onclick = function() {
                cerrarModalU();
                if(callbackSi) callbackSi();
            };
        } else {
            uBtnNo.style.display = 'none';
            btnLimpio.innerText = 'Entendido';
            btnLimpio.onclick = cerrarModalU;
        }

        uModal.classList.add('active');
    }

    // REEMPLAZO DE LA FUNCIÓN mostrarAlerta
    function mostrarAlerta(titulo, mensaje) {
        usarModalBonito(titulo, mensaje, false, null);
    }

    // --- MAPA ---
    const productosMap = {};
    <?php foreach($listaInsumos as $ins): ?>
        productosMap[<?= $ins['pro_id'] ?>] = {
            nombre: "<?= htmlspecialchars($ins['pro_nombre']) ?>",
            unidad: "<?= htmlspecialchars($ins['pro_unidad']) ?>", 
            contenido: <?= (float)$ins['pro_contenido'] ?>,
            uconsumo: "<?= htmlspecialchars($ins['pro_unidad_consumo']) ?>"
        };
    <?php endforeach; ?>

    const recetaGuardada = <?= json_encode($servicio['receta'] ?? []) ?>;

    // --- INICIO ---
    document.addEventListener('DOMContentLoaded', () => {
        for(const [pid, cant] of Object.entries(recetaGuardada)) {
            agregarInsumo(pid, cant);
        }
        actualizarUI(); // Inicia el contador
        actualizarPlaceholders();
    });

    // --- JS DEL BORRADO QUE FUNCIONA ---
    const MAX_FOTOS = 3;
    let fotosGuardadas = <?= count($servicio['galeria'] ?? []) ?>;
    let fotosNuevas = [];
    
    const inputFotos = document.getElementById('inputFotos');
    const container = document.getElementById('galeriaMosaico');
    const btnUpload = document.getElementById('btnUploadCard');
    const counter = document.getElementById('conteoTotal');

    inputFotos.addEventListener('change', function(e) {
        const files = Array.from(this.files);
        if (MAX_FOTOS - (fotosGuardadas + fotosNuevas.length) <= 0) return;
        
        files.forEach(file => {
            if (['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                if (fotosNuevas.length + fotosGuardadas < MAX_FOTOS) fotosNuevas.push(file);
            }
        });
        renderizarNuevas(); actualizarUI(); this.value = '';
    });

    function renderizarNuevas() {
        document.querySelectorAll('.thumb-new').forEach(el => el.remove());
        fotosNuevas.forEach((file, index) => {
            const reader = new FileReader();
            const div = document.createElement('div'); div.className = 'thumb-box thumb-new';
            const btn = document.createElement('div'); btn.className = 'btn-remove-img';
            btn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            btn.onclick = () => { fotosNuevas.splice(index, 1); renderizarNuevas(); actualizarUI(); };
            const img = document.createElement('img'); img.className = 'thumb-img';
            reader.onload = (e) => { img.src = e.target.result; };
            reader.readAsDataURL(file);
            div.appendChild(img); div.appendChild(btn); 
            container.insertBefore(div, btnUpload);
        });
    }

    // --- LA FUNCIÓN BORRAR FOTO (REEMPLAZANDO CONFIRM POR MODAL) ---
    window.borrarFotoServidor = function(imgId, url) {
        // En lugar de confirm(), usamos el nuevo modal bonito
        usarModalBonito(
            "Eliminar Imagen", 
            "¿Estás seguro de eliminar esta foto permanentemente?", 
            true, 
            function() {
                // AQUÍ TU LÓGICA DE SIEMPRE
                const fd = new FormData(); fd.append('img_id', imgId); fd.append('url', url);
                fetch('<?= ruta_accion("servicio", "borrar_foto", [], false) ?>', { method: 'POST', body: fd })
                .then(r => r.json()).then(d => {
                    if(d.success) {
                        const el = document.getElementById('saved-' + imgId);
                        if(el) el.remove();
                        fotosGuardadas--; actualizarUI();
                    } else {
                        mostrarAlerta('Error', d.message);
                    }
                });
            }
        );
    };

    function actualizarUI() {
        let total = fotosGuardadas + fotosNuevas.length;
        counter.innerText = total;
        btnUpload.style.display = (total >= MAX_FOTOS) ? 'none' : 'flex';
    }

    document.getElementById('formServicio').addEventListener('submit', function(e) {
        const dt = new DataTransfer();
        fotosNuevas.forEach(f => dt.items.add(f));
        inputFotos.files = dt.files;
    });

    // --- NAVEGACIÓN ---
    function cambiarPaso(dir) {
        if (dir === 1) {
            if (currentStep === 1 && !document.getElementById('inputCategoriaId').value) {
                mostrarAlerta("Paso 1", "Selecciona una categoría."); return;
            }
            if (currentStep === 2 && !document.getElementById('txtNombre').value) {
                mostrarAlerta("Paso 2", "Escribe el nombre del servicio."); return;
            }
            if (currentStep === 3 && !document.getElementById('precioBase').value) {
                mostrarAlerta("Paso 3", "Define el precio base."); return;
            }
        }
        document.getElementById('step' + currentStep).classList.remove('active-panel');
        document.getElementById('stepIndicator' + currentStep).classList.remove('active');
        currentStep += dir;
        document.getElementById('step' + currentStep).classList.add('active-panel');
        document.getElementById('stepIndicator' + currentStep).classList.add('active');
        if(dir === 1) document.getElementById('stepIndicator' + (currentStep-1)).classList.add('completed');
        actualizarBotones();
    }

    function actualizarBotones() {
        const dots = document.querySelectorAll('.dot');
        dots.forEach((d, i) => d.classList.toggle('active', i === (currentStep - 1)));
        document.getElementById('btnPrev').disabled = (currentStep === 1);
        if (currentStep === totalSteps) {
            document.getElementById('btnNext').style.display = 'none';
            document.getElementById('btnFinish').style.display = 'inline-flex';
        } else {
            document.getElementById('btnNext').style.display = 'inline-flex';
            document.getElementById('btnFinish').style.display = 'none';
        }
    }

    // --- OTRAS FUNCIONES ---
    function seleccionarCategoria(card, id) {
        document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('inputCategoriaId').value = id;
    }
    function abrirModalCategoria() { document.getElementById('modalNuevaCat').classList.add('active'); setTimeout(() => document.getElementById('txtNuevaCat').focus(), 100); }
    function cerrarModalCat() { document.getElementById('modalNuevaCat').classList.remove('active'); document.getElementById('txtNuevaCat').value = ''; }
    function guardarCategoriaExpress() {
        const nombre = document.getElementById('txtNuevaCat').value.trim();
        if(!nombre) { mostrarAlerta("Atención", "Escribe un nombre."); return; }
        const fd = new FormData(); fd.append('nombre', nombre);
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token'); 
        fetch(`index.php?c=tiposervicio&m=guardar_categoria_ajax&token=${token}`, { method: 'POST', body: fd })
        .then(r => r.json()).then(data => {
            if(data.success) {
                cerrarModalCat();
                const grid = document.getElementById('gridCategorias');
                const btnNew = grid.querySelector('.add-new');
                const div = document.createElement('div');
                div.className = 'cat-card selected'; div.style.animation = "popIn 0.5s ease";
                div.onclick = function() { seleccionarCategoria(this, data.id); };
                div.innerHTML = `<div class="cat-icon-bg"><i class="fa-solid fa-scissors"></i></div><span class="cat-name">${data.nombre}</span><div class="check-mark"><i class="fa-solid fa-check"></i></div>`;
                grid.insertBefore(div, btnNew);
                document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
                div.classList.add('selected');
                document.getElementById('inputCategoriaId').value = data.id;
            } else { mostrarAlerta("Error", data.message); }
        }).catch(err => mostrarAlerta("Error", "Error de conexión."));
    }

    // --- RECETA ---
    const selectInsumo = document.getElementById('selectInsumo');
    const inputCant = document.getElementById('cantInsumo');
    const infoConv = document.getElementById('infoConversion');
    window.actualizarUnidadVisual = function() {
        if (!selectInsumo.value) { document.getElementById('lblUnidadUso').innerText = "..."; infoConv.style.display = 'none'; return; }
        const opt = selectInsumo.options[selectInsumo.selectedIndex];
        document.getElementById('lblUnidadUso').innerText = opt.dataset.uconsumo;
        calcular();
    };
    inputCant.addEventListener('input', calcular);
    function calcular() {
        if (!selectInsumo.value || !inputCant.value) { infoConv.style.display = 'none'; return; }
        const opt = selectInsumo.options[selectInsumo.selectedIndex];
        const uso = parseFloat(inputCant.value);
        const contenido = parseFloat(opt.dataset.contenido) || 1;
        document.getElementById('valUso').innerText = uso;
        document.getElementById('txtUso').innerText = opt.dataset.uconsumo;
        document.getElementById('valDesc').innerText = (uso / contenido).toFixed(4);
        document.getElementById('txtBodega').innerText = opt.dataset.unidad;
        infoConv.style.display = 'flex';
    }
    window.agregarInsumo = function(proId = null, cantBd = null) {
        let id, nom, cont, uUso, uG, vis, bd;
        if(proId) { 
            const d = productosMap[proId]; if(!d) return;
            id = proId; nom = d.nombre; cont = d.contenido; uUso = d.uconsumo; uG = d.unidad;
            bd = parseFloat(cantBd); vis = bd * cont;
        } else { 
            if(!selectInsumo.value || !inputCant.value) return;
            const opt = selectInsumo.options[selectInsumo.selectedIndex];
            id = selectInsumo.value; nom = opt.dataset.nombre; cont = parseFloat(opt.dataset.contenido);
            uUso = opt.dataset.uconsumo; uG = opt.dataset.unidad;
            vis = parseFloat(inputCant.value); bd = vis / cont;
        }
        if (document.getElementById('row-ins-' + id)) { if(!proId) mostrarAlerta("Duplicado", "Ya está en la lista."); return; }
        const lista = document.getElementById('listaInsumos');
        document.getElementById('emptyRecipeMsg').style.display = 'none';
        const div = document.createElement('div');
        div.className = 'recipe-item'; div.id = 'row-ins-' + id;
        div.innerHTML = `<div><div class="ri-info">${nom}</div><div class="ri-sub"><span class="ri-badge">Descarga: ${bd.toFixed(4)} ${uG}</span></div></div><div style="display:flex; align-items:center; gap:10px;"><span style="font-weight:700;">${vis.toFixed(2)} ${uUso}</span><i class="fa-solid fa-trash ri-trash" onclick="borrarInsumo('${id}')"></i><input type="hidden" name="insumos[${id}]" value="${bd.toFixed(6)}"></div>`;
        lista.appendChild(div);
        if(!proId) { selectInsumo.value = ""; inputCant.value = ""; infoConv.style.display = 'none'; }
    };
    window.borrarInsumo = function(id) {
        document.getElementById('row-ins-' + id).remove();
        if (document.getElementById('listaInsumos').children.length <= 1) document.getElementById('emptyRecipeMsg').style.display = 'block';
    };
    function actualizarPlaceholders() {
        const base = document.getElementById('precioBase').value;
        document.querySelectorAll('.input-sucursal').forEach(i => i.placeholder = base ? '$' + base : 'Igual');
    }
    function toggleSucursal(chk, id) {
        const input = document.getElementById('precio_' + id);
        input.disabled = !chk.checked;
        input.style.opacity = chk.checked ? "1" : "0.5";
        if(!chk.checked) input.value = '';
    }
</script>