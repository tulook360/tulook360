<div class="section-header">
    <div>
        <h1 class="section-title kalam">Historial de Ingresos</h1>
        <p class="section-subtitle">Bitácora de compras y reposiciones.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('compra', 'crear')): ?>
            <a href="<?= ruta_accion('compra', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo Ingreso</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="search-floating-bar">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="buscadorGlobal" placeholder="Buscar proveedor, documento o fecha..." onkeyup="filtrarContenido()">
</div>

<div class="grid-ingresos" id="contenedorIngresos">
    <?php if (empty($listaCompras)): ?>
        <div class="empty-state">
            <div class="icon-box"><i class="fa-solid fa-box-open"></i></div>
            <h3>Sin Historial</h3>
            <p>No se han registrado ingresos de mercadería todavía.</p>
        </div>
    <?php else: ?>
        <?php 
            $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        ?>
        <?php foreach ($listaCompras as $c): ?>
            <?php 
                $ts = strtotime($c['com_fecha']);
                $fechaEsp = date('d', $ts) . ' ' . $meses[date('n', $ts)] . ' ' . date('Y', $ts);
                $horaEsp = date('H:i', $ts);
            ?>
            
            <div class="ingreso-card filter-item">
                <div class="card-top">
                    <div class="date-badge">
                        <i class="fa-regular fa-calendar"></i> <?= $fechaEsp ?> <span style="opacity:0.6;">| <?= $horaEsp ?></span>
                    </div>
                    <?php if ($c['com_estado'] == 'A'): ?>
                        <span class="status-badge success">Aprobado</span>
                    <?php else: ?>
                        <span class="status-badge danger">Anulado</span>
                    <?php endif; ?>
                </div>

                <div class="card-main">
                    <div class="provider-row">
                        <div class="prov-name"><?= htmlspecialchars($c['com_proveedor']) ?></div>
                        <div class="total-amount">$<?= number_format($c['com_total'], 2) ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-item">
                            <span class="label">Documento</span>
                            <?php if($c['com_tipo_doc'] == 'SIN_SOPORTE'): ?>
                                <span class="val warning"><i class="fa-solid fa-store-slash"></i> Informal</span>
                            <?php else: ?>
                                <span class="val"><i class="fa-solid fa-file-invoice"></i> <?= htmlspecialchars($c['com_numero_doc'] ?? 'S/N') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="info-item right">
                            <span class="label">Registrado por</span>
                            <span class="val user"><i class="fa-solid fa-user-check"></i> <?= explode(' ', $c['usu_nombres'])[0] ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-actions">
                    <?php if (!empty($c['com_evidencia'])): ?>
                        <a href="<?= htmlspecialchars($c['com_evidencia']) ?>" target="_blank" class="btn-action secondary" title="Ver Foto">
                            <i class="fa-solid fa-camera"></i>
                        </a>
                    <?php else: ?>
                        <span class="btn-placeholder"></span>
                    <?php endif; ?>

                    <button class="btn-action primary" onclick="abrirModalCompra(<?= $c['com_id'] ?>, '<?= htmlspecialchars($c['com_proveedor']) ?>', '<?= number_format($c['com_total'], 2) ?>')">
                        Ver Detalles <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalCompraDetalle">
    <div class="modal-box modal-responsive">
        
        <div class="modal-header-custom">
            <div class="modal-info">
                <h3 id="mdProv">Proveedor</h3>
                <span class="tag-total">Total: $<span id="mdTotal">0.00</span></span>
            </div>
            <button class="btn-close-clean" onclick="cerrarModalCompra()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-scroll-area">
            <div id="loaderModal" class="loader-state">
                <div class="spinner"></div><p>Cargando...</p>
            </div>

            <table class="table-detail-clean" id="tablaDetalleContenido" style="display:none;">
                <thead>
                    <tr>
                        <th width="50">Img</th>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="mdTbody"></tbody>
            </table>
            
            <div id="errorModal" class="error-state" style="display:none;">
                <p id="txtError" class="text-danger"></p>
            </div>
        </div>

        <div class="modal-footer-custom">
            <button class="btn-modal btn-confirm w-100" onclick="cerrarModalCompra()">Cerrar</button>
        </div>
    </div>
</div>

<style>
    /* --- GRID RESPONSIVE --- */
    .grid-ingresos { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; padding-bottom: 40px; }

    /* --- TARJETA --- */
    .ingreso-card { background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; display: flex; flex-direction: column; transition: transform 0.2s; }
    .ingreso-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }

    .card-top { padding: 12px 15px; background: #fcfcfc; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
    .date-badge { font-size: 0.8rem; color: #636e72; font-weight: 600; }
    .status-badge { font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; }
    .status-badge.success { background: #e3fcf7; color: #00b894; }
    .status-badge.danger { background: #ffecec; color: #d63031; }

    .card-main { padding: 15px; flex-grow: 1; }
    .provider-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .prov-name { font-weight: 700; font-size: 1.05rem; color: #2d3436; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 65%; }
    .total-amount { font-weight: 800; font-size: 1.1rem; color: var(--color-primario); }

    .info-row { display: flex; justify-content: space-between; margin-top: 5px; }
    .info-item { display: flex; flex-direction: column; }
    .info-item.right { align-items: flex-end; }
    .label { font-size: 0.7rem; color: #b2bec3; text-transform: uppercase; margin-bottom: 2px; }
    .val { font-size: 0.85rem; color: #636e72; font-weight: 500; }
    .val.warning { color: #d35400; font-weight: 600; }
    .val.user { color: #0984e3; }

    .card-actions { padding: 12px 15px; border-top: 1px solid #f0f0f0; display: flex; gap: 10px; align-items: center; }
    .btn-action { padding: 8px 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .btn-action.secondary { background: #eef2f3; color: #636e72; width: 40px; }
    .btn-action.secondary:hover { background: #dfe6e9; color: #2d3436; }
    .btn-action.primary { background: var(--color-primario); color: white; flex-grow: 1; gap: 8px; }
    .btn-action.primary:hover { opacity: 0.9; }
    .btn-placeholder { width: 40px; }

    /* --- BUSCADOR --- */
    .search-floating-bar { background: white; padding: 12px 20px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; display: flex; align-items: center; gap: 12px; border: 1px solid #f0f0f0; max-width: 500px; }
    .search-floating-bar input { border: none; outline: none; width: 100%; font-size: 1rem; color: #2d3436; }

    /* --- EMPTY STATE --- */
    .empty-state { text-align: center; padding: 3rem; grid-column: 1 / -1; }
    .icon-box { width: 80px; height: 80px; background: #f1f2f6; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; color: #b2bec3; font-size: 2rem; }
    .empty-state h3 { margin: 0; color: #2d3436; }
    .empty-state p { color: #636e72; }

    /* --- MODAL --- */
    .modal-responsive { width: 100%; max-width: 600px; height: auto; max-height: 90vh; display: flex; flex-direction: column; border-radius: 16px; padding: 0; overflow: hidden; }
    .modal-header-custom { padding: 15px 20px; background: white; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-info h3 { margin: 0; font-size: 1.1rem; color: #2d3436; }
    .tag-total { background: var(--color-primario); color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; margin-top: 4px; display: inline-block; }
    .btn-close-clean { background: none; border: none; font-size: 1.2rem; color: #b2bec3; cursor: pointer; }
    
    .modal-scroll-area { flex: 1; overflow-y: auto; background: #f9f9f9; padding: 0; position: relative; min-height: 200px; }
    .table-detail-clean { width: 100%; border-collapse: collapse; }
    .table-detail-clean th { background: #f1f2f6; color: #636e72; font-size: 0.75rem; text-transform: uppercase; padding: 12px 15px; text-align: left; position: sticky; top: 0; }
    .table-detail-clean td { padding: 12px 15px; border-bottom: 1px solid #eee; background: white; font-size: 0.9rem; vertical-align: middle; }
    .thumb-mini { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: #eee; border: 1px solid #ddd; }
    .no-thumb { width: 40px; height: 40px; border-radius: 8px; background: #f1f2f6; display: flex; align-items: center; justify-content: center; color: #ccc; }
    
    .modal-footer-custom { padding: 15px; background: white; border-top: 1px solid #f0f0f0; }
    .w-100 { width: 100%; }
    .loader-state, .error-state { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: white; z-index: 10; }
    .spinner { width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid var(--color-primario); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 10px; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
    function filtrarmContenido() {
        const filter = document.getElementById('buscadorGlobal').value.toLowerCase();
        const items = document.querySelectorAll('.filter-item');
        items.forEach(item => {
            const texto = item.innerText.toLowerCase();
            item.style.display = texto.includes(filter) ? "flex" : "none";
        });
    }

    // --- FUNCIÓN DE APERTURA (NOMBRE ÚNICO) ---
    function abrirModalCompra(id, proveedor, total) {
        const modal = document.getElementById('modalCompraDetalle');
        const loader = document.getElementById('loaderModal');
        const tabla = document.getElementById('tablaDetalleContenido');
        const error = document.getElementById('errorModal');
        const tbody = document.getElementById('mdTbody');

        document.getElementById('mdProv').innerText = proveedor;
        document.getElementById('mdTotal').innerText = total;

        modal.classList.add('active');
        loader.style.display = 'flex';
        tabla.style.display = 'none';
        error.style.display = 'none';
        tbody.innerHTML = '';

        fetch(`<?= ruta_accion('compra', 'ver_detalle', [], false) ?>&id=${id}`)
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                if(data.success) {
                    tabla.style.display = 'table';
                    if(data.datos.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center p-3 text-muted">Sin detalles</td></tr>';
                        return;
                    }
                    data.datos.forEach(d => {
                        const imgTag = d.pro_foto 
                            ? `<img src="${d.pro_foto}" class="thumb-mini">` 
                            : `<div class="no-thumb"><i class="fa-solid fa-box"></i></div>`;

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-center">${imgTag}</td>
                            <td>
                                <div style="font-weight:700; color:#2d3436;">${d.pro_nombre}</div>
                                <div style="font-size:0.75rem; color:#b2bec3;">${d.pro_codigo}</div>
                            </td>
                            <td class="text-center">
                                <span style="background:#f1f2f6; padding:2px 8px; border-radius:4px; font-weight:600; font-size:0.85rem;">
                                    ${parseFloat(d.det_cantidad)}
                                </span>
                            </td>
                            <td class="text-right" style="font-weight:700; color:var(--color-primario);">
                                $${parseFloat(d.det_subtotal).toFixed(2)}
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    document.getElementById('txtError').innerText = data.error || 'Error';
                    error.style.display = 'flex';
                }
            })
            .catch(err => {
                loader.style.display = 'none';
                document.getElementById('txtError').innerText = "Error de conexión";
                error.style.display = 'flex';
            });
    }

    // --- FUNCIÓN DE CIERRE (NOMBRE ÚNICO) ---
    // Cambié el nombre a 'cerrarModalCompra' para evitar conflicto con 'cerrarModal' global
    function cerrarModalCompra() {
        document.getElementById('modalCompraDetalle').classList.remove('active');
    }
</script>