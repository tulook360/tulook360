<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --accent-color: #e84393;
        --accent-hover: #d63384;
        --accent-light: #fdf2f8;
        --success-color: #10b981;
        --border-color: #e2e8f0;
        --input-bg: #f1f5f9;
    }

    body { background-color: var(--bg-color); font-family: 'Outfit', sans-serif; color: var(--text-primary); }

    /* LAYOUT PRINCIPAL */
    .fid-layout {
        max-width: 1100px; margin: 0 auto; padding: 2rem;
        display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px;
        align-items: start;
    }

    .fid-header { grid-column: 1 / -1; margin-bottom: 10px; }
    .fid-title { font-family: 'Kalam', cursive; font-size: 2.5rem; margin: 0; line-height: 1.1; color: var(--text-primary); }
    .fid-subtitle { color: var(--text-secondary); font-size: 1rem; margin-top: 5px; }

    /* --- TARJETA DE CONFIGURACIÓN --- */
    .fid-card {
        background: var(--card-bg); border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-color);
        overflow: hidden; padding: 0;
    }

    /* FILAS (ROWS) */
    .fid-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 30px; border-bottom: 1px solid var(--border-color);
        gap: 20px; /* Espacio entre texto y controles */
    }
    .fid-row:last-child { border-bottom: none; }

    /* TEXTOS */
    .fid-label { flex: 1; } /* El texto ocupa todo el espacio posible */
    .fid-label h3 { font-size: 1.1rem; font-weight: 700; margin: 0 0 6px; color: var(--text-primary); }
    .fid-label p { font-size: 0.9rem; color: var(--text-secondary); margin: 0; line-height: 1.4; }

    /* --- SWITCH (MEJORADO) --- */
    .switch-wrapper { display: flex; align-items: center; }
    .switch-pro { position: relative; width: 52px; height: 30px; flex-shrink: 0; }
    .switch-pro input { opacity: 0; width: 0; height: 0; }
    .slider-pro {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1; transition: .3s; border-radius: 34px;
    }
    .slider-pro:before {
        position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px;
        background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    input:checked + .slider-pro { background-color: var(--success-color); }
    input:checked + .slider-pro:before { transform: translateX(22px); }

    /* --- CONTROLES DE TIEMPO (ALINEACIÓN PERFECTA) --- */
    .form-days-wrapper { 
        display: flex; align-items: center; gap: 8px; 
    }

    /* Cápsula del Input + Select */
    .time-control {
        display: flex; align-items: center; background: var(--input-bg);
        border-radius: 12px; padding: 0 10px; height: 46px; /* Altura Fija */
        border: 2px solid transparent; transition: 0.3s;
    }
    .time-control:focus-within { border-color: var(--accent-color); background: white; }
    
    .tc-input {
        width: 45px; border: none; background: transparent; text-align: center;
        font-weight: 700; font-size: 1.1rem; color: var(--text-primary); outline: none;
        height: 100%;
    }
    .tc-select {
        border: none; background: transparent; font-size: 0.9rem; color: var(--text-secondary);
        font-weight: 600; cursor: pointer; padding-left: 5px; outline: none; height: 100%;
    }

    /* Botón Guardar (Cuadrado con esquinas redondas) */
    .btn-save-mini {
        width: 46px; height: 46px; /* Mismo alto que el input */
        border-radius: 12px; border: none; flex-shrink: 0;
        background: var(--text-primary); color: white; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s; font-size: 1.1rem;
    }
    .btn-save-mini:hover { background: var(--accent-color); transform: scale(1.05); }

    /* --- COLUMNA DERECHA (LA QUE TE GUSTÓ) --- */
    .info-card {
        background: linear-gradient(135deg, #fff 0%, #fff5f9 100%);
        border-radius: 20px; padding: 30px; border: 1px solid #ffebf4;
        position: relative; overflow: hidden;
    }
    .info-card::before {
        content: '\f005'; font-family: "Font Awesome 6 Free"; font-weight: 900;
        position: absolute; top: -20px; right: -20px; font-size: 10rem;
        color: var(--accent-color); opacity: 0.05; transform: rotate(15deg);
    }
    .info-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; position: relative; z-index: 2; }
    .info-icon {
        width: 50px; height: 50px; background: white; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: var(--accent-color); font-size: 1.5rem; box-shadow: 0 5px 15px rgba(232, 67, 147, 0.15);
    }
    .info-title h3 { font-size: 1.2rem; font-weight: 800; margin: 0; color: var(--text-primary); }
    .info-title span { font-size: 0.85rem; color: var(--accent-color); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    .info-list { list-style: none; padding: 0; margin: 0; position: relative; z-index: 2; }
    .info-item { display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-start; }
    .item-check {
        width: 24px; height: 24px; background: var(--accent-light); color: var(--accent-color);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; flex-shrink: 0; margin-top: 2px;
    }
    .item-text h4 { font-size: 0.95rem; font-weight: 700; margin: 0 0 3px; color: var(--text-primary); }
    .item-text p { font-size: 0.85rem; color: var(--text-secondary); margin: 0; line-height: 1.4; }
    .pro-tip {
        background: rgba(255, 255, 255, 0.8); border-radius: 12px; padding: 15px;
        border-left: 4px solid var(--accent-color); margin-top: 20px;
        font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; position: relative; z-index: 2;
    }

    /* TOAST */
    .toast-box {
        position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px);
        background: #1e293b; color: white; padding: 12px 25px; border-radius: 50px;
        font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 3000;
        opacity: 0; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex; gap: 10px; align-items: center;
    }
    .toast-box.show { transform: translateX(-50%) translateY(0); opacity: 1; }

    /* --- RESPONSIVE (MÓVIL PULIDO) --- */
    @media (max-width: 900px) {
        .fid-layout { grid-template-columns: 1fr; padding: 1.5rem; gap: 20px; }
        .info-card { order: -1; margin-bottom: 10px; } /* Info arriba */
        
        /* Ajuste específico para Filas */
        .fid-row { padding: 20px 15px; }
        
        /* FILA 1: SWITCH (Mantiene horizontalidad) */
        /* No cambiamos nada, flex-row por defecto funciona bien aquí */

        /* FILA 2: TIEMPO (Stack vertical) */
        .fid-row.row-time { flex-direction: column; align-items: flex-start; gap: 15px; }
        
        .form-days-wrapper { width: 100%; }
        .time-control { flex: 1; justify-content: center; } /* Input ocupa el espacio disponible */
        .btn-save-mini { width: 50px; height: 46px; } /* Botón un poco más ancho en móvil */
    }
</style>

<div class="fid-layout">
    
    <div class="fid-header">
        <h1 class="fid-title">Programa de <span style="color: var(--accent-color);">Lealtad</span></h1>
        <p class="fid-subtitle">Configura las reglas para premiar a tus clientes frecuentes.</p>
    </div>

    <div class="fid-left">
        <div class="fid-card">
            
            <div class="fid-row">
                <div class="fid-label">
                    <h3>Estado del Sistema</h3>
                    <p id="statusTxt" style="<?= ($estaActivo == 1) ? 'color: var(--success-color); font-weight:600;' : '' ?>">
                        <?= ($estaActivo == 1) ? 'Los clientes están acumulando puntos.' : 'El sistema está pausado.' ?>
                    </p>
                </div>
                <div class="switch-wrapper">
                    <label class="switch-pro">
                        <input type="checkbox" id="checkActivo" <?= ($estaActivo == 1) ? 'checked' : '' ?> onchange="cambiarEstado(this)">
                        <span class="slider-pro"></span>
                    </label>
                </div>
            </div>

            <div class="fid-row row-time">
                <div class="fid-label">
                    <h3>Vigencia de Puntos</h3>
                    <p>Tiempo límite para canjear recompensas.</p>
                </div>
                
                <form action="index.php?c=fidelidad&a=guardar_dias&token=<?= $_GET['token'] ?? '' ?>" method="POST" class="form-days-wrapper">
                    <input type="hidden" name="dias_vencimiento" id="diasDB" value="<?= $diasVenc ?>">

                    <div class="time-control">
                        <input type="number" id="uiNum" class="tc-input" min="1" required oninput="calcDias()">
                        <select id="uiUnit" class="tc-select" onchange="calcDias()">
                            <option value="1">Días</option>
                            <option value="30">Meses</option>
                            <option value="365">Años</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-save-mini" title="Guardar Cambios">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="fid-right">
        <div class="info-card">
            <div class="info-header">
                <div class="info-icon"><i class="fa-solid fa-gift"></i></div>
                <div class="info-title">
                    <span>Estrategia</span>
                    <h3>¿Por qué activarlo?</h3>
                </div>
            </div>
            <ul class="info-list">
                <li class="info-item">
                    <div class="item-check"><i class="fa-solid fa-check"></i></div>
                    <div class="item-text">
                        <h4>Retención de Clientes</h4>
                        <p>Un cliente con puntos tiene un 70% más de probabilidad de volver.</p>
                    </div>
                </li>
                <li class="info-item">
                    <div class="item-check"><i class="fa-solid fa-check"></i></div>
                    <div class="item-text">
                        <h4>Aumenta el Ticket</h4>
                        <p>Los clientes gastan más para alcanzar recompensas.</p>
                    </div>
                </li>
            </ul>
            <div class="pro-tip">
                <strong>💡 Tip Pro:</strong> Recomendamos una vigencia de <strong>6 Meses</strong> para crear urgencia.
            </div>
        </div>
    </div>

</div>

<div id="toast" class="toast-box">
    <i class="fa-solid fa-circle-check" style="color:#4ade80"></i>
    <span id="toastMsg">Cambios guardados</span>
</div>

<script>
    // --- LÓGICA DE TIEMPO (Convertidor) ---
    const diasDB = parseInt(document.getElementById('diasDB').value) || 180;
    const uiNum = document.getElementById('uiNum');
    const uiUnit = document.getElementById('uiUnit');

    function initTime() {
        if(diasDB % 365 === 0) { uiNum.value = diasDB/365; uiUnit.value = "365"; }
        else if(diasDB % 30 === 0) { uiNum.value = diasDB/30; uiUnit.value = "30"; }
        else { uiNum.value = diasDB; uiUnit.value = "1"; }
    }

    function calcDias() {
        const n = parseInt(uiNum.value) || 0;
        const u = parseInt(uiUnit.value);
        document.getElementById('diasDB').value = n * u;
    }
    initTime();

    // --- SWITCH AJAX ---
    function cambiarEstado(chk) {
        const estado = chk.checked;
        const txt = document.getElementById('statusTxt');
        
        if(estado) {
            txt.innerText = 'Los clientes están acumulando puntos.';
            txt.style.color = 'var(--success-color)';
            txt.style.fontWeight = '600';
        } else {
            txt.innerText = 'El sistema está pausado.';
            txt.style.color = 'var(--text-secondary)';
            txt.style.fontWeight = '400';
        }

        const params = new URLSearchParams(window.location.search);
        const token = params.get('token');

        fetch(`index.php?c=fidelidad&a=ajax_cambiar_estado&token=${token}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ activo: estado })
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) showToast('Estado actualizado');
            else { chk.checked = !estado; showToast('Error al guardar'); }
        })
        .catch(() => { chk.checked = !estado; showToast('Error de conexión'); });
    }

    // --- TOAST ---
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('ok')) showToast("Vigencia actualizada");

    function showToast(msg) {
        document.getElementById('toastMsg').innerText = msg;
        const t = document.getElementById('toast');
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
        if(urlParams.has('ok')) {
            window.history.replaceState({}, document.title, window.location.pathname + "?c=fidelidad&a=configuracion&token=" + urlParams.get('token'));
        }
    }
</script>