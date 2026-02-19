<link rel="stylesheet" href="<?= asset('recursos/css/producto.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Inventario Global</h1>
        <p class="section-subtitle">Bodega central y catálogo.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('compra', 'crear')): ?>
            <a href="<?= ruta_accion('compra', 'crear') ?>" class="btn-create btn-secondary">
                <i class="fa-solid fa-cart-flatbed"></i> <span>Registrar Compra</span>
            </a>
        <?php endif; ?>

        <?php if (tiene_permiso('producto', 'crear')): ?>
            <a href="<?= ruta_accion('producto', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo Producto</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('producto', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-box-open"></i> Activos
    </a>
    <a href="<?= ruta_accion('producto', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<?php if ($filtroActual === 'activos'): ?>
<div class="pills-container">
    <button class="filter-pill active" onclick="filtrarTipo('todos', this)">Todos</button>
    <button class="filter-pill" onclick="filtrarTipo('venta', this)"><i class="fa-solid fa-tag"></i> Venta</button>
    <button class="filter-pill" onclick="filtrarTipo('insumo', this)"><i class="fa-solid fa-flask"></i> Insumos</button>
</div>
<?php endif; ?>

<div class="filters-bar">
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
        <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" class="search-input" placeholder="Buscar producto..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('producto', 'listar', ['filtro' => $filtroActual]) ?>" class="clear-search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-filter">Buscar</button>
    </form>
</div>

<div class="grid-cards" id="gridProductos">
    <?php if (empty($listaProductos)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-boxes-stacked empty-icon"></i>
            <p>No hay productos en <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>
        <?php foreach ($listaProductos as $pro): ?>
            <?php 
                // Datos
                $esVenta = ($pro['pro_venta'] == 1);
                $esInsumo = ($pro['pro_insumo'] == 1);
                $dataTipo = ($esVenta ? "venta " : "") . ($esInsumo ? "insumo " : "");
                
                $precio = (float)$pro['pro_precio'];
                $costo = (float)($pro['pro_costo_compra'] ?? 0);
                $ganancia = $precio - $costo;
                $fotos = !empty($pro['galeria_urls']) ? explode(',', $pro['galeria_urls']) : [];
            ?>

            <div class="card-item producto-card filter-item <?= $dataTipo ?>">
                
                <div class="card-img-area">
                    <div class="card-badge">
                        <?php if($esVenta && $esInsumo): ?><span class="bg-purple">Mixto</span>
                        <?php elseif($esVenta): ?><span class="bg-green">Venta</span>
                        <?php else: ?><span class="bg-gray">Insumo</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($fotos)): ?>
                        <div class="carousel" id="car-<?= $pro['pro_id'] ?>">
                            <div class="carousel-inner">
                                <?php foreach($fotos as $i => $url): ?>
                                    <img src="<?= htmlspecialchars($url) ?>" class="c-img <?= $i===0?'active':'' ?>">
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if(count($fotos) > 1): ?>
                                <button class="c-btn prev" onclick="moveSlide(<?= $pro['pro_id'] ?>, -1)">&#10094;</button>
                                <button class="c-btn next" onclick="moveSlide(<?= $pro['pro_id'] ?>, 1)">&#10095;</button>
                                <div class="c-dots">
                                    <?php for($k=0; $k<count($fotos); $k++): ?>
                                        <span class="dot <?= $k===0?'active':'' ?>" id="dot-<?= $pro['pro_id'] ?>-<?= $k ?>"></span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-img"><i class="fa-solid fa-box-open"></i></div>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="meta-top">
                        <span class="cat-tag"><?= htmlspecialchars($pro['tpro_nombre']) ?></span>
                        <span class="cod-tag">#<?= htmlspecialchars($pro['pro_codigo']) ?></span>
                    </div>
                    
                    <h3 class="prod-title"><?= htmlspecialchars($pro['pro_nombre']) ?></h3>

                    <div class="stats-grid">
                        <div class="stat">
                            <small>Costo</small>
                            <span class="num text-orange">$<?= number_format($costo, 2) ?></span>
                        </div>
                        <?php if($esVenta): ?>
                            <div class="stat border-l">
                                <small>PVP</small>
                                <span class="num text-green">$<?= number_format($precio, 2) ?></span>
                            </div>
                            <div class="stat border-l">
                                <small>Ganancia</small>
                                <span class="num text-blue">$<?= number_format($ganancia, 2) ?></span>
                            </div>
                        <?php else: ?>
                            <div class="stat border-l flex-grow">
                                <small>Uso</small>
                                <span class="num text-muted" style="font-size:0.8rem; font-style:italic;">Interno</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="stock-row">
                        <?php 
                            $stock = (float)$pro['pro_stock'];
                            $stockClass = ($stock <= 5) ? (($stock == 0) ? 'out' : 'low') : 'ok';
                        ?>
                        <div class="stock-pill <?= $stockClass ?>">
                            <i class="fa-solid fa-cubes"></i> 
                            <b><?= $stock ?></b> <?= htmlspecialchars($pro['pro_unidad']) ?>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <?php if ($filtroActual === 'activos'): ?>
                        <div class="actions-group full-width">
                            <?php if (tiene_permiso('producto', 'editar')): ?>
                                <a href="<?= ruta_accion('producto', 'editar', ['id' => $pro['pro_id']]) ?>" class="btn-action edit">
                                    <i class="fa-solid fa-pencil"></i> Editar
                                </a>
                            <?php endif; ?>
                            
                            <?php if (tiene_permiso('producto', 'eliminar')): ?>
                                <button class="btn-action delete" onclick="preguntar('<?= ruta_accion('producto', 'eliminar', ['id' => $pro['pro_id']]) ?>', '¿Desactivar?', 'No se podrá usar.', 'Sí, Desactivar', 'danger')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if (tiene_permiso('producto', 'reactivar')): ?>
                            <button class="btn-restore" onclick="preguntar('<?= ruta_accion('producto', 'reactivar', ['id' => $pro['pro_id']]) ?>', '¿Restaurar?', 'Volverá al stock.', 'Sí, Restaurar', 'success')">
                                <i class="fa-solid fa-rotate-left"></i> Restaurar
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    /* --- FILTROS (PILLS) --- */
    .pills-container { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-pill {
        background: white; border: 1px solid #dfe6e9; color: #636e72;
        padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 0.9rem;
        cursor: pointer; transition: all 0.2s; outline: none;
    }
    .filter-pill:hover { background: #f8f9fa; transform: translateY(-1px); }
    .filter-pill.active {
        background: var(--color-primario); color: white; border-color: var(--color-primario);
        box-shadow: 0 4px 10px rgba(253, 121, 168, 0.3);
    }

    /* GRID */
    .grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; padding-bottom: 40px; }

    /* TARJETA */
    .producto-card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #f0f0f0; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: flex; flex-direction: column; transition: transform 0.2s; }
    .producto-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }

    /* IMAGEN & CARRUSEL (CORREGIDO CONTAIN) */
    .card-img-area { position: relative; height: 200px; background: #fff; overflow: hidden; border-bottom: 1px solid #f9f9f9; }
    .carousel, .carousel-inner { width: 100%; height: 100%; position: relative; }
    
    .c-img { 
        width: 100%; height: 100%; 
        object-fit: contain; /* ¡AQUÍ ESTÁ EL CAMBIO! */
        padding: 10px; /* Margen para que no toque bordes */
        position: absolute; top: 0; left: 0; opacity: 0; transition: opacity 0.3s; 
    }
    .c-img.active { opacity: 1; z-index: 1; }
    .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #dfe6e9; }
    
    .c-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.2); color: white; border: none; padding: 0; cursor: pointer; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; z-index: 10; transition: 0.2s; }
    .c-btn:hover { background: rgba(0,0,0,0.6); }
    .prev { left: 5px; } .next { right: 5px; }
    
    .c-dots { position: absolute; bottom: 5px; width: 100%; text-align: center; z-index: 10; }
    .dot { height: 6px; width: 6px; background-color: rgba(0,0,0,0.2); border-radius: 50%; display: inline-block; margin: 0 2px; }
    .dot.active { background-color: var(--color-primario); transform: scale(1.2); }

    .card-badge { position: absolute; top: 10px; left: 10px; z-index: 5; }
    .card-badge span { padding: 4px 10px; border-radius: 20px; color: white; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .bg-purple { background: #a29bfe; } .bg-green { background: #00b894; } .bg-gray { background: #636e72; }

    /* CUERPO */
    .card-body { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
    .meta-top { display: flex; justify-content: space-between; font-size: 0.75rem; color: #b2bec3; margin-bottom: 5px; }
    .prod-title { font-size: 1.1rem; font-weight: 700; color: #2d3436; margin: 0 0 10px; line-height: 1.3; }
    
    .stats-grid { display: flex; background: #f8f9fa; border-radius: 8px; padding: 8px; margin-bottom: 10px; }
    .stat { flex: 1; text-align: center; display: flex; flex-direction: column; }
    .stat small { font-size: 0.65rem; color: #b2bec3; text-transform: uppercase; }
    .stat .num { font-weight: 700; font-size: 0.9rem; }
    .border-l { border-left: 1px solid #eee; }
    .text-orange { color: #e17055; } .text-green { color: #00b894; } .text-blue { color: #0984e3; }

    .stock-row { margin-top: auto; }
    .stock-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; width: 100%; justify-content: center; }
    .stock-pill.ok { background: #e3fcf7; color: #00b894; }
    .stock-pill.low { background: #fff8e1; color: #fdcb6e; }
    .stock-pill.out { background: #ffecec; color: #d63031; }

    /* FOOTER */
    .card-footer { padding: 10px 15px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: white; }
    .actions-group { display: flex; gap: 10px; width: 100%; }
    .btn-action { flex: 1; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: 0.2s; }
    .btn-action.edit { background: #eef2f3; color: #636e72; }
    .btn-action.edit:hover { background: #dfe6e9; color: #2d3436; }
    .btn-action.delete { background: #ffecec; color: #d63031; max-width: 40px; }
    .btn-action.delete:hover { background: #d63031; color: white; }
    
    .btn-restore { width: 100%; background: #e3fcf7; color: #00b894; border:none; padding: 8px; border-radius: 6px; font-weight: 600; cursor: pointer; }
</style>

<script>
    // --- FILTRADO VISUAL TABS ---
    function filtrarTipo(tipo, btn) {
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const items = document.querySelectorAll('.filter-item');
        items.forEach(item => {
            if (tipo === 'todos') item.style.display = 'flex';
            else item.style.display = item.classList.contains(tipo) ? 'flex' : 'none';
        });
    }

    // --- CARRUSEL JS PURO (Manual + Automático) ---
    function moveSlide(id, dir) {
        const container = document.getElementById('car-' + id);
        // Validación de seguridad por si el elemento no existe al cambiar de página
        if (!container) return; 

        const imgs = container.querySelectorAll('.c-img');
        const dots = container.querySelectorAll('.dot');
        const total = imgs.length;
        
        // Si solo hay 1 foto, no hacemos nada
        if (total <= 1) return;

        let current = 0;
        // Buscar cual está activa actualmente
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

    // --- [NUEVO] AUTO-PLAY AL CARGAR ---
    document.addEventListener('DOMContentLoaded', () => {
        const carousels = document.querySelectorAll('.carousel');
        
        carousels.forEach(car => {
            // Verificar si tiene más de 1 imagen para activar el auto-play
            const images = car.querySelectorAll('.c-img');
            if (images.length > 1) {
                // Extraer el ID del producto desde el ID del div (ej: car-15 -> 15)
                const prodId = car.id.split('-')[1];
                
                // Configurar intervalo de 3.5 segundos (3500 ms)
                setInterval(() => {
                    // Mover +1 (derecha)
                    moveSlide(prodId, 1);
                }, 3500); 
            }
        });
    });
</script>