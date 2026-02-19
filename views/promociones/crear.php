<?php
// views/promociones/crear.php
?>
<style>
    /* --- ESTRUCTURA GLOBAL --- */
    :root {
        --color-brand: #e84393; /* Tu rosa principal */
        --bg-body: #f8f9fa;
        --text-dark: #2d3436;
    }

    /* 1. HEADER EXTERNO (FUERA DE LA CAJA) */
    .page-header-external {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 30px;
        padding: 0 10px; /* Pequeño margen lateral */
    }
    .kalam-title { font-family: 'Kalam', cursive; font-size: 2.5rem; color: var(--text-dark); margin: 0; line-height: 1; }
    .subtitle { color: #636e72; margin: 5px 0 0 5px; font-size: 1rem; }
    
    .btn-cancel-external {
        background: #dfe6e9; color: #636e72; 
        padding: 12px 25px; border-radius: 50px; text-decoration: none; 
        font-weight: 700; display: inline-flex; align-items: center; gap: 10px;
        transition: 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .btn-cancel-external:hover { background: #b2bec3; color: white; transform: translateY(-2px); }

    /* 2. CONTENEDOR PRINCIPAL (LA TARJETA BLANCA) */
    .main-card-wrapper {
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.04);
        max-width: 1100px; /* MÁS ANCHO EN PC */
        margin: 0 auto 50px auto; /* Centrado horizontal */
        overflow: hidden;
        position: relative;
        display: flex; flex-direction: column;
        min-height: 600px;
    }

    /* --- STEPPER (ENCABEZADO DE LA TARJETA) --- */
    .stepper-header {
        background: #fff;
        padding: 40px 60px 20px 60px;
        border-bottom: 1px solid #f1f2f6;
        position: relative;
    }
    .stepper-track {
        position: absolute; top: 58px; left: 100px; right: 100px;
        height: 3px; background: #f1f2f6; z-index: 1;
    }
    .stepper-items { display: flex; justify-content: space-between; position: relative; z-index: 2; }
    .step-unit { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    
    .step-circle {
        width: 40px; height: 40px; border-radius: 50%;
        background: #fff; border: 2px solid #e0e0e0;
        color: #b2bec3; display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; transition: 0.3s;
    }
    .step-label { font-size: 0.8rem; font-weight: 700; color: #b2bec3; text-transform: uppercase; letter-spacing: 1px; }

    /* Estados del Stepper */
    .step-unit.active .step-circle {
        border-color: var(--color-brand); background: var(--color-brand); color: #fff;
        box-shadow: 0 0 0 5px rgba(232, 67, 147, 0.15); transform: scale(1.1);
    }
    .step-unit.active .step-label { color: var(--color-brand); }
    .step-unit.completed .step-circle { background: #00b894; border-color: #00b894; color: #fff; }

    /* --- CUERPO DEL FORMULARIO --- */
    .form-body { padding: 50px 60px; flex: 1; }
    
    /* Paneles (La lógica de mostrar/ocultar) */
    .step-panel { display: none; animation: fadeIn 0.4s ease; }
    .step-panel.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .panel-header { text-align: center; margin-bottom: 50px; }
    .panel-header h3 { font-size: 1.8rem; color: var(--text-dark); margin-bottom: 10px; font-weight: 800; }
    .panel-header p { color: #636e72; font-size: 1rem; }

    /* --- COMPONENTES VISUALES --- */
    /* Tarjetas de Selección (Paso 1 y 3) */
    .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px; max-width: 800px; margin: 0 auto; }
    .selection-card {
        border: 2px solid #f1f2f6; border-radius: 20px; padding: 30px 20px;
        text-align: center; cursor: pointer; transition: 0.2s; position: relative;
    }
    .selection-card:hover { transform: translateY(-5px); border-color: var(--color-brand); box-shadow: 0 10px 20px rgba(232, 67, 147, 0.1); }
    .selection-card.selected { border-color: var(--color-brand); background: #fff0f6; }
    
    .card-icon { font-size: 2.5rem; color: #dfe6e9; margin-bottom: 15px; transition: 0.3s; }
    .selection-card.selected .card-icon { color: var(--color-brand); }
    .card-label { font-weight: 800; font-size: 1.1rem; color: var(--text-dark); }
    .check-circle { 
        position: absolute; top: 15px; right: 15px; width: 24px; height: 24px; 
        background: var(--color-brand); color: #fff; border-radius: 50%; 
        display: none; align-items: center; justify-content: center; font-size: 0.8rem;
    }
    .selection-card.selected .check-circle { display: flex; }

    /* Inputs Modernos */
    .input-modern { width: 100%; padding: 15px; border: 2px solid #f1f2f6; border-radius: 12px; font-size: 1rem; outline: none; transition: 0.2s; }
    .input-modern:focus { border-color: var(--color-brand); background: #fff; }
    .label-modern { display: block; font-weight: 700; color: #636e72; margin-bottom: 8px; font-size: 0.9rem; text-transform: uppercase; }

    /* Split Layout (Paso 2) */
    .layout-split { display: grid; grid-template-columns: 300px 1fr; gap: 40px; }
    .photo-area { 
        border: 2px dashed #dfe6e9; border-radius: 20px; display: flex; flex-direction: column; 
        align-items: center; justify-content: center; height: 350px; cursor: pointer; color: #b2bec3; transition: 0.2s;
    }
    .photo-area:hover { border-color: var(--color-brand); background: #fffbfd; }

    /* Price Box (Paso 3) */
    .price-display { 
        background: #fff; border: 2px solid #eee; border-radius: 20px; padding: 30px; 
        text-align: center; max-width: 400px; margin: 30px auto 0;
    }
    .price-input-row { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; }
    .currency { font-size: 2.5rem; font-weight: 700; color: var(--text-dark); }
    .price-field { 
        border: none; border-bottom: 3px solid #eee; width: 180px; font-size: 3rem; 
        font-weight: 800; text-align: center; color: var(--color-brand); outline: none; 
    }

    /* Footer Navegación */
    .card-footer-nav {
        padding: 25px 60px; border-top: 1px solid #f1f2f6;
        display: flex; justify-content: space-between; align-items: center; background: #fff;
    }
    .nav-btn {
        padding: 12px 30px; border-radius: 50px; border: none; font-weight: 700; font-size: 1rem;
        cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: 0.2s;
    }
    .btn-prev { background: #fff; color: #636e72; border: 2px solid #f1f2f6; }
    .btn-prev:hover:not(:disabled) { background: #f1f2f6; color: var(--text-dark); }
    .btn-prev:disabled { opacity: 0.5; cursor: not-allowed; }
    
    .btn-next { background: var(--color-brand); color: #fff; box-shadow: 0 5px 15px rgba(232, 67, 147, 0.3); }
    .btn-next:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(232, 67, 147, 0.4); }
    .btn-finish { background: #00b894; color: #fff; display: none; }

    /* Responsive */
    @media (max-width: 992px) {
        .layout-split { grid-template-columns: 1fr; }
        .photo-area { height: 200px; }
        .stepper-header, .form-body, .card-footer-nav { padding-left: 20px; padding-right: 20px; }
        .stepper-track { left: 40px; right: 40px; }
    }



    /* ESTILOS NUEVOS PARA GRILLA DE ITEMS */
    .items-grid-container {
        display: grid;
        grid-template-columns: repeat(5, 1fr); /* 5 por fila en PC */
        gap: 15px;
        max-height: 400px;
        overflow-y: auto;
        padding: 5px;
    }
    
    .item-card-mini {
        background: #fff;
        border: 2px solid #f1f2f6;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .item-card-mini:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); border-color: var(--color-brand); }
    .item-card-mini.selected { border-color: var(--color-brand); background-color: #fff5f8; box-shadow: 0 0 0 2px #fff0f6; }

    .item-img-box {
        height: 120px; /* Aumentamos altura */
        background: #fff; /* Fondo blanco */
        display: flex; 
        align-items: center; 
        justify-content: center;
        color: #b2bec3; 
        font-size: 1.8rem;
        padding: 5px;
    }
    .item-card-mini.selected .item-img-box { background: rgba(232, 67, 147, 0.1); color: var(--color-brand); }

    .item-info-box { padding: 10px; text-align: center; }
    .item-name {
        font-size: 0.8rem; font-weight: 700; color: var(--text-dark);
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        line-height: 1.2; margin-bottom: 5px; height: 30px; /* Altura fija para 2 líneas */
    }
    .item-price { font-size: 0.9rem; color: var(--color-brand); font-weight: 800; }

    .mini-check {
        position: absolute; top: 5px; right: 5px; width: 18px; height: 18px;
        background: var(--color-brand); color: #fff; border-radius: 50%; font-size: 0.6rem;
        display: none; align-items: center; justify-content: center;
    }
    .item-card-mini.selected .mini-check { display: flex; }

    @media (max-width: 992px) {
        .items-grid-container { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 600px) {
        .items-grid-container { grid-template-columns: repeat(2, 1fr); }
    }


    /* --- ESTILOS CALCULADORA DE PRECIO (PASO 3) --- */
    .price-calculator-wrapper {
        display: flex; align-items: center; justify-content: center;
        gap: 20px; flex-wrap: wrap; background: #fff; padding: 30px;
        border-radius: 16px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border: 1px solid #f1f1f1;
    }
    .price-block { text-align: center; }
    .price-label-top {
        font-size: 0.85rem; font-weight: 700; letter-spacing: 1px;
        margin-bottom: 10px; display: block; text-transform: uppercase;
    }
    .price-input-container {
        background: #f5f6fa; border-radius: 12px;
        padding: 10px 15px; display: flex; align-items: center; border: 2px solid #dfe6e9;
        font-size: 1.5rem; font-weight: 800; color: #2d3436;
    }
    
    /* Precio Original (Tachado) */
    .original-price-block .price-input-container {
        background: #f1f1f1; border-color: transparent; color: #a4b0be;
        text-decoration: line-through;
    }
    .original-price-block .price-label-top { color: #a4b0be; }

    /* Precio Oferta (Input Activo) */
    .offer-price-block .price-input-container {
        background: #fff; border-color: var(--color-brand); color: var(--color-brand);
        box-shadow: 0 5px 15px rgba(232, 67, 147, 0.2);
    }
    .offer-price-block .price-label-top { color: var(--color-brand); }
    .offer-price-block input {
        border: none; background: transparent; font-size: 1.5rem; font-weight: 800;
        color: var(--color-brand); width: 100px; text-align: left; outline: none; padding-left: 5px;
    }

    /* Badge Verde de Descuento */
    .discount-badge-pop {
        background: #00b894; color: white; padding: 12px 25px;
        border-radius: 50px; font-weight: 900; font-size: 1.3rem;
        box-shadow: 0 10px 20px rgba(0, 184, 148, 0.3);
        animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: none;
    }
    @keyframes popIn { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

    /* Estilos para la sección de Puntos (integrado) */
    .points-input-wrapper {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #f1f1f1;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        display: none; /* Se activa con JS */
    }
    
    .points-label {
        font-weight: 700;
        color: #0984e3;
        text-transform: uppercase;
        font-size: 0.9rem;
    }

    .points-field-container {
        background: #e3f2fd; /* Azul muy suave */
        border: 2px solid #74b9ff;
        border-radius: 12px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
    }
    
    .points-field-container i { color: #fdcb6e; font-size: 1.2rem; } /* Moneda dorada */
    
    .points-field-container input {
        border: none;
        background: transparent;
        font-size: 1.3rem;
        font-weight: 800;
        color: #0984e3;
        width: 80px;
        text-align: center;
        outline: none;
    }
    
    .points-field-container input::placeholder { color: #81ecec; }

    /* --- ARREGLO PARA MÓVILES (SECCIÓN 3) --- */
    .price-row-responsive {
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 20px; 
        width: 100%;
    }

    @media (max-width: 768px) {
        .price-row-responsive {
            flex-direction: column !important; /* Poner uno debajo de otro */
            gap: 15px !important;
        }
        
        #arrow_separator {
            transform: rotate(90deg); /* Girar flecha hacia abajo */
            margin: 5px 0 !important;
        }

        .discount-badge-pop {
            margin-left: 0 !important;
            margin-top: 10px;
            width: 100%; /* Badge ancho completo en movil */
            text-align: center;
        }
    }
</style>

<div class="page-header-external">
    <div>
        <h1 class="kalam-title">Nueva Promoción</h1>
        <p class="subtitle">Diseña una oferta estratégica para tu negocio.</p>
    </div>
    <a href="<?= ruta_accion('promocion', 'listar') ?>" class="btn-cancel-external">
        <i class="fa-solid fa-xmark"></i> <span>Cancelar</span>
    </a>
</div>

<div class="main-card-wrapper">
    
    <div class="stepper-header">
        <div class="stepper-track"></div>
        <div class="stepper-items">
            <div class="step-unit active" id="stepIndicator1">
                <div class="step-circle"><i class="fa-solid fa-layer-group"></i></div>
                <span class="step-label">Ítem</span>
            </div>
            <div class="step-unit" id="stepIndicator2">
                <div class="step-circle"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <span class="step-label">Detalles</span>
            </div>
            <div class="step-unit" id="stepIndicator3">
                <div class="step-circle"><i class="fa-solid fa-tag"></i></div>
                <span class="step-label">Pago</span>
            </div>
            <div class="step-unit" id="stepIndicator4">
                <div class="step-circle"><i class="fa-solid fa-users-gear"></i></div>
                <span class="step-label">Alcance</span>
            </div>
        </div>
    </div>

    <form id="formPromocion" class="form-body" autocomplete="off">
        
        <div class="step-panel active" id="step1">
            <div class="panel-header">
                <h3>Selecciona el Origen</h3>
                <p>¿Qué quieres poner en oferta hoy?</p>
            </div>
            
            <input type="hidden" name="tipo_item" id="inputTipoItem" value="SERVICIO">

            <div class="grid-cards compact-grid" style="max-width: 600px; gap: 20px; grid-template-columns: 1fr 1fr;">
                <div class="selection-card selected small-card" onclick="seleccionarTipo(this, 'SERVICIO')" style="padding: 15px;">
                    <div class="check-circle" style="top: 8px; right: 8px; width: 18px; height: 18px;"><i class="fa-solid fa-check" style="font-size: 0.6rem;"></i></div>
                    <div class="card-icon" style="font-size: 1.5rem; margin-bottom: 5px;"><i class="fa-solid fa-scissors"></i></div>
                    <div class="card-label" style="font-size: 0.9rem;">Servicio</div>
                </div>
                <div class="selection-card small-card" onclick="seleccionarTipo(this, 'PRODUCTO')" style="padding: 15px;">
                    <div class="check-circle" style="top: 8px; right: 8px; width: 18px; height: 18px;"><i class="fa-solid fa-check" style="font-size: 0.6rem;"></i></div>
                    <div class="card-icon" style="font-size: 1.5rem; margin-bottom: 5px;"><i class="fa-solid fa-box-open"></i></div>
                    <div class="card-label" style="font-size: 0.9rem;">Producto</div>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <label class="label-modern" style="margin-bottom: 15px; display:block;">Selecciona del catálogo:</label>
                
                <input type="hidden" name="item_id" id="inputItemId" required>

                <div id="gridCatalogItems" class="items-grid-container">
                    <div style="text-align: center; padding: 40px; color: #b2bec3;">
                        <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                        <p style="margin-top: 10px;">Cargando catálogo...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header">
                <h3>Identidad de la Promoción</h3>
                <p>Ponle un nombre atractivo y explica la razón de la oferta.</p>
            </div>

            <div style="max-width: 600px; margin: 0 auto; padding-top: 20px;">
                
                <div class="input-group-modern">
                    <label class="label-modern">Nombre de la Promoción</label>
                    <input type="text" name="nombre" id="txtNombre" class="input-modern" placeholder="Ej: Especial Día de la Madre">
                </div>

                <div class="input-group-modern" style="margin-top: 30px;">
                    <label class="label-modern">Descripción / Motivo</label>
                    <textarea name="descripcion" class="input-modern" rows="4" placeholder="Ej: Descuento especial para fidelizar clientes nuevos..."></textarea>
                </div>

            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header">
                <h3>Configuración de Precio</h3>
                <p>Elige la modalidad de cobro para esta oferta.</p>
            </div>

            <input type="hidden" name="modalidad" id="inputModalidad" value="PRECIO">

            <div class="grid-cards" style="max-width: 900px; grid-template-columns: 1fr 1fr 1fr;">
                <div class="selection-card selected" id="card_PRECIO" onclick="setModalidad('PRECIO')">
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                    <div class="card-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div class="card-label">Dinero</div>
                </div>
                <div class="selection-card" id="card_MIXTO" onclick="setModalidad('MIXTO')">
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                    <div class="card-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                    <div class="card-label">Mixto</div>
                </div>
                <div class="selection-card" id="card_PUNTOS" onclick="setModalidad('PUNTOS')">
                    <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                    <div class="card-icon"><i class="fa-solid fa-coins"></i></div>
                    <div class="card-label">Puntos</div>
                </div>
            </div>

            <div id="zona_dinero_calculo" style="margin-top: 40px;">
                <div class="price-calculator-wrapper" style="flex-direction: column;">
                    
                    <div class="price-row-responsive"> <div class="price-block original-price-block" id="block_precio_real">
                            <span class="price-label-top">Precio Real</span>
                            <div class="price-input-container">
                                <span id="lblPrecioOriginal">$0.00</span>
                            </div>
                        </div>

                        <i class="fa-solid fa-arrow-right" id="arrow_separator" style="color: #dfe6e9; font-size: 1.5rem;"></i>

                        <div class="price-block offer-price-block" id="block_precio_oferta">
                            <span class="price-label-top">Precio Oferta</span>
                            <div class="price-input-container">
                                <span>$</span>
                                <input type="number" step="0.01" name="precio_oferta" id="inputPrecioOferta" 
                                    placeholder="0.00" oninput="calcularDescuento()">
                            </div>
                        </div>

                        <div id="badgeDescuento" class="discount-badge-pop" style="margin-left: 10px;">
                            0% OFF
                        </div>
                    </div>

                    <div id="row_puntos_extra" class="points-input-wrapper">
                        <span class="points-label">+ PUNTOS REQUERIDOS:</span>
                        <div class="points-field-container">
                            <i class="fa-solid fa-coins"></i>
                            <input type="number" name="puntos_req" id="inputPuntos" placeholder="0">
                        </div>
                    </div>

                </div>
                
                <p style="text-align: center; color: #b2bec3; margin-top: 15px; font-size: 0.9rem;" id="txtAyudaPrecio">
                    Ingresa el precio de oferta y calcularemos el descuento.
                </p>
            </div>
        </div>

        <div class="step-panel" id="step4">
            <div class="panel-header">
                <h3>Vigencia y Límites</h3>
                <p>¿Cuándo caduca esta promoción?</p>
            </div>

            <div style="max-width: 700px; margin: 0 auto;">
                
                <input type="hidden" name="tipo_vigencia" id="inputTipoVigencia" value="FECHA">
                
                <div class="grid-cards compact-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                    
                    <div class="selection-card selected" id="card_v_FECHA" onclick="setVigencia('FECHA')">
                        <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                        <div class="card-icon"><i class="fa-regular fa-calendar-days"></i></div>
                        <div class="card-label">Por Rango de Fechas</div>
                        <div style="font-size: 0.8rem; color: #b2bec3; margin-top: 5px;">Ilimitado durante el tiempo</div>
                    </div>

                    <div class="selection-card" id="card_v_CUPOS" onclick="setVigencia('CUPOS')">
                        <div class="check-circle"><i class="fa-solid fa-check"></i></div>
                        <div class="card-icon"><i class="fa-solid fa-ticket"></i></div>
                        <div class="card-label">Por Límite de Cupos</div>
                        <div style="font-size: 0.8rem; color: #b2bec3; margin-top: 5px;">Se acaba al venderse todo</div>
                    </div>
                </div>

                <div id="bloque_fechas">
                    <h4 style="text-align:center; color:var(--text-dark); margin-bottom:20px;">Selecciona el periodo válido</h4>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="input-group-modern">
                            <label class="label-modern">Fecha Inicio</label>
                            <input type="date" name="fecha_ini" id="fecha_ini" class="input-modern">
                        </div>
                        <div class="input-group-modern">
                            <label class="label-modern">Fecha Fin</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="input-modern">
                        </div>
                    </div>
                </div>

                <div id="bloque_cupos" style="display:none;">
                    <div class="price-display" style="border-color: var(--color-brand);">
                        <label class="label-modern" style="text-align:center; color: var(--color-brand);">CANTIDAD A VENDER</label>
                        <div class="price-input-row">
                            <span class="currency" style="font-size: 1.5rem;">#</span>
                            <input type="number" name="limite" id="inputLimite" class="price-field" placeholder="0" style="width: 120px;">
                        </div>
                        <p style="text-align:center; color:#999; margin-top:10px; font-size:0.9rem;">
                            La promoción se desactivará automáticamente<br>después de <b><span id="lblCupos">0</span> ventas</b>.
                        </p>
                        
                        <div class="input-group-modern" style="max-width: 200px; margin: 20px auto 0;">
                            <label class="label-modern" style="text-align:center;">Válido desde:</label>
                            <input type="date" name="fecha_ini_cupo" id="fecha_ini_cupo" class="input-modern">
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>

    <div class="card-footer-nav">
        <button type="button" class="nav-btn btn-prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled>
            <i class="fa-solid fa-arrow-left"></i> Anterior
        </button>
        
        <button type="button" class="nav-btn btn-next" id="btnNext" onclick="cambiarPaso(1)">
            Siguiente <i class="fa-solid fa-arrow-right"></i>
        </button>
        
        <button type="button" class="nav-btn btn-next btn-finish" id="btnFinish" onclick="enviarFormulario()" style="display:none; background:#00b894;">
            Lanzar Promo <i class="fa-solid fa-rocket"></i>
        </button>
    </div>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 4;
    
    // 1. CAPTURAMOS EL TOKEN DE SEGURIDAD DE LA URL ACTUAL
    const paramsUrl = new URLSearchParams(window.location.search);
    const tokenSeguridad = paramsUrl.get('token');

    // 2. LO CONCATENAMOS A LAS URLS AJAX
    const URL_LOAD_ITEMS = `index.php?c=promocion&a=ajax_cargar_items&token=${tokenSeguridad}`;
    const URL_GUARDAR    = `index.php?c=promocion&a=guardar_promo_ajax&token=${tokenSeguridad}`;

    /* --- 1. LÓGICA DE SELECCIÓN DE TIPO --- */
    function seleccionarTipo(element, tipo) {
        // Limpiar selección visual
        document.querySelectorAll('#step1 .selection-card').forEach(c => c.classList.remove('selected'));
        // Marcar actual
        element.classList.add('selected');
        // Asignar valor
        document.getElementById('inputTipoItem').value = tipo;
        // Cargar datos
        cargarItemsCatalog();
    }

    function cargarItemsCatalog() {
        const tipo = document.getElementById('inputTipoItem').value;
        const container = document.getElementById('gridCatalogItems');
        
        // Loader
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #b2bec3; grid-column: 1 / -1;">
                <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                <p style="margin-top: 10px;">Cargando catálogo...</p>
            </div>`;

        // Limpiar input oculto
        document.getElementById('inputItemId').value = "";

        fetch(`${URL_LOAD_ITEMS}&tipo=${tipo}`)
            .then(r => r.json())
            .then(res => {
                container.innerHTML = ''; // Limpiar loader

                if(res.success && res.data.length > 0) {
                    const iconClass = (tipo === 'SERVICIO') ? 'fa-scissors' : 'fa-box-open';

                    res.data.forEach(it => {
                        // Crear tarjeta
                        const card = document.createElement('div');
                        card.className = 'item-card-mini';
                        card.onclick = function() { seleccionarItem(this, it.id); };
                        
                        // Procesar imagen (tomar la primera si es una galería separada por comas)
                        let imgUrl = '';
                        if(it.foto) {
                            const fotos = it.foto.split(','); 
                            imgUrl = fotos[0].trim(); // Tomamos la primera
                        }

                        // Decidir si mostrar FOTO o ICONO
                        let contenidoVisual = '';
                        if (imgUrl) {
                            contenidoVisual = `<img src="${imgUrl}" style="width:100%; height:100%; object-fit:contain;">`;
                        } else {
                            contenidoVisual = `<i class="fa-solid ${iconClass}"></i>`;
                        }

                        card.onclick = function() { seleccionarItem(this, it.id, it.precio); }; // <--- CAMBIO AQUÍ: Pasamos el precio
                        
                        card.innerHTML = `
                            <div class="mini-check"><i class="fa-solid fa-check"></i></div>
                            <div class="item-img-box" style="${imgUrl ? 'padding:0; background:#fff;' : ''}">
                                ${contenidoVisual}
                            </div>
                            <div class="item-info-box">
                                <div class="item-name" title="${it.nombre}">${it.nombre}</div>
                                <div class="item-price">$${parseFloat(it.precio).toFixed(2)}</div>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = `<div style="text-align:center; grid-column: 1/-1; padding:20px; color:#999;">No hay items disponibles.</div>`;
                }
            });
    }

    // --- AGREGA ESTA VARIABLE AL INICIO DEL SCRIPT ---
    let precioOriginalGlobal = 0; 

    // --- REEMPLAZA LA FUNCIÓN seleccionarItem POR ESTA ---
    function seleccionarItem(element, id, precio) {
        document.querySelectorAll('.item-card-mini').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        
        document.getElementById('inputItemId').value = id;
        
        // Guardamos el precio y actualizamos la vista previa del Paso 3
        precioOriginalGlobal = parseFloat(precio);
        document.getElementById('lblPrecioOriginal').innerText = '$' + precioOriginalGlobal.toFixed(2);
        
        // Reseteamos el cálculo
        calcularDescuento();
    }

    /* --- 3. NAVEGACIÓN STEPPER --- */
    function cambiarPaso(dir) {
        // Validaciones básicas antes de avanzar
        if (dir === 1) {
            if (currentStep === 1 && !document.getElementById('inputItemId').value) return alert("Selecciona un ítem.");
            if (currentStep === 2 && !document.getElementById('txtNombre').value) return alert("Escribe un nombre.");
        }

        // Ocultar actual
        document.getElementById('step' + currentStep).classList.remove('active');
        document.getElementById('stepIndicator' + currentStep).classList.remove('active');
        if(dir === 1) document.getElementById('stepIndicator' + currentStep).classList.add('completed');

        currentStep += dir;

        // Mostrar nuevo
        document.getElementById('step' + currentStep).classList.add('active');
        document.getElementById('stepIndicator' + currentStep).classList.add('active');

        actualizarBotones();
    }

    function actualizarBotones() {
        // Botón Prev
        document.getElementById('btnPrev').disabled = (currentStep === 1);

        // Botón Next vs Finish
        const btnNext = document.getElementById('btnNext');
        const btnFinish = document.getElementById('btnFinish');

        if (currentStep === totalSteps) {
            btnNext.style.display = 'none';
            btnFinish.style.display = 'inline-flex';
        } else {
            btnNext.style.display = 'inline-flex';
            btnFinish.style.display = 'none';
        }
    }

    /* --- 4. GUARDAR --- */
    /* --- 4. GUARDAR (CORREGIDO PARA NO SACARTE DEL SISTEMA) --- */
    function enviarFormulario() {
        const form = document.getElementById('formPromocion');
        const data = Object.fromEntries(new FormData(form).entries());

        // Desactivar botón para evitar doble clic
        const btnFinish = document.getElementById('btnFinish');
        btnFinish.disabled = true;
        btnFinish.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        fetch(URL_GUARDAR, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // AQUÍ ESTABA EL ERROR: Faltaba concatenar el tokenSeguridad
                window.location.href = `index.php?c=promocion&a=listar&token=${tokenSeguridad}`;
            } else {
                alert("Error: " + (res.error || 'Desconocido'));
                // Reactivar botón si falló
                btnFinish.disabled = false;
                btnFinish.innerHTML = 'Lanzar Promo <i class="fa-solid fa-rocket"></i>';
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error de red al guardar.");
            btnFinish.disabled = false;
            btnFinish.innerHTML = 'Lanzar Promo <i class="fa-solid fa-rocket"></i>';
        });
    }

    // --- LÓGICA DE VIGENCIA (PASO 2) ---
    function setVigencia(tipo) {
        document.getElementById('inputTipoVigencia').value = tipo;
        
        // Manejo visual de las tarjetas
        document.getElementById('card_v_FECHA').classList.remove('selected');
        document.getElementById('card_v_CUPOS').classList.remove('selected');
        document.getElementById('card_v_' + tipo).classList.add('selected');

        // Mostrar/Ocultar bloques
        if(tipo === 'FECHA') {
            document.getElementById('bloque_fechas').style.display = 'block';
            document.getElementById('bloque_cupos').style.display = 'none';
            // Resetear cupos a 0 (ilimitado)
            document.getElementById('inputLimite').value = ""; 
        } else {
            document.getElementById('bloque_fechas').style.display = 'none';
            document.getElementById('bloque_cupos').style.display = 'block';
            // Resetear fecha fin (para que no caduque por tiempo, sino por cupos)
            document.getElementById('fecha_fin').value = "";
        }
    }

    // Pequeño extra visual: Actualizar el número en el texto de abajo
    document.getElementById('inputLimite').addEventListener('input', function(e) {
        document.getElementById('lblCupos').innerText = e.target.value || '0';
    });

    // --- CÁLCULO AUTOMÁTICO DEL % DE DESCUENTO ---
    function calcularDescuento() {
        const inputOferta = document.getElementById('inputPrecioOferta');
        const badge = document.getElementById('badgeDescuento');
        
        let precioOferta = parseFloat(inputOferta.value);

        if (!precioOferta || precioOferta <= 0 || precioOriginalGlobal <= 0) {
            badge.style.display = 'none';
            return;
        }

        if (precioOferta >= precioOriginalGlobal) {
            badge.style.display = 'none'; // No es oferta si es más caro
            return;
        }

        // Fórmula: ((Original - Oferta) / Original) * 100
        let descuento = ((precioOriginalGlobal - precioOferta) / precioOriginalGlobal) * 100;
        
        badge.innerText = Math.round(descuento) + '% OFF';
        badge.style.display = 'block';
    }

    // --- CONTROL DE MODALIDAD ACTUALIZADO ---
    // --- CONTROL DE MODALIDAD (CORREGIDO PARA TU HTML ACTUAL) ---
    function setModalidad(mod) {
        // 1. Visual Cards (Resaltar la seleccionada)
        document.querySelectorAll('#step3 .selection-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('card_' + mod).classList.add('selected');
        document.getElementById('inputModalidad').value = mod;

        // 2. REFERENCIAS A LOS ELEMENTOS REALES QUE TIENES EN TU HTML
        const blockReal = document.getElementById('block_precio_real');
        const arrow = document.getElementById('arrow_separator');
        const blockOferta = document.getElementById('block_precio_oferta');
        const badge = document.getElementById('badgeDescuento');
        const rowPuntos = document.getElementById('row_puntos_extra'); // <--- ESTE ES EL ID QUE EXISTE AHORA
        const txtAyuda = document.getElementById('txtAyudaPrecio');

        // 3. LÓGICA DE MOSTRAR/OCULTAR
        if (mod === 'PRECIO') {
            // Mostrar todo lo de dinero
            blockReal.style.display = 'block';
            arrow.style.display = 'block';
            blockOferta.style.display = 'block';
            badge.style.display = 'block';
            
            // Ocultar la fila de puntos
            rowPuntos.style.display = 'none'; 
            
            if(txtAyuda) txtAyuda.innerText = "Ingresa el precio de oferta y calcularemos el descuento.";
            calcularDescuento();
        } 
        else if (mod === 'MIXTO') {
            // Mostrar TODO (Dinero + Puntos)
            blockReal.style.display = 'block';
            arrow.style.display = 'block';
            blockOferta.style.display = 'block';
            badge.style.display = 'block';
            
            rowPuntos.style.display = 'flex'; // Mostrar puntos
            
            if(txtAyuda) txtAyuda.innerText = "Define el precio rebajado Y los puntos necesarios para el canje.";
            calcularDescuento();
        } 
        else { // PUNTOS
            // Ocultar todo lo de dinero
            blockReal.style.display = 'none';
            arrow.style.display = 'none';
            blockOferta.style.display = 'none';
            badge.style.display = 'none';
            
            // Mostrar SOLO puntos
            rowPuntos.style.display = 'flex';
            
            if(txtAyuda) txtAyuda.innerText = "Define cuántos puntos cuesta canjear este ítem totalmente gratis.";
        }
    }

    // Inicio
    document.addEventListener('DOMContentLoaded', cargarItemsCatalog);
</script>