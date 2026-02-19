<?php
// views/publico/checkout.php

// 1. Agrupar items por Negocio
$grupos = [];
if(isset($items) && is_array($items)){
    foreach ($items as $item) {
        $grupos[$item['neg_nombre']][] = $item;
    }
}

// 2. GENERAR URLS SEGURAS
$urlProcesar   = ruta_accion('publico', 'procesar_compra_ajax');
$urlMisPedidos = ruta_accion('publico', 'mis_pedidos'); 
$urlCotizar = ruta_accion('publico', 'cotizar_envio_ajax');

// --- NUEVO: Calcular puntos totales del pedido ---
$totalPuntosPedido = 0;
if(isset($items)) {
    foreach($items as $item) {
        if(!empty($item['prom_id'])) {
            $totalPuntosPedido += (intval($item['puntos_necesarios'] ?? 0) * intval($item['car_cantidad']));
        }
    }
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    :root { --chk-primary: #ff3366; --chk-dark: #2d3436; --chk-gray: #f8f9fa; --chk-border: #eee; }
    .checkout-wrapper { max-width: 1250px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1.4fr 1fr; gap: 40px; font-family: 'Outfit', sans-serif; color: var(--chk-dark); }
    
    /* ESTILOS GENERALES (MANTENIDOS) */
    .chk-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 15px 35px rgba(0,0,0,0.04); border: 1px solid var(--chk-border); transition: transform 0.3s ease; }
    .chk-header { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px dashed var(--chk-border); }
    .chk-icon-header { width: 45px; height: 45px; border-radius: 12px; background: #fff0f3; color: var(--chk-primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .chk-title h2 { margin: 0; font-size: 1.3rem; font-weight: 800; }
    .chk-title p { margin: 3px 0 0; font-size: 0.9rem; color: #888; }
    
    /* PRODUCTOS */
    .biz-group { margin-bottom: 25px; }
    .biz-badge { display: inline-flex; align-items: center; gap: 6px; background: var(--chk-dark); color: white; padding: 6px 15px; border-radius: 50px; font-size: 0.75rem; font-weight: 700; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
    .prod-item { display: flex; align-items: center; gap: 15px; padding: 12px; border: 1px solid var(--chk-border); border-radius: 15px; margin-bottom: 10px; transition: 0.2s; }
    .prod-img { width: 55px; height: 55px; border-radius: 10px; object-fit: contain; mix-blend-mode: multiply; }
    .prod-info h4 { margin: 0; font-size: 0.95rem; font-weight: 700; }
    .prod-info span { font-size: 0.8rem; color: #777; background: #eee; padding: 2px 8px; border-radius: 4px; }
    .prod-price { font-weight: 800; color: var(--chk-primary); font-size: 1rem; margin-left: auto; }

    /* ENTREGA & MAPA */
    .delivery-options { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    .del-card { border: 2px solid var(--chk-border); border-radius: 16px; padding: 15px; cursor: pointer; text-align: center; transition: all 0.3s; position: relative; overflow: hidden; }
    .del-card:hover { border-color: #ccc; transform: translateY(-3px); }
    .del-card.active { border-color: var(--chk-primary); background: #fff0f3; box-shadow: 0 10px 20px rgba(255, 51, 102, 0.15); }
    .del-card i { font-size: 1.8rem; color: #aaa; margin-bottom: 8px; transition: 0.3s; }
    .del-card.active i { color: var(--chk-primary); transform: scale(1.1); }
    .del-card span { display: block; font-weight: 700; font-size: 0.95rem; }
    
    .info-box { background: #e3f2fd; border-radius: 12px; padding: 15px; display: flex; gap: 12px; align-items: start; margin-bottom: 20px; font-size: 0.9rem; color: #1565c0; line-height: 1.5; }
    #mapa-canvas { width: 100%; height: 280px; border-radius: 15px; margin: 15px 0; z-index: 1; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    
    .btn-gps { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--chk-dark); color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; margin-bottom: 10px; font-size: 0.95rem; }
    .btn-gps:hover { background: #444; transform: scale(1.02); }
    .btn-gps.success { background: #27ae60; pointer-events: none; }

    /* INPUTS */
    .input-group { position: relative; margin-bottom: 15px; }
    .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 1rem; }
    .modern-input { width: 100%; padding: 14px 15px 14px 45px; border: 2px solid var(--chk-border); border-radius: 12px; font-size: 0.95rem; outline: none; transition: 0.3s; font-family: inherit; }
    .modern-input:focus { border-color: var(--chk-primary); background: white; }
    
    /* TOTALES */
    .summary-sticky { position: sticky; top: 100px; }
    .bill-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: #666; font-size: 0.95rem; }
    .bill-total { margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--chk-border); display: flex; justify-content: space-between; align-items: center; font-size: 1.4rem; font-weight: 900; color: var(--chk-dark); }
    
    .btn-confirmar { width: 100%; padding: 18px; border: none; border-radius: 15px; background: linear-gradient(135deg, var(--chk-primary) 0%, #ff5e78 100%); color: white; font-size: 1.1rem; font-weight: 800; cursor: pointer; margin-top: 25px; transition: 0.3s; box-shadow: 0 10px 20px rgba(255, 51, 102, 0.25); display: flex; justify-content: space-between; align-items: center; padding: 18px 25px; }
    .btn-confirmar:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(255, 51, 102, 0.4); }
    .btn-confirmar:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none; }

    /* MENSAJE DE ERROR (NUEVO) */
    #checkout-error {
        display: none; /* Oculto por defecto */
        margin-top: 15px;
        padding: 12px;
        border-radius: 12px;
        background-color: #fff5f5;
        border: 1px solid #ff7675;
        color: #d63031;
        font-weight: 600;
        font-size: 0.9rem;
        text-align: center;
        animation: shake 0.4s ease;
    }
    @keyframes shake { 0%, 100% {transform: translateX(0);} 25% {transform: translateX(-5px);} 75% {transform: translateX(5px);} }

    .hidden { display: none; }
    .fade-in { animation: fadeIn 0.4s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 900px) { .checkout-wrapper { grid-template-columns: 1fr; margin-top: 20px; gap: 25px; } .summary-sticky { position: relative; top: 0; } .chk-card { padding: 20px; } .order-1-mob { order: 1; } .order-2-mob { order: 2; } }
</style>

<div class="checkout-wrapper">

    <div class="order-2-mob">
        <div class="chk-card">
            <div class="chk-header">
                <div class="chk-icon-header"><i class="fa-solid fa-bag-shopping"></i></div>
                <div class="chk-title">
                    <h2>Resumen del Pedido</h2>
                    <p>Revisa tus productos antes de confirmar</p>
                </div>
            </div>

            <?php if(!empty($grupos)): ?>
                <?php foreach ($grupos as $negocio => $productos): ?>
                    <div class="biz-group">
                        <div class="biz-badge"><i class="fa-solid fa-shop"></i> <?= htmlspecialchars($negocio) ?></div>
                        <?php foreach ($productos as $p): ?>
                            <div class="prod-item">
                                <img src="<?= $p['imagen'] ?>" class="prod-img">
                                <div class="prod-info">
                                    <h4><?= $p['pro_nombre'] ?></h4>
                                    <span>x<?= $p['car_cantidad'] ?></span>
                                </div>
                                <div class="prod-price" style="text-align: right;">
                                    <?php if(!empty($p['prom_id'])): ?>
                                        <?php if($p['prom_modalidad'] === 'PUNTOS'): ?>
                                            <span style="color:#0984e3;"><i class="fa-solid fa-coins"></i> <?= number_format($p['puntos_necesarios'] * $p['car_cantidad']) ?> pts</span>
                                        <?php elseif($p['prom_modalidad'] === 'MIXTO'): ?>
                                            $<?= number_format($p['subtotal'], 2) ?><br>
                                            <span style="font-size:0.75rem; color:#0984e3;">+ <?= number_format($p['puntos_necesarios'] * $p['car_cantidad']) ?> pts</span>
                                        <?php else: ?>
                                            $<?= number_format($p['subtotal'], 2) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        $<?= number_format($p['subtotal'], 2) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding: 40px; color:#aaa;">
                    <i class="fa-solid fa-cart-arrow-down fa-3x"></i>
                    <p style="margin-top:15px;">Tu carrito está vacío.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="order-1-mob">
        <div class="summary-sticky">
            <div class="chk-card">
                <div class="chk-header">
                    <div class="chk-icon-header"><i class="fa-solid fa-truck-fast"></i></div>
                    <div class="chk-title">
                        <h2>Envío y Pago</h2>
                        <p>Elige cómo quieres recibirlo</p>
                    </div>
                </div>

                <div class="delivery-options">
                    <div class="del-card active" onclick="setEntrega('RETIRO', this)">
                        <i class="fa-solid fa-check-circle check-mark"></i>
                        <i class="fa-solid fa-person-walking"></i>
                        <span>Retirar en Local</span>
                    </div>
                    <div class="del-card" onclick="setEntrega('DOMICILIO', this)">
                        <i class="fa-solid fa-check-circle check-mark"></i>
                        <i class="fa-solid fa-motorcycle"></i>
                        <span>A Domicilio</span>
                    </div>
                </div>

                <div id="panel-retiro" class="fade-in">
                    <div class="info-box">
                        <i class="fa-solid fa-circle-info"></i>
                        <div>
                            <strong>¿Cómo funciona?</strong><br>
                            Generaremos un Código QR. Acércate al local y muéstralo para recibir tu pedido.
                        </div>
                    </div>
                    
                    <button type="button" class="btn-gps" onclick="getGPS('RETIRO')">
                        <i class="fa-solid fa-location-crosshairs"></i> Usar mi ubicación para optimizar
                    </button>
                    <p id="msg-gps-retiro" style="font-size:0.8rem; color:#27ae60; text-align:center; display:none; margin-top:5px;"><i class="fa-solid fa-check"></i> Ubicación lista</p>
                </div>

                <div id="panel-domicilio" class="hidden fade-in">
                    <div class="info-box" style="background:#fff3e0; color:#e65100;">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <div>
                            <strong>Importante</strong><br>
                            Necesitamos tu ubicación exacta para el repartidor.
                        </div>
                    </div>

                    <button type="button" class="btn-gps" onclick="getGPS('DOMICILIO')">
                        <i class="fa-solid fa-location-dot"></i> Detectar mi Ubicación
                    </button>
                    
                    <div id="mapa-canvas"></div>
                    
                    <div class="input-group">
                        <i class="fa-solid fa-sign-hanging input-icon"></i>
                        <input type="text" id="txt-direccion" class="modern-input" placeholder="Calles principales (Ej: Av. Amazonas y Colón)">
                    </div>

                    <div class="input-group">
                        <i class="fa-solid fa-clipboard-list input-icon area"></i>
                        <textarea id="txt-referencia" class="modern-input" style="padding-top:15px; min-height:80px;" placeholder="Instrucciones (Ej: Casa blanca, timbre dañado...)"></textarea>
                    </div>
                </div>

                <div class="bill-total-section">
                    <div class="bill-row" style="margin-top:20px;">
                        <span>Subtotal Productos</span>
                        <span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="bill-row" id="row-shipping" style="display:none;">
                        <span>Costo de Envío</span>
                        <span id="lbl-shipping">$0.00</span>
                    </div>
                    <div class="bill-total">
                        <span style="font-size:1rem; font-weight:600; color:#888;">Total a Pagar</span>
                        <span id="lbl-total">
                            $<?= number_format($subtotal, 2) ?>
                            <?php if($totalPuntosPedido > 0): ?>
                                <span style="color:#0984e3; font-size:1rem;"> + <i class="fa-solid fa-coins"></i> <?= number_format($totalPuntosPedido) ?> pts</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div style="background:#f1f2f6; padding:12px; border-radius:10px; margin-top:15px; font-size:0.85rem; color:#666; display:flex; gap:10px; align-items:center;">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Pago en <strong>Efectivo</strong> al momento de la entrega.</span>
                </div>

                <button class="btn-confirmar" id="btn-submit" onclick="enviarPedido()">
                    <span>Confirmar Pedido</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>

                <div id="checkout-error"></div>

            </div>
        </div>
    </div>

</div>

<script>
    // --- CONFIGURACIÓN ---
    const API_URLS = {
        procesar: '<?= str_replace("+", "%2B", $urlProcesar) ?>',
        misPedidos: '<?= str_replace("+", "%2B", $urlMisPedidos) ?>',
        cotizar: '<?= str_replace("+", "%2B", $urlCotizar) ?>'
    };

    // Coordenadas por defecto (Solo para inicializar el mapa visualmente)
    const DEF_LAT = -0.180653;
    const DEF_LON = -78.467834;

    let entregaTipo = 'RETIRO';
    let coords = { lat: DEF_LAT, lon: DEF_LON }; 
    const baseSubtotal = <?= $subtotal ?>;
    
    // Bandera para saber si ya tenemos ubicación real confirmada
    let ubicacionConfirmada = false; 

    let mapaObj = null;
    let marcadorObj = null;

    // 1. CAMBIO DE PESTAÑA
    function setEntrega(tipo, el) {
        entregaTipo = tipo;
        ocultarError();

        // Visual
        document.querySelectorAll('.del-card').forEach(d => d.classList.remove('active'));
        el.classList.add('active');

        const panelRet = document.getElementById('panel-retiro');
        const panelDom = document.getElementById('panel-domicilio');
        const rowShip  = document.getElementById('row-shipping');

        if (tipo === 'RETIRO') {
            panelRet.classList.remove('hidden');
            panelDom.classList.add('hidden');
            rowShip.style.display = 'none';
            
            // Retiro siempre es gratis y válido
            updateTotal(0); 
            ubicacionConfirmada = true; 
        
        } else {
            panelRet.classList.add('hidden');
            panelDom.classList.remove('hidden');
            rowShip.style.display = 'flex';
            
            // AQUI EL CAMBIO: NO CALCULAMOS AÚN
            ubicacionConfirmada = false; // Reseteamos validez
            document.getElementById('lbl-shipping').innerHTML = '<small style="color:#e67e22">Defina Ubicación</small>';
            updateTotal(0); // Mantenemos el total base por ahora

            // Solo mostramos el mapa, no cotizamos
            setTimeout(() => { initMap(); }, 200);
        }
    }

    function updateTotal(costoEnvio) {
        const totalDinero = baseSubtotal + costoEnvio;
        const totalPuntos = <?= $totalPuntosPedido ?>; // Pasamos el valor desde PHP
        
        let html = '$' + totalDinero.toFixed(2);
        if(totalPuntos > 0) {
            html += ` <span style="color:#0984e3; font-size:1.1rem;"> + <i class="fa-solid fa-coins"></i> ${totalPuntos} pts</span>`;
        }
        document.getElementById('lbl-total').innerHTML = html;
    }

    // 2. INICIALIZAR MAPA
    function initMap() {
        if (mapaObj) { mapaObj.invalidateSize(); return; }
        
        mapaObj = L.map('mapa-canvas').setView([DEF_LAT, DEF_LON], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapaObj);
        
        marcadorObj = L.marker([DEF_LAT, DEF_LON], { draggable: true }).addTo(mapaObj);

        // EVENTO: SOLO COTIZAMOS CUANDO EL USUARIO MUEVE EL PIN
        marcadorObj.on('dragend', function(e) {
            const pos = marcadorObj.getLatLng();
            coords.lat = pos.lat;
            coords.lon = pos.lng;
            ocultarError();
            
            // ¡AHORA SÍ! EL USUARIO ELIGIÓ SITIO
            cotizarEnvio(pos.lat, pos.lng);
        });
    }

    // 3. COTIZAR (BACKEND)
    function cotizarEnvio(lat, lon) {
        document.getElementById('lbl-shipping').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';
        
        fetch(API_URLS.cotizar, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ lat: lat, lon: lon })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                const costo = parseFloat(res.costo);
                document.getElementById('lbl-shipping').innerText = '$' + costo.toFixed(2);
                updateTotal(costo);
                
                // Marcamos como válida la ubicación para dejar enviar
                ubicacionConfirmada = true; 
            } else {
                console.error("Error:", res.error);
                document.getElementById('lbl-shipping').innerText = 'Error';
            }
        })
        .catch(err => {
            console.error(err); 
            // Si falla por el error de HTML, avisamos
            document.getElementById('lbl-shipping').innerText = 'Error API';
        });
    }

    // 4. GPS
    function getGPS(origen) {
        if (!navigator.geolocation) { mostrarError("Navegador sin GPS."); return; }

        const btn = event.currentTarget;
        const txtOriginal = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> ...';
        btn.disabled = true;
        ocultarError();

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                coords.lat = pos.coords.latitude;
                coords.lon = pos.coords.longitude;

                btn.innerHTML = '<i class="fa-solid fa-check"></i> Listo';
                btn.classList.add('success');

                if (origen === 'DOMICILIO') {
                    if(mapaObj) {
                        const nuevaPos = new L.LatLng(coords.lat, coords.lon);
                        mapaObj.setView(nuevaPos, 16);
                        marcadorObj.setLatLng(nuevaPos);
                    }
                    // EL USUARIO USÓ GPS -> COTIZAMOS DE UNA VEZ
                    cotizarEnvio(coords.lat, coords.lon);
                } else {
                    document.getElementById('msg-gps-retiro').style.display = 'block';
                }
            },
            (err) => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = txtOriginal;
                mostrarError("No pudimos obtener tu ubicación.");
            },
            { enableHighAccuracy: true }
        );
    }

    // 5. ENVIAR PEDIDO
    function enviarPedido() {
        const btn = document.getElementById('btn-submit');
        const direccion = document.getElementById('txt-direccion').value.trim();
        const referencia = document.getElementById('txt-referencia').value.trim();
        
        ocultarError();

        // VALIDACIONES
        if (entregaTipo === 'DOMICILIO') {
            // Validamos nuestra bandera
            if (!ubicacionConfirmada) {
                mostrarError("⚠️ Por favor, mueve el pin en el mapa o usa el GPS para calcular el envío.");
                // Scroll al mapa
                document.getElementById('mapa-canvas').scrollIntoView({behavior: 'smooth', block: 'center'});
                return;
            }
            if (direccion.length < 5) {
                mostrarError("⚠️ Escribe una dirección de referencia.");
                document.getElementById('txt-direccion').focus();
                return;
            }
        }

        // ENVIAR
        btn.disabled = true;
        btn.innerHTML = '<span>Procesando...</span> <i class="fa-solid fa-circle-notch fa-spin"></i>';

        const datos = {
            tipo_entrega: entregaTipo,
            direccion: direccion,
            referencia: referencia,
            lat: coords.lat,
            lon: coords.lon
        };

        fetch(API_URLS.procesar, { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                window.location.href = API_URLS.misPedidos; 
            } else {
                mostrarError("Error: " + res.error);
                resetBtn();
            }
        })
        .catch(err => {
            console.error(err);
            mostrarError("Error de conexión.");
            resetBtn();
        });
    }

    function mostrarError(msg) {
        const caja = document.getElementById('checkout-error');
        caja.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
        caja.style.display = 'block';
    }
    function ocultarError() { document.getElementById('checkout-error').style.display = 'none'; }
    function resetBtn() {
        const btn = document.getElementById('btn-submit');
        btn.disabled = false;
        btn.innerHTML = '<span>Confirmar Pedido</span> <i class="fa-solid fa-arrow-right"></i>';
    }
</script>