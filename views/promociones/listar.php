<?php
// views/promociones/listar.php
// Variables esperadas: $promos (array), $filtroActual (string)
?>

<link rel="stylesheet" href="<?= asset('recursos/css/servicio.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Marketing y Promociones</h1>
        <p class="section-subtitle">Gestiona las ofertas y canjes de tu negocio.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('promocion', 'crear')): ?>
            <a href="<?= ruta_accion('promocion', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nueva Promoción</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('promocion', 'listar', ['filtro' => 'activos']) ?>" 
    class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
    <i class="fa-solid fa-tags"></i> Activas
    </a>
    <a href="<?= ruta_accion('promocion', 'listar', ['filtro' => 'inactivos']) ?>" 
    class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
    <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="filters-bar">
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
        <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" class="search-input" placeholder="Buscar promoción..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('promocion', 'listar', ['filtro' => $filtroActual]) ?>" class="clear-search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-filter">Buscar</button>
    </form>
</div>

<div class="grid-services">
    <?php if (empty($promos)): ?>
        <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 50px;">
            <div class="icon-circle"><i class="fa-solid fa-tags"></i></div>
            <h3>Sin Promociones</h3>
            <p>No hay registros activos en este momento.</p>
        </div>
    <?php else: ?>
        <?php foreach ($promos as $p): ?>
            <?php 
                // 1. IMAGEN (Usamos la que viene del JOIN 'foto_item' o fallback 'galeria_urls')
                $imgUrl = '';
                if (!empty($p['foto_item'])) $imgUrl = $p['foto_item'];
                elseif (!empty($p['galeria_urls'])) { $f = explode(',', $p['galeria_urls']); $imgUrl = $f[0]; }
                
                $iconoFallback = ($p['prom_tipo'] === 'SERVICIO') ? 'fa-scissors' : 'fa-box-open';

                // 2. MODALIDAD (Color del Badge)
                $colorBadge = '#00b894'; // Verde (Dinero)
                if($p['prom_modalidad'] === 'MIXTO') $colorBadge = '#fdcb6e';
                if($p['prom_modalidad'] === 'PUNTOS') $colorBadge = '#0984e3';

                // 3. PRECIOS
                $precioReal = $p['precio_real'] ?? 0;
                $precioOferta = $p['prom_precio_oferta'];

                // 4. LÓGICA DE VIGENCIA (CRUCIAL)
                $esPorCupos = ($p['prom_limite_usos'] > 0);
                $ini = date('d/m/Y', strtotime($p['prom_ini']));
                $fin = $p['prom_fin'] ? date('d/m/Y', strtotime($p['prom_fin'])) : '---';
            ?>

            <div class="service-card">
                <span class="category-badge" style="background: <?= $colorBadge ?>;">
                    <?= $p['prom_modalidad'] ?>
                </span>

                <div class="card-image-container">
                    <?php if($imgUrl): ?>
                        <img src="<?= htmlspecialchars($imgUrl) ?>" class="c-img">
                    <?php else: ?>
                        <div class="no-img"><i class="fa-solid <?= $iconoFallback ?>"></i></div>
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <h3 class="service-title"><?= htmlspecialchars($p['prom_nombre']) ?></h3>

                    <div class="price-compare-row">
                        <?php if($p['prom_modalidad'] === 'PUNTOS'): ?>
                            <div class="price-new-col">
                                <span class="lbl-new" style="color:#0984e3;">CANJE TOTAL</span>
                                <span class="val-new" style="color:#0984e3;">
                                    <i class="fa-solid fa-coins"></i> <?= number_format($p['puntos_necesarios'] ?? 0) ?>
                                </span>
                                <span class="pts-extra">PUNTOS</span>
                            </div>
                        <?php else: ?>
                            <div class="price-old-col">
                                <span class="lbl-old">Precio Real</span>
                                <span class="val-old">$<?= number_format($precioReal, 2) ?></span>
                            </div>
                            
                            <i class="fa-solid fa-arrow-right price-arrow"></i>
                            
                            <div class="price-new-col">
                                <span class="lbl-new">Precio Oferta</span>
                                <span class="val-new">$<?= number_format($precioOferta, 2) ?></span>
                                <?php if($p['prom_modalidad'] === 'MIXTO'): ?>
                                    <span class="pts-extra">+ <?= number_format($p['puntos_necesarios'] ?? 0) ?> pts</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="logic-container">
                        <?php if($esPorCupos): ?>
                            <?php 
                                $usados = $p['total_usos'] ?? 0;
                                $total = $p['prom_limite_usos'];
                                $pct = ($total > 0) ? ($usados / $total) * 100 : 0;
                            ?>
                            <div class="quota-header">
                                <span>CUPOS DISPONIBLES</span>
                                <span style="color:#e84393;"><?= $usados ?> / <?= $total ?></span>
                            </div>
                            <div class="quota-bar"><div class="quota-fill" style="width: <?= $pct ?>%;"></div></div>
                            
                            <div class="logic-row" style="margin-top: 12px; font-weight: 600;">
                                <i class="fa-regular fa-calendar-check" style="color:#00b894;"></i> 
                                <span>Inicia: <span class="logic-val"><?= $ini ?></span></span>
                            </div>

                        <?php else: ?>
                            <div class="logic-row" style="color:#0984e3; font-weight: 800;">
                                <i class="fa-solid fa-infinity"></i>
                                <span>STOCK ILIMITADO</span>
                            </div>
                            <div class="logic-row" style="margin-top:8px; background: #f8f9fa; padding: 8px; border-radius: 10px;">
                                <i class="fa-regular fa-calendar" style="color:#b2bec3;"></i> 
                                <div style="font-size: 0.8rem; line-height: 1.4;">
                                    <div>Vence: <span class="logic-val"><?= $fin ?></span></div>
                                    <div style="font-size: 0.7rem; color:#b2bec3;">Válido desde <?= $ini ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer">
                    <?php if ($p['prom_estado'] === 'A'): ?>
                        <a href="<?= ruta_accion('promocion', 'editar', ['id' => $p['prom_id']]) ?>" class="btn-action">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </a>
                        <button class="btn-action btn-delete" onclick="eliminarPromo(<?= $p['prom_id'] ?>)">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn-action" style="color:#0984e3; border-color:#0984e3; flex:1;" 
                                onclick='abrirModalReanudar(<?= json_encode($p) ?>)'>
                            <i class="fa-solid fa-play"></i> Reanudar Campaña
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
// Generamos la URL segura para volver a la lista de activos después de reanudar
$urlListarActivos = ruta_accion('promocion', 'listar', ['filtro' => 'activos']); 
?>

<div id="modalReanudar" class="generic-modal-overlay">
    <div class="generic-modal-box">
        <div class="gm-icon"><i class="fa-solid fa-arrows-rotate" style="color:var(--primary)"></i></div>
        <h3 class="gm-title" id="gmTitleReanudar">Reanudar Promoción</h3>
        <p class="gm-desc" id="reanudarDesc">Se reiniciarán los contadores de uso a 0.</p>
        
        <div class="gm-form-group">
            <div class="gm-field">
                <label>NUEVA FECHA DE INICIO</label>
                <input type="date" id="new_f_ini" value="<?= date('Y-m-d') ?>">
            </div>
            
            <div id="box_f_fin" class="gm-field">
                <label>NUEVA FECHA DE FIN</label>
                <input type="date" id="new_f_fin">
            </div>
        </div>

        <div class="gm-actions">
            <button class="btn-gm btn-gm-secondary" onclick="cerrarModalReanudar()">Cancelar</button>
            <button class="btn-gm btn-gm-primary" id="btnConfirmReanudar">Lanzar Ahora</button>
        </div>
    </div>
</div>



<script>
    // Lógica de carrusel idéntica a servicios
    function moveSlide(id, dir) {
        const container = document.getElementById('car-' + id);
        if (!container) return;
        const imgs = container.querySelectorAll('.c-img');
        const dots = container.querySelectorAll('.dot');
        const total = imgs.length;
        if (total <= 1) return;
        let current = 0;
        imgs.forEach((img, idx) => {
            if(img.classList.contains('active')) current = idx;
            img.classList.remove('active');
            if(dots[idx]) dots[idx].classList.remove('active');
        });
        let next = (current + dir + total) % total;
        imgs[next].classList.add('active');
        if(dots[next]) dots[next].classList.add('active');
    }

    // Handlers para las acciones
    function abrirModalCrear() {
        // Aquí llamaremos al modal que haremos en el siguiente paso
        console.log("Abrir creación de promo");
    }

    function editarPromo(id) {
        console.log("Editar promo ID:", id);
    }

    function preguntarEliminar(id) {
        // Usamos tu función global de 'preguntar' si existe, o un confirm estándar
        if(confirm("¿Deseas enviar esta promoción a la papelera?")) {
            console.log("Eliminando promo:", id);
        }
    }


    const URL_ELIMINAR = '<?= ruta_accion('promocion', 'eliminar_promo_ajax') ?>';
    const URL_REANUDAR = '<?= ruta_accion('promocion', 'reanudar_promo_ajax') ?>';
    const URL_SUCCESS  = '<?= $urlListarActivos ?>'; // <--- DIRECCIÓN SEGURA CON TOKEN
    
    let promoActualId = null;

    function abrirModalReanudar(promo) {
        promoActualId = promo.prom_id;
        document.getElementById('gmTitleReanudar').innerText = "Reanudar: " + promo.prom_nombre;
        
        const esPorCupos = (parseInt(promo.prom_limite_usos) > 0);
        document.getElementById('box_f_fin').style.display = esPorCupos ? 'none' : 'block';
        
        document.getElementById('modalReanudar').classList.add('active');
    }

    function cerrarModalReanudar() { document.getElementById('modalReanudar').classList.remove('active'); }

    document.getElementById('btnConfirmReanudar').onclick = function() {
        const data = {
            id: promoActualId,
            f_ini: document.getElementById('new_f_ini').value,
            f_fin: document.getElementById('new_f_fin').value
        };

        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Lanzando...';
        this.disabled = true;

        fetch(URL_REANUDAR, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                // REDIRECCIÓN SEGURA: Usamos la URL que generamos arriba con el token
                window.location.href = URL_SUCCESS; 
            } else {
                alert("Error: " + (res.message || "Sin permisos"));
                this.innerHTML = 'Lanzar Ahora';
                this.disabled = false;
            }
        });
    };

    function eliminarPromo(id) {
        if(!confirm("¿Mover esta promoción a la papelera?")) return;
        fetch(URL_ELIMINAR, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        }).then(r => r.json()).then(res => {
            if(res.success) location.reload();
        });
    }
</script>

<style>
    /* --- GRID --- */
/* --- GRID --- */
.grid-services {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    padding: 20px 0;
}

/* --- TARJETA PREMIUM --- */
.service-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    border: 1px solid #f1f2f6;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    position: relative;
}
.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(232, 67, 147, 0.12);
    border-color: #ffd1df;
}

/* --- CONTENEDOR IMAGEN --- */
.card-image-container {
    height: 200px;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.c-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.service-card:hover .c-img { transform: scale(1.08); }
.no-img { font-size: 3.5rem; color: #dfe6e9; }

/* --- BADGE MODALIDAD --- */
.category-badge {
    position: absolute; top: 15px; right: 15px; z-index: 5;
    padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 0.75rem;
    color: #fff; text-transform: uppercase; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    letter-spacing: 0.5px;
}

/* --- CONTENIDO --- */
.card-content { padding: 25px; flex: 1; display: flex; flex-direction: column; }
.service-title { font-size: 1.25rem; font-weight: 800; color: #2d3436; margin: 0 0 15px 0; line-height: 1.2; }

/* --- ZONA DE PRECIOS (ESTILO TICKET) --- */
.price-compare-row {
    display: flex; align-items: center; justify-content: center; gap: 15px;
    margin-bottom: 20px; background: #fdf2f7; padding: 12px; border-radius: 15px;
    border: 1px solid #fce4ec;
}
.price-old-col { text-align: center; }
.lbl-old { font-size: 0.65rem; color: #b2bec3; font-weight: 800; text-transform: uppercase; }
.val-old { font-size: 1rem; color: #a4b0be; text-decoration: line-through; font-weight: 600; display: block; }
.price-arrow { color: #e84393; font-size: 1.2rem; opacity: 0.5; }
.price-new-col { text-align: center; }
.lbl-new { font-size: 0.65rem; color: #e84393; font-weight: 800; text-transform: uppercase; }
.val-new { font-size: 1.6rem; font-weight: 900; color: #e84393; line-height: 1; }
.pts-extra { font-size: 0.75rem; color: #0984e3; font-weight: 800; display: block; margin-top: 4px; }

/* --- LÓGICA (CUPOS / FECHAS) --- */
.logic-container { margin-top: auto; padding-top: 15px; border-top: 1px solid #f1f2f6; }
.logic-row { display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #636e72; margin-bottom: 8px; }
.logic-val { font-weight: 700; color: #2d3436; }

.quota-header { display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 800; margin-bottom: 6px; }
.quota-bar { width: 100%; height: 8px; background: #eee; border-radius: 10px; overflow: hidden; }
.quota-fill { height: 100%; background: linear-gradient(90deg, #e84393, #fd79a8); border-radius: 10px; transition: width 0.5s ease; }

/* --- FOOTER --- */
.card-footer { padding: 15px 25px; background: #fbfbfb; border-top: 1px solid #f1f2f6; display: flex; gap: 12px; }
.btn-action {
    flex: 1; padding: 10px; border-radius: 12px; border: 1px solid #dfe6e9;
    background: #fff; color: #636e72; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.9rem; transition: 0.2s;
    text-decoration: none;
}
.btn-action:hover { background: #f1f2f6; color: #2d3436; border-color: #b2bec3; }
.btn-delete:hover { background: #fff5f5; color: #d63031; border-color: #fab1a0; }






.generic-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999;
        background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);
        display: none; align-items: center; justify-content: center;
    }
    .generic-modal-overlay.active { display: flex; animation: fadeIn 0.3s ease; }
    
    .generic-modal-box {
        background: white; width: 90%; max-width: 420px; border-radius: 30px; padding: 40px;
        text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.2); 
        transform: scale(0.9); transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .generic-modal-overlay.active .generic-modal-box { transform: scale(1); }

    .gm-icon { font-size: 3.5rem; margin-bottom: 20px; color: var(--primary); }
    .gm-title { font-size: 1.4rem; font-weight: 800; color: #2d3436; margin: 0 0 10px; }
    .gm-desc { color: #636e72; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5; }

    /* Formulario dentro del modal */
    .gm-form-group { background: #f8f9fa; padding: 20px; border-radius: 20px; margin-bottom: 25px; }
    .gm-field { text-align: left; margin-bottom: 15px; }
    .gm-field:last-child { margin-bottom: 0; }
    .gm-field label { display: block; font-size: 0.7rem; font-weight: 800; color: #b2bec3; margin-bottom: 8px; letter-spacing: 1px; }
    .gm-field input { 
        width: 100%; padding: 12px; border-radius: 12px; border: 2px solid #eee; 
        font-family: inherit; font-weight: 600; color: #2d3436; transition: 0.2s;
    }
    .gm-field input:focus { border-color: var(--primary); outline: none; background: white; }

    /* Botones */
    .gm-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .btn-gm { padding: 15px; border-radius: 15px; border: none; font-weight: 800; cursor: pointer; transition: 0.3s; font-size: 0.9rem; }
    .btn-gm-secondary { background: #eee; color: #636e72; }
    .btn-gm-primary { 
        background: #ff3366 !important; /* Forzamos el color rosa */
        color: #ffffff !important;      /* Forzamos texto blanco */
        display: block !important;       /* Aseguramos que se pinte */
        visibility: visible !important;
        box-shadow: 0 8px 20px rgba(255, 51, 102, 0.4); 
    }
    .btn-gm:hover { transform: translateY(-3px); filter: brightness(1.1); }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>