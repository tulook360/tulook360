<div class="section-header">
    <div>
        <h1 class="section-title kalam">Mis Pedidos</h1>
        <p class="section-subtitle">Gestiona tus solicitudes y recepciona la mercadería.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('inventario', 'solicitar') ?>" class="btn-create">
            <i class="fa-solid fa-plus"></i> <span>Solicitar Stock</span>
        </a>
    </div>
</div>

<div class="pills-container">
    <button class="filter-pill active" id="btn-pendiente" onclick="filtrarTab('pendiente')">
        <i class="fa-regular fa-clock"></i> Pendientes
    </button>
    <button class="filter-pill" id="btn-encamino" onclick="filtrarTab('encamino')">
        <i class="fa-solid fa-truck-fast"></i> En Camino
    </button>
    <button class="filter-pill" id="btn-recibido" onclick="filtrarTab('recibido')">
        <i class="fa-solid fa-box-archive"></i> Historial
    </button>
</div>

<div class="search-floating-bar" style="margin-top: 0;">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="globalSearch" placeholder="Buscar por código #123..." onkeyup="aplicarFiltros()">
    <i class="fa-solid fa-xmark clear-icon" onclick="limpiarBusqueda()" style="display:none;" id="btnClear"></i>
</div>

<div class="grid-pedidos">
    <?php if (empty($misPedidos)): ?>
        <div class="empty-state">
            <div class="icon-circle"><i class="fa-solid fa-clipboard-list"></i></div>
            <h3>Sin historial</h3>
            <p>No has realizado solicitudes todavía.</p>
        </div>
    <?php else: ?>
        <?php 
            $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        ?>
        <?php foreach ($misPedidos as $ped): ?>
            <?php 
                $estado = strtoupper($ped['ped_estado']);
                
                // Clasificación para Filtros
                $filtro = 'recibido'; // Default
                $claseEstado = 'is-received';
                $icono = 'fa-check';
                
                if ($estado === 'PENDIENTE') {
                    $filtro = 'pendiente';
                    $claseEstado = 'is-pending';
                    $icono = 'fa-hourglass-half';
                } elseif ($estado === 'EN CAMINO') {
                    $filtro = 'encamino';
                    $claseEstado = 'is-shipping';
                    $icono = 'fa-truck';
                } elseif ($estado === 'CANCELADO' || $estado === 'RECHAZADO') {
                    $filtro = 'recibido';
                    $claseEstado = 'is-cancelled';
                    $icono = 'fa-ban';
                }

                $codigoDisplay = str_replace('PED-', '', $ped['ped_codigo']);
                
                // Fecha en Español
                $ts = strtotime($ped['ped_fecha_solicitud']);
                $fechaEsp = date('d', $ts) . ' ' . $meses[date('n', $ts)] . ' ' . date('Y', $ts);
                $horaEsp = date('H:i', $ts);
                
                // Data Search: Incluimos todo para que el buscador encuentre fácil
                $searchData = strtolower($ped['ped_codigo'] . ' ' . $codigoDisplay . ' ' . $ped['usu_nombres']);
            ?>

            <div class="pedido-card filter-item" id="card-<?= $ped['ped_id'] ?>" data-category="<?= $filtro ?>" data-search="<?= $searchData ?>">
                
                <div class="card-header-row">
                    <div class="ped-id">
                        <i class="fa-solid fa-hashtag"></i> <?= $codigoDisplay ?>
                    </div>
                    <div class="ped-date">
                        <?= $fechaEsp ?> <small><?= $horaEsp ?></small>
                    </div>
                </div>

                <div class="card-body-row">
                    <div class="status-pill <?= $claseEstado ?>">
                        <i class="fa-solid <?= $icono ?>"></i> <?= $estado ?>
                    </div>
                    
                    <div class="ped-summary">
                        <div class="summary-item">
                            <i class="fa-solid fa-box"></i> <b><?= $ped['total_items'] ?></b> Productos
                        </div>
                        <div class="summary-item">
                            <i class="fa-solid fa-user-circle"></i> <?= explode(' ', $ped['usu_nombres'])[0] ?>
                        </div>
                    </div>
                </div>

                <div class="card-actions">
                    <?php if ($estado === 'EN CAMINO'): ?>
                        <button class="btn-action primary pulse-animation" onclick="abrirRecepcion(<?= $ped['ped_id'] ?>, '<?= $ped['ped_codigo'] ?>')">
                            <i class="fa-solid fa-box-open"></i> Confirmar Llegada
                        </button>
                    <?php elseif ($estado === 'PENDIENTE'): ?>
                        <button class="btn-action secondary" onclick="verDetalle(<?= $ped['ped_id'] ?>, '<?= $ped['ped_codigo'] ?>')">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button class="btn-action danger" onclick="preguntarCancelar(<?= $ped['ped_id'] ?>)">
                            <i class="fa-solid fa-trash-can"></i> Cancelar
                        </button>
                    <?php else: ?>
                        <button class="btn-action secondary full-width" onclick="verDetalle(<?= $ped['ped_id'] ?>, '<?= $ped['ped_codigo'] ?>')">
                            <i class="fa-solid fa-eye"></i> Ver Detalle
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalRecepcion">
    <div class="modal-box modal-lg">
        <div class="modal-header-custom">
            <div class="modal-info">
                <h3><i class="fa-solid fa-clipboard-check"></i> Revisión de Entrega</h3>
                <span class="tag-total" id="lblCodRecepcion">...</span>
            </div>
            <button class="btn-close-clean" onclick="cerrarModalPedido('modalRecepcion')"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-scroll-area">
            <div class="alert-box info">
                <i class="fa-solid fa-circle-info"></i>
                <p>Verifica físicamente que los productos coincidan con lo enviado.</p>
            </div>
            <div class="checklist-container" id="cuerpoRecepcion">
                </div>
        </div>

        <div class="modal-footer-custom">
            <button class="btn-modal btn-cancel" onclick="cerrarModalPedido('modalRecepcion')">Cancelar</button>
            <button class="btn-modal btn-confirm" onclick="confirmarRecepcionReal()">
                <i class="fa-solid fa-check-double"></i> Ingresar Stock
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box modal-lg">
        <div class="modal-header-custom">
            <div class="modal-info">
                <h3>Detalle de Solicitud</h3>
                <span class="tag-total" id="lblCodigoDetalle">...</span>
            </div>
            <button class="btn-close-clean" onclick="cerrarModalPedido('modalDetalle')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div class="modal-scroll-area">
            <div class="checklist-container" id="cuerpoDetalle"></div>
        </div>
        
        <div class="modal-footer-custom">
            <button class="btn-modal btn-cancel width-100" onclick="cerrarModalPedido('modalDetalle')">Cerrar</button>
        </div>
    </div>
</div>

<style>
    /* --- TABS PÍLDORA --- */
    .pills-container { display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding: 5px 5px 10px 5px; }
    .filter-pill {
        background: white; border: 1px solid #dfe6e9; color: #636e72;
        padding: 8px 25px; border-radius: 50px; font-weight: 600; font-size: 0.95rem;
        cursor: pointer; transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1); outline: none; white-space: nowrap;
        display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .filter-pill:hover { background: #fff; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: var(--color-primario); color: var(--color-primario); }
    .filter-pill.active {
        background: var(--color-primario); color: white; border-color: var(--color-primario);
        box-shadow: 0 4px 10px rgba(253, 121, 168, 0.4); transform: translateY(0);
    }

    /* --- BUSCADOR --- */
    .search-floating-bar {
        background: white; padding: 12px 20px; border-radius: 50px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px;
        display: flex; align-items: center; gap: 12px; border: 1px solid #f0f0f0;
        max-width: 600px; position: relative;
    }
    .search-floating-bar input { border: none; outline: none; width: 100%; font-size: 1rem; color: #2d3436; }
    .clear-icon { cursor: pointer; color: #b2bec3; font-size: 1.1rem; }

    /* --- GRID --- */
    .grid-pedidos { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

    /* --- TARJETA --- */
    .pedido-card {
        background: white; border-radius: 16px; overflow: hidden;
        border: 1px solid #f0f0f0; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        display: flex; flex-direction: column; transition: transform 0.2s;
    }
    .pedido-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.05); }

    .card-header-row { padding: 15px; border-bottom: 1px solid #f9f9f9; display: flex; justify-content: space-between; align-items: center; }
    .ped-id { font-family: monospace; font-size: 1.1rem; font-weight: 700; color: #2d3436; }
    .ped-date { font-size: 0.85rem; color: #b2bec3; font-weight: 500; }

    .card-body-row { padding: 20px 15px; flex-grow: 1; }
    
    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
        margin-bottom: 15px;
    }
    .status-pill.is-pending { background: #fff8e1; color: #f1c40f; border: 1px solid #ffeeba; }
    .status-pill.is-shipping { background: #e3f2fd; color: #0984e3; border: 1px solid #b3e5fc; }
    .status-pill.is-received { background: #e8f5e9; color: #00b894; border: 1px solid #c8e6c9; }
    .status-pill.is-cancelled { background: #ffecec; color: #d63031; border: 1px solid #ffcccc; }

    .ped-summary { display: flex; flex-direction: column; gap: 5px; font-size: 0.9rem; color: #636e72; }
    .summary-item i { width: 20px; color: #b2bec3; }

    .card-actions { padding: 12px 15px; background: #fff; border-top: 1px solid #f0f0f0; display: flex; gap: 10px; }
    .btn-action { padding: 10px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; font-size: 0.9rem; text-decoration: none; }
    .btn-action.primary { background: #0984e3; color: white; flex: 1; }
    .btn-action.primary:hover { background: #00cec9; }
    .btn-action.secondary { background: #f1f2f6; color: #2d3436; flex: 1; }
    .btn-action.secondary:hover { background: #dfe6e9; }
    .btn-action.danger { background: #ffecec; color: #d63031; }
    .btn-action.danger:hover { background: #ff7675; color: white; }
    .btn-action.full-width { width: 100%; }
    
    .pulse-animation { animation: pulseBtn 2s infinite; }
    @keyframes pulseBtn { 0% { box-shadow: 0 0 0 0 rgba(9, 132, 227, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(9, 132, 227, 0); } 100% { box-shadow: 0 0 0 0 rgba(9, 132, 227, 0); } }

    /* --- MODALES PREMIUM --- */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(3px); opacity: 0; transition: opacity 0.3s; }
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-lg { max-width: 600px; width: 90%; }
    .modal-box { background: white; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; max-height: 85vh; box-shadow: 0 20px 50px rgba(0,0,0,0.3); transform: translateY(20px); transition: transform 0.3s; }
    .modal-overlay.active .modal-box { transform: translateY(0); }

    .modal-header-custom { padding: 20px 25px; background: white; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-info h3 { margin: 0; font-size: 1.2rem; color: #2d3436; font-weight: 700; }
    .tag-total { background: #f1f2f6; color: #636e72; padding: 3px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; display: inline-block; margin-top: 5px; }
    
    .btn-close-clean { background: #f8f9fa; border: none; width: 35px; height: 35px; border-radius: 50%; color: #636e72; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; font-size: 1rem; }
    .btn-close-clean:hover { background: #ff7675; color: white; }

    .modal-scroll-area { flex: 1; overflow-y: auto; background: #fbfbfb; padding: 25px; }
    
    /* Checklist */
    .checklist-container { display: flex; flex-direction: column; gap: 12px; }
    .check-item { display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #eee; border-radius: 10px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    
    .check-thumb { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f9f9f9; border: 1px solid #eee; margin-right: 15px; }
    .no-thumb { width: 50px; height: 50px; border-radius: 8px; background: #f1f2f6; display: flex; align-items: center; justify-content: center; color: #ccc; margin-right: 15px; font-size: 1.2rem; }

    .check-info b { display: block; color: #2d3436; font-size: 1rem; margin-bottom: 2px; }
    .check-info small { color: #b2bec3; font-weight: 500; }
    
    .check-stats { text-align: right; }
    .stat-req { font-size: 0.85rem; color: #b2bec3; }
    .stat-sent { font-size: 1.1rem; color: #0984e3; font-weight: 800; }
    .stat-alert { font-size: 0.75rem; color: #d63031; background: #fff0f0; padding: 4px 8px; border-radius: 6px; margin-top: 5px; display: inline-block; font-weight: 600; }

    .modal-footer-custom { padding: 20px 25px; background: white; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 12px; }
    .btn-modal { padding: 12px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; transition: 0.2s; }
    .btn-cancel { background: #f1f2f6; color: #636e72; } .btn-cancel:hover { background: #e2e6ea; color: #2d3436; }
    .btn-confirm { background: #00b894; color: white; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(0, 184, 148, 0.3); }
    .btn-confirm:hover { background: #00a884; transform: translateY(-1px); }
    .width-100 { width: 100%; }
    
    .alert-box { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 12px; font-size: 0.9rem; line-height: 1.5; align-items: flex-start; }
    .alert-box.info { background: #e3f2fd; color: #0984e3; border: 1px solid #bbdefb; }
    .alert-box i { font-size: 1.2rem; margin-top: 2px; }

    /* Empty State */
    .empty-state { text-align: center; color: #b2bec3; padding: 4rem; grid-column: 1/-1; }
    .icon-circle { width: 80px; height: 80px; background: #f8f9fa; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .icon-circle i { font-size: 2.5rem; color: #dfe6e9; }
</style>

<script>
    let tabActual = 'pendiente'; 
    let pedidoRecepcionId = null;

    // --- 1. BUSCADOR INTELIGENTE ---
    // Si busco algo y no está en la pestaña actual, me cambia a la correcta
    function aplicarFiltros() {
        const txt = document.getElementById('globalSearch').value.toLowerCase().trim();
        const btnClear = document.getElementById('btnClear');
        const cards = document.querySelectorAll('.filter-item');
        
        btnClear.style.display = txt === '' ? 'none' : 'block';

        let encontradosEnTabActual = 0;
        let encontradosEnOtros = null; // Guardará el ID del tab donde encontró algo

        // Primera pasada: Buscar coincidencias
        cards.forEach(card => {
            const cat = card.dataset.category;
            const data = card.dataset.search;
            
            if (data.includes(txt)) {
                if (cat === tabActual) encontradosEnTabActual++;
                else if (!encontradosEnOtros) encontradosEnOtros = cat; // Guardar el primero que encuentre
            }
        });

        // LÓGICA SMART SWITCH
        // Si hay texto, NO hay resultados aquí, pero SÍ en otro lado -> Cambiar tab
        if (txt !== '' && encontradosEnTabActual === 0 && encontradosEnOtros) {
            cambiarTabVisual(encontradosEnOtros);
        }

        // Segunda pasada: Mostrar/Ocultar basado en el tab (que pudo haber cambiado)
        cards.forEach(card => {
            const cat = card.dataset.category;
            const data = card.dataset.search;
            const matchTab = (cat === tabActual);
            const matchTxt = (txt === '' || data.includes(txt));

            if (matchTab && matchTxt) card.style.display = 'flex';
            else card.style.display = 'none';
        });
    }

    function filtrarTab(categoria) {
        cambiarTabVisual(categoria);
        document.getElementById('globalSearch').value = ''; // Limpiar búsqueda al cambiar manual
        aplicarFiltros();
    }

    function cambiarTabVisual(categoria) {
        tabActual = categoria;
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        document.getElementById('btn-' + categoria).classList.add('active');
    }

    function limpiarBusqueda() {
        document.getElementById('globalSearch').value = '';
        aplicarFiltros();
    }

    // --- 2. MODALES ---
    function cerrarModalPedido(id) {
        document.getElementById(id).classList.remove('active');
    }

    // --- 3. RECEPCIÓN ---
    function abrirRecepcion(pedId, codigo) {
        pedidoRecepcionId = pedId;
        document.getElementById('lblCodRecepcion').innerText = codigo;
        document.getElementById('modalRecepcion').classList.add('active');
        document.getElementById('cuerpoRecepcion').innerHTML = '<div style="text-align:center; padding:20px; color:#b2bec3;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>';

        cargarDetalle(pedId, (items) => {
            let html = '';
            items.forEach(item => {
                let pedido = parseFloat(item.det_cant_solicitada);
                let enviado = parseFloat(item.det_cantidad_despachada) || 0;
                
                let imgHtml = item.pro_foto ? `<img src="${item.pro_foto}" class="check-thumb">` : `<div class="no-thumb"><i class="fa-solid fa-box"></i></div>`;
                
                let alerta = '';
                if (enviado < pedido) {
                    let obs = item.det_observacion || 'Sin justificación';
                    alerta = `<div class="stat-alert"><i class="fa-solid fa-triangle-exclamation"></i> Faltante: ${obs}</div>`;
                }

                html += `
                <div class="check-item">
                    <div style="display:flex; align-items:center;">
                        ${imgHtml}
                        <div class="check-info">
                            <b>${item.pro_nombre}</b>
                            <small>${item.pro_unidad}</small>
                        </div>
                    </div>
                    <div class="check-stats">
                        <div class="stat-req">Pediste: ${pedido}</div>
                        <div class="stat-sent">Recibes: ${enviado}</div>
                        ${alerta}
                    </div>
                </div>`;
            });
            document.getElementById('cuerpoRecepcion').innerHTML = html;
        });
    }

    // --- 3. RECEPCIÓN (CONFIRMACIÓN CON MODAL PROPIO) ---
    
    function confirmarRecepcionReal() {
        // Ocultamos el modal de revisión temporalmente
        document.getElementById('modalRecepcion').classList.remove('active');

        // Configuramos tu modal de confirmación (#modalConfirm)
        const modal = document.getElementById('modalConfirm');
        document.getElementById('modalTitle').innerText = "Ingresar Stock";
        document.getElementById('modalText').innerText = "¿Confirmas que has contado los productos y coinciden?";
        
        const btnConfirm = document.getElementById('btnModalConfirmar');
        // Clonar botón para limpiar eventos previos
        let newBtn = btnConfirm.cloneNode(true);
        btnConfirm.parentNode.replaceChild(newBtn, btnConfirm);
        
        newBtn.innerText = "Sí, Confirmar";
        newBtn.style.backgroundColor = "#00b894"; // Verde
        
        newBtn.onclick = function() {
            document.getElementById('modalConfirm').classList.remove('active');
            enviarRecepcionBackend(); // Llamamos al envío real
        };

        // Botón cancelar: regresa al modal de revisión
        document.getElementById('btnModalCancelar').onclick = function() {
            document.getElementById('modalConfirm').classList.remove('active');
            document.getElementById('modalRecepcion').classList.add('active');
        };

        modal.classList.add('active');
    }

    function enviarRecepcionBackend() {
        const url = "index.php?c=inventario&m=confirmar_recepcion" + (location.search.includes('token') ? '&token='+new URLSearchParams(location.search).get('token') : '');
        const fd = new FormData(); 
        fd.append('ped_id', pedidoRecepcionId);

        fetch(url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                // ÉXITO: Usamos el mismo modal pero en modo "Mensaje"
                mostrarMensajeExito("¡Stock Actualizado!", "Los productos han sido sumados a tu inventario.");
            } else {
                mostrarMensajeError("Error", data.message);
            }
        })
        .catch(err => mostrarMensajeError("Error de Conexión", "Inténtalo de nuevo."));
    }

    // --- FUNCIONES AUXILIARES PARA REUTILIZAR TU MODAL ---
    function mostrarMensajeExito(titulo, texto) {
        const modal = document.getElementById('modalConfirm');
        document.getElementById('modalTitle').innerText = titulo;
        document.getElementById('modalText').innerText = texto;
        
        const btn = document.getElementById('btnModalConfirmar');
        btn.innerText = "Aceptar";
        btn.onclick = () => window.location.reload(); // Recargar al aceptar
        
        document.getElementById('btnModalCancelar').style.display = 'none'; // Ocultar cancelar
        modal.classList.add('active');
    }

    function mostrarMensajeError(titulo, texto) {
        const modal = document.getElementById('modalConfirm');
        document.getElementById('modalTitle').innerText = titulo;
        document.getElementById('modalText').innerText = texto;
        
        const btn = document.getElementById('btnModalConfirmar');
        btn.innerText = "Entendido";
        btn.style.backgroundColor = "#d63031";
        btn.onclick = () => {
            modal.classList.remove('active');
            document.getElementById('btnModalCancelar').style.display = 'inline-block'; // Restaurar botón
            document.getElementById('modalRecepcion').classList.add('active'); // Volver a abrir
        };
        
        document.getElementById('btnModalCancelar').style.display = 'none';
        modal.classList.add('active');
    }

    // --- 4. DETALLE ---
    function verDetalle(pedId, codigo) {
        document.getElementById('lblCodigoDetalle').innerText = codigo;
        document.getElementById('modalDetalle').classList.add('active');
        document.getElementById('cuerpoDetalle').innerHTML = '<div style="text-align:center; padding:20px;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
        
        cargarDetalle(pedId, (items) => {
            let html = '';
            items.forEach(item => {
                let imgHtml = item.pro_foto ? `<img src="${item.pro_foto}" class="check-thumb">` : `<div class="no-thumb"><i class="fa-solid fa-box"></i></div>`;
                let enviado = item.det_cantidad_despachada ? parseFloat(item.det_cantidad_despachada) : '-';
                
                html += `
                <div class="check-item">
                    <div style="display:flex; align-items:center;">
                        ${imgHtml}
                        <div class="check-info"><b>${item.pro_nombre}</b><small>${item.pro_unidad}</small></div>
                    </div>
                    <div class="check-stats">
                        <div class="stat-req">Pedido</div>
                        <div class="stat-sent" style="color:#2d3436">${item.det_cant_solicitada}</div>
                        ${enviado !== '-' ? `<small style='color:#0984e3'>Env: ${enviado}</small>` : ''}
                    </div>
                </div>`;
            });
            document.getElementById('cuerpoDetalle').innerHTML = html;
        });
    }

    // --- 5. CANCELAR ---
    function preguntarCancelar(pedId) {
        if(!confirm("¿Cancelar solicitud?")) return;
        const url = "index.php?c=inventario&m=cancelar_pedido" + (location.search.includes('token') ? '&token='+new URLSearchParams(location.search).get('token') : '');
        const fd = new FormData(); fd.append('ped_id', pedId);
        fetch(url, { method: 'POST', body: fd }).then(r => r.json()).then(d => {
            if(d.success) window.location.reload(); else alert(d.message);
        });
    }

    // --- AJAX HELPER ---
    function cargarDetalle(id, callback) {
        const url = "index.php?c=inventario&m=ver_detalle_ajax" + (location.search.includes('token') ? '&token='+new URLSearchParams(location.search).get('token') : '');
        const fd = new FormData(); fd.append('ped_id', id);
        fetch(url, { method:'POST', body:fd }).then(r=>r.json()).then(d => { if(d.success) callback(d.datos); });
    }

    document.addEventListener("DOMContentLoaded", () => aplicarFiltros());
</script>