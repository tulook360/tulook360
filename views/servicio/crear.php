<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nuevo Servicio</h1>
        <p class="section-subtitle">Diseña una experiencia única para tus clientes.</p>
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
        
        <div class="step-item active" id="stepIndicator1">
            <div class="step-circle"><i class="fa-solid fa-layer-group"></i></div>
            <span class="step-label">Categoría</span>
        </div>
        <div class="step-item" id="stepIndicator2">
            <div class="step-circle"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <span class="step-label">Detalles</span>
        </div>
        <div class="step-item" id="stepIndicator3">
            <div class="step-circle"><i class="fa-solid fa-tag"></i></div>
            <span class="step-label">Precio</span>
        </div>
        <div class="step-item" id="stepIndicator4">
            <div class="step-circle"><i class="fa-solid fa-flask"></i></div>
            <span class="step-label">Receta</span>
        </div>
    </div>

    <form action="<?= ruta_accion('servicio', 'guardar') ?>" method="POST" enctype="multipart/form-data" autocomplete="off" id="formServicio" class="form-content-box">
        
        <div class="step-panel active-panel" id="step1">
            <div class="panel-header">
                <h3>¿A qué familia pertenece?</h3>
                <p>Selecciona una categoría o crea una nueva al instante.</p>
            </div>
            
            <input type="hidden" name="tser_id" id="inputCategoriaId" required>
            
            <div class="category-grid" id="gridCategorias">
                <?php foreach($listaCategorias as $cat): ?>
                    <div class="cat-card" onclick="seleccionarCategoria(this, <?= $cat['tser_id'] ?>)">
                        <div class="cat-icon-bg"><i class="fa-solid fa-scissors"></i></div>
                        <span class="cat-name"><?= htmlspecialchars($cat['tser_nombre']) ?></span>
                        <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cat-card add-new" onclick="abrirModalCategoria()">
                    <div class="cat-icon-bg"><i class="fa-solid fa-plus"></i></div>
                    <span class="cat-name">Crear Nueva</span>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header">
                <h3>Detalles del Servicio</h3>
                <p>Haz que tu servicio luzca atractivo en la App.</p>
            </div>

            <div class="split-layout">
                <div class="upload-section">
                    <label class="photo-uploader" for="inputFotos">
                        <input type="file" name="fotos[]" id="inputFotos" accept="image/*" multiple hidden>
                        
                        <div class="upload-placeholder" id="placeholderGaleria">
                            <div class="icon-upload"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <strong>Galería de Fotos</strong>
                            <span>Clic aquí para subir (Máx 3)</span>
                        </div>

                        <div id="previewContainer" class="preview-grid"></div>
                    </label>
                </div>

                <div class="inputs-section">
                    <div class="input-group-modern">
                        <label>Nombre del Servicio</label>
                        <input type="text" name="nombre" id="txtNombre" placeholder="Ej: Corte Degradado + Barba">
                    </div>

                    <div class="row-inputs">
                        <div class="input-group-modern">
                            <label>Duración (min)</label>
                            <input type="number" name="duracion" placeholder="30">
                        </div>
                        <div class="input-group-modern">
                            <label>Limpieza (min)</label>
                            <input type="number" name="espera" placeholder="0">
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="4" placeholder="Describe qué incluye el servicio para tus clientes..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header">
                <h3>Estrategia de Precios</h3>
                <p>Define el valor de tu trabajo.</p>
            </div>

            <div class="price-hero">
                <label>Precio Base</label>
                <div class="price-input-wrapper">
                    <span class="currency-symbol">$</span>
                    <input type="number" step="0.01" name="precio" id="precioBase" placeholder="0.00" oninput="actualizarPlaceholders()">
                </div>
            </div>

            <div class="branches-container">
                <h4>Precios por Sucursal (Opcional)</h4>
                <?php foreach($listaSucursales as $suc): ?>
                    <div class="branch-row">
                        <div class="branch-toggle">
                            <input type="checkbox" name="sucursales[]" value="<?= $suc['suc_id'] ?>" id="suc_<?= $suc['suc_id'] ?>" checked onchange="toggleSucursal(this, '<?= $suc['suc_id'] ?>')">
                            <label for="suc_<?= $suc['suc_id'] ?>"><?= htmlspecialchars($suc['suc_nombre']) ?></label>
                        </div>
                        <div class="branch-input">
                            <input type="number" step="0.01" name="precio_sucursal[<?= $suc['suc_id'] ?>]" id="precio_<?= $suc['suc_id'] ?>" class="input-mini" placeholder="Igual al base">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step-panel" id="step4">
            <div class="panel-header">
                <h3>Receta de Inventario</h3>
                <p>Automatiza el descuento de productos.</p>
            </div>

            <div class="recipe-box">
                <div class="recipe-controls">
                    <div class="input-group-modern grow">
                        <label>Producto a descontar</label>
                        <select id="selectInsumo" onchange="actualizarUnidadVisual()">
                            <option value="">-- Buscar Insumo --</option>
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
                    <div class="input-group-modern small">
                        <label>Cantidad (<span id="lblUnidadUso">...</span>)</label>
                        <input type="number" id="cantInsumo" placeholder="0">
                    </div>
                    <button type="button" class="btn-add-recipe" onclick="agregarInsumo()">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                <div id="infoConversion" class="conversion-pill animate-pop" style="display:none;">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Usas <b><span id="valUso">0</span> <span id="txtUso">ml</span></b>. Se descontará <b><span id="valDesc">0</span> <span id="txtBodega">Botella</span></b> del inventario.</span>
                </div>

                <div class="recipe-list-container" id="listaInsumos">
                    <div class="empty-state-recipe" id="emptyRecipeMsg">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <p>No has agregado insumos a esta receta.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-nav prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled>
                <i class="fa-solid fa-arrow-left"></i> Anterior
            </button>
            
            <div class="steps-dots">
                <span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>

            <button type="button" class="btn-nav next" id="btnNext" onclick="cambiarPaso(1)">
                Siguiente <i class="fa-solid fa-arrow-right"></i>
            </button>
            
            <button type="submit" class="btn-nav finish" id="btnFinish" style="display:none;">
                <i class="fa-solid fa-check"></i> Finalizar
            </button>
        </div>

    </form>
</div>

<div class="modal-overlay" id="modalNuevaCat">
    <div class="modal-container animate-pop">
        <div class="modal-top-bar">
            <span>Nueva Categoría</span>
            <button class="btn-close-modal" onclick="cerrarModalCat()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-content-inner">
            <div class="icon-float"><i class="fa-solid fa-tags"></i></div>
            <h3>Organiza tus servicios</h3>
            <p>Crea una nueva categoría para agrupar tus servicios.</p>
            
            <div class="input-modal-wrap">
                <input type="text" id="txtNuevaCat" placeholder="Ej: Tratamientos Faciales" autocomplete="off">
            </div>
        </div>
        <div class="modal-actions-row">
            <button class="btn-ghost" onclick="cerrarModalCat()">Cancelar</button>
            <button class="btn-save-modal" onclick="guardarCategoriaExpress()">Guardar</button>
        </div>
    </div>
</div>

<style>
    /* --- LAYOUT GENERAL --- */
    .main-wrapper { max-width: 850px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; display: flex; flex-direction: column; min-height: 600px; }
    
    /* --- STEPPER --- */
    .stepper-container { background: #fdfdfd; padding: 25px 40px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; position: relative; }
    .stepper-track { position: absolute; top: 40px; left: 60px; right: 60px; height: 3px; background: #eee; z-index: 1; }
    .step-item { z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: default; transition: 0.3s; }
    .step-circle { width: 35px; height: 35px; border-radius: 50%; background: #fff; border: 2px solid #ddd; color: #bbb; display: flex; align-items: center; justify-content: center; transition: 0.3s; font-size: 0.9rem; }
    .step-label { font-size: 0.75rem; font-weight: 600; color: #ccc; text-transform: uppercase; letter-spacing: 0.5px; transition: 0.3s; }
    
    .step-item.active .step-circle { background: var(--color-primario); border-color: var(--color-primario); color: white; box-shadow: 0 0 0 5px rgba(253, 121, 168, 0.15); transform: scale(1.1); }
    .step-item.active .step-label { color: var(--color-primario); }
    .step-item.completed .step-circle { background: #00b894; border-color: #00b894; color: white; }

    /* --- PANELES --- */
    .form-content-box { padding: 30px; flex: 1; position: relative; }
    .step-panel { display: none; animation: slideFade 0.4s cubic-bezier(0.2, 0.8, 0.2, 1); }
    .step-panel.active-panel { display: block; }
    @keyframes slideFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .panel-header { text-align: center; margin-bottom: 35px; }
    .panel-header h3 { margin: 0 0 5px; font-size: 1.6rem; color: #2d3436; font-weight: 800; }
    .panel-header p { margin: 0; color: #b2bec3; }

    /* --- PASO 1: CATEGORÍAS --- */
    .category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
    .cat-card { background: white; border: 2px solid #f1f2f6; border-radius: 16px; padding: 25px 15px; display: flex; flex-direction: column; align-items: center; gap: 15px; cursor: pointer; transition: 0.2s; position: relative; overflow: hidden; }
    .cat-card:hover { transform: translateY(-5px); border-color: var(--color-primario); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    .cat-card.selected { border-color: var(--color-primario); background: #fff5f8; }
    .cat-card.selected .check-mark { transform: scale(1); opacity: 1; }
    
    .cat-icon-bg { width: 50px; height: 50px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #636e72; transition: 0.2s; }
    .cat-card.selected .cat-icon-bg { background: var(--color-primario); color: white; }
    .cat-name { font-weight: 700; color: #2d3436; text-align: center; font-size: 0.95rem; }
    .check-mark { position: absolute; top: 10px; right: 10px; width: 24px; height: 24px; background: var(--color-primario); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transform: scale(0); opacity: 0; transition: 0.3s; }
    .add-new { border-style: dashed; border-color: #ccc; }

    /* --- PASO 2: DETALLES & FOTOS (CORREGIDO) --- */
    .split-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; height: 100%; }
    
    /* Área de Upload */
    .upload-section { display: flex; flex-direction: column; }
    .photo-uploader { flex: 1; border: 2px dashed #dfe6e9; border-radius: 16px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; background: #fdfdfd; position: relative; overflow: hidden; min-height: 300px; }
    .photo-uploader:hover { border-color: var(--color-primario); background: #fff9fc; }
    
    .upload-placeholder { text-align: center; color: #b2bec3; display: flex; flex-direction: column; align-items: center; gap: 10px; }
    .icon-upload { font-size: 3rem; color: #e2e8f0; margin-bottom: 5px; }
    .upload-placeholder strong { color: #636e72; font-size: 1.1rem; }
    
    /* Grid de Fotos Subidas */
    .preview-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; width: 100%; height: 100%; padding: 10px; position: absolute; top: 0; left: 0; background: white; overflow-y: auto; display: none; }
    .preview-grid.has-files { display: grid; } /* Se activa con JS */
    
    .img-card { position: relative; width: 100%; height: 140px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .img-card img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Botón Eliminar Foto */
    .btn-delete-img { position: absolute; top: 5px; right: 5px; width: 28px; height: 28px; background: rgba(255, 255, 255, 0.9); border-radius: 50%; color: #ff7675; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.2s; z-index: 10; }
    .btn-delete-img:hover { background: #ff7675; color: white; transform: scale(1.1); }

    /* Inputs */
    .inputs-section { display: flex; flex-direction: column; gap: 20px; }
    .input-group-modern label { display: block; font-weight: 700; color: #636e72; margin-bottom: 8px; font-size: 0.9rem; }
    .input-group-modern input, .input-group-modern textarea, .input-group-modern select { width: 100%; padding: 12px 15px; border: 2px solid #f1f2f6; border-radius: 10px; font-size: 1rem; outline: none; transition: 0.2s; color: #2d3436; font-family: inherit; }
    .input-group-modern input:focus { border-color: var(--color-primario); background: #fff; }
    .row-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    /* --- PASO 3: PRECIOS --- */
    .price-hero { text-align: center; margin-bottom: 30px; }
    .price-input-wrapper { position: relative; display: inline-block; width: 200px; }
    .currency-symbol { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-size: 1.5rem; font-weight: 700; color: #2d3436; }
    .price-hero input { width: 100%; padding: 15px 15px 15px 40px; font-size: 2rem; font-weight: 800; color: var(--color-primario); border: none; border-bottom: 3px solid #eee; text-align: center; outline: none; background: transparent; transition: 0.2s; }
    .branches-container { background: #f9f9f9; padding: 20px; border-radius: 12px; }
    .branch-row { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #eee; }
    .input-mini { width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; text-align: right; }

    /* --- PASO 4: RECETA --- */
    .recipe-box { border: 2px solid #f1f2f6; border-radius: 16px; padding: 20px; }
    .recipe-controls { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 15px; }
    .grow { flex: 1; } .small { width: 120px; }
    .btn-add-recipe { background: var(--color-primario); color: white; border: none; width: 48px; height: 48px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 2px; }
    .conversion-pill { background: #e3f2fd; color: #0984e3; padding: 10px 15px; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .recipe-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f0f0f0; animation: popIn 0.3s ease; }
    .ri-trash { color: #ff7675; cursor: pointer; }
    .empty-state-recipe { text-align: center; padding: 30px; color: #dfe6e9; } .empty-state-recipe i { font-size: 2rem; display: block; margin-bottom: 10px; }

    /* --- FOOTER NAVEGACIÓN (CORREGIDO MÓVIL) --- */
    .form-footer { padding: 20px 40px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff; border-radius: 0 0 20px 20px; }
    .steps-dots { display: flex; gap: 8px; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: #eee; transition: 0.3s; }
    .dot.active { background: var(--color-primario); transform: scale(1.2); }
    .btn-nav { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
    .prev { background: #fff; color: #636e72; border: 1px solid #ddd; } .prev:disabled { opacity: 0.5; }
    .next { background: var(--color-primario); color: white; box-shadow: 0 4px 15px rgba(253, 121, 168, 0.4); }
    .finish { background: #00b894; color: white; }

    /* --- MODAL --- */
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

    /* --- RESPONSIVE (CELULAR) --- */
    @media (max-width: 768px) {
        .split-layout { grid-template-columns: 1fr; }
        .photo-uploader { min-height: 200px; margin-bottom: 20px; }
        .recipe-controls { flex-direction: column; align-items: stretch; }
        .small { width: 100%; } .btn-add-recipe { width: 100%; }
        
        /* Arreglo Footer Móvil */
        .form-footer { padding: 15px 20px; }
        .steps-dots { display: none; } /* Ocultamos los puntos para dar espacio */
        .btn-nav { font-size: 0.85rem; padding: 10px 18px; }
        
        .panel-header h3 { font-size: 1.3rem; }
        .stepper-container { padding: 20px 10px; }
        .step-label { display: none; } /* Ocultar texto stepper en móvil */
        .stepper-track { left: 30px; right: 30px; }
    }
    
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.9); } 100% { opacity: 1; transform: scale(1); } }
</style>

<script>
    let currentStep = 1;
    const totalSteps = 4;

    // --- ALERTA MODAL ---
    function mostrarAlerta(titulo, mensaje) {
        const modal = document.getElementById('modalConfirm');
        if(!modal) return alert(mensaje);

        document.getElementById('modalTitle').innerText = titulo;
        document.getElementById('modalText').innerText = mensaje;
        
        const btnCancel = document.getElementById('btnModalCancelar');
        if(btnCancel) btnCancel.style.display = 'none';

        const btnConfirm = document.getElementById('btnModalConfirmar');
        btnConfirm.innerText = "Entendido";
        btnConfirm.style.backgroundColor = "var(--color-primario)";
        
        const newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.onclick = function() {
            modal.classList.remove('active');
            setTimeout(() => { if(btnCancel) btnCancel.style.display = 'inline-block'; }, 300);
        };
        modal.classList.add('active');
    }

    // --- CATEGORÍA ---
    function seleccionarCategoria(card, id) {
        document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('inputCategoriaId').value = id;
    }

    function abrirModalCategoria() {
        document.getElementById('modalNuevaCat').classList.add('active');
        setTimeout(() => document.getElementById('txtNuevaCat').focus(), 100);
    }
    function cerrarModalCat() {
        document.getElementById('modalNuevaCat').classList.remove('active');
        document.getElementById('txtNuevaCat').value = '';
    }

    function guardarCategoriaExpress() {
        const nombre = document.getElementById('txtNuevaCat').value.trim();
        if(!nombre) { mostrarAlerta("Atención", "Escribe un nombre."); return; }

        const fd = new FormData();
        fd.append('nombre', nombre);
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token'); 

        fetch(`index.php?c=tiposervicio&m=guardar_categoria_ajax&token=${token}`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                cerrarModalCat();
                const grid = document.getElementById('gridCategorias');
                const btnNew = grid.querySelector('.add-new');
                
                const div = document.createElement('div');
                div.className = 'cat-card selected';
                div.style.animation = "popIn 0.5s ease";
                div.onclick = function() { seleccionarCategoria(this, data.id); };
                div.innerHTML = `<div class="cat-icon-bg"><i class="fa-solid fa-scissors"></i></div><span class="cat-name">${data.nombre}</span><div class="check-mark"><i class="fa-solid fa-check"></i></div>`;
                
                grid.insertBefore(div, btnNew);
                document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
                div.classList.add('selected');
                document.getElementById('inputCategoriaId').value = data.id;
            } else {
                mostrarAlerta("Error", data.message);
            }
        })
        .catch(err => mostrarAlerta("Error", "Error de conexión."));
    }

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

    // --- GALERÍA FOTOS (MEJORADO CON PREVIEW Y BORRAR) ---
    const inputFotos = document.getElementById('inputFotos');
    const previewCont = document.getElementById('previewContainer');
    const placeholder = document.getElementById('placeholderGaleria');
    let archivos = []; // Array global para manejar los archivos reales

    inputFotos.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if (archivos.length + files.length > 3) { mostrarAlerta("Límite", "Máximo 3 fotos."); return; }
        
        files.forEach(f => archivos.push(f));
        actualizarInputFiles(); // Sincroniza el input real
        renderizarFotos();
    });

    function renderizarFotos() {
        previewCont.innerHTML = '';
        
        if(archivos.length > 0) {
            placeholder.style.display = 'none';
            previewCont.classList.add('has-files'); // Activa el grid CSS
            
            archivos.forEach((f, i) => {
                // Crear tarjeta de imagen
                const div = document.createElement('div');
                div.className = 'img-card';
                
                const img = document.createElement('img');
                img.src = URL.createObjectURL(f);
                
                // Botón Borrar (X)
                const btnDel = document.createElement('div');
                btnDel.className = 'btn-delete-img';
                btnDel.innerHTML = '<i class="fa-solid fa-times"></i>';
                btnDel.onclick = (e) => {
                    e.preventDefault(); // Evita que abra el selector de archivos
                    borrarFoto(i);
                };

                div.appendChild(img);
                div.appendChild(btnDel);
                previewCont.appendChild(div);
            });
        } else {
            placeholder.style.display = 'flex'; // Vuelve a mostrar el mensaje vacío
            previewCont.classList.remove('has-files');
        }
    }

    function borrarFoto(index) {
        archivos.splice(index, 1); // Borra del array
        actualizarInputFiles(); // Actualiza input
        renderizarFotos(); // Redibuja
    }

    // Truco: DataTransfer permite modificar los archivos de un input type="file"
    function actualizarInputFiles() {
        const dt = new DataTransfer();
        archivos.forEach(f => dt.items.add(f));
        inputFotos.files = dt.files;
    }

    // --- RECETA / INSUMOS ---
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

    window.agregarInsumo = function() {
        if (!selectInsumo.value || !inputCant.value) return;
        const opt = selectInsumo.options[selectInsumo.selectedIndex];
        const id = selectInsumo.value;
        if (document.getElementById('row-ins-' + id)) { mostrarAlerta("Duplicado", "Este insumo ya está agregado."); return; }

        const lista = document.getElementById('listaInsumos');
        document.getElementById('emptyRecipeMsg').style.display = 'none';

        const contenido = parseFloat(opt.dataset.contenido) || 1;
        const cantidadUso = parseFloat(inputCant.value);
        const valorGuardar = cantidadUso / contenido;

        const div = document.createElement('div');
        div.className = 'recipe-item';
        div.id = 'row-ins-' + id;
        div.innerHTML = `
            <div>
                <div class="ri-info">${opt.dataset.nombre}</div>
                <div class="ri-sub"><span class="ri-badge">Descarga: ${valorGuardar.toFixed(4)} ${opt.dataset.unidad}</span></div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:700;">${cantidadUso} ${opt.dataset.uconsumo}</span>
                <i class="fa-solid fa-trash ri-trash" onclick="borrarInsumo('${id}')"></i>
                <input type="hidden" name="insumos[${id}]" value="${valorGuardar.toFixed(6)}">
            </div>
        `;
        lista.appendChild(div);
        selectInsumo.value = ""; inputCant.value = ""; infoConv.style.display = 'none';
    };

    window.borrarInsumo = function(id) {
        document.getElementById('row-ins-' + id).remove();
        if (document.getElementById('listaInsumos').children.length <= 1) document.getElementById('emptyRecipeMsg').style.display = 'block';
    };

    // --- PRECIOS ---
    function actualizarPlaceholders() {
        const base = document.getElementById('precioBase').value;
        document.querySelectorAll('.input-mini').forEach(i => i.placeholder = base ? '$' + base : 'Igual');
    }
    
    function toggleSucursal(chk, id) {
        const input = document.getElementById('precio_' + id);
        input.disabled = !chk.checked;
        input.style.opacity = chk.checked ? "1" : "0.5";
    }
</script>