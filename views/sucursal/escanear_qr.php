<?php $pageTitle = "Entrega de Pedidos"; ?>

<script src="https://unpkg.com/html5-qrcode"></script>

<style>
    /* ESTILOS STEPPER (Igual que en Citas) */
    .main-wrapper { 
        max-width: 900px; margin: 0 auto; background: #fff; border-radius: 20px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden; display: flex; flex-direction: column; min-height: 600px; 
    }

    .stepper-container { 
        background: #fdfdfd; padding: 25px 40px; border-bottom: 1px solid #f0f0f0; 
        display: flex; justify-content: space-between; position: relative; 
    }
    .stepper-track { 
        position: absolute; top: 40px; left: 60px; right: 60px; height: 3px; background: #eee; z-index: 1; 
    }
    
    .step-item { z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; transition: 0.3s; }
    .step-circle { 
        width: 35px; height: 35px; border-radius: 50%; background: #fff; border: 2px solid #ddd; 
        color: #bbb; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: 0.3s;
    }
    .step-label { font-size: 0.75rem; font-weight: 700; color: #ccc; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .step-item.active .step-circle { 
        background: var(--color-primario); border-color: var(--color-primario); color: white; 
        box-shadow: 0 0 0 5px rgba(255, 51, 102, 0.15); transform: scale(1.1); 
    }
    .step-item.active .step-label { color: var(--color-primario); }
    .step-item.completed .step-circle { background: #00b894; border-color: #00b894; color: white; }
    .step-item.completed .step-label { color: #00b894; }

    .form-content-box { padding: 30px; flex: 1; position: relative; }
    .step-panel { display: none; animation: slideFade 0.4s ease; }
    .step-panel.active-panel { display: block; }
    @keyframes slideFade { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .panel-header { text-align: center; margin-bottom: 30px; }
    .panel-header h3 { margin: 0 0 5px; font-size: 1.6rem; color: #2d3436; font-weight: 800; }
    .panel-header p { margin: 0; color: #b2bec3; }

    /* PASO 1: ESCANER */
    .scanner-frame {
        max-width: 350px; margin: 0 auto; border-radius: 20px; overflow: hidden; 
        border: 4px solid #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative;
        background: #000; height: 350px;
    }
    #reader { width: 100%; height: 100%; object-fit: cover; }
    
    .manual-group { 
        max-width: 350px; margin: 20px auto 0; display: flex; gap: 10px; 
        background: #f8f9fa; padding: 5px; border-radius: 12px; border: 1px solid #e0e0e0;
    }
    .inp-token { 
        border: none; background: transparent; width: 100%; padding: 10px; 
        text-align: center; font-weight: 700; color: #2d3436; letter-spacing: 2px; outline: none; text-transform: uppercase;
    }
    .btn-search { 
        border: none; background: #fff; width: 40px; border-radius: 10px; cursor: pointer; 
        color: var(--color-primario); box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s;
    }

    /* PASO 2: LISTA DE ITEMS */
    .order-review-box { max-width: 600px; margin: 0 auto; }
    .client-header { background: #f8f9fa; padding: 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .client-header h4 { margin: 0; font-size: 1rem; color: #2d3436; }
    .ord-badge { background: var(--color-primario); color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; }

    .items-list { max-height: 300px; overflow-y: auto; border: 1px solid #eee; border-radius: 12px; padding: 10px; }
    .item-row { display: flex; gap: 15px; padding: 10px; border-bottom: 1px solid #f5f5f5; align-items: center; }
    .item-img { width: 50px; height: 50px; border-radius: 8px; object-fit: contain; border: 1px solid #eee; }
    .item-info h5 { margin: 0; font-size: 0.9rem; color: #2d3436; }
    .item-info span { font-size: 0.8rem; color: #888; }
    .item-status { margin-left: auto; font-size: 1.2rem; }
    
    .total-display { text-align: right; margin-top: 20px; font-size: 1.4rem; font-weight: 800; color: var(--color-primario); }

    /* PASO 3: PAGOS DIVIDIDOS */
    .pay-container { max-width: 600px; margin: 0 auto; }
    .pay-total-header { text-align: center; margin-bottom: 25px; padding: 15px; background: #fff0f6; border-radius: 15px; color: var(--color-primario); }
    .pay-total-header strong { font-size: 2rem; font-weight: 900; display: block; line-height: 1; }

    .pay-list { display: flex; flex-direction: column; gap: 10px; }
    .pay-row { 
        display: grid; grid-template-columns: 1fr 1fr 40px; gap: 10px; align-items: center;
        background: #fff; border: 1px solid #e0e0e0; padding: 10px; border-radius: 12px;
    }
    .pay-ctl { width: 100%; padding: 10px; border: 1px solid #eee; border-radius: 8px; font-weight: 600; outline: none; }
    .btn-del-row { background: #ffe0e6; color: #d63031; border: none; height: 40px; border-radius: 8px; cursor: pointer; }
    
    .ref-box { grid-column: 1 / -1; display: none; margin-top: 5px; }
    .ref-box.show { display: block; animation: slideFade 0.2s; }
    .ref-inp { width: 100%; padding: 10px; background: #fafafa; border: 1px solid #eee; border-radius: 8px; }

    .btn-add-pay { 
        width: 100%; padding: 12px; margin-top: 15px; background: transparent; border: 2px dashed #ddd; 
        color: #aaa; font-weight: 700; border-radius: 12px; cursor: pointer;
    }
    .btn-add-pay:hover { border-color: var(--color-primario); color: var(--color-primario); background: #fff0f6; }

    .calc-summary { display: flex; justify-content: space-between; margin-top: 20px; font-weight: 700; border-top: 1px solid #eee; padding-top: 15px; }
    .c-ok { color: #00b894; } .c-err { color: #d63031; }

    /* FOOTER */
    .form-footer { padding: 20px 40px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; background: #fff; border-radius: 0 0 20px 20px; }
    .btn-nav { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
    .btn-prev { background: #fff; color: #636e72; border: 1px solid #ddd; }
    .btn-next { background: #2d3436; color: white; }
    .btn-finish { background: #00b894; color: white; display: none; }
    .btn-disabled { opacity: 0.5; pointer-events: none; }

    /* TOAST */
    #toast { position: fixed; top: 20px; right: 20px; z-index: 10000; }
    .t-msg { background: white; padding: 12px 20px; border-radius: 50px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px; border: 1px solid #eee; animation: slideIn 0.3s; }
    .t-success i { color: #00b894; } .t-error i { color: #d63031; }
    @keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Entrega de Pedidos</h1>
        <p class="section-subtitle">Gestión de entregas y cobros de productos.</p>
    </div>
</div>

<div class="main-wrapper">
    
    <div class="stepper-container">
        <div class="stepper-track"></div>
        <div class="step-item active" id="st1">
            <div class="step-circle"><i class="fa-solid fa-qrcode"></i></div>
            <span class="step-label">Identificar</span>
        </div>
        <div class="step-item" id="st2">
            <div class="step-circle"><i class="fa-solid fa-boxes-stacked"></i></div>
            <span class="step-label">Revisión</span>
        </div>
        <div class="step-item" id="st3">
            <div class="step-circle"><i class="fa-solid fa-wallet"></i></div>
            <span class="step-label">Cobro</span>
        </div>
    </div>

    <div class="form-content-box">
        
        <div id="p1" class="step-panel active-panel">
            <div class="panel-header"><h3>Buscar Orden</h3><p>Escanea el QR o ingresa el código.</p></div>
            <div class="scanner-frame"><div id="reader"></div></div>
            <div class="manual-group">
                <input type="text" id="inputToken" class="inp-token" placeholder="ORD-XXXX" autocomplete="off">
                <button class="btn-search" onclick="buscarManual()"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

        <div id="p2" class="step-panel">
            <div class="panel-header"><h3>Contenido del Pedido</h3><p>Verifica los productos a entregar.</p></div>
            <div class="order-review-box">
                <div class="client-header">
                    <h4 id="resCliente">--</h4>
                    <span class="ord-badge" id="resCodigo">--</span>
                </div>
                <div class="items-list" id="listaItems">
                    </div>
                <div class="total-display" id="resTotal">$0.00</div>
            </div>
        </div>

        <div id="p3" class="step-panel">
            <div class="panel-header"><h3>Método de Pago</h3><p>Registra el cobro de los productos.</p></div>
            <div class="pay-container">
                <div class="pay-total-header">
                    <span>Monto a Cobrar</span>
                    <strong id="displayTotal">$0.00</strong>
                </div>
                
                <div id="pagosList" class="pay-list"></div>
                <button class="btn-add-pay" onclick="agregarFilaPago()">+ Agregar otro método</button>

                <div class="calc-summary">
                    <span>Pagado: <span id="txtPagado" class="c-ok">$0.00</span></span>
                    <span id="txtFalta" class="c-err">Falta: $0.00</span>
                </div>
            </div>
        </div>

    </div>

    <div class="form-footer">
        <button class="btn-nav btn-prev" id="btnPrev" onclick="nav(-1)" disabled><i class="fa-solid fa-arrow-left"></i> Atrás</button>
        <button class="btn-nav btn-next" id="btnNext" onclick="nav(1)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
        <button class="btn-nav btn-finish" id="btnFinish" onclick="confirmarEntrega()">Finalizar Entrega <i class="fa-solid fa-check"></i></button>
    </div>

</div>

<div id="toast"></div>

<script>
    const API_BUSCAR = '<?= $urlBuscar ?>';
    const API_CONFIRMAR = '<?= $urlConfirmar ?>';
    
    let currentStep = 1;
    let html5QrcodeScanner = null;
    let isScanning = false;
    let ordenId = null;
    let totalOrden = 0;
    let itemsPendientes = 0;

    // INICIO
    document.addEventListener('DOMContentLoaded', () => { iniciarScanner(); });

    // --- NAVEGACIÓN ---
    function nav(dir) {
        if (dir === 1) {
            if (currentStep === 1) {
                const token = document.getElementById('inputToken').value.trim();
                if (!token) return toast("Escanea o escribe un código", "error");
                buscarOrden(token); return; 
            }
            if (currentStep === 2) {
                if (itemsPendientes === 0) { // Si ya todo está pagado
                    return toast("Esta orden ya fue entregada por completo", "error");
                }
                prepararPagos();
            }
        }
        
        if (dir === -1 && currentStep === 2) {
            document.getElementById('inputToken').value = '';
            if(html5QrcodeScanner) { html5QrcodeScanner.resume(); isScanning = true; }
        }

        cambiarPaso(currentStep + dir);
    }

    function cambiarPaso(step) {
        document.getElementById('p' + currentStep).classList.remove('active-panel');
        document.getElementById('st' + currentStep).classList.remove('active');
        if(step > currentStep) document.getElementById('st' + currentStep).classList.add('completed');
        else document.getElementById('st' + currentStep).classList.remove('completed');

        currentStep = step;
        document.getElementById('p' + currentStep).classList.add('active-panel');
        document.getElementById('st' + currentStep).classList.add('active');
        document.getElementById('st' + currentStep).classList.remove('completed');

        // Botones
        const prev = document.getElementById('btnPrev');
        const next = document.getElementById('btnNext');
        const fin = document.getElementById('btnFinish');

        prev.disabled = (currentStep === 1);
        next.style.display = (currentStep === 3) ? 'none' : 'flex';
        fin.style.display = (currentStep === 3) ? 'flex' : 'none';

        if(currentStep === 1) next.innerHTML = 'Buscar <i class="fa-solid fa-search"></i>';
        else if(currentStep === 2) next.innerHTML = 'Ir a Pagar <i class="fa-solid fa-arrow-right"></i>';

        if(currentStep === 3) validarMontos();
    }

    // --- ESCANER ---
    function iniciarScanner() {
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrcodeScanner = new Html5Qrcode("reader");
        html5QrcodeScanner.start({ facingMode: "environment" }, config, onScan, ()=>{})
        .then(() => isScanning = true);
    }

    function onScan(decodedText) {
        if(!isScanning) return;
        isScanning = false;
        document.getElementById('inputToken').value = decodedText;
        buscarOrden(decodedText);
    }

    function buscarManual() { nav(1); }

    function buscarOrden(token) {
        const btn = document.getElementById('btnNext');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        fetch(API_BUSCAR, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ token: token })
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                if(resp.tipo_respuesta === 'WRONG_BRANCH') {
                    toast("Esta orden pertenece a: " + resp.lugares[0].suc_nombre, "error");
                    btn.innerHTML = 'Buscar <i class="fa-solid fa-search"></i>';
                    isScanning = true;
                } else {
                    llenarDatos(resp.orden, resp.items);
                    if(html5QrcodeScanner) html5QrcodeScanner.pause();
                    isScanning = false;
                    cambiarPaso(2);
                }
            } else {
                toast(resp.error, "error");
                btn.innerHTML = 'Buscar <i class="fa-solid fa-search"></i>';
                isScanning = true;
            }
        });
    }

    function llenarDatos(orden, items) {
        ordenId = orden.ord_id;
        totalOrden = parseFloat(orden.total_cobrar_sucursal);
        
        document.getElementById('resCliente').innerText = orden.usu_nombres + ' ' + orden.usu_apellidos;
        document.getElementById('resCodigo').innerText = orden.ord_codigo;
        document.getElementById('resTotal').innerText = '$' + totalOrden.toFixed(2);

        const lista = document.getElementById('listaItems');
        lista.innerHTML = '';
        itemsPendientes = 0;

        items.forEach(i => {
            const entregado = (i.odet_estado === 'RECOGIDO');
            if(!entregado) itemsPendientes++;
            
            const icon = entregado ? '<i class="fa-solid fa-check" style="color:#00b894"></i>' : '<i class="fa-regular fa-clock" style="color:#ccc"></i>';
            const img = i.imagen || 'recursos/img/sin_foto.png';
            const opacity = entregado ? '0.5' : '1';

            lista.innerHTML += `
                <div class="item-row" style="opacity:${opacity}">
                    <img src="${img}" class="item-img">
                    <div class="item-info" style="flex:1">
                        <h5>${i.pro_nombre}</h5>
                        <span>${i.odet_cantidad} x ${i.pro_unidad_consumo}</span>
                    </div>
                    <div class="item-status">${icon}</div>
                </div>
            `;
        });
    }

    // --- PAGOS (LOGICA DE SPLIT) ---
    function prepararPagos() {
        document.getElementById('displayTotal').innerText = '$' + totalOrden.toFixed(2);
        document.getElementById('pagosList').innerHTML = '';
        agregarFilaPago(totalOrden);
        validarMontos();
    }

    function agregarFilaPago(montoIni = 0) {
        if(montoIni === 0) {
            const pagado = calcPagado();
            montoIni = Math.max(0, totalOrden - pagado);
        }
        
        const id = Date.now();
        const div = document.createElement('div');
        div.className = 'pay-row';
        div.id = 'row-' + id;
        div.innerHTML = `
            <select class="pay-ctl" id="met-${id}" onchange="checkRef(${id})">
                <option value="1">Efectivo</option>
                <option value="2">Transferencia</option>
                <option value="3">Tarjeta</option>
            </select>
            <input type="number" class="pay-ctl inp-mon" id="mon-${id}" value="${montoIni.toFixed(2)}" step="0.01" oninput="validarMontos()">
            <button class="btn-del-row" onclick="delRow(${id})"><i class="fa-solid fa-trash"></i></button>
            <div class="ref-box" id="box-${id}">
                <input type="text" class="ref-inp" id="ref-${id}" placeholder="# Comprobante">
            </div>
        `;
        document.getElementById('pagosList').appendChild(div);
        validarMontos();
    }

    function delRow(id) {
        if(document.querySelectorAll('.pay-row').length > 1) {
            document.getElementById('row-' + id).remove();
            validarMontos();
        } else {
            toast("Mínimo un pago requerido", "error");
        }
    }

    function checkRef(id) {
        const val = document.getElementById('met-' + id).value;
        const box = document.getElementById('box-' + id);
        if(val == 2 || val == 3) box.classList.add('show');
        else {
            box.classList.remove('show');
            document.getElementById('ref-' + id).value = '';
        }
    }

    function calcPagado() {
        let sum = 0;
        document.querySelectorAll('.inp-mon').forEach(i => sum += parseFloat(i.value || 0));
        return sum;
    }

    function validarMontos() {
        const pagado = calcPagado();
        const falta = totalOrden - pagado;
        const btn = document.getElementById('btnFinish');
        
        document.getElementById('txtPagado').innerText = '$' + pagado.toFixed(2);
        const lbl = document.getElementById('txtFalta');
        
        if (Math.abs(falta) < 0.01) {
            lbl.innerText = "¡Completo!"; lbl.className = "c-ok";
            btn.classList.remove('btn-disabled'); btn.disabled = false;
        } else if (falta > 0) {
            lbl.innerText = "Falta: $" + falta.toFixed(2); lbl.className = "c-err";
            btn.classList.add('btn-disabled'); btn.disabled = true;
        } else {
            lbl.innerText = "Sobra: $" + Math.abs(falta).toFixed(2); lbl.className = "c-err";
            btn.classList.add('btn-disabled'); btn.disabled = true;
        }
    }

    function confirmarEntrega() {
        let pagos = [];
        let error = false;

        document.querySelectorAll('.pay-row').forEach(row => {
            const id = row.id.split('-')[1];
            const met = document.getElementById('met-' + id).value;
            const mon = parseFloat(document.getElementById('mon-' + id).value);
            const ref = document.getElementById('ref-' + id).value.trim();

            if((met == 2 || met == 3) && ref.length < 3) {
                document.getElementById('ref-' + id).style.borderColor = "red";
                error = true;
            }
            pagos.push({ metodo_id: met, monto: mon, referencia: ref });
        });

        if(error) return toast("Faltan comprobantes", "error");

        const btn = document.getElementById('btnFinish');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

        fetch(API_CONFIRMAR, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ord_id: ordenId, pagos: pagos, total: totalOrden })
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                toast("Entrega Registrada", "success");
                btn.innerHTML = '¡Listo!';
                setTimeout(() => location.reload(), 2000);
            } else {
                toast(resp.error, "error");
                btn.innerHTML = 'Reintentar';
            }
        });
    }

    function toast(msg, type) {
        const d = document.createElement('div');
        d.className = `t-msg t-${type}`;
        d.innerHTML = `<i class="fa-solid fa-${type=='success'?'check':'exclamation-circle'}"></i> ${msg}`;
        if(type=='error') d.style.borderLeft="5px solid #d63031"; else d.style.borderLeft="5px solid #00b894";
        document.getElementById('toast').appendChild(d);
        setTimeout(() => d.remove(), 3000);
    }
</script>