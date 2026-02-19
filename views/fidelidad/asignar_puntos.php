<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --bg-body: #f8fafc;
        --text-main: #334155;
        --text-light: #64748b;
        --brand-pink: #e84393;
        --brand-pink-soft: #fdf2f8;
        --brand-blue: #0ea5e9;
        --brand-blue-soft: #f0f9ff;
        --success: #10b981;
        --border-color: #e2e8f0;
    }

    body { background-color: var(--bg-body); font-family: 'Outfit', sans-serif; color: var(--text-main); }

    .layout-friendly { max-width: 1200px; margin: 0 auto; padding: 2rem; }

    /* --- 1. BANNER DE BIENVENIDA (ESTILO MARCA) --- */
    .welcome-banner {
        background: linear-gradient(to right, #fff1f2, #f0f9ff);
        border-radius: 20px; padding: 35px; display: flex; align-items: center; gap: 25px;
        margin-bottom: 35px; border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
    }
    
    .banner-icon {
        width: 70px; height: 70px; background: white; border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: var(--brand-pink); box-shadow: 0 5px 15px rgba(232, 67, 147, 0.15);
        flex-shrink: 0;
    }

    /* CORRECCIÓN DE TÍTULO AQUÍ */
    .banner-text h1 { 
        font-family: 'Kalam', cursive; /* Tipografía de la marca */
        font-size: 2.8rem; /* Tamaño grande */
        font-weight: 700; 
        margin: 0 0 5px; 
        color: var(--text-main); 
        line-height: 1.1;
    }
    
    .banner-text p { margin: 0; font-size: 1rem; color: var(--text-light); line-height: 1.5; max-width: 700px; }
    .banner-highlight { color: var(--brand-blue); font-weight: 700; }

    /* --- 2. BARRA DE HERRAMIENTAS --- */
    .toolbar-friendly {
        background: white; border-radius: 16px; padding: 10px 10px 10px 25px;
        display: flex; align-items: center; justify-content: space-between; gap: 20px;
        border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 30px;
    }

    .search-friendly { flex: 1; display: flex; align-items: center; gap: 12px; }
    .search-friendly i { color: #94a3b8; font-size: 1.1rem; }
    .search-friendly input {
        border: none; width: 100%; outline: none; font-size: 1rem; font-weight: 500;
        font-family: 'Outfit'; color: var(--text-main);
    }

    .tabs-friendly { display: flex; gap: 5px; background: #f1f5f9; padding: 5px; border-radius: 12px; }
    .tab-btn {
        padding: 10px 25px; border-radius: 10px; border: none; cursor: pointer; font-weight: 700;
        color: var(--text-light); background: transparent; transition: 0.3s; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;
    }
    .tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.5); }
    .tab-btn.active { background: white; color: var(--brand-pink); box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .tab-btn.active.prod { color: var(--brand-blue); }
    
    .toolbar-friendly.searching .tabs-friendly { opacity: 0.4; pointer-events: none; filter: grayscale(1); }

    /* --- 3. GRID DE TARJETAS --- */
    .grid-friendly {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 25px; padding-bottom: 50px;
    }

    .friendly-card {
        background: white; border-radius: 18px; overflow: hidden;
        border: 1px solid var(--border-color); transition: all 0.3s ease;
        display: flex; flex-direction: column; position: relative;
        border-top: 4px solid transparent;
    }
    .friendly-card.serv { border-top-color: var(--brand-pink); }
    .friendly-card.prod { border-top-color: var(--brand-blue); }

    .friendly-card:hover { transform: translateY(-7px); box-shadow: 0 15px 30px -5px rgba(0,0,0,0.1); }

    /* IMAGEN (Fixed) */
    .img-container-fixed {
        height: 190px; width: 100%; background: #ffffff; padding: 20px;
        display: flex; align-items: center; justify-content: center;
        border-bottom: 1px solid #f1f5f9; position: relative; overflow: hidden; 
    }
    .img-container-fixed img {
        max-width: 100%; max-height: 100%; object-fit: contain;
        transition: transform 0.4s ease; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.05));
    }
    .friendly-card:hover .img-container-fixed img { transform: scale(1.08); }

    .type-badge-pill {
        position: absolute; top: 12px; left: 12px;
        padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); z-index: 2;
    }
    .friendly-card.serv .type-badge-pill { background: var(--brand-pink-soft); color: var(--brand-pink); }
    .friendly-card.prod .type-badge-pill { background: var(--brand-blue-soft); color: var(--brand-blue); }

    /* INFO */
    .card-content { padding: 20px; flex: 1; display: flex; flex-direction: column; }
    .info-title { font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin: 0 0 5px; line-height: 1.3; height: 42px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .info-price { font-size: 0.95rem; color: var(--text-light); font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 5px; }
    
    /* ACTION ZONE */
    .action-box {
        background: #f8fafc; border: 2px solid transparent;
        border-radius: 14px; padding: 5px 5px 5px 15px; margin-top: auto;
        display: flex; align-items: center; justify-content: space-between; transition: 0.3s;
    }
    .friendly-card.serv .action-box:focus-within { border-color: var(--brand-pink); background: white; }
    .friendly-card.prod .action-box:focus-within { border-color: var(--brand-blue); background: white; }

    .input-pts-clean {
        border: none; background: transparent; width: 100%; outline: none;
        font-weight: 800; font-size: 1.2rem; color: var(--text-main);
    }
    .label-pts-clean { font-size: 0.75rem; font-weight: 700; color: var(--text-light); margin-right: 10px; }

    .btn-save-clean {
        width: 45px; height: 45px; border-radius: 12px; border: none; cursor: pointer;
        background: var(--text-main); color: white; display: flex; align-items: center; justify-content: center;
        transition: 0.2s; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .btn-save-clean:hover { transform: scale(1.05); }
    .friendly-card.serv .btn-save-clean:hover { background: var(--brand-pink); }
    .friendly-card.prod .btn-save-clean:hover { background: var(--brand-blue); }

    /* Feedback */
    .saved-tag {
        position: absolute; bottom: 85px; right: 20px; background: white;
        padding: 6px 12px; border-radius: 30px; font-size: 0.8rem; font-weight: 700; color: var(--success);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 5px;
        opacity: 0; transform: translateY(10px); transition: 0.4s; pointer-events: none;
    }
    .saved-tag.show { opacity: 1; transform: translateY(0); }

    .empty-state-friendly { grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; border: 2px dashed var(--border-color); }

    @media (max-width: 800px) {
        .layout-friendly { padding: 1.5rem 1rem; }
        .welcome-banner { flex-direction: column; text-align: center; padding: 25px; gap: 15px; }
        .toolbar-friendly { flex-direction: column-reverse; padding: 15px; gap: 15px; align-items: stretch; }
        .tabs-friendly { justify-content: space-between; }
        .tab-btn { flex: 1; justify-content: center; padding: 10px 15px; }
    }
</style>

<div class="layout-friendly">
    
    <div class="welcome-banner">
        <div class="banner-icon">
            <i class="fa-solid fa-gift"></i>
        </div>
        <div class="banner-text">
            <h1>Catálogo de <span style="color: var(--brand-pink);">Puntos</span></h1>
            <p>Aquí defines el valor de tus servicios y productos. <br>¡Dale a tus clientes una razón extra para volver! Los ítems con <span class="banner-highlight">más puntos</span> suelen ser los más deseados.</p>
        </div>
    </div>

    <div class="toolbar-friendly" id="toolbar">
        <div class="search-friendly">
            <i class="fa-solid fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Busca un servicio o producto..." onkeyup="busquedaGlobal()">
        </div>

        <div class="tabs-friendly">
            <button class="tab-btn active" onclick="filtrarTab('SERVICIO', this)">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Servicios
            </button>
            <button class="tab-btn" onclick="filtrarTab('PRODUCTO', this)">
                <i class="fa-solid fa-box-open"></i> Productos
            </button>
        </div>
    </div>

    <div class="grid-friendly" id="mainGrid">
        
        <?php foreach ($catalogo as $item): 
            $esServicio = ($item['tipo'] === 'SERVICIO');
            $claseTipo = $esServicio ? 'serv' : 'prod';
            $textoTipo = $esServicio ? 'Servicio' : 'Producto';
            $iconoFallback = $esServicio ? 'fa-scissors' : 'fa-box';
            $imgUrl = !empty($item['imagen']) ? $item['imagen'] : '';
        ?>
            <div class="friendly-card <?= $claseTipo ?> item-target" 
                 data-tipo="<?= $item['tipo'] ?>" 
                 data-nombre="<?= strtolower($item['nombre']) ?>">
                
                <div class="img-container-fixed">
                    <span class="type-badge-pill"><?= $textoTipo ?></span>
                    <?php if($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" alt="<?= $item['nombre'] ?>">
                    <?php else: ?>
                        <i class="fa-solid <?= $iconoFallback ?>" style="font-size: 3.5rem; color: #e2e8f0;"></i>
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <h3 class="info-title"><?= $item['nombre'] ?></h3>
                    <div class="info-price">
                        <i class="fa-solid fa-tag"></i> $<?= number_format($item['precio'], 2) ?>
                    </div>

                    <div class="action-box">
                        <span class="label-pts-clean">PTS:</span>
                        <input type="number" id="inp_<?= $item['tipo'] ?>_<?= $item['id'] ?>" 
                               class="input-pts-clean" value="<?= $item['puntos_actuales'] ?>" placeholder="0" min="0">
                        
                        <button class="btn-save-clean" onclick="guardar('<?= $item['tipo'] ?>', <?= $item['id'] ?>, this)" title="Guardar">
                            <i class="fa-solid fa-floppy-disk"></i>
                        </button>
                    </div>
                </div>

                <div id="msg_<?= $item['tipo'] ?>_<?= $item['id'] ?>" class="saved-tag">
                    <i class="fa-solid fa-check-circle"></i> ¡Guardado!
                </div>
            </div>
        <?php endforeach; ?>

        <div class="empty-state-friendly" id="noResultsMsg" style="display: none;">
            <i class="fa-regular fa-face-sad-tear" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
            <p style="color: var(--text-light); font-size: 1.1rem;">Ups, no encontramos coincidencias.</p>
        </div>

    </div>
</div>

<script>
    let tabActual = 'SERVICIO';

    document.addEventListener('DOMContentLoaded', () => {
        aplicarFiltros();
    });

    function filtrarTab(tipo, btn) {
        document.getElementById('globalSearch').value = '';
        document.getElementById('toolbar').classList.remove('searching');
        
        tabActual = tipo;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active', 'prod'));
        btn.classList.add('active');
        if(tipo === 'PRODUCTO') btn.classList.add('prod');

        aplicarFiltros();
    }

    function busquedaGlobal() {
        const texto = document.getElementById('globalSearch').value.toLowerCase().trim();
        const toolbar = document.getElementById('toolbar');

        if (texto.length > 0) {
            toolbar.classList.add('searching');
            aplicarFiltros(texto); 
        } else {
            toolbar.classList.remove('searching');
            aplicarFiltros(); 
        }
    }

    function aplicarFiltros(textoBusqueda = null) {
        const items = document.querySelectorAll('.item-target');
        let visibles = 0;

        items.forEach(card => {
            const tipoCard = card.getAttribute('data-tipo');
            const nombreCard = card.getAttribute('data-nombre');
            let mostrar = false;

            if (textoBusqueda) {
                if (nombreCard.includes(textoBusqueda)) mostrar = true;
            } else {
                if (tipoCard === tabActual) mostrar = true;
            }

            card.style.display = mostrar ? 'flex' : 'none';
            if(mostrar) visibles++;
        });

        document.getElementById('noResultsMsg').style.display = (visibles === 0) ? 'block' : 'none';
    }

    function guardar(tipo, id, btn) {
        const inputId = `inp_${tipo}_${id}`;
        const msgId = `msg_${tipo}_${id}`;
        const input = document.getElementById(inputId);
        const feedback = document.getElementById(msgId);
        const puntos = input.value;

        const iconoOriginal = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btn.disabled = true;

        const params = new URLSearchParams(window.location.search);
        const token = params.get('token');

        fetch(`index.php?c=fidelidad&a=ajax_guardar_punto_item&token=${token}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo: tipo, id: id, puntos: puntos })
        })
        .then(r => r.json())
        .then(d => {
            btn.innerHTML = iconoOriginal;
            btn.disabled = false;

            if (d.success) {
                feedback.classList.add('show');
                setTimeout(() => feedback.classList.remove('show'), 2500);
            } else {
                alert('Error al guardar');
            }
        })
        .catch(err => {
            btn.innerHTML = iconoOriginal;
            btn.disabled = false;
            alert('Error de conexión');
        });
    }
</script>