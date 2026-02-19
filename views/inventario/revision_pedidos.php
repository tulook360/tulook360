<div class="section-header">
    <div>
        <h1 class="section-title kalam">Gestión de Pedidos</h1>
        <p class="section-subtitle">Administra las solicitudes de tus sucursales.</p>
    </div>
</div>

<div class="pills-container">
    <button class="filter-pill active" onclick="filtrarTab('pendiente', this)">
        <i class="fa-solid fa-bell"></i> Pendientes
        <span class="count-badge" id="count-pen">0</span>
    </button>
    <button class="filter-pill" onclick="filtrarTab('encamino', this)">
        <i class="fa-solid fa-truck-fast"></i> En Camino
    </button>
    <button class="filter-pill" onclick="filtrarTab('procesado', this)">
        <i class="fa-solid fa-check-double"></i> Finalizados
    </button>
</div>

<div class="orders-list">
    <?php if (empty($pedidos)): ?>
        <div class="empty-state">
            <div class="icon-circle"><i class="fa-solid fa-clipboard-check"></i></div>
            <h3>Todo al día</h3>
            <p>No hay pedidos pendientes de atención.</p>
        </div>
    <?php else: ?>
        <?php 
            $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            $pendientesCount = 0;
        ?>
        <?php foreach ($pedidos as $ped): ?>
            <?php 
                $estado = strtoupper($ped['ped_estado']);
                $filtro = 'procesado';
                $claseEstado = 'is-done';
                $icono = 'fa-check';

                if ($estado === 'PENDIENTE') {
                    $filtro = 'pendiente';
                    $claseEstado = 'is-pending';
                    $icono = 'fa-clock';
                    $pendientesCount++;
                } elseif ($estado === 'EN CAMINO') {
                    $filtro = 'encamino';
                    $claseEstado = 'is-shipping';
                    $icono = 'fa-truck';
                } elseif ($estado === 'CANCELADO' || $estado === 'RECHAZADO') {
                    $filtro = 'procesado';
                    $claseEstado = 'is-cancelled';
                    $icono = 'fa-ban';
                } elseif ($estado === 'RECIBIDO') {
                    $filtro = 'procesado';
                    $claseEstado = 'is-received';
                    $icono = 'fa-check-circle';
                }

                $ts = strtotime($ped['ped_fecha_solicitud']);
                $fecha = date('d', $ts) . ' ' . $meses[date('n', $ts)] . ' ' . date('Y', $ts);
                $codigo = str_replace('PED-', '', $ped['ped_codigo']);
            ?>

            <div class="order-card filter-item" data-category="<?= $filtro ?>">
                <div class="order-left">
                    <div class="sucursal-icon">
                        <?= strtoupper(substr($ped['suc_nombre'], 0, 2)) ?>
                    </div>
                    <div class="order-info">
                        <h4><?= htmlspecialchars($ped['suc_nombre']) ?></h4>
                        <div class="order-meta">
                            <span><i class="fa-solid fa-user"></i> <?= explode(' ', $ped['usu_nombres'])[0] ?></span>
                            <span>&bull;</span>
                            <span><i class="fa-solid fa-calendar"></i> <?= $fecha ?></span>
                        </div>
                    </div>
                </div>

                <div class="order-center">
                    <div class="status-badge <?= $claseEstado ?>">
                        <i class="fa-solid <?= $icono ?>"></i> <?= $estado ?>
                    </div>
                    <div class="items-count">
                        <b><?= $ped['total_items'] ?></b> Productos
                    </div>
                </div>

                <div class="order-right">
                    <?php if($estado === 'PENDIENTE'): ?>
                        <button class="btn-action primary" onclick="abrirDespacho(<?= $ped['ped_id'] ?>, '<?= htmlspecialchars($ped['suc_nombre'], ENT_QUOTES) ?>')">
                            <i class="fa-solid fa-box-open"></i> Despachar
                        </button>
                    <?php else: ?>
                        <button class="btn-action secondary" onclick="verDetalleAdmin(<?= $ped['ped_id'] ?>, '<?= $ped['ped_codigo'] ?>')">
                            <i class="fa-solid fa-eye"></i> Detalle
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <script>document.getElementById('count-pen').innerText = '<?= $pendientesCount ?>';</script>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalDespacho">
    <div class="modal-box modal-xl animate-pop">
        <div class="modal-header-premium">
            <div class="header-icon-box"><i class="fa-solid fa-truck-ramp-box"></i></div>
            <div class="header-titles">
                <h3>Despachar Pedido</h3>
                <span class="subtitle-sucursal">Destino: <b id="lblSucursalDespacho">...</b></span>
            </div>
            <button class="btn-close-clean" onclick="cerrarModalAdmin('modalDespacho')"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-scroll-area">
            <div class="alert-box warning-soft">
                <div class="alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="alert-content">
                    <strong>Verifica Stock Físico</strong>
                    <p>Si cambias la cantidad a enviar, deberás justificar el motivo.</p>
                </div>
            </div>

            <div class="table-container">
                <table class="dispatch-table">
                    <thead>
                        <tr>
                            <th width="45%">Producto</th>
                            <th width="15%" class="text-center">Pidieron</th>
                            <th width="15%" class="text-center">Tienes</th>
                            <th width="25%" class="text-center">Enviar</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoDespacho">
                        </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer-premium">
            <button class="btn-ghost" onclick="cerrarModalAdmin('modalDespacho')">Cancelar</button>
            <button class="btn-primary-shadow" onclick="confirmarEnvio()">
                Confirmar Despacho <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box modal-lg animate-pop">
        <div class="modal-header-premium">
            <div class="header-icon-box" style="background: #e3f2fd; color: #0984e3;"><i class="fa-solid fa-file-invoice"></i></div>
            <div class="header-titles">
                <h3>Detalle de Solicitud</h3>
                <span class="subtitle-sucursal">Ref: <b id="lblCodDetalle">...</b></span>
            </div>
            <button class="btn-close-clean" onclick="cerrarModalAdmin('modalDetalle')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div class="modal-scroll-area">
            <div class="checklist-container" id="cuerpoDetalle"></div>
        </div>
        
        <div class="modal-footer-premium center">
            <button class="btn-ghost" onclick="cerrarModalAdmin('modalDetalle')">Cerrar Ventana</button>
        </div>
    </div>
</div>

<style>
    /* ANIMACIONES */
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.95) translateY(10px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
    .animate-pop { animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); }

    /* PILLS (TABS) */
    .pills-container { display: flex; gap: 10px; margin-bottom: 25px; overflow-x: auto; padding: 5px; }
    .filter-pill { background: white; border: 1px solid #dfe6e9; color: #636e72; padding: 10px 20px; border-radius: 50px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .filter-pill.active { background: var(--color-primario); color: white; border-color: var(--color-primario); box-shadow: 0 4px 10px rgba(253, 121, 168, 0.4); }
    .count-badge { background: #ff7675; color: white; font-size: 0.75rem; padding: 2px 6px; border-radius: 10px; margin-left: 5px; }

    /* LISTADO DE PEDIDOS */
    .orders-list { display: flex; flex-direction: column; gap: 15px; }
    .order-card { background: white; border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #f0f0f0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .order-left { display: flex; align-items: center; gap: 15px; flex: 2; }
    .sucursal-icon { width: 50px; height: 50px; background: #f1f2f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #b2bec3; font-size: 1.2rem; }
    .order-info h4 { margin: 0 0 5px; color: #2d3436; font-size: 1.1rem; }
    .order-meta { font-size: 0.85rem; color: #b2bec3; display: flex; gap: 10px; }
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; display: inline-flex; align-items: center; gap: 6px; }
    .is-pending { background: #fff8e1; color: #f1c40f; } .is-shipping { background: #e3f2fd; color: #0984e3; } .is-received { background: #e8f5e9; color: #00b894; } .is-cancelled { background: #ffecec; color: #d63031; }
    .order-center { flex: 1; display: flex; flex-direction: column; align-items: center; }
    .order-right { flex: 1; display: flex; justify-content: flex-end; }
    .btn-action { padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; }
    .btn-action.primary { background: #0984e3; color: white; box-shadow: 0 4px 10px rgba(9, 132, 227, 0.3); }
    .btn-action.secondary { background: #f1f2f6; color: #2d3436; }

    /* --- MODALES PREMIUM --- */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.6); z-index: 9999; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .modal-overlay.active { display: flex; }
    .modal-xl { width: 90%; max-width: 850px; } .modal-lg { width: 90%; max-width: 600px; }
    .modal-box { background: white; border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; max-height: 85vh; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .modal-header-premium { padding: 20px 30px; background: #fff; display: flex; align-items: center; gap: 15px; border-bottom: 1px solid #f1f5f9; }
    .header-icon-box { width: 48px; height: 48px; border-radius: 12px; background: #fff1f2; color: var(--color-primario); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .header-titles h3 { margin: 0; font-size: 1.25rem; font-weight: 800; color: #1e293b; letter-spacing: -0.5px; }
    .subtitle-sucursal { font-size: 0.9rem; color: #64748b; }
    .btn-close-clean { margin-left: auto; background: transparent; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; transition: 0.2s; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .btn-close-clean:hover { background: #f1f5f9; color: #ef4444; }
    .modal-footer-premium { padding: 20px 30px; border-top: 1px solid #f1f5f9; background: #fff; display: flex; justify-content: flex-end; gap: 12px; }
    .modal-scroll-area { flex: 1; overflow-y: auto; padding: 30px; background: #ffffff; }
    .btn-ghost { background: transparent; border: none; color: #64748b; font-weight: 600; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
    .btn-ghost:hover { background: #f1f5f9; color: #334155; }
    .btn-primary-shadow { background: var(--color-primario); color: white; border: none; padding: 10px 25px; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 12px rgba(253, 121, 168, 0.4); cursor: pointer; display: flex; align-items: center; gap: 8px; }
    
    .alert-box { display: flex; gap: 15px; padding: 16px; border-radius: 12px; margin-bottom: 25px; align-items: flex-start; }
    .warning-soft { background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; }
    .alert-icon { font-size: 1.2rem; margin-top: 2px; }
    .alert-content p { margin: 0; font-size: 0.9rem; line-height: 1.4; opacity: 0.9; }

    /* TABLA */
    .table-container { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
    .dispatch-table { width: 100%; border-collapse: collapse; }
    .dispatch-table th { background: #f8fafc; color: #64748b; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    .dispatch-table th.text-center { text-align: center; }
    .dispatch-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .prod-cell { display: flex; align-items: center; gap: 15px; }
    .prod-thumb { width: 45px; height: 45px; border-radius: 8px; object-fit: cover; background: #f1f5f9; border: 1px solid #e2e8f0; }
    .prod-thumb-placeholder { width: 45px; height: 45px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 1.2rem; }
    .prod-name { font-weight: 600; color: #334155; font-size: 0.95rem; display:block;}
    .prod-unit { font-size: 0.8rem; color: #94a3b8; }
    .badge-stock { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; min-width: 40px; text-align: center; }
    .stock-ok { background: #dcfce7; color: #166534; } .stock-low { background: #fee2e2; color: #991b1b; }
    .input-dispatch { width: 70px; text-align: center; border: 2px solid #e2e8f0; border-radius: 8px; padding: 8px; font-weight: 700; color: #334155; font-size: 1rem; outline: none; }
    .input-dispatch.changed { border-color: #f59e0b; background: #fffbeb; }
    
    .row-justification td { background: #fffbeb; padding: 10px 15px !important; border-top: 1px dashed #fcd34d; }
    .just-box { display: flex; align-items: center; gap: 10px; width: 100%; }
    .input-just { width: 100%; background: transparent; border: none; font-size: 0.9rem; color: #92400e; outline: none; }

    .empty-state { text-align: center; color: #b2bec3; padding: 4rem; }
    .icon-circle { width: 80px; height: 80px; background: #f8f9fa; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .icon-circle i { font-size: 2.5rem; color: #dfe6e9; }

    /* ======================================================== */
    /* 🚀 AQUÍ ESTÁ LA MAGIA PARA CELULARES (RESPONSIVE TABLE) */
    /* ======================================================== */
    @media (max-width: 768px) {
        /* Ajuste de tarjetas de pedidos */
        .order-card { flex-direction: column; align-items: flex-start; gap: 15px; }
        .order-center, .order-right { width: 100%; justify-content: flex-start; }
        .order-right button { width: 100%; }

        /* Ajuste del Modal */
        .modal-box { width: 95%; max-height: 95vh; height: 100%; border-radius: 12px; }
        .modal-header-premium { padding: 15px; }
        .modal-scroll-area { padding: 15px; }

        /* --- TRANSFORMACIÓN DE TABLA A TARJETAS --- */
        .dispatch-table, .dispatch-table tbody, .dispatch-table tr, .dispatch-table td {
            display: block; width: 100%;
        }
        
        .dispatch-table thead { display: none; } /* Ocultar cabecera */

        .dispatch-table tr {
            margin-bottom: 15px;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            display: flex; flex-wrap: wrap; /* Permitir que los datos fluyan */
        }

        /* Fila 1: Producto (Nombre y Foto) */
        .dispatch-table td:first-child {
            width: 100%;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 10px; margin-bottom: 10px;
        }

        /* Fila 2: Datos numéricos (3 Columnas) */
        .dispatch-table td:nth-child(2),
        .dispatch-table td:nth-child(3),
        .dispatch-table td:nth-child(4) {
            width: 33.33%;
            border: none; padding: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        /* Etiquetas automáticas (Ya que ocultamos el thead) */
        .dispatch-table td:nth-child(2)::before { content: 'PIDIERON'; font-size: 0.65rem; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
        .dispatch-table td:nth-child(3)::before { content: 'TIENES'; font-size: 0.65rem; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
        .dispatch-table td:nth-child(4)::before { content: 'ENVIAR'; font-size: 0.65rem; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }

        /* Input más cómodo */
        .input-dispatch { width: 100%; max-width: 60px; height: 35px; }
        
        /* Arreglo para la fila de Justificación */
        .row-justification[style*="display: table-row"] { display: flex !important; }
        .row-justification td { width: 100% !important; border:none; padding:10px !important; background: #fffbeb; border-radius: 8px; }
    }
</style>

<script>
    let pedidoActualId = null;

    // --- FILTROS TABS ---
    function filtrarTab(categoria, btn) {
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.filter-item').forEach(item => {
            item.style.display = (item.dataset.category === categoria) ? 'flex' : 'none';
        });
    }

    // --- MODALES ---
    function cerrarModalAdmin(id) { document.getElementById(id).classList.remove('active'); }

    // --- DESPACHO LÓGICA ---
    function abrirDespacho(pedId, nombreSucursal) {
        pedidoActualId = pedId;
        document.getElementById('lblSucursalDespacho').innerText = nombreSucursal;
        document.getElementById('modalDespacho').classList.add('active');
        document.getElementById('cuerpoDespacho').innerHTML = '<tr><td colspan="4" class="text-center" style="padding:30px; color:#b2bec3;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando inventario...</td></tr>';

        const url = "index.php?c=inventario&m=cargar_despacho" + (location.search.includes('token') ? '&token='+new URLSearchParams(location.search).get('token') : '');
        const fd = new FormData(); fd.append('ped_id', pedId);

        fetch(url, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if(data.success) dibujarTablaDespacho(data.items);
            else alert("Error: " + data.message);
        });
    }

    function dibujarTablaDespacho(items) {
        let html = '';
        items.forEach(item => {
            let stock = parseFloat(item.stock_bodega) || 0;
            let solicitado = parseFloat(item.det_cant_solicitada) || 0;
            let sugerido = Math.min(solicitado, stock); 
            let claseStock = (stock < solicitado) ? 'stock-low' : 'stock-ok';

            let imgHtml = item.pro_foto ? `<img src="${item.pro_foto}" class="prod-thumb">` : `<div class="prod-thumb-placeholder"><i class="fa-solid fa-box"></i></div>`;

            html += `
            <tr style="border-bottom:1px solid #f9f9f9;">
                <td style="padding:15px 10px;">
                    <div class="prod-cell">
                        ${imgHtml}
                        <div class="prod-info">
                            <span class="prod-name">${item.pro_nombre}</span>
                            <span class="prod-unit">${item.pro_unidad}</span>
                        </div>
                    </div>
                </td>
                <td class="text-center" style="color:#64748b; font-weight:600;">${solicitado}</td>
                <td class="text-center"><span class="badge-stock ${claseStock}">${stock}</span></td>
                <td class="text-center">
                    <input type="number" class="input-dispatch" 
                           id="envio_${item.det_id}" 
                           data-id="${item.det_id}"
                           data-solicitado="${solicitado}"
                           data-stock="${stock}"
                           value="${sugerido}" min="0"
                           oninput="validarCantidad(this)">
                </td>
            </tr>
            <tr id="row_just_${item.det_id}" class="row-justification" style="display:none;">
                <td colspan="4">
                    <div class="just-box">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i>
                        <input type="text" class="input-just" 
                               id="just_${item.det_id}" 
                               placeholder="¿Por qué envías diferente?">
                    </div>
                </td>
            </tr>`;
        });
        document.getElementById('cuerpoDespacho').innerHTML = html;
        setTimeout(() => document.querySelectorAll('.input-dispatch').forEach(i => validarCantidad(i)), 100);
    }

    // --- VALIDACIÓN CANTIDAD ---
    function validarCantidad(input) {
        let id = input.dataset.id;
        let valor = parseFloat(input.value) || 0;
        let solicitado = parseFloat(input.dataset.solicitado);
        let stock = parseFloat(input.dataset.stock);
        
        const rowJust = document.getElementById('row_just_' + id);
        const inputJust = document.getElementById('just_' + id);

        if (valor > stock) { input.value = stock; valor = stock; }

        if (valor !== solicitado) {
            input.classList.add('changed');
            // Usamos remove/add class para evitar conflictos con el CSS responsive
            rowJust.style.display = 'table-row'; 
            
            if (valor < solicitado && valor === stock && inputJust.value === '') {
                inputJust.value = "Stock insuficiente en bodega central.";
            }
        } else {
            input.classList.remove('changed');
            rowJust.style.display = 'none';
            inputJust.value = '';
        }
    }

    // --- CONFIRMAR ENVÍO ---
    function confirmarEnvio() {
        document.getElementById('modalDespacho').classList.remove('active');
        let items = [];
        document.querySelectorAll('.input-dispatch').forEach(inp => {
            let id = inp.dataset.id;
            items.push({
                det_id: id,
                cantidad: parseFloat(inp.value),
                observacion: document.getElementById('just_' + id).value
            });
        });

        const modal = document.getElementById('modalConfirm');
        document.getElementById('modalTitle').innerText = "Confirmar Despacho";
        document.getElementById('modalText').innerText = "Se descontará del stock y se enviará.";
        const btn = document.getElementById('btnModalConfirmar');
        let newBtn = btn.cloneNode(true); btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.innerText = "Sí, Despachar";
        newBtn.style.backgroundColor = "#0984e3";
        newBtn.onclick = () => {
            document.getElementById('modalConfirm').classList.remove('active');
            enviarDatosBackend(items);
        };
        
        document.getElementById('btnModalCancelar').onclick = () => {
            document.getElementById('modalConfirm').classList.remove('active');
            document.getElementById('modalDespacho').classList.add('active');
        };
        modal.classList.add('active');
    }

    // --- NUEVA FUNCIÓN: ENVIAR DATOS SIN ALERTAS FEAS ---
    function enviarDatosBackend(items) {
        const url = "index.php?c=inventario&m=guardar_despacho" + (location.search.includes('token') ? '&token='+new URLSearchParams(location.search).get('token') : '');
        
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ped_id: pedidoActualId, items: items })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                // EN LUGAR DE ALERT, USAMOS TU MODAL DE ÉXITO
                mostrarModalExito("¡Despacho Exitoso!", "El inventario ha sido actualizado y el pedido está en camino.");
            } else {
                // EN LUGAR DE ALERT, MOSTRAMOS ERROR Y REABRIMOS
                mostrarModalError("Error en el Despacho", data.message);
            }
        })
        .catch(err => {
            mostrarModalError("Error de Conexión", "No se pudo conectar con el servidor.");
        });
    }

    // --- FUNCIONES AUXILIARES PARA REUTILIZAR TU MODAL ---
    
    function mostrarModalExito(titulo, mensaje) {
        const modal = document.getElementById('modalConfirm');
        const h3 = document.getElementById('modalTitle');
        const p = document.getElementById('modalText');
        const btnConfirm = document.getElementById('btnModalConfirmar');
        const btnCancel = document.getElementById('btnModalCancelar');

        // 1. Configurar Textos
        h3.innerText = titulo;
        p.innerText = mensaje;

        // 2. Configurar Botón Único (Aceptar)
        btnConfirm.innerText = "Aceptar";
        btnConfirm.style.backgroundColor = "#00b894"; // Verde Éxito
        btnConfirm.onclick = function() {
            window.location.reload(); // Recargar al dar clic
        };

        // 3. Ocultar botón Cancelar (No es necesario en éxito)
        btnCancel.style.display = 'none';

        // 4. Mostrar
        modal.classList.add('active');
    }

    function mostrarModalError(titulo, mensaje) {
        const modal = document.getElementById('modalConfirm');
        document.getElementById('modalTitle').innerText = titulo;
        document.getElementById('modalText').innerText = mensaje;
        
        const btnConfirm = document.getElementById('btnModalConfirmar');
        const btnCancel = document.getElementById('btnModalCancelar');

        // Configurar para solo cerrar
        btnConfirm.innerText = "Entendido";
        btnConfirm.style.backgroundColor = "#d63031"; // Rojo Error
        btnConfirm.onclick = function() {
            modal.classList.remove('active');
            document.getElementById('modalDespacho').classList.add('active'); // Volver al despacho
        };
        
        btnCancel.style.display = 'none'; // Ocultar cancelar
        modal.classList.add('active');
    }

    // --- DETALLE SIMPLE ---
    function verDetalleAdmin(pedId, codigo) {
        document.getElementById('lblCodDetalle').innerText = codigo;
        document.getElementById('modalDetalle').classList.add('active');
        document.getElementById('cuerpoDetalle').innerHTML = '<div class="text-center p-3">Cargando...</div>';
        
        const url = "index.php?c=inventario&m=cargar_despacho" + (location.search.includes('token') ? '&token='+new URLSearchParams(location.search).get('token') : '');
        const fd = new FormData(); fd.append('ped_id', pedId);

        fetch(url, { method: 'POST', body: fd }).then(r=>r.json()).then(data => {
            let html = '';
            data.items.forEach(item => {
                let imgHtml = item.pro_foto ? `<img src="${item.pro_foto}" class="prod-thumb">` : `<div class="prod-thumb-placeholder"><i class="fa-solid fa-box"></i></div>`;
                html += `
                <div class="check-item" style="padding:15px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:15px;">
                    ${imgHtml}
                    <div style="flex:1;">
                        <div class="prod-name">${item.pro_nombre}</div>
                        <div class="prod-unit">${item.pro_unidad}</div>
                    </div>
                    <div style="font-weight:700; color:#1e293b;">${item.det_cant_solicitada}</div>
                </div>`;
            });
            document.getElementById('cuerpoDetalle').innerHTML = html;
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        const firstTab = document.querySelector('.filter-pill');
        if(firstTab) filtrarTab('pendiente', firstTab);
    });
</script>