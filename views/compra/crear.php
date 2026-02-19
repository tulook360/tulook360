<div class="section-header">
    <div>
        <h1 class="section-title kalam">Registrar Ingreso</h1>
        <p class="section-subtitle">Reponer stock en Bodega Central.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('compra', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-clock-rotate-left"></i> <span class="hide-mobile">Historial</span>
        </a>
    </div>
</div>

<div class="form-container">
    <form action="<?= ruta_accion('compra', 'guardar') ?>" method="POST" enctype="multipart/form-data" id="formCompra">
        
        <input type="hidden" name="detalle_json" id="inputDetalleJson">
        <input type="hidden" name="total_compra" id="inputTotalCompra">

        <div class="compra-grid-top">
            <div class="card-item card-form">
                <div class="form-section-label" style="margin-top:0;">1. Datos del Proveedor</div>
                <div class="form-group">
                    <label class="form-label">Proveedor <span class="required">*</span></label>
                    <input type="text" name="proveedor" class="form-control" placeholder="Ej: Distribuidora Central" required>
                </div>
                <div class="row-2-cols">
                    <div class="form-group">
                        <label class="form-label">Tipo Documento</label>
                        <select name="tipo_doc" id="selectTipoDoc" class="form-control" onchange="verificarEvidencia()">
                            <option value="NOTA_VENTA">Nota de Venta</option>
                            <option value="FACTURA">Factura Fiscal</option>
                            <option value="SIN_SOPORTE" style="color:#d63031; font-weight:bold;">Sin Soporte (Calle)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nro. Documento</label>
                        <input type="text" name="numero_doc" class="form-control" placeholder="001-001...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Observación</label>
                    <textarea name="observacion" class="form-control" rows="1" placeholder="Ej: Pago caja chica..."></textarea>
                </div>
            </div>

            <div class="card-item card-form d-flex-col">
                <div class="form-section-label" style="margin-top:0;">2. Evidencia <small>(Foto)</small></div>
                <label class="evidence-box" id="evidenceWrapper">
                    <input type="file" name="evidencia" id="inputEvidencia" accept="image/*" capture="environment" style="display:none;">
                    <div id="evidencePlaceholder">
                        <i class="fa-solid fa-camera"></i>
                        <p>Tocar para foto</p>
                    </div>
                    <div id="evidencePreview" style="display:none;">
                        <img id="imgPreview" src="" alt="Evidencia">
                        <div class="btn-remove-ev" onclick="borrarEvidencia(event)"><i class="fa-solid fa-trash"></i></div>
                    </div>
                </label>
                <small id="msgEvidencia" class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> ¡Foto obligatoria!</small>
            </div>
        </div>

        <div class="card-item card-form mt-4">
            <div class="form-section-label" style="margin-top:0;">3. Agregar Productos</div>
            <div class="builder-bar">
                
                <div class="form-group grow-2">
                    <label class="form-label">Producto</label>
                    <input type="text" list="listaProds" id="txtBuscarProd" class="form-control" placeholder="Buscar..." onchange="seleccionarProducto()">
                    
                    <datalist id="listaProds">
                        <?php foreach($listaProductos as $p): ?>
                            <option value="<?= htmlspecialchars($p['pro_codigo'] . ' - ' . $p['pro_nombre']) ?>" 
                                    data-id="<?= $p['pro_id'] ?>" 
                                    data-costo="<?= $p['pro_costo_compra'] ?>"
                                    data-unidad="<?= $p['pro_unidad'] ?>"
                                    data-stock="<?= (float)$p['pro_stock'] ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" id="idProdSeleccionado">
                    <input type="hidden" id="unidadProdSeleccionado">
                </div>

                <div class="row-mobile-inputs">
                    <div class="form-group w-small">
                        <label class="form-label">Cant.</label>
                        <input type="number" id="txtCantidad" class="form-control center-text" step="0.01" value="1">
                    </div>

                    <div class="form-group w-medium">
                        <label class="form-label">Costo U.</label>
                        <input type="number" id="txtCosto" class="form-control right-text" step="0.01" placeholder="0.00">
                    </div>
                </div>

                <div class="form-group d-flex-end">
                    <button type="button" class="btn-create btn-add" onclick="agregarAlCarrito()">
                        <i class="fa-solid fa-plus"></i> <span class="hide-mobile">Agregar</span>
                    </button>
                </div>
            </div>
            <div id="infoProdSeleccionado" style="margin-top:8px; font-size:0.85rem; color:#0984e3; font-weight:600; min-height:1.2em; padding-left:5px;"></div>
        </div>

        <div class="cart-container mt-4">
            
            <div id="emptyState" class="empty-state-box">
                <div class="icon-circle">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <h3>Tu ingreso está vacío</h3>
                <p>Usa el buscador de arriba para agregar productos a la lista.</p>
            </div>

            <div id="tableContainer" class="table-responsive show-desktop" style="display:none;">
                <table class="table-cart">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Costo U.</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-center" width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="tablaCuerpo"></tbody>
                </table>
            </div>

            <div id="mobileCardsContainer" class="mobile-cards show-mobile" style="display:none;"></div>

            <div id="footerTotal" class="footer-total" style="display:none;">
                <span class="lbl">TOTAL INGRESO:</span>
                <span class="val">$<span id="lblTotalGeneral">0.00</span></span>
            </div>

        </div>

        <div class="form-footer mt-4">
            <button type="button" class="btn-create btn-save btn-block-mobile" onclick="abrirModalConfirmacion()">
                <i class="fa-solid fa-check-circle"></i> <span>Finalizar Ingreso</span>
            </button>
        </div>

    </form>
</div>

<style>
    .mt-4 { margin-top: 1.5rem; }
    .center-text { text-align: center; }
    .right-text { text-align: right; }
    .d-flex-col { display: flex; flex-direction: column; }
    .d-flex-end { display: flex; align-items: flex-end; }
    
    .compra-grid-top { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
    .row-2-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .row-mobile-inputs { display: flex; gap: 10px; }

    .evidence-box { 
        flex: 1; border: 2px dashed #b2bec3; border-radius: 12px; background: #f9fafb; 
        display: flex; align-items: center; justify-content: center; flex-direction: column; 
        cursor: pointer; position: relative; min-height: 150px; overflow: hidden; transition: 0.2s;
    }
    .evidence-box:hover { border-color: var(--color-primario); background: #fff; }
    #evidencePlaceholder i { font-size: 2rem; color: #b2bec3; }
    #evidencePlaceholder p { margin: 5px 0 0; color: #636e72; font-size: 0.85rem; }
    #evidencePreview { position: absolute; inset: 0; background: white; display: flex; justify-content: center; align-items: center; }
    #imgPreview { width: 100%; height: 100%; object-fit: contain; }
    .btn-remove-ev { position: absolute; top: 5px; right: 5px; background: #d63031; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; }
    .error-msg { color: #d63031; display: none; text-align: center; margin-top: 5px; font-weight: 600; font-size: 0.8rem; }

    .builder-bar { display: flex; gap: 10px; flex-wrap: wrap; background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
    .grow-2 { flex-grow: 1; min-width: 200px; }
    .w-small { width: 80px; }
    .w-medium { width: 110px; }
    .btn-add { background: var(--color-primario); color: white; height: 42px; padding: 0 20px; border:none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .btn-add:active { transform: scale(0.95); }

    .empty-state-box { text-align: center; padding: 3rem 1rem; background: #fff; border-radius: 12px; border: 1px dashed #dfe6e9; }
    .icon-circle { width: 70px; height: 70px; background: #f1f2f6; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
    .icon-circle i { font-size: 1.8rem; color: #b2bec3; }
    .empty-state-box h3 { margin: 0 0 5px; color: #2d3436; font-size: 1.1rem; }
    .empty-state-box p { margin: 0; color: #636e72; font-size: 0.9rem; }

    .show-desktop { display: block; }
    .show-mobile { display: none; }
    
    .table-cart { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .table-cart th { text-align: left; padding: 15px; background: #f8f9fa; color: #636e72; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
    .table-cart td { padding: 15px; border-bottom: 1px solid #f1f2f6; vertical-align: middle; color: #2d3436; }
    .btn-trash { color: #ff7675; cursor: pointer; padding: 5px; transition: 0.2s; }
    .btn-trash:hover { color: #d63031; transform: scale(1.1); }

    .mobile-cart-item { background: white; border-radius: 12px; padding: 15px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #f1f2f6; display: flex; justify-content: space-between; align-items: center; }
    .m-info { flex-grow: 1; }
    .m-title { font-weight: 700; color: #2d3436; font-size: 0.95rem; margin-bottom: 4px; display: block; }
    .m-details { font-size: 0.85rem; color: #636e72; }
    .m-price { font-weight: 800; color: var(--color-primario); font-size: 1rem; margin-top: 4px; display: block; }
    
    .m-actions { margin-left: 15px; padding-left: 15px; border-left: 1px solid #eee; }
    .btn-trash-mobile { width: 36px; height: 36px; background: #ffecec; color: #d63031; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; }

    .footer-total { background: #fff; padding: 15px 20px; border-radius: 12px; margin-top: 15px; display: flex; justify-content: space-between; align-items: center; border: 2px solid var(--color-primario); background: #fff5f8; }
    .footer-total .lbl { font-weight: 700; color: #2d3436; }
    .footer-total .val { font-weight: 800; font-size: 1.4rem; color: var(--color-primario); }

    @media (max-width: 768px) {
        .compra-grid-top { grid-template-columns: 1fr; gap: 20px; }
        .hide-mobile { display: none; }
        .row-mobile-inputs { width: 100%; justify-content: space-between; margin-bottom: 10px; }
        .w-small, .w-medium { width: 48%; }
        .d-flex-end { width: 100%; } 
        .btn-add { width: 100%; }
        .btn-block-mobile { width: 100%; padding: 15px; font-size: 1.1rem; }
        .show-desktop { display: none !important; }
        .show-mobile { display: block !important; }
        .empty-state-box { border: none; background: transparent; }
    }
</style>

<script>
    let carrito = [];

    // --- SELECCIÓN Y CARRITO (Igual que antes) ---
    function seleccionarProducto() {
        const input = document.getElementById('txtBuscarProd');
        const val = input.value;
        const datalist = document.getElementById('listaProds');
        const infoDiv = document.getElementById('infoProdSeleccionado');
        let encontrado = false;
        
        for (let i = 0; i < datalist.options.length; i++) {
            const opt = datalist.options[i];
            if (opt.value === val) {
                document.getElementById('idProdSeleccionado').value = opt.getAttribute('data-id');
                document.getElementById('unidadProdSeleccionado').value = opt.getAttribute('data-unidad');
                document.getElementById('txtCosto').value = opt.getAttribute('data-costo');
                infoDiv.innerHTML = `<i class="fa-solid fa-check"></i> Stock Actual: ${opt.getAttribute('data-stock')} ${opt.getAttribute('data-unidad')}`;
                document.getElementById('txtCantidad').focus();
                encontrado = true;
                break;
            }
        }
        if(!encontrado) {
            document.getElementById('idProdSeleccionado').value = '';
            infoDiv.innerHTML = '';
        }
    }

    function agregarAlCarrito() {
        const idProd = document.getElementById('idProdSeleccionado').value;
        const nombreFull = document.getElementById('txtBuscarProd').value;
        const unidad = document.getElementById('unidadProdSeleccionado').value;
        const cant = parseFloat(document.getElementById('txtCantidad').value);
        const costo = parseFloat(document.getElementById('txtCosto').value);

        if (!idProd) return alert('Selecciona un producto válido.');
        if (isNaN(cant) || cant <= 0) return alert('Cantidad inválida.');
        if (isNaN(costo) || costo < 0) return alert('Costo inválido.');

        const nombreLimpio = nombreFull.split(' - ')[1] || nombreFull;

        carrito.push({
            pro_id: idProd,
            nombre: nombreLimpio,
            unidad: unidad,
            cantidad: cant,
            costo: costo,
            subtotal: cant * costo
        });

        renderizarCarrito();
        limpiarBuilder();
    }

    function limpiarBuilder() {
        document.getElementById('txtBuscarProd').value = '';
        document.getElementById('idProdSeleccionado').value = '';
        document.getElementById('infoProdSeleccionado').innerHTML = '';
        document.getElementById('txtCantidad').value = '1';
        document.getElementById('txtCosto').value = '';
        document.getElementById('txtBuscarProd').focus();
    }

    function eliminarItem(index) {
        if(confirm('¿Quitar del ingreso?')) {
            carrito.splice(index, 1);
            renderizarCarrito();
        }
    }

    function renderizarCarrito() {
        const tableBody = document.getElementById('tablaCuerpo');
        const mobileContainer = document.getElementById('mobileCardsContainer');
        const emptyState = document.getElementById('emptyState');
        const tableContainer = document.getElementById('tableContainer');
        const footerTotal = document.getElementById('footerTotal');
        const lblTotal = document.getElementById('lblTotalGeneral');

        tableBody.innerHTML = '';
        mobileContainer.innerHTML = '';

        if (carrito.length === 0) {
            emptyState.style.display = 'block';
            tableContainer.style.display = 'none';
            mobileContainer.style.display = 'none';
            footerTotal.style.display = 'none';
            document.getElementById('inputTotalCompra').value = 0;
            document.getElementById('inputDetalleJson').value = '';
            return;
        }

        emptyState.style.display = 'none';
        tableContainer.removeAttribute('style'); 
        mobileContainer.removeAttribute('style');
        footerTotal.style.display = 'flex';

        let totalGeneral = 0;

        carrito.forEach((item, index) => {
            totalGeneral += item.subtotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><b>${item.nombre}</b><br><small style="color:#636e72;">(${item.unidad})</small></td>
                <td class="text-center">${item.cantidad}</td>
                <td class="text-right">$${item.costo.toFixed(2)}</td>
                <td class="text-right"><b>$${item.subtotal.toFixed(2)}</b></td>
                <td class="text-center"><i class="fa-solid fa-trash btn-trash" onclick="eliminarItem(${index})"></i></td>
            `;
            tableBody.appendChild(tr);

            const card = document.createElement('div');
            card.className = 'mobile-cart-item';
            card.innerHTML = `
                <div class="m-info">
                    <span class="m-title">${item.nombre}</span>
                    <div class="m-details">${item.cantidad} ${item.unidad} x $${item.costo.toFixed(2)}</div>
                    <span class="m-price">$${item.subtotal.toFixed(2)}</span>
                </div>
                <div class="m-actions">
                    <div class="btn-trash-mobile" onclick="eliminarItem(${index})"><i class="fa-solid fa-trash"></i></div>
                </div>
            `;
            mobileContainer.appendChild(card);
        });

        lblTotal.innerText = totalGeneral.toFixed(2);
        document.getElementById('inputTotalCompra').value = totalGeneral.toFixed(2);
        document.getElementById('inputDetalleJson').value = JSON.stringify(carrito);
    }

    // --- EVIDENCIA ---
    const inputEv = document.getElementById('inputEvidencia');
    inputEv.addEventListener('change', function(e) {
        if (this.files[0]) {
            const r = new FileReader();
            r.onload = (evt) => {
                document.getElementById('imgPreview').src = evt.target.result;
                document.getElementById('evidencePlaceholder').style.display = 'none';
                document.getElementById('evidencePreview').style.display = 'flex';
                document.getElementById('msgEvidencia').style.display = 'none';
            }
            r.readAsDataURL(this.files[0]);
        }
    });

    function borrarEvidencia(e) {
        e.preventDefault(); e.stopPropagation();
        inputEv.value = '';
        document.getElementById('evidencePlaceholder').style.display = 'flex';
        document.getElementById('evidencePreview').style.display = 'none';
    }

    // --- NUEVO: INTEGRACIÓN CON MODAL DEL FOOTER ---
    function abrirModalConfirmacion() {
        // 1. Validaciones
        if (carrito.length === 0) {
            alert("El carrito está vacío.");
            return;
        }

        const tipo = document.getElementById('selectTipoDoc').value;
        const hayFoto = inputEv.files.length > 0;
        
        if (tipo === 'SIN_SOPORTE' && !hayFoto) {
            document.getElementById('msgEvidencia').style.display = 'block';
            alert("Falta la foto obligatoria.");
            return;
        }

        // 2. Usar tu modal universal (#modalConfirm)
        const modal = document.getElementById('modalConfirm');
        const titulo = document.getElementById('modalTitle');
        const texto = document.getElementById('modalText');
        const btnConfirmar = document.getElementById('btnModalConfirmar');
        const btnCancelar = document.getElementById('btnModalCancelar');

        if (!modal) {
            // Fallback por si acaso no cargó el footer
            if(confirm("¿Confirmar Ingreso?")) document.getElementById('formCompra').submit();
            return;
        }

        titulo.innerText = "¿Confirmar Ingreso?";
        texto.innerHTML = `Registrar <b>${carrito.length} productos</b> por <b>$${document.getElementById('lblTotalGeneral').innerText}</b>.<br>El stock se actualizará de inmediato.`;

        // Resetear evento onclick del botón confirmar (clonándolo)
        const nuevoBtn = btnConfirmar.cloneNode(true);
        btnConfirmar.parentNode.replaceChild(nuevoBtn, btnConfirmar);

        nuevoBtn.onclick = function(e) {
            e.preventDefault();
            document.getElementById('formCompra').submit();
        };

        btnCancelar.onclick = function() {
            modal.classList.remove('active');
        };

        modal.classList.add('active');
    }
</script>