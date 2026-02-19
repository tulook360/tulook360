<?php
// views/promociones/editar.php
?>

<style>
    /* --- ESTRUCTURA Y VARIABLES --- */
    :root { --color-brand: #e84393; --bg-body: #f8f9fa; --text-dark: #2d3436; }
    
    /* Header Externo */
    .page-header-external { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; padding: 0 10px; }
    .kalam-title { font-family: 'Kalam', cursive; font-size: 2.5rem; color: var(--text-dark); margin: 0; line-height: 1; }
    .subtitle { color: #636e72; margin: 5px 0 0 5px; font-size: 1rem; }
    .btn-cancel-external { background: #dfe6e9; color: #636e72; padding: 12px 25px; border-radius: 50px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; transition: 0.2s; }
    
    /* Contenedor Principal */
    .main-card-wrapper { background: #fff; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.04); max-width: 1100px; margin: 0 auto 50px auto; overflow: hidden; display: flex; flex-direction: column; min-height: 600px; }

    /* Stepper */
    .stepper-header { background: #fff; padding: 40px 60px 20px 60px; border-bottom: 1px solid #f1f2f6; position: relative; }
    .stepper-track { position: absolute; top: 58px; left: 100px; right: 100px; height: 3px; background: #f1f2f6; z-index: 1; }
    .stepper-items { display: flex; justify-content: space-between; position: relative; z-index: 2; }
    .step-unit { display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .step-circle { width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 2px solid #e0e0e0; color: #b2bec3; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: 0.3s; }
    .step-label { font-size: 0.8rem; font-weight: 700; color: #b2bec3; text-transform: uppercase; letter-spacing: 1px; }
    .step-unit.active .step-circle { border-color: var(--color-brand); background: var(--color-brand); color: #fff; transform: scale(1.1); }
    .step-unit.active .step-label { color: var(--color-brand); }
    .step-unit.completed .step-circle { background: #00b894; border-color: #00b894; color: #fff; }

    /* Formulario */
    .form-body { padding: 50px 60px; flex: 1; }
    .step-panel { display: none; animation: fadeIn 0.4s ease; }
    .step-panel.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .panel-header { text-align: center; margin-bottom: 50px; }
    .panel-header h3 { font-size: 1.8rem; color: var(--text-dark); margin-bottom: 10px; font-weight: 800; }

    /* Inputs Modernos */
    .input-modern { width: 100%; padding: 15px; border: 2px solid #f1f2f6; border-radius: 12px; font-size: 1rem; outline: none; transition: 0.2s; background: #fff; }
    .input-modern:focus { border-color: var(--color-brand); }
    .label-modern { display: block; font-weight: 700; color: #636e72; margin-bottom: 8px; font-size: 0.9rem; text-transform: uppercase; }

    /* Estilos Paso 1 (Ítem Fijo) */
    .selected-item-box { max-width: 450px; margin: 0 auto; background: #fff; border: 2px solid var(--color-brand); border-radius: 20px; padding: 25px; text-align: center; box-shadow: 0 10px 30px rgba(232, 67, 147, 0.08); }
    
    /* Estilos Paso 3 (Precios) */
    .price-calculator-wrapper { display: flex; align-items: center; justify-content: center; gap: 20px; flex-wrap: wrap; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border: 1px solid #f1f1f1; }
    .price-input-container { background: #f5f6fa; border-radius: 12px; padding: 10px 15px; display: flex; align-items: center; border: 2px solid #dfe6e9; font-size: 1.5rem; font-weight: 800; color: #2d3436; }
    .original-price-block .price-input-container { background: #f1f1f1; border-color: transparent; color: #a4b0be; text-decoration: line-through; }
    .offer-price-block .price-input-container { background: #fff; border-color: var(--color-brand); color: var(--color-brand); box-shadow: 0 5px 15px rgba(232, 67, 147, 0.2); }
    .offer-price-block input { border: none; background: transparent; font-size: 1.5rem; font-weight: 800; color: var(--color-brand); width: 100px; text-align: left; outline: none; }
    .discount-badge-pop { background: #00b894; color: white; padding: 12px 25px; border-radius: 50px; font-weight: 900; font-size: 1.3rem; display:none; }
    
    /* Puntos */
    .points-input-wrapper { margin-top: 20px; padding-top: 20px; border-top: 2px dashed #f1f1f1; width: 100%; display: none; justify-content: center; align-items: center; gap: 15px; }
    .points-field-container { background: #e3f2fd; border: 2px solid #74b9ff; border-radius: 12px; padding: 8px 15px; display: flex; align-items: center; gap: 10px; }
    .points-field-container input { border: none; background: transparent; font-size: 1.3rem; font-weight: 800; color: #0984e3; width: 80px; text-align: center; outline: none; }

    /* Cards Selección */
    .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px; max-width: 800px; margin: 0 auto; }
    .selection-card { border: 2px solid #f1f2f6; border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: 0.2s; position: relative; }
    .selection-card.selected { border-color: var(--color-brand); background: #fff0f6; }
    .card-icon { font-size: 2.5rem; color: #dfe6e9; margin-bottom: 15px; }
    .selection-card.selected .card-icon { color: var(--color-brand); }
    .check-circle { position: absolute; top: 15px; right: 15px; width: 24px; height: 24px; background: var(--color-brand); color: #fff; border-radius: 50%; display: none; align-items: center; justify-content: center; }
    .selection-card.selected .check-circle { display: flex; }

    /* Paso 4 (Cupos Gigantes) */
    .price-display { background: #fff; border: 2px solid #eee; border-radius: 20px; padding: 30px; text-align: center; max-width: 450px; margin: 0 auto; }
    .price-input-row { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 10px; }
    .currency { font-size: 2.5rem; font-weight: 700; color: var(--text-dark); }
    .price-field { border: none; border-bottom: 3px solid #eee; width: 180px; font-size: 3rem; font-weight: 800; text-align: center; color: var(--color-brand); outline: none; }

    /* Footer */
    .card-footer-nav { padding: 25px 60px; border-top: 1px solid #f1f2f6; display: flex; justify-content: space-between; align-items: center; background: #fff; }
    .nav-btn { padding: 12px 30px; border-radius: 50px; border: none; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: 0.2s; text-decoration: none; }
    .btn-prev { background: #fff; color: #636e72; border: 2px solid #f1f2f6; }
    .btn-next { background: var(--color-brand); color: #fff; }
    .btn-finish { background: #00b894; color: #fff; display: none; }

    /* Responsive */
    .price-row-responsive { display: flex; align-items: center; justify-content: center; gap: 20px; width: 100%; }
    @media (max-width: 768px) {
        .stepper-header, .form-body, .card-footer-nav { padding: 20px; }
        .stepper-track, .step-label { display: none; }
        .price-row-responsive { flex-direction: column; }
        #arrow_separator { transform: rotate(90deg); margin: 10px 0; }
        .grid-cards { grid-template-columns: 1fr; }
        .card-footer-nav { flex-direction: column-reverse; gap: 10px; }
        .nav-btn { width: 100%; justify-content: center; }
    }
</style>

<div class="page-header-external">
    <div>
        <h1 class="kalam-title">Editar Promoción</h1>
        <p class="subtitle">Modifica los parámetros de tu oferta activa.</p>
    </div>
    <a href="<?= ruta_accion('promocion', 'listar') ?>" class="btn-cancel-external">
        <i class="fa-solid fa-xmark"></i> <span>Cancelar</span>
    </a>
</div>

<div class="main-card-wrapper">
    <div class="stepper-header">
        <div class="stepper-track"></div>
        <div class="stepper-items">
            <div class="step-unit active" id="stepIndicator1"><div class="step-circle"><i class="fa-solid fa-layer-group"></i></div><span class="step-label">Ítem</span></div>
            <div class="step-unit" id="stepIndicator2"><div class="step-circle"><i class="fa-solid fa-wand-magic-sparkles"></i></div><span class="step-label">Detalles</span></div>
            <div class="step-unit" id="stepIndicator3"><div class="step-circle"><i class="fa-solid fa-tag"></i></div><span class="step-label">Pago</span></div>
            <div class="step-unit" id="stepIndicator4"><div class="step-circle"><i class="fa-solid fa-users-gear"></i></div><span class="step-label">Alcance</span></div>
        </div>
    </div>

    <form id="formPromocion" class="form-body" autocomplete="off">
        <input type="hidden" name="prom_id" value="<?= $promo['prom_id'] ?>">

        <div class="step-panel active" id="step1">
            <div class="panel-header"><h3>Ítem en Edición</h3><p>El producto o servicio no se puede cambiar.</p></div>
            <div class="selected-item-box">
                <i class="fa-solid <?= $promo['prom_tipo'] === 'SERVICIO' ? 'fa-scissors' : 'fa-box-open' ?>" style="font-size: 4rem; color: var(--color-brand); margin-bottom: 15px;"></i>
                <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-dark);"><?= htmlspecialchars($promo['prom_nombre']) ?></div>
                <div style="font-size: 1.2rem; color: var(--color-brand); font-weight: 700; margin-top: 5px;">Precio Original: $<?= number_format($promo['precio_real'], 2) ?></div>
            </div>
            <input type="hidden" name="tipo_item" value="<?= $promo['prom_tipo'] ?>">
            <input type="hidden" name="item_id" value="<?= $promo['item_id'] ?>">
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header"><h3>Identidad de la Promo</h3></div>
            <div style="max-width: 650px; margin: 0 auto;">
                <div class="input-group-modern">
                    <label class="label-modern">Nombre de la Promoción</label>
                    <input type="text" name="nombre" id="txtNombre" class="input-modern" value="<?= htmlspecialchars($promo['prom_nombre']) ?>">
                </div>
                <div class="input-group-modern" style="margin-top: 30px;">
                    <label class="label-modern">Descripción / Motivo</label>
                    <textarea name="descripcion" class="input-modern" rows="4"><?= htmlspecialchars($promo['prom_desc']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header"><h3>Configuración de Pago</h3></div>
            <input type="hidden" name="modalidad" id="inputModalidad" value="<?= $promo['prom_modalidad'] ?>">
            <div class="grid-cards">
                <div class="selection-card" id="card_PRECIO" onclick="setModalidad('PRECIO')"><div class="check-circle"><i class="fa-solid fa-check"></i></div><div class="card-icon"><i class="fa-solid fa-money-bill-wave"></i></div><div class="card-label">Dinero</div></div>
                <div class="selection-card" id="card_MIXTO" onclick="setModalidad('MIXTO')"><div class="check-circle"><i class="fa-solid fa-check"></i></div><div class="card-icon"><i class="fa-solid fa-scale-balanced"></i></div><div class="card-label">Mixto</div></div>
                <div class="selection-card" id="card_PUNTOS" onclick="setModalidad('PUNTOS')"><div class="check-circle"><i class="fa-solid fa-check"></i></div><div class="card-icon"><i class="fa-solid fa-coins"></i></div><div class="card-label">Puntos</div></div>
            </div>

            <div class="price-calculator-wrapper" style="flex-direction: column; margin-top: 40px;">
                <div class="price-row-responsive">
                    <div class="price-block original-price-block" id="block_precio_real">
                        <span class="price-label-top">Precio Real</span>
                        <div class="price-input-container"><span id="lblPrecioOriginal">$<?= number_format($promo['precio_real'], 2) ?></span></div>
                    </div>
                    <i class="fa-solid fa-arrow-right" id="arrow_separator" style="color:#dfe6e9; font-size: 1.5rem;"></i>
                    <div class="price-block offer-price-block" id="block_precio_oferta">
                        <span class="price-label-top">Precio Oferta</span>
                        <div class="price-input-container"><span>$</span><input type="number" step="0.01" name="precio_oferta" id="inputPrecioOferta" value="<?= $promo['prom_precio_oferta'] ?>" oninput="calcularDescuento()"></div>
                    </div>
                    <div id="badgeDescuento" class="discount-badge-pop" style="margin-left: 10px;">0% OFF</div>
                </div>
                <div id="row_puntos_extra" class="points-input-wrapper">
                    <span class="points-label">+ PUNTOS:</span>
                    <div class="points-field-container"><i class="fa-solid fa-coins" style="color:#fdcb6e;"></i><input type="number" name="puntos_req" id="inputPuntos" value="<?= $promo['puntos_necesarios'] ?? 0 ?>"></div>
                </div>
            </div>
        </div>

        <div class="step-panel" id="step4">
            <div class="panel-header"><h3>Vigencia y Límites</h3></div>
            <?php $esCupo = ($promo['prom_limite_usos'] > 0); ?>
            <input type="hidden" name="tipo_vigencia" id="inputTipoVigencia" value="<?= $esCupo ? 'CUPOS' : 'FECHA' ?>">
            
            <div class="grid-cards compact-grid" style="max-width: 600px; margin-bottom: 40px;">
                <div class="selection-card" id="card_v_FECHA" onclick="setVigencia('FECHA')"><div class="check-circle"><i class="fa-solid fa-check"></i></div><div class="card-icon"><i class="fa-regular fa-calendar-days"></i></div><div class="card-label">Por Fechas</div></div>
                <div class="selection-card" id="card_v_CUPOS" onclick="setVigencia('CUPOS')"><div class="check-circle"><i class="fa-solid fa-check"></i></div><div class="card-icon"><i class="fa-solid fa-ticket"></i></div><div class="card-label">Por Cupos</div></div>
            </div>

            <div id="bloque_fechas">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; max-width:550px; margin:0 auto;">
                    <div class="input-group-modern">
                        <label class="label-modern">Fecha Inicio</label>
                        <input type="date" name="fecha_ini" id="fecha_ini" class="input-modern" 
                               value="<?= (strtotime($promo['prom_ini']) > 0) ? date('Y-m-d', strtotime($promo['prom_ini'])) : '' ?>">
                    </div>
                    <div class="input-group-modern">
                        <label class="label-modern">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="input-modern" 
                               value="<?= (strtotime($promo['prom_fin']) > 0) ? date('Y-m-d', strtotime($promo['prom_fin'])) : '' ?>">
                    </div>
                </div>
            </div>

            <div id="bloque_cupos" style="display:none;">
                <div class="price-display" style="border-color: var(--color-brand);">
                    <label class="label-modern" style="text-align:center; color: var(--color-brand);">CANTIDAD A VENDER</label>
                    <div class="price-input-row">
                        <span class="currency">#</span>
                        <input type="number" name="limite" id="inputLimite" class="price-field" style="width: 150px;" value="<?= $promo['prom_limite_usos'] ?>">
                    </div>
                    
                    <div class="input-group-modern" style="max-width: 250px; margin: 30px auto 0;">
                        <label class="label-modern" style="text-align:center;">VÁLIDO DESDE:</label>
                        <input type="date" name="fecha_ini_cupo" id="fecha_ini_cupo" class="input-modern"
                               value="<?= (strtotime($promo['prom_ini']) > 0) ? date('Y-m-d', strtotime($promo['prom_ini'])) : '' ?>">
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card-footer-nav">
        <button type="button" class="nav-btn btn-prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled><i class="fa-solid fa-arrow-left"></i> Anterior</button>
        <button type="button" class="nav-btn btn-next" id="btnNext" onclick="cambiarPaso(1)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
        <button type="button" class="nav-btn btn-finish" id="btnFinish" onclick="enviarFormulario()">Actualizar Promo <i class="fa-solid fa-rocket"></i></button>
    </div>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 4;
    const precioOriginalGlobal = <?= $promo['precio_real'] ?>;
    const tokenSeguridad = new URLSearchParams(window.location.search).get('token');
    const URL_ACTUALIZAR = `index.php?c=promocion&a=actualizar_promo_ajax&token=${tokenSeguridad}`;

    document.addEventListener('DOMContentLoaded', () => {
        setModalidad('<?= $promo['prom_modalidad'] ?>');
        setVigencia('<?= $esCupo ? 'CUPOS' : 'FECHA' ?>');
        calcularDescuento();
    });

    function cambiarPaso(dir) {
        document.getElementById('step' + currentStep).classList.remove('active');
        document.getElementById('stepIndicator' + currentStep).classList.remove('active');
        if(dir === 1) document.getElementById('stepIndicator' + currentStep).classList.add('completed');
        currentStep += dir;
        document.getElementById('step' + currentStep).classList.add('active');
        document.getElementById('stepIndicator' + currentStep).classList.add('active');
        actualizarBotones();
    }

    function actualizarBotones() {
        document.getElementById('btnPrev').disabled = (currentStep === 1);
        const btnNext = document.getElementById('btnNext');
        const btnFinish = document.getElementById('btnFinish');
        if (currentStep === totalSteps) { btnNext.style.display = 'none'; btnFinish.style.display = 'inline-flex'; }
        else { btnNext.style.display = 'inline-flex'; btnFinish.style.display = 'none'; }
    }

    function setModalidad(mod) {
        document.querySelectorAll('#step3 .selection-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('card_' + mod).classList.add('selected');
        document.getElementById('inputModalidad').value = mod;

        const el = { real: document.getElementById('block_precio_real'), arrow: document.getElementById('arrow_separator'), oferta: document.getElementById('block_precio_oferta'), badge: document.getElementById('badgeDescuento'), puntos: document.getElementById('row_puntos_extra') };
        if (mod === 'PRECIO') { el.real.style.display = 'block'; el.arrow.style.display = 'block'; el.oferta.style.display = 'block'; el.badge.style.display = 'block'; el.puntos.style.display = 'none'; calcularDescuento(); }
        else if (mod === 'MIXTO') { el.real.style.display = 'block'; el.arrow.style.display = 'block'; el.oferta.style.display = 'block'; el.badge.style.display = 'block'; el.puntos.style.display = 'flex'; calcularDescuento(); }
        else { el.real.style.display = 'none'; el.arrow.style.display = 'none'; el.oferta.style.display = 'none'; el.badge.style.display = 'none'; el.puntos.style.display = 'flex'; }
    }

    function setVigencia(tipo) {
        document.querySelectorAll('#step4 .selection-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('card_v_' + tipo).classList.add('selected');
        document.getElementById('inputTipoVigencia').value = tipo;
        document.getElementById('bloque_fechas').style.display = (tipo === 'FECHA') ? 'block' : 'none';
        document.getElementById('bloque_cupos').style.display = (tipo === 'CUPOS') ? 'block' : 'none';
    }

    function calcularDescuento() {
        const val = parseFloat(document.getElementById('inputPrecioOferta').value);
        const badge = document.getElementById('badgeDescuento');
        if (!val || val <= 0 || precioOriginalGlobal <= 0 || val >= precioOriginalGlobal) { badge.style.display = 'none'; return; }
        badge.innerText = Math.round(((precioOriginalGlobal - val) / precioOriginalGlobal) * 100) + '% OFF';
        badge.style.display = 'block';
    }

    function enviarFormulario() {
        const formData = new FormData(document.getElementById('formPromocion'));
        const data = Object.fromEntries(formData.entries());

        // LÓGICA DE UNIFICACIÓN: Si es CUPOS, usamos la fecha de ese bloque
        if (data.tipo_vigencia === 'CUPOS') {
            data.fecha_ini = data.fecha_ini_cupo;
            data.fecha_fin = null;
        }
        delete data.fecha_ini_cupo; // Limpiar

        const btn = document.getElementById('btnFinish');
        btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Actualizando...';

        fetch(URL_ACTUALIZAR, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
        .then(async r => {
            const text = await r.text();
            try { return JSON.parse(text); } catch (e) { throw new Error(text); }
        })
        .then(res => {
            if (res.success) window.location.href = `index.php?c=promocion&a=listar&token=${tokenSeguridad}`;
            else { alert(res.error || 'Error al guardar'); btn.disabled = false; btn.innerHTML = 'Actualizar Promo <i class="fa-solid fa-rocket"></i>'; }
        })
        .catch(err => {
            console.error(err); alert("Error del servidor (Ver consola)"); btn.disabled = false; btn.innerHTML = 'Actualizar Promo <i class="fa-solid fa-rocket"></i>';
        });
    }
</script>