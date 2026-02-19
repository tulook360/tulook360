<link rel="stylesheet" href="<?= asset('recursos/css/servicio.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Catálogo de Servicios</h1>
        <p class="section-subtitle">Gestiona tu menú de servicios.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('servicio', 'crear')): ?>
            <a href="<?= ruta_accion('servicio', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo Servicio</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('servicio', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-scissors"></i> Activos
    </a>
    <a href="<?= ruta_accion('servicio', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="filters-bar">
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
        <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" class="search-input" placeholder="Buscar servicio..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('servicio', 'listar', ['filtro' => $filtroActual]) ?>" class="clear-search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-filter">Buscar</button>
    </form>
</div>

<div class="grid-services">
    <?php if (empty($listaServicios)): ?>
        <div class="empty-state">
            <div class="icon-circle"><i class="fa-solid fa-cut"></i></div>
            <h3>Sin Servicios</h3>
            <p>No hay registros en <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>
        <?php foreach ($listaServicios as $serv): ?>
            <?php 
                $fotos = !empty($serv['galeria_urls']) ? explode(',', $serv['galeria_urls']) : [];
                $precio = number_format($serv['serv_precio'], 2);
                $duracion = $serv['serv_duracion'];
            ?>
            
            <div class="service-card">
                
                <div class="card-image-container">
                    <span class="category-badge"><?= htmlspecialchars($serv['tser_nombre'] ?? 'General') ?></span>

                    <?php if (!empty($fotos)): ?>
                        <div class="carousel" id="car-<?= $serv['serv_id'] ?>">
                            <?php foreach($fotos as $i => $url): ?>
                                <img src="<?= htmlspecialchars($url) ?>" class="c-img <?= $i===0?'active':'' ?>">
                            <?php endforeach; ?>
                            
                            <?php if(count($fotos) > 1): ?>
                                <button class="c-btn prev" onclick="moveSlide(<?= $serv['serv_id'] ?>, -1)">&#10094;</button>
                                <button class="c-btn next" onclick="moveSlide(<?= $serv['serv_id'] ?>, 1)">&#10095;</button>
                                <div class="c-dots">
                                    <?php for($k=0; $k<count($fotos); $k++): ?>
                                        <span class="dot <?= $k===0?'active':'' ?>"></span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-img"><i class="fa-solid fa-scissors"></i></div>
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <div class="card-header-row">
                        <h3 class="service-title"><?= htmlspecialchars($serv['serv_nombre']) ?></h3>
                    </div>
                    
                    <div class="service-meta">
                        <div class="meta-item"><i class="fa-regular fa-clock"></i> <?= $duracion ?> min</div>
                        <div class="meta-item price">$<?= $precio ?></div>
                    </div>

                    <p class="service-desc">
                        <?= htmlspecialchars($serv['serv_resumen'] ?: $serv['serv_descripcion'] ?: 'Sin descripción') ?>
                    </p>
                </div>

                <div class="card-footer">
                    <?php if ($filtroActual === 'activos'): ?>
                        <?php if (tiene_permiso('servicio', 'editar')): ?>
                            <a href="<?= ruta_accion('servicio', 'editar', ['id' => $serv['serv_id']]) ?>" class="btn-action edit"><i class="fa-solid fa-pencil"></i> Editar</a>
                        <?php endif; ?>
                        <?php if (tiene_permiso('servicio', 'eliminar')): ?>
                            <button class="btn-action delete" onclick="preguntar('<?= ruta_accion('servicio', 'eliminar', ['id' => $serv['serv_id']]) ?>', '¿Desactivar?', 'No aparecerá en la agenda.', 'Desactivar', 'danger')"><i class="fa-solid fa-trash"></i></button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn-restore" onclick="preguntar('<?= ruta_accion('servicio', 'reactivar', ['id' => $serv['serv_id']]) ?>', '¿Restaurar?', 'Volverá al catálogo activo.', 'Restaurar', 'success')"><i class="fa-solid fa-rotate-left"></i> Restaurar</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    /* GRID */
    .grid-services { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; padding-bottom: 40px; }

    /* TARJETA */
    .service-card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #f0f0f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: flex; flex-direction: column; transition: transform 0.3s; }
    .service-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.06); }

    /* --- SOLUCIÓN IMAGEN COMPLETA --- */
    .card-image-container { 
        position: relative; 
        height: 200px; /* Altura controlada para que no sea enorme */
        background: #fff; /* Fondo blanco limpio para los espacios vacíos */
        border-bottom: 1px solid #f5f5f5;
        display: flex; align-items: center; justify-content: center;
        padding: 5px; /* Pequeño margen interno */
    }
    .carousel { width: 100%; height: 100%; position: relative; }
    
    .c-img { 
        width: 100%; 
        height: 100%; 
        object-fit: contain; /* ¡ESTO HACE LA MAGIA! Ajusta la foto completa sin cortar */
        position: absolute; top: 0; left: 0; opacity: 0; transition: opacity 0.4s; 
    }
    .c-img.active { opacity: 1; z-index: 1; }
    
    .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #dfe6e9; }

    /* Controles */
    .c-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.1); color: #2d3436; border: none; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; transition: 0.2s; }
    .c-btn:hover { background: rgba(0,0,0,0.2); }
    .prev { left: 5px; } .next { right: 5px; }
    .c-dots { position: absolute; bottom: 5px; width: 100%; text-align: center; z-index: 10; }
    .dot { height: 6px; width: 6px; background: rgba(0,0,0,0.1); border-radius: 50%; display: inline-block; margin: 0 3px; }
    .dot.active { background: var(--color-primario); transform: scale(1.2); }

    /* Badge */
    .category-badge { position: absolute; top: 10px; left: 10px; z-index: 5; background: rgba(0,0,0,0.7); color: white; padding: 2px 10px; border-radius: 15px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }

    /* Contenido */
    .card-content { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
    .service-title { font-size: 1.1rem; font-weight: 700; color: #2d3436; margin: 0 0 10px; line-height: 1.2; }
    .service-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f5f5f5; }
    .meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #636e72; font-weight: 600; }
    .meta-item.price { color: var(--color-primario); font-size: 1.1rem; font-weight: 800; }
    .service-desc { font-size: 0.85rem; color: #b2bec3; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin: 0; }

    /* Footer */
    .card-footer { padding: 10px 15px; background: #fff; border-top: 1px solid #f0f0f0; display: flex; gap: 10px; }
    .btn-action { flex: 1; padding: 8px; border-radius: 6px; border: none; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: 0.2s; text-decoration: none; }
    .btn-action.edit { background: #f1f2f6; color: #2d3436; } .btn-action.edit:hover { background: #dfe6e9; }
    .btn-action.delete { background: #fff0f0; color: #d63031; max-width: 40px; } .btn-action.delete:hover { background: #ffecec; }
    .btn-restore { width: 100%; background: #e3fcf7; color: #00b894; border:none; padding:10px; border-radius:8px; font-weight:700; cursor:pointer; }

    .empty-state { grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; }
    .icon-circle { width: 80px; height: 80px; background: #f8f9fa; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .icon-circle i { font-size: 2.5rem; color: #dfe6e9; }
    .empty-state h3 { margin: 0; color: #2d3436; }
    .empty-state p { color: #b2bec3; margin-top: 5px; }
</style>

<script>
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
        let next = current + dir;
        if(next < 0) next = total - 1;
        if(next >= total) next = 0;
        imgs[next].classList.add('active');
        if(dots[next]) dots[next].classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const carousels = document.querySelectorAll('.carousel');
        carousels.forEach(car => {
            if (car.querySelectorAll('.c-img').length > 1) {
                const id = car.id.split('-')[1];
                setInterval(() => moveSlide(id, 1), 4000);
            }
        });
    });
</script>