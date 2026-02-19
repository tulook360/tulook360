<?php $pageTitle = "Recepción de Citas"; ?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
    /* ESTILOS DEL STEPPER (Basado en tu diseño de Sucursal) */
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
    
    /* Estados del Stepper */
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
    .btn-search:hover { transform: scale(1.05); }

    /* PASO 2: TICKET */
    .ticket-review {
        background: #fff; border: 2px dashed #eee; border-radius: 20px; padding: 30px; max-width: 500px; margin: 0 auto;
    }
    .client-head { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
    .client-pic { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid #f0f0f0; }
    .client-data h2 { margin: 0; font-size: 1.3rem; color: #2d3436; }
    .client-data span { font-size: 0.9rem; color: #636e72; }
    
    .info-line { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #f9f9f9; padding-bottom: 10px; }
    .il-label { font-size: 0.8rem; font-weight: 700; color: #b2bec3; text-transform: uppercase; }
    .il-val { font-weight: 600; color: #2d3436; }
    .price-tag { font-size: 1.8rem; font-weight: 900; color: var(--color-primario); }

    /* PASO 3: PAGOS DIVIDIDOS */
    .pay-container { max-width: 600px; margin: 0 auto; }
    .pay-total-header { text-align: center; margin-bottom: 25px; padding: 15px; background: #fff0f6; border-radius: 15px; color: var(--color-primario); }
    .pay-total-header span { font-size: 0.8rem; font-weight: 800; text-transform: uppercase; }
    .pay-total-header strong { font-size: 2rem; font-weight: 900; display: block; line-height: 1; }

    .pay-list { display: flex; flex-direction: column; gap: 10px; }
    .pay-row { 
        display: grid; grid-template-columns: 1fr 1fr 40px; gap: 10px; align-items: center;
        background: #fff; border: 1px solid #e0e0e0; padding: 10px; border-radius: 12px;
    }
    .pay-ctl { width: 100%; padding: 10px; border: 1px solid #eee; border-radius: 8px; font-weight: 600; color: #2d3436; outline: none; }
    .btn-del-row { background: #ffe0e6; color: #d63031; border: none; height: 40px; border-radius: 8px; cursor: pointer; }
    
    .ref-box { grid-column: 1 / -1; display: none; margin-top: 5px; }
    .ref-box.show { display: block; animation: slideFade 0.2s; }
    .ref-inp { width: 100%; padding: 10px; background: #fafafa; border: 1px solid #eee; border-radius: 8px; font-size: 0.9rem; }

    .btn-add-pay { 
        width: 100%; padding: 12px; margin-top: 15px; background: transparent; border: 2px dashed #ddd; 
        color: #aaa; font-weight: 700; border-radius: 12px; cursor: pointer; transition: 0.2s;
    }
    .btn-add-pay:hover { border-color: var(--color-primario); color: var(--color-primario); background: #fff0f6; }

    .calc-summary { display: flex; justify-content: space-between; margin-top: 20px; font-weight: 700; font-size: 1rem; border-top: 1px solid #eee; padding-top: 15px; }
    .c-ok { color: #00b894; } .c-err { color: #d63031; }

    /* FOOTER */
    .form-footer { padding: 20px 40px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; background: #fff; border-radius: 0 0 20px 20px; }
    .btn-nav { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; transition: 0.2s; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; }
    .btn-prev { background: #fff; color: #636e72; border: 1px solid #ddd; }
    .btn-next { background: #2d3436; color: white; } .btn-next:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .btn-finish { background: #00b894; color: white; display: none; } .btn-finish:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,184,148,0.3); }
    .btn-disabled { opacity: 0.5; pointer-events: none; }

    /* TOAST */
    #toast { position: fixed; top: 20px; right: 20px; z-index: 10000; }
    .t-msg { background: white; padding: 12px 20px; border-radius: 50px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px; border: 1px solid #eee; animation: slideIn 0.3s; }
    .t-success i { color: #00b894; } .t-error i { color: #d63031; }
    @keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .stepper-container { padding: 20px 10px; } .step-label { display: none; } .stepper-track { left: 20px; right: 20px; }
        .form-footer { padding: 15px; } .btn-nav { font-size: 0.9rem; padding: 10px 20px; }
    }
</style>

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Recepción de Citas</h1>
        <p class="section-subtitle">Gestiona la entrada y el cobro del cliente.</p>
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
            <div class="step-circle"><i class="fa-solid fa-list-check"></i></div>
            <span class="step-label">Detalles</span>
        </div>
        <div class="step-item" id="st3">
            <div class="step-circle"><i class="fa-solid fa-wallet"></i></div>
            <span class="step-label">Cobro</span>
        </div>
    </div>

    <div class="form-content-box">
        
        <div id="p1" class="step-panel active-panel">
            <div class="panel-header"><h3>Identificar Cliente</h3><p>Escanea el QR o ingresa el código.</p></div>
            <div class="scanner-frame"><div id="reader"></div></div>
            <div class="manual-group">
                <input type="text" id="inputToken" class="inp-token" placeholder="T360-XXXX" autocomplete="off">
                <button class="btn-search" onclick="buscarManual()"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>

        <div id="p2" class="step-panel">
            <div class="panel-header"><h3>Confirmar Detalles</h3><p>Revisa la información antes de cobrar.</p></div>
            <div class="ticket-review">
                <div class="client-head">
                    <img src="" id="resFoto" class="client-pic">
                    <div class="client-data"><h2 id="resNombre">--</h2><span id="resCedula">--</span></div>
                </div>
                <div class="info-line"><span class="il-label">Servicio</span><span class="il-val" id="resServicio">--</span></div>
                <div class="info-line"><span class="il-label">Especialista</span><span class="il-val" id="resExperto">--</span></div>
                <div class="info-line"><span class="il-label">Fecha</span><span class="il-val" id="resFecha">--</span></div>
                <div class="info-line" style="border:none; margin-top:20px;">
                    <span class="il-label">Total a Cobrar</span>
                    <span class="price-tag" id="resPrecio">$0.00</span>
                </div>
            </div>
        </div>

        <div id="p3" class="step-panel">
            <div class="panel-header"><h3>Método de Pago</h3><p>Divide el pago si es necesario.</p></div>
            <div class="pay-container">
                <div class="pay-total-header">
                    <span>Monto Total</span>
                    <strong id="displayTotal">$0.00</strong>
                </div>
                
                <div id="pagosList" class="pay-list">
                    </div>
                
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
        <button class="btn-nav btn-finish" id="btnFinish" onclick="confirmarCobro()">Finalizar Cobro <i class="fa-solid fa-check"></i></button>
    </div>

</div>

<div id="toast"></div>

<script>
    const API_BUSCAR = '<?= $urlBuscar ?>';
    const API_CONFIRMAR = '<?= $urlConfirmar ?>';
    
    let currentStep = 1;
    let html5QrcodeScanner = null;
    let isScanning = false;
    let citaId = null;
    let totalCita = 0;

    // INICIO
    document.addEventListener('DOMContentLoaded', () => {
        iniciarScanner();
    });

    // --- NAVEGACIÓN PASOS ---
    function nav(dir) {
        // Validaciones al avanzar
        if (dir === 1) {
            if (currentStep === 1) {
                const token = document.getElementById('inputToken').value.trim();
                if (!token) return toast("Escanea o escribe un código", "error");
                buscarCita(token); // La búsqueda maneja el avance si es exitosa
                return;
            }
            if (currentStep === 2) {
                // Preparar pantalla de pago
                prepararPagos();
            }
        }

        // Acciones al retroceder
        if (dir === -1) {
            if (currentStep === 2) {
                // Volver a escanear
                document.getElementById('inputToken').value = '';
                if(html5QrcodeScanner) { 
                    html5QrcodeScanner.resume(); 
                    isScanning = true; 
                }
            }
        }

        cambiarPaso(currentStep + dir);
    }

    function cambiarPaso(step) {
        // Ocultar actual
        document.getElementById('p' + currentStep).classList.remove('active-panel');
        document.getElementById('st' + currentStep).classList.remove('active');
        if(step > currentStep) document.getElementById('st' + currentStep).classList.add('completed');
        else document.getElementById('st' + currentStep).classList.remove('completed');

        currentStep = step;

        // Mostrar nuevo
        document.getElementById('p' + currentStep).classList.add('active-panel');
        document.getElementById('st' + currentStep).classList.add('active');
        document.getElementById('st' + currentStep).classList.remove('completed');

        // Botones
        const prev = document.getElementById('btnPrev');
        const next = document.getElementById('btnNext');
        const fin = document.getElementById('btnFinish');

        prev.disabled = (currentStep === 1);
        
        if (currentStep === 1) {
            next.style.display = 'flex'; next.innerHTML = 'Buscar <i class="fa-solid fa-search"></i>';
            fin.style.display = 'none';
        } else if (currentStep === 2) {
            next.style.display = 'flex'; next.innerHTML = 'Ir a Pagar <i class="fa-solid fa-arrow-right"></i>';
            fin.style.display = 'none';
        } else if (currentStep === 3) {
            next.style.display = 'none';
            fin.style.display = 'flex';
            validarMontos(); // Verificar si habilita botón
        }
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
        buscarCita(decodedText);
    }

    function buscarManual() {
        nav(1);
    }

    function buscarCita(token) {
        const btn = document.getElementById('btnNext');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

        fetch(API_BUSCAR, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ token: token })
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                llenarDatos(resp.cita);
                if(html5QrcodeScanner) html5QrcodeScanner.pause();
                isScanning = false;
                cambiarPaso(2);
            } else {
                toast(resp.error, "error");
                document.getElementById('inputToken').value = '';
                isScanning = true;
                btn.innerHTML = 'Buscar <i class="fa-solid fa-search"></i>';
            }
        });
    }

    function llenarDatos(c) {
        citaId = c.cita_id;
        totalCita = parseFloat(c.det_precio);
        const puntosCanje = parseInt(c.det_puntos_canje) || 0; // NUEVO

        document.getElementById('resNombre').innerText = c.cli_nombre + ' ' + c.cli_apellido;
        document.getElementById('resCedula').innerText = c.usu_cedula || 'S/N';
        document.getElementById('resFoto').src = c.usu_foto || `https://ui-avatars.com/api/?name=${c.cli_nombre}`;
        document.getElementById('resServicio').innerText = c.serv_nombre;
        document.getElementById('resExperto').innerText = c.usu_nombres;
        document.getElementById('resFecha').innerText = new Date(c.cita_fecha).toLocaleString();
        
        // --- MOSTRAR PRECIO Y PUNTOS ---
        let htmlPrecio = '$' + totalCita.toFixed(2);
        if (puntosCanje > 0) {
            htmlPrecio += ` <span style="font-size: 1rem; color: #3b82f6;">+ ${puntosCanje} pts</span>`;
        }
        document.getElementById('resPrecio').innerHTML = htmlPrecio;

        if(c.det_estado !== 'RESERVADO') {
            toast("Cita ya procesada: " + c.det_estado, "error");
            setTimeout(() => location.reload(), 2000);
        }
    }

    // --- PAGOS (LOGICA DE SPLIT) ---
    function prepararPagos() {
        document.getElementById('displayTotal').innerText = '$' + totalCita.toFixed(2);
        document.getElementById('pagosList').innerHTML = '';
        agregarFilaPago(totalCita);
        validarMontos();
    }

    function agregarFilaPago(montoIni = 0) {
        if(montoIni === 0) {
            const pagado = calcPagado();
            montoIni = Math.max(0, totalCita - pagado);
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
        const falta = totalCita - pagado;
        const btn = document.getElementById('btnFinish');
        
        document.getElementById('txtPagado').innerText = '$' + pagado.toFixed(2);
        
        const lbl = document.getElementById('txtFalta');
        if (Math.abs(falta) < 0.01) {
            lbl.innerText = "¡Completo!";
            lbl.className = "c-ok";
            btn.classList.remove('btn-disabled');
            btn.disabled = false;
        } else if (falta > 0) {
            lbl.innerText = "Falta: $" + falta.toFixed(2);
            lbl.className = "c-err";
            btn.classList.add('btn-disabled');
            btn.disabled = true;
        } else {
            lbl.innerText = "Sobra: $" + Math.abs(falta).toFixed(2);
            lbl.className = "c-err";
            btn.classList.add('btn-disabled');
            btn.disabled = true;
        }
    }

    function confirmarCobro() {
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
            body: JSON.stringify({ cita_id: citaId, pagos: pagos, total: totalCita })
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                toast("Cobro Exitoso", "success");
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