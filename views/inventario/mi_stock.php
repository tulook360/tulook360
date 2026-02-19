<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">

    <div class="header-flex">
        <div>
            <h1 class="kalam" style="font-size: 2.5rem; margin: 0; color: var(--color-texto); line-height: 1;">
                Mi <span style="color: var(--color-primario);">Inventario</span>
            </h1>
            <p style="margin-top: 5px; color: #64748b; font-size: 0.95rem; font-weight: 500;">
                Vista general de existencias.
            </p>
        </div>
        <a href="<?= ruta_accion('inventario', 'solicitar') ?>" class="btn btn--primario btn-responsive">
            <i class="fa-solid fa-plus"></i> <span class="btn-text">Reabastecer</span>
        </a>
    </div>

    <div class="stats-container">
        <div class="stat-box">
            <span class="stat-num text-dark"><?= $totalProductos ?></span>
            <span class="stat-lbl">Total Items</span>
        </div>
        <div class="stat-box">
            <span class="stat-num text-danger"><?= $criticos ?></span>
            <span class="stat-lbl">Stock Bajo</span>
        </div>
        <div class="stat-box">
            <span class="stat-num text-success"><?= $totalProductos - $criticos - $agotados ?></span>
            <span class="stat-lbl">Óptimos</span>
        </div>
    </div>

    <div class="search-wrapper">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="stockSearch" placeholder="Buscar producto..." onkeyup="filterTable()">
    </div>

    <div class="responsive-table-container">
        <?php if (empty($miInventario)): ?>
            <div class="empty-msg">
                <i class="fa-solid fa-box-open"></i>
                <p>No tienes inventario registrado.</p>
            </div>
        <?php else: ?>
            <table class="r-table" id="invTable">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Estado</th>
                        <th>En Bodega</th>
                        <th>En Uso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($miInventario as $prod): 
                        $cerrado = floatval($prod['stock_cerrado']);
                        $abierto = floatval($prod['stock_abierto']);
                        $contenido = floatval($prod['pro_contenido']);
                        $foto = $prod['pro_foto'] ?: 'recursos/img/sin_foto.png';

                        $pct = ($contenido > 0) ? ($abierto / $contenido) * 100 : 0;
                        if($pct > 100) $pct = 100;

                        $badgeClass = 'bg-success-light text-success'; $badgeText = 'Disponible';
                        if($cerrado == 0 && $abierto == 0) { $badgeClass = 'bg-danger-light text-danger'; $badgeText = 'Agotado'; }
                        elseif($cerrado == 0) { $badgeClass = 'bg-warning-light text-warning'; $badgeText = 'Última Unidad'; }
                    ?>
                    <tr class="r-row" data-filter="<?= strtolower($prod['pro_nombre'] . ' ' . $prod['pro_codigo']) ?>">
                        
                        <td data-label="Producto">
                            <div class="prod-info">
                                <img src="<?= $foto ?>" class="prod-img">
                                <div>
                                    <div class="prod-name"><?= $prod['pro_nombre'] ?></div>
                                    <div class="prod-ref">Ref: <?= $prod['pro_codigo'] ?></div>
                                </div>
                            </div>
                        </td>

                        <td data-label="Estado">
                            <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                        </td>

                        <td data-label="En Bodega">
                            <div class="stock-data">
                                <strong><?= $cerrado ?></strong> <?= $prod['pro_unidad'] ?>s
                            </div>
                        </td>

                        <td data-label="En Uso">
                            <div class="usage-data">
                                <div class="usage-nums">
                                    <span><?= number_format($abierto, 0) ?></span>
                                    <small>/ <?= number_format($contenido, 0) ?> <?= $prod['pro_unidad_consumo'] ?></small>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
                                </div>
                            </div>
                        </td>

                        <td data-label="Acción">
                            <?php if($cerrado <= 1): ?>
                                <a href="<?= ruta_accion('inventario', 'solicitar') ?>" class="btn-action-icon">
                                    <i class="fa-solid fa-cart-plus"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<style>
    /* --- CSS BASE (PC FIRST) --- */
    .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    
    .stats-container {
        display: flex; justify-content: space-around; margin-bottom: 2rem; 
        background: transparent; padding: 10px 0;
    }
    .stat-box { text-align: center; display: flex; flex-direction: column; }
    .stat-num { font-size: 2rem; font-weight: 800; line-height: 1; }
    .stat-lbl { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #b2bec3; margin-top: 5px; letter-spacing: 1px; }
    
    .text-dark { color: var(--color-texto); }
    .text-danger { color: #ff7675; }
    .text-success { color: #00b894; }

    .search-wrapper { position: relative; max-width: 400px; margin-bottom: 2rem; }
    .search-wrapper input {
        width: 100%; padding: 12px 20px 12px 45px; border-radius: 50px; 
        border: 2px solid transparent; background: #fff; font-size: 0.95rem; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); outline: none; transition: 0.3s;
    }
    .search-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: var(--color-primario); }

    /* TABLA BASE (PC) */
    .responsive-table-container { background: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); }
    .r-table { width: 100%; border-collapse: collapse; }
    .r-table th { text-align: left; padding: 15px; color: #b2bec3; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f8f9fa; }
    .r-table td { padding: 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }
    
    /* Elementos Internos */
    .prod-info { display: flex; align-items: center; gap: 15px; }
    .prod-img { width: 45px; height: 45px; border-radius: 10px; object-fit: contain; background: #f8f9fa; border: 1px solid #eee; }
    .prod-name { font-weight: 700; color: var(--color-texto); font-size: 0.95rem; }
    .prod-ref { font-size: 0.75rem; color: #b2bec3; margin-top: 2px; }

    .status-badge { padding: 5px 12px; border-radius: 30px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
    .bg-success-light { background: #e1fcf0; } .text-success { color: #00b894; }
    .bg-danger-light { background: #fff5f5; } .text-danger { color: var(--color-secundario); }
    .bg-warning-light { background: #fff8e1; } .text-warning { color: #f59e0b; }

    .stock-data { font-weight: 500; color: var(--color-texto); }
    .stock-data strong { font-size: 1.1rem; font-weight: 800; }

    .usage-data { width: 100%; max-width: 150px; }
    .usage-nums { display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; }
    .progress-track { width: 100%; height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, var(--color-secundario), var(--color-primario)); border-radius: 10px; }

    .btn-action-icon { display: inline-flex; width: 35px; height: 35px; border-radius: 50%; background: var(--color-primario); color: white; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 10px rgba(232, 67, 147, 0.3); }

    .empty-msg { padding: 4rem; text-align: center; color: #b2bec3; }
    .empty-msg i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }

    /* --- MEDIA QUERIES (MÓVIL MAGIA) --- */
    @media (max-width: 768px) {
        /* Ajustes Generales */
        .page-main { padding: 1rem; }
        .header-flex { flex-direction: column; align-items: flex-start; gap: 15px; margin-bottom: 1.5rem; }
        .btn-responsive { width: 100%; justify-content: center; }
        
        .stats-container { gap: 10px; justify-content: space-between; }
        .stat-num { font-size: 1.5rem; }
        .stat-lbl { font-size: 0.65rem; }

        .responsive-table-container { padding: 0; background: transparent; box-shadow: none; }

        /* DESTRUCCIÓN DE LA TABLA -> TARJETAS */
        .r-table, .r-table thead, .r-table tbody, .r-table th, .r-table td, .r-table tr { display: block; }
        
        /* Ocultar encabezados de tabla */
        .r-table thead tr { position: absolute; top: -9999px; left: -9999px; }
        
        .r-table tr { 
            background: #fff; border-radius: 16px; margin-bottom: 1rem; 
            padding: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0f0f0;
            position: relative;
        }

        .r-table td { 
            border: none; padding: 5px 0; position: relative; padding-left: 0; display: flex; justify-content: space-between; align-items: center;
        }

        /* Reorganización Visual en Móvil */
        
        /* 1. Producto Arriba del todo */
        td[data-label="Producto"] { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #f5f5f5; width: 100%; }
        
        /* 2. Etiquetas para los datos */
        td[data-label="En Bodega"]::before { content: "En Bodega"; font-weight: 700; font-size: 0.8rem; color: #b2bec3; text-transform: uppercase; }
        td[data-label="En Uso"]::before { content: "Abierto"; font-weight: 700; font-size: 0.8rem; color: #b2bec3; text-transform: uppercase; }
        td[data-label="Estado"]::before { content: "Estado"; font-weight: 700; font-size: 0.8rem; color: #b2bec3; text-transform: uppercase; }

        /* 3. Ajuste de barra de progreso en móvil */
        .usage-data { width: 50%; max-width: none; text-align: right; }
        .usage-nums { justify-content: flex-end; gap: 5px; }

        /* 4. Botón Acción (Flotante o Alineado) */
        td[data-label="Acción"] { justify-content: flex-end; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f5f5f5; }
        .btn-action-icon { width: 100%; border-radius: 10px; height: 40px; font-weight: 700; gap: 8px; }
        .btn-action-icon::after { content: "Solicitar Stock"; font-size: 0.9rem; }
    }
</style>

<script>
    function filterTable() {
        const term = document.getElementById('stockSearch').value.toLowerCase();
        document.querySelectorAll('.r-row').forEach(row => {
            const txt = row.getAttribute('data-filter');
            row.style.display = txt.includes(term) ? '' : 'none'; // En móvil 'block', en PC 'table-row' (automático)
        });
    }
</script>