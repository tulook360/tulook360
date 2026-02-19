<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nuevo Producto</h1>
        <p class="section-subtitle">Registra ítems para venta o consumo interno.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('producto', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-xmark"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="main-wrapper">
    
    <div class="stepper-container">
        <div class="stepper-track"></div>
        <div class="step-item active" id="stepIndicator1"><div class="step-circle"><i class="fa-solid fa-layer-group"></i></div><span class="step-label">Categoría</span></div>
        <div class="step-item" id="stepIndicator2"><div class="step-circle"><i class="fa-solid fa-box-open"></i></div><span class="step-label">Detalles</span></div>
        <div class="step-item" id="stepIndicator3"><div class="step-circle"><i class="fa-solid fa-sliders"></i></div><span class="step-label">Configuración</span></div>
    </div>

    <form action="<?= ruta_accion('producto', 'guardar') ?>" method="POST" enctype="multipart/form-data" autocomplete="off" id="formProducto" class="form-content-box">
        
        <div class="step-panel active-panel" id="step1">
            <div class="panel-header">
                <h3>Clasificación del Producto</h3>
                <p>Selecciona una categoría obligatoria.</p>
            </div>
            
            <input type="hidden" name="tpro_id" id="inputCategoriaId">
            
            <div class="category-grid" id="gridCategorias">
                <?php foreach($listaCategorias as $cat): ?>
                    <div class="cat-card" onclick="seleccionarCategoria(this, <?= $cat['tpro_id'] ?>)">
                        <div class="cat-icon-bg"><i class="fa-solid fa-box"></i></div>
                        <span class="cat-name"><?= htmlspecialchars($cat['tpro_nombre']) ?></span>
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
                <h3>Información del Producto</h3>
                <p>Todos los campos e imágenes son obligatorios.</p>
            </div>

            <div class="split-layout">
                <div class="upload-section">
                    <label class="photo-uploader" id="boxFotoUploader" for="inputFotos">
                        <input type="file" name="fotos[]" id="inputFotos" accept="image/*" multiple hidden>
                        <div class="upload-placeholder" id="placeholderGaleria">
                            <div class="icon-upload"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <strong>Fotos del Producto <span style="color:red">*</span></strong>
                            <span>Sube al menos 1 imagen</span>
                        </div>
                        <div id="previewContainer" class="preview-grid"></div>
                    </label>
                    <div id="errorFotos" class="error-msg" style="display:none; text-align:center;">Debes subir al menos 1 foto.</div>
                </div>

                <div class="inputs-section">
                    <div class="input-group-modern">
                        <label>Nombre del Producto <span style="color:red">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-tag input-icon"></i>
                            <input type="text" name="nombre" id="txtNombre" placeholder="Ej: Shampoo Argán 500ml">
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label>Código de Barras / SKU <span style="color:red">*</span></label>
                        <div class="input-icon-wrap" style="position:relative;">
                            <i class="fa-solid fa-barcode input-icon"></i>
                            <input type="text" name="codigo" id="txtCodigo" placeholder="Escribe o genera...">
                            <i class="fa-solid fa-arrows-rotate icon-inside-right" onclick="generarCodigo()" title="Generar al azar"></i>
                        </div>
                    </div>

                    <div class="row-inputs">
                        <div class="input-group-modern">
                            <label>Stock Inicial <span style="color:red">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-boxes-stacked input-icon"></i>
                                <input type="number" name="stock" id="txtStock" placeholder="0">
                            </div>
                        </div>
                        <div class="input-group-modern">
                            <label>Costo de Compra ($) <span style="color:red">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="fa-solid fa-sack-dollar input-icon"></i>
                                <input type="number" step="0.01" name="costo" id="txtCosto" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label>Descripción <span style="color:red">*</span></label>
                        <div class="input-icon-wrap">
                            <i class="fa-solid fa-align-left input-icon" style="top: 15px;"></i>
                            <textarea name="descripcion" id="txtDescripcion" rows="3" placeholder="Detalles del producto..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header">
                <h3>Configuración de Uso</h3>
                <p>Selecciona el tipo y define la presentación.</p>
            </div>

            <div class="product-type-selector" id="boxTiposUso">
                <label class="type-card" id="cardVenta">
                    <input type="checkbox" name="venta" value="1" id="checkVenta" onchange="toggleSections()">
                    <div class="type-icon"><i class="fa-solid fa-cash-register"></i></div>
                    <div class="type-info">
                        <strong>Para Venta</strong>
                        <span>El cliente se lo lleva.</span>
                    </div>
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                </label>

                <label class="type-card" id="cardInsumo">
                    <input type="checkbox" name="insumo" value="1" id="checkInsumo" onchange="toggleSections()">
                    <div class="type-icon"><i class="fa-solid fa-flask"></i></div>
                    <div class="type-info">
                        <strong>Es Insumo</strong>
                        <span>Se usa en servicios.</span>
                    </div>
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                </label>
            </div>
            <div id="errorTiposUso"></div>

            <div id="sectionVenta" class="dynamic-box animate-pop" style="display:none;">
                <div class="price-hero">
                    <label>Precio de Venta ($) <span style="color:red">*</span></label>
                    <div class="price-input-wrapper">
                        <span class="currency-symbol">$</span>
                        <input type="number" step="0.01" name="precio" id="inputPrecioVenta" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div id="sectionPresentacion" class="dynamic-box animate-pop" style="border-color:#6c5ce7;">
                <h4 style="color:#6c5ce7; margin:0 0 15px;"><i class="fa-solid fa-box-open"></i> Presentación y Medida</h4>
                
                <div class="conversion-row">
                    <div class="conv-group">
                        <label>Unidad de Compra</label>
                        <select name="unidad" id="unidadCompra" onchange="actualizarFrases()">
                            <option value="Unidad">Unidad (Suelta)</option>
                            <option value="Botella">Botella</option>
                            <option value="Caja">Caja</option>
                            <option value="Paquete">Paquete</option>
                            <option value="Galon">Galón</option>
                        </select>
                    </div>
                    <div class="conv-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                    <div class="conv-group">
                        <label>Contiene <span style="color:red">*</span></label>
                        <div class="input-icon-wrap small-icon">
                            <i class="fa-solid fa-hashtag input-icon"></i>
                            <input type="number" step="0.01" name="contenido" id="inputContenido" placeholder="Cant.">
                        </div>
                    </div>
                    <div class="conv-group">
                        <label>Unidad de Uso</label>
                        <select name="unidad_consumo" id="unidadConsumo" onchange="actualizarFrases()">
                            <option value="ml">Mililitros (ml)</option>
                            <option value="gr">Gramos (gr)</option>
                            <option value="oz">Onzas</option>
                            <option value="unidades">Unidades</option>
                            <option value="pares">Pares</option>
                            <option value="aplicaciones">Aplicaciones</option>
                        </select>
                    </div>
                </div>
                <p class="helper-text" id="lblEjemploConversion">Define la cantidad (Ej: 1000 ml).</p>
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-nav prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled>
                <i class="fa-solid fa-arrow-left"></i> Anterior
            </button>
            <div class="steps-dots">
                <span class="dot active"></span><span class="dot"></span><span class="dot"></span>
            </div>
            <button type="button" class="btn-nav next" id="btnNext" onclick="cambiarPaso(1)">
                Siguiente <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="submit" class="btn-nav finish" id="btnFinish" style="display:none;">
                <i class="fa-solid fa-check"></i> Guardar Producto
            </button>
        </div>
    </form>
</div>

<div class="modal-overlay" id="modalNuevaCat">
    <div class="modal-container animate-pop">
        <div class="modal-top-bar">
            <span>Nueva Categoría de Producto</span>
            <button class="btn-close-modal" onclick="cerrarModalCat()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-content-inner">
            <div class="icon-float"><i class="fa-solid fa-boxes-stacked"></i></div>
            <h3>Organiza tu Inventario</h3>
            <p>Crea una nueva categoría para agrupar tus productos.</p>
            <div class="input-modal-wrap">
                <input type="text" id="txtNuevaCat" placeholder="Ej: Shampoos, Bebidas, Insumos..." autocomplete="off">
            </div>
        </div>
        <div class="modal-actions-row">
            <button class="btn-ghost" onclick="cerrarModalCat()">Cancelar</button>
            <button class="btn-save-modal" onclick="guardarCategoriaExpress()">Guardar</button>
        </div>
    </div>
</div>

<style>
    /* VALIDACIÓN VISUAL */
    .input-error { border-color: #ff4757 !important; background-color: #fff5f5 !important; }
    .photo-uploader.input-error { border-color: #ff4757; background-color: #fff5f5; }
    .error-msg { color: #ff4757; font-size: 0.75rem; font-weight: 700; margin-top: 5px; display: block; animation: fadeIn 0.3s ease; }
    
    /* INPUTS CON ICONOS (NUEVO) */
    .input-icon-wrap { position: relative; width: 100%; }
    .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #b2bec3; font-size: 1rem; transition: 0.2s; pointer-events: none; }
    /* Agregamos .input-icon-wrap input para que afecte también al campo de conversión */
    .input-group-modern input, .input-group-modern textarea, .input-icon-wrap input { 
        padding-left: 45px !important; 
    } 
    .input-group-modern textarea { padding-top: 12px; }
    .input-group-modern input:focus + .input-icon, 
    .input-group-modern input:focus ~ .input-icon,
    .input-group-modern textarea:focus ~ .input-icon { color: var(--color-primario); }
    
    /* ICONO RIGHT (BARCODE) */
    .icon-inside-right { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #b2bec3; cursor: pointer; transition: 0.2s; font-size: 1.1rem; }
    .icon-inside-right:hover { color: var(--color-primario); transform: scale(1.1); }

    /* LAYOUT & ESTILOS BASE */
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
    .photo-uploader { flex: 1; border: 2px dashed #dfe6e9; border-radius: 16px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; background: #fdfdfd; position: relative; overflow: hidden; min-height: 300px; }
    .photo-uploader:hover { border-color: var(--color-primario); background: #fff9fc; }
    .upload-placeholder { text-align: center; color: #b2bec3; display: flex; flex-direction: column; align-items: center; gap: 10px; }
    .icon-upload { font-size: 3rem; color: #e2e8f0; margin-bottom: 5px; }
    .preview-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; width: 100%; height: 100%; padding: 10px; position: absolute; top: 0; left: 0; background: white; overflow-y: auto; display: none; }
    .preview-grid.has-files { display: grid; }
    .img-card { position: relative; width: 100%; height: 140px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .img-card img { width: 100%; height: 100%; object-fit: cover; }
    .btn-delete-img { position: absolute; top: 5px; right: 5px; width: 28px; height: 28px; background: rgba(255, 255, 255, 0.9); border-radius: 50%; color: #ff7675; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; z-index: 10; }
    .btn-delete-img:hover { background: #ff7675; color: white; transform: scale(1.1); }
    .input-group-modern { margin-bottom: 20px; }
    .input-group-modern label { display: block; font-weight: 700; color: #636e72; margin-bottom: 8px; font-size: 0.9rem; }
    .input-group-modern input, .input-group-modern textarea, .input-group-modern select { width: 100%; padding: 12px 15px; border: 2px solid #f1f2f6; border-radius: 10px; font-size: 1rem; outline: none; transition: 0.2s; color: #2d3436; }
    .input-group-modern input:focus, .input-group-modern select:focus, .input-group-modern textarea:focus { border-color: var(--color-primario); background: #fff; }
    .row-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .product-type-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .type-card { background: white; border: 2px solid #f1f2f6; border-radius: 16px; padding: 20px; cursor: pointer; display: flex; align-items: center; gap: 15px; transition: 0.2s; position: relative; }
    .type-card:hover { transform: translateY(-3px); border-color: #dfe6e9; }
    .type-card input { display: none; }
    #cardVenta:has(input:checked) { border-color: #00b894; background: #f0fdf4; box-shadow: 0 4px 15px rgba(0, 184, 148, 0.1); }
    #cardVenta .type-icon { color: #00b894; background: #e3fcf7; }
    #cardInsumo:has(input:checked) { border-color: #e84393; background: #fff0f6; box-shadow: 0 4px 15px rgba(232, 67, 147, 0.1); }
    #cardInsumo .type-icon { color: #e84393; background: #ffe3ef; }
    .type-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; background: #f8f9fa; color: #b2bec3; transition: 0.2s; }
    .type-info strong { display: block; font-size: 1rem; margin-bottom: 2px; }
    .type-info span { font-size: 0.8rem; color: #636e72; }
    .check-circle { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ddd; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: 0.2s; margin-left: auto; }
    .type-card input:checked ~ .check-circle { background: currentColor; border-color: currentColor; }
    #cardVenta input:checked ~ .check-circle { background: #00b894; border-color: #00b894; }
    #cardInsumo input:checked ~ .check-circle { background: #e84393; border-color: #e84393; }
    .dynamic-box { padding: 25px; border-radius: 12px; border: 2px solid #f1f2f6; margin-bottom: 20px; animation: popIn 0.3s ease; }
    .price-hero { text-align: center; }
    .price-input-wrapper { position: relative; display: inline-block; width: 200px; }
    .currency-symbol { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); font-size: 1.5rem; font-weight: 700; color: #2d3436; }
    .price-hero input { width: 100%; padding: 15px 15px 15px 40px; font-size: 2rem; font-weight: 800; color: var(--color-primario); border: none; border-bottom: 3px solid #eee; text-align: center; outline: none; background: transparent; }
    .conversion-row { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .conv-group { flex: 1; }
    .conv-group label { font-size: 0.75rem; font-weight: 700; color: #636e72; margin-bottom: 5px; display: block; }
    .conv-group select, .conv-group input { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; }
    .conv-arrow { color: #b2bec3; font-size: 1.2rem; }
    .helper-text { font-size: 0.85rem; color: #636e72; margin-top: 10px; text-align: center; background: #fff; padding: 8px; border-radius: 8px; font-style: italic; }
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
        .product-type-selector { grid-template-columns: 1fr; }
        .conversion-row { flex-direction: column; align-items: stretch; }
        .conv-arrow { transform: rotate(90deg); margin: 10px 0; text-align: center; }
        .form-footer { padding: 15px 20px; }
        .steps-dots { display: none; }
        .btn-nav { font-size: 0.85rem; padding: 10px 18px; }
        .stepper-container { padding: 20px 10px; } .step-label { display: none; } .stepper-track { left: 30px; right: 30px; }
    }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.9); } 100% { opacity: 1; transform: scale(1); } }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
    let currentStep = 1;
    const totalSteps = 3;

    document.addEventListener('DOMContentLoaded', () => {
        toggleSections();
        actualizarFrases();
    });

    // --- VALIDACIÓN VISUAL ---
    function validarCampo(idCampo, mensajeError) {
        const input = document.getElementById(idCampo);
        if (!input) return true;

        input.classList.remove('input-error');
        const parent = input.closest('.input-group-modern') || input.parentElement;
        const oldMsg = parent.querySelector('.error-msg');
        if (oldMsg) oldMsg.remove();

        const valor = input.value.trim();
        if (valor === '') {
            input.classList.add('input-error');
            const msg = document.createElement('span');
            msg.className = 'error-msg';
            msg.innerText = mensajeError;
            parent.appendChild(msg);
            return false;
        }
        return true;
    }

    // --- NAVEGACIÓN ---
    function cambiarPaso(dir) {
        if (dir === 1) {
            // VALIDACIÓN PASO 1 (Categoría)
            if (currentStep === 1) {
                if (!document.getElementById('inputCategoriaId').value) {
                    mostrarAlerta("Atención", "Selecciona una categoría.");
                    return;
                }
            }

            // VALIDACIÓN PASO 2 (Detalles + FOTOS OBLIGATORIAS)
            if (currentStep === 2) {
                let valido = true;
                
                // Inputs
                if (!validarCampo('txtNombre', 'Nombre obligatorio.')) valido = false;
                if (!validarCampo('txtCodigo', 'Código obligatorio.')) valido = false;
                if (!validarCampo('txtStock', 'Ingresa el stock.')) valido = false;
                if (!validarCampo('txtCosto', 'Ingresa el costo.')) valido = false;
                if (!validarCampo('txtDescripcion', 'Escribe una descripción.')) valido = false;

                // Fotos
                const boxFoto = document.getElementById('boxFotoUploader');
                const errFoto = document.getElementById('errorFotos');
                boxFoto.classList.remove('input-error');
                errFoto.style.display = 'none';

                if (archivos.length === 0) {
                    boxFoto.classList.add('input-error');
                    errFoto.style.display = 'block';
                    valido = false;
                }

                if (!valido) return; 
            }
        }

        document.getElementById('step' + currentStep).classList.remove('active-panel');
        document.getElementById('stepIndicator' + currentStep).classList.remove('active');
        currentStep += dir;
        document.getElementById('step' + currentStep).classList.add('active-panel');
        document.getElementById('stepIndicator' + currentStep).classList.add('active');
        actualizarBotones();
    }

    // --- VALIDACIÓN FINAL (SUBMIT) ---
    const form = document.getElementById('formProducto');
    form.addEventListener('submit', function(e) {
        let esValido = true;
        
        // Paso 3: Validar Checkboxes
        const isVenta = document.getElementById('checkVenta').checked;
        const isInsumo = document.getElementById('checkInsumo').checked;
        const errorBox = document.getElementById('errorTiposUso');
        errorBox.innerHTML = ''; 

        if (!isVenta && !isInsumo) {
            errorBox.innerHTML = '<span class="error-msg" style="text-align:center; margin-bottom:15px;">Selecciona al menos una opción.</span>';
            esValido = false;
        }

        // Paso 3: Validar Precio (Solo si es venta)
        if (isVenta) {
            if (!validarCampo('inputPrecioVenta', 'Precio obligatorio.')) esValido = false;
        }

        // Paso 3: Validar Contenido (Siempre obligatorio)
        if (!validarCampo('inputContenido', 'Cantidad obligatoria.')) esValido = false;

        if (!esValido) {
            e.preventDefault();
            mostrarAlerta("Faltan Datos", "Completa los campos en rojo.");
        } else {
            const dt = new DataTransfer();
            archivos.forEach(f => dt.items.add(f));
            inputFotos.files = dt.files;
        }
    });

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
        fetch(`index.php?c=tipoproducto&m=guardar_categoria_ajax&token=${token}`, { method: 'POST', body: fd })
        .then(r => r.json()).then(data => {
            if(data.success) {
                cerrarModalCat();
                const grid = document.getElementById('gridCategorias');
                const btnNew = grid.querySelector('.add-new');
                const div = document.createElement('div');
                div.className = 'cat-card selected'; div.style.animation = "popIn 0.5s ease";
                div.onclick = function() { seleccionarCategoria(this, data.id); };
                div.innerHTML = `<div class="cat-icon-bg"><i class="fa-solid fa-box"></i></div><span class="cat-name">${data.nombre}</span><div class="check-mark"><i class="fa-solid fa-check"></i></div>`;
                grid.insertBefore(div, btnNew);
                document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
                div.classList.add('selected');
                document.getElementById('inputCategoriaId').value = data.id;
            } else { mostrarAlerta("Error", data.message); }
        })
        .catch(err => mostrarAlerta("Error", "Error de conexión."));
    }

    function toggleSections() {
        const isVenta = document.getElementById('checkVenta').checked;
        document.getElementById('sectionVenta').style.display = isVenta ? 'block' : 'none';
        
        // Limpiar validaciones si se oculta
        if (!isVenta) {
            const precioInput = document.getElementById('inputPrecioVenta');
            precioInput.classList.remove('input-error');
            const parent = precioInput.closest('.price-input-wrapper').parentElement;
            const msg = parent.querySelector('.error-msg');
            if(msg) msg.remove();
        }
    }

    function actualizarFrases() {
        const u = document.getElementById('unidadCompra').value;
        const c = document.getElementById('inputContenido').value || '_';
        const uc = document.getElementById('unidadConsumo').value;
        document.getElementById('lblEjemploConversion').innerHTML = `Ej: 1 <b>${u}</b> contiene <b>${c} ${uc}</b>.`;
    }
    
    document.getElementById('unidadCompra').addEventListener('change', actualizarFrases);
    document.getElementById('inputContenido').addEventListener('input', actualizarFrases);
    document.getElementById('unidadConsumo').addEventListener('change', actualizarFrases);

    function generarCodigo() {
        document.getElementById('txtCodigo').value = Math.floor(Math.random() * 90000000) + 10000000;
    }

    // --- GALERÍA ---
    const inputFotos = document.getElementById('inputFotos');
    const previewCont = document.getElementById('previewContainer');
    const placeholder = document.getElementById('placeholderGaleria');
    let archivos = [];

    inputFotos.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        if (archivos.length + files.length > 3) { mostrarAlerta("Límite", "Máximo 3 fotos."); return; }
        files.forEach(f => archivos.push(f));
        renderNuevas();
        // Limpiar error visual si ya hay fotos
        if(archivos.length > 0) {
            document.getElementById('boxFotoUploader').classList.remove('input-error');
            document.getElementById('errorFotos').style.display = 'none';
        }
    });

    function renderNuevas() {
        previewCont.innerHTML = '';
        if(archivos.length > 0) {
            placeholder.style.display = 'none';
            previewCont.classList.add('has-files');
            archivos.forEach((f, i) => {
                const div = document.createElement('div'); div.className = 'img-card';
                div.innerHTML = `<img src="${URL.createObjectURL(f)}"><div class="btn-delete-img"><i class="fa-solid fa-times"></i></div>`;
                div.querySelector('.btn-delete-img').onclick = (e) => { e.preventDefault(); borrarNueva(i); };
                previewCont.appendChild(div);
            });
        } else {
            placeholder.style.display = 'flex';
            previewCont.classList.remove('has-files');
        }
    }

    function borrarNueva(index) {
        archivos.splice(index, 1);
        renderNuevas();
    }
</script>