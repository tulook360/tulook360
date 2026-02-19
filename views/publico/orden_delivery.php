<?php
// 1. CONFIGURACIÓN
$urlVolver = ruta_accion('publico', 'mis_pedidos');
$urlPolling = ruta_accion('publico', 'consultar_ubicacion_driver_ajax') . '&id=' . $orden['ord_id'];

$latCliente = $orden['ord_ubicacion_lat'];
$lonCliente = $orden['ord_ubicacion_lon'];

$qrData = $orden['ord_token_qr'];
$qrImagen = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&color=000000&bgcolor=ffffff&data=" . $qrData;
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
    /* 1. RESET */
    .page-content { padding: 0 !important; height: calc(100vh - 75px) !important; overflow: hidden !important; display: flex; flex-direction: column; position: relative; }
    :root { 
        --app-primary: #ff3366; --app-dark: #111827; --app-gray: #f9fafb; 
        --app-green: #10b981; --sidebar-w: 450px;
    }
    * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }

    /* LAYOUT */
    .delivery-ui { display: flex; width: 100%; height: 100%; background: #fff; position: relative; }
    #mainLayout { display: flex; width: 100%; height: 100%; background: #fff; opacity: 0; transition: opacity 0.8s ease; }
    #mainLayout.visible { opacity: 1; }

    /* PANEL IZQUIERDO */
    .info-panel { 
        width: var(--sidebar-w); 
        background: white; 
        display: flex; 
        flex-direction: column; 
        z-index: 20; 
        box-shadow: 5px 0 30px rgba(0,0,0,0.05); 
        border-right: 1px solid #f3f4f6; 
    }

    /* CORRECCIÓN 1: Header Flexible para evitar encimamiento */
    .panel-header { 
        padding: 80px 25px 25px 25px;
        border-bottom: 1px solid #f3f4f6; 
        background: #fff; 
        flex-shrink: 0;
        display: flex;         /* FLEX ADDED */
        flex-direction: column; /* VERTICAL STACK */
        gap: 15px;             /* ESPACIO ENTRE FILAS */
    }

    .header-top { display: flex; justify-content: space-between; align-items: center; }
    
    /* Botón volver oculto en panel (usaremos el flotante global) */
    .btn-back-panel { display: none; } 

    .price-pill { background: #fff1f2; color: var(--app-primary); padding: 5px 15px; border-radius: 20px; font-weight: 800; font-size: 1rem; align-self: flex-end; }
    
    .ord-title h1 { margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--app-dark); letter-spacing: -0.5px; }
    .ord-subtitle { font-size: 0.9rem; color: #6b7280; margin-top: 5px; font-weight: 500; }

    /* CORRECCIÓN 2: Altura automática para que no se corte */
    .action-row { 
        display: flex; 
        gap: 10px; 
        min-height: 70px; /* Mínimo, pero crece si hace falta */
        height: auto; 
    }
    
    .driver-card-compact { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; display: flex; align-items: center; gap: 12px; padding: 10px 15px; transition: 0.3s; }
    .driver-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex-shrink: 0; }
    .driver-info { display: flex; flex-direction: column; justify-content: center; overflow: hidden; }
    .driver-info h4 { margin: 0; font-size: 0.9rem; color: var(--app-dark); font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .driver-info span { font-size: 0.75rem; color: var(--app-green); font-weight: 600; text-transform: uppercase; }
    
    .btn-qr-square { width: 70px; background: var(--app-dark); border-radius: 14px; border: none; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; gap: 5px; transition: 0.2s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); flex-shrink: 0; padding: 10px 0; }
    .btn-qr-square:hover { transform: translateY(-2px); background: #000; }
    .btn-qr-square i { font-size: 1.5rem; }
    .btn-qr-square span { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
    .btn-qr-square.pulse { animation: pulse-black 2s infinite; border: 2px solid var(--app-primary); }
    @keyframes pulse-black { 0% { box-shadow: 0 0 0 0 rgba(255, 51, 102, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(255, 51, 102, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 51, 102, 0); } }

    /* LISTA */
    .panel-scroll { flex: 1; overflow-y: auto; padding: 25px; background: #fdfdfd; }
    .timeline-title { color: #9ca3af; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 1px; }
    .step-card { display: flex; gap: 15px; margin-bottom: 30px; position: relative; transition: all 0.5s ease; }
    .step-card::after { content: ''; position: absolute; left: 19px; top: 40px; bottom: -35px; width: 2px; background: #f3f4f6; z-index: 0; }
    .step-card:last-child::after { display: none; }
    .step-num { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; z-index: 1; background: #fff; border: 2px solid #e5e7eb; color: #9ca3af; transition: 0.3s; flex-shrink: 0; }
    .step-card.done .step-num { background: var(--app-green); border-color: var(--app-green); color: white; }
    .step-card.done { opacity: 0.6; }
    .step-content h3 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--app-dark); }
    .step-content ul { margin: 5px 0 0 0; padding-left: 0; list-style: none; font-size: 0.8rem; color: #6b7280; }
    .badge-qty { background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; }

    /* MAPA */
    .map-wrapper { 
        flex: 1; 
        position: relative; 
        background: #e5e7eb; 
        overflow: hidden;
        border-radius: 20px 0 0 0; /* Opcional: un borde redondeado para que se vea más moderno */
    }
    #mapa-full { width: 100%; height: 100%; opacity: 0; transition: opacity 0.5s ease; }
    #mapa-full.visible { opacity: 1; }

    /* RADAR FULL SCREEN */
    .full-screen-radar { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 50; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: opacity 0.6s ease, visibility 0.6s; }
    .full-screen-radar.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
    
    .radar-circle { position: relative; width: 140px; height: 140px; display: flex; align-items: center; justify-content: center; margin-bottom: 40px; }
    .radar-wave { position: absolute; border: 2px solid var(--app-primary); border-radius: 50%; width: 100%; height: 100%; opacity: 0; animation: wave 2.5s infinite; }
    .radar-wave:nth-child(2) { animation-delay: 0.8s; } .radar-wave:nth-child(3) { animation-delay: 1.6s; }
    @keyframes wave { 0% { transform: scale(0.5); opacity: 0; border-width: 4px; } 50% { opacity: 1; } 100% { transform: scale(2.5); opacity: 0; border-width: 0px; } }
    .radar-icon-box { width: 100px; height: 100px; background: var(--app-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; z-index: 2; box-shadow: 0 20px 40px rgba(255, 51, 102, 0.4); transition: transform 0.3s ease; }
    .status-text h2 { margin: 0 0 10px 0; font-size: 2rem; font-weight: 800; color: var(--app-dark); text-align: center; }
    .status-text p { margin: 0; font-size: 1.1rem; color: #6b7280; font-weight: 500; text-align: center; }
    .dots::after { content: ' .'; animation: dots 1.5s steps(5, end) infinite; }
    @keyframes dots { 0%, 20% { content: ' .'; } 40% { content: ' ..'; } 60% { content: ' ...'; } 80%, 100% { content: ''; } }

    /* CORRECCIÓN 3: BOTÓN VOLVER GLOBAL FLOTANTE */
    .btn-global-back { 
        position: fixed; 
        top: 100px; /* Más arriba */
        left: 20px; 
        z-index: 99999; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        padding: 10px 20px; 
        background: #ffffff; 
        border: 1px solid #e5e7eb; 
        border-radius: 50px; 
        text-decoration: none; 
        color: var(--app-dark); 
        font-weight: 700; 
        font-size: 0.9rem; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.2); /* Más sombra */
        transition: all 0.2s ease; 
    }
    .btn-global-back:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(255, 51, 102, 0.2); color: var(--app-primary); }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .page-content { height: 100vh !important; }
        #mainLayout { flex-direction: column; }
        
        /* Mapa ocupa el 50% superior */
        .map-wrapper { height: 50vh; order: 1; flex: none; width: 100%; z-index: 1; }
        
        /* Panel ocupa el 50% inferior */
        .info-panel { 
            width: 100%; 
            height: 50vh; 
            order: 2; 
            border-radius: 25px 25px 0 0; 
            margin-top: -25px; 
            box-shadow: 0 -10px 40px rgba(0,0,0,0.15); /* Sombra hacia arriba */
            border-right: none; 
            overflow: hidden;
            z-index: 20;
        }
        
        /* Ajuste de header */
        .panel-header { 
            padding: 20px 25px 10px 25px; 
        }
        
        /* Barra visual de arrastre */
        .panel-header::before {
            content: ''; display: block; width: 40px; height: 5px; 
            background: #e5e7eb; border-radius: 10px; margin: 0 auto 10px auto;
        }

        /* Ajuste botón volver en móvil */
        .btn-global-back { top: 90px; left: 10px; }
    }
    
    .leaflet-routing-container { display: none !important; }

    /* MODAL */
    .modal-overlay-custom { position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 10000; display: none; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; }
    .modal-overlay-custom.show { opacity: 1; display: flex; }
    .modal-box-custom { background: white; width: 90%; max-width: 320px; border-radius: 20px; padding: 30px; text-align: center; transform: translateY(20px); transition: 0.3s; }
    .modal-overlay-custom.show .modal-box-custom { transform: translateY(0); }
    .modal-qr-img { width: 200px; height: 200px; border: 5px solid white; border-radius: 15px; margin-bottom: 10px; }
    .btn-m { width: 100%; padding: 12px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; background: #f3f4f6; color: #555; }
    .btn-success { background: var(--app-green); color: white; margin-top: 10px; }
</style>

<a href="<?= $urlVolver ?>" class="btn-global-back">
    <i class="fa-solid fa-arrow-left"></i> Volver
</a>

<div class="full-screen-radar" id="fullScreenRadar">
    <div class="radar-circle">
        <div class="radar-wave"></div>
        <div class="radar-wave"></div>
        <div class="radar-wave"></div>
        <div class="radar-icon-box" id="radarIcon"><i class="fa-solid fa-store"></i></div>
    </div>
    <div class="status-text">
        <h2 id="radarTitle">Confirmando...</h2>
        <p id="radarDesc">La tienda está revisando tu orden<span class="dots"></span></p>
    </div>
</div>

<div id="mainLayout">
    <div class="info-panel">
        <div class="panel-header">
            <div class="header-top">
                <div class="ord-title">
                    <h1>Tu Pedido</h1>
                    <div class="ord-subtitle" id="lblEstadoCabecera">Buscando...</div>
                </div>
                <div class="price-pill">$<?= number_format($orden['ord_total'], 2) ?></div>
            </div>

            <div class="action-row">
                <div class="driver-card-compact" id="driverCard" style="display:none; opacity:0;">
                    <img src="" class="driver-avatar" id="imgChofer">
                    <div class="driver-info">
                        <h4 id="txtChofer">Asignando...</h4>
                        <span id="txtEstadoDriver">En camino</span>
                    </div>
                </div>
                <button class="btn-qr-square" id="btnQrMain" onclick="abrirQR()">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>QR</span>
                </button>
            </div>
        </div>

        <div class="panel-scroll">
            <div class="timeline-title">Seguimiento en Vivo</div>
            <div id="listaParadas"></div>
            
            <div class="step-card pending">
                <div class="step-num" style="background:var(--app-dark); border:none; color:white;"><i class="fa-solid fa-flag-checkered"></i></div>
                <div class="step-content">
                    <h3>Punto de Entrega</h3>
                    <p><?= $orden['ord_direccion_envio'] ?></p>
                    <?php if($orden['ord_referencia']): ?>
                        <ul style="margin:5px 0 0; padding:0; list-style:none; font-size:0.75rem; color:#f59e0b;">
                            <li><i class="fa-solid fa-circle-info"></i> <?= $orden['ord_referencia'] ?></li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="map-wrapper">
        <div id="mapa-full"></div>
    </div>
</div>

<div class="modal-overlay-custom" id="modalQr">
    <div class="modal-box-custom">
        <h3 style="margin-top:0; font-family:'Plus Jakarta Sans'; font-size:1.2rem;">Código de Retiro</h3>
        <img src="<?= $qrImagen ?>" class="modal-qr-img">
        <p style="color:#6b7280; font-size:0.9rem; margin-bottom:20px;">Muestra este código al repartidor.</p>
        <button class="btn-m" onclick="cerrarQR()">Cerrar</button>
    </div>
</div>

<div class="modal-overlay-custom" id="modalSuccess">
    <div class="modal-box-custom">
        <div style="width:80px; height:80px; background:#ecfdf5; color:var(--app-green); font-size:2.5rem; display:flex; align-items:center; justify-content:center; border-radius:50%; margin:0 auto 20px auto;">
            <i class="fa-solid fa-check"></i>
        </div>
        <h2 style="margin:0; font-family:'Plus Jakarta Sans'; color:var(--app-dark);">¡Entregado!</h2>
        <p style="color:#6b7280; margin:10px 0 25px 0;">Tu pedido ha sido completado con éxito.</p>
        <button class="btn-m" onclick="cerrarModalSuccess()">Ver Mapa</button>
        <a href="<?= $urlVolver ?>" class="btn-m btn-success" style="text-decoration:none; display:inline-block;">Salir y Calificar</a>
    </div>
</div>

<script>
    // --- DATOS INICIALES ---
    const URL_POLLING = '<?= $urlPolling ?>';
    const CLIENTE_LAT = <?= $latCliente ?>;
    const CLIENTE_LON = <?= $lonCliente ?>;
    const destinoFinal = L.latLng(CLIENTE_LAT, CLIENTE_LON);

    // --- CONFIGURACIÓN MAPA ---
    const map = L.map('mapa-full', { zoomControl: false }).setView([CLIENTE_LAT, CLIENTE_LON], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '' }).addTo(map);

    // --- ICONOS ---
    const iconStore = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/3082/3082383.png', iconSize: [35, 35], popupAnchor: [0, -15] });
    const iconClient = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/9356/9356230.png', iconSize: [40, 40], popupAnchor: [0, -20] });
    const iconCheck = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/190/190411.png', iconSize: [30, 30], popupAnchor: [0, -15] });
    const iconMoto = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/171/171253.png', iconSize: [45, 45], popupAnchor: [0, -20], className: 'moto-icon' });

    L.marker(destinoFinal, {icon: iconClient}).addTo(map).bindPopup("<b>Tú</b>");

    // --- VARIABLES DE ESTADO ---
    let markersTiendas = {};
    let markerMoto = null;
    let routingActiva = null; 
    let routingPlan = null;
    let mapaVisible = false; 
    let radarInterval = null; 
    let modalExitoMostrado = false;

    // --- RUTAS ---
    routingActiva = L.Routing.control({
        waypoints: [], createMarker: function() { return null; },
        lineOptions: { styles: [{color: '#10b981', opacity: 1, weight: 7}] },
        show: false, containerClassName: 'routing-hidden'
    }).addTo(map);

    routingPlan = L.Routing.control({
        waypoints: [], createMarker: function() { return null; },
        lineOptions: { styles: [{color: '#8b5cf6', opacity: 0.5, weight: 5, dashArray: '10,15'}] },
        show: false, containerClassName: 'routing-hidden'
    }).addTo(map);

    // --- LÓGICA DE ACTUALIZACIÓN ---
    function procesarDatosServidor(data) {
        const est = data.estado; 
        const radarFS = document.getElementById('fullScreenRadar');
        const mainLayout = document.getElementById('mainLayout');

        if (est === 'ENTREGADO' || est === 'COMPLETADO') {
            document.getElementById('lblEstadoCabecera').innerText = "Finalizado";
            
            if (!modalExitoMostrado) {
                radarFS.classList.add('hidden');
                mainLayout.classList.add('visible');
                document.getElementById('modalSuccess').classList.add('show');
                modalExitoMostrado = true; 
            }
            return;
        }

        if (est === 'PENDIENTE') {
            radarFS.classList.remove('hidden');
            mainLayout.classList.remove('visible');
            iniciarAnimacionRadar();
            document.getElementById('driverCard').style.display = 'none';
            mapaVisible = false;
            return;
        } 

        if (est === 'CANCELADO') {
            radarFS.classList.remove('hidden');
            mainLayout.classList.remove('visible');
            detenerAnimacionRadar();
            mostrarErrorRadar();
            return;
        }

        detenerAnimacionRadar();
        radarFS.classList.add('hidden');
        
        if(!mapaVisible) {
            mainLayout.classList.add('visible');
            setTimeout(() => { map.invalidateSize(); document.getElementById('mapa-full').classList.add('visible'); }, 600); 
            mapaVisible = true;
        }

        if(data.chofer) {
            const card = document.getElementById('driverCard');
            card.style.display = 'flex';
            setTimeout(() => card.style.opacity = '1', 50);
            
            document.getElementById('txtChofer').innerText = data.chofer;
            document.getElementById('imgChofer').src = data.foto_chofer || 'recursos/img/sin_foto.png';
            
            let textoEstado = "En camino";
            const todoRecogido = data.paradas.every(p => p.estado_parada === 'COMPLETADO');
            
            if(todoRecogido) {
                textoEstado = "Llevando a tu domicilio";
                document.getElementById('lblEstadoCabecera').innerText = "Último Tramo";
                document.getElementById('btnQrMain').classList.add('pulse');
            } else {
                document.getElementById('lblEstadoCabecera').innerText = est.replace('_', ' ');
            }
            document.getElementById('txtEstadoDriver').innerText = textoEstado;
        }

        try {
            actualizarMapa(data);
            renderizarLista(data.paradas);
        } catch (err) {
            console.warn("Error pintando mapa:", err);
        }
    }

    // --- FUNCIONES DE MAPA ---
    function actualizarMapa(data) {
        let posicionChofer = null;
        if (data.lat_driver && data.lon_driver) {
            posicionChofer = L.latLng(data.lat_driver, data.lon_driver);
        }

        let paradas = data.paradas || [];
        let pendientes = [];
        let ultimaTiendaCompletada = null;

        paradas.forEach(p => {
            const lat = parseFloat(p.info.lat);
            const lon = parseFloat(p.info.lon);
            const sid = p.info.suc_id;
            const isDone = (p.estado_parada === 'COMPLETADO');

            if (!markersTiendas[sid]) { 
                markersTiendas[sid] = L.marker([lat, lon], { icon: iconStore }).addTo(map).bindPopup(p.info.nombre); 
            }
            markersTiendas[sid].setIcon(isDone ? iconCheck : iconStore);
            
            if(isDone) {
                ultimaTiendaCompletada = L.latLng(lat, lon);
            } else {
                pendientes.push(p);
            }
        });

        if (ultimaTiendaCompletada) {
            posicionChofer = ultimaTiendaCompletada;
        }

        if (posicionChofer) {
            if (markerMoto) markerMoto.setLatLng(posicionChofer);
            else markerMoto = L.marker(posicionChofer, {icon: iconMoto, zIndexOffset: 1000}).addTo(map);

            let siguienteDestino = null;
            let puntosFuturos = [];

            if (pendientes.length > 0) {
                pendientes.sort((a, b) => {
                    const distA = posicionChofer.distanceTo(L.latLng(a.info.lat, a.info.lon));
                    const distB = posicionChofer.distanceTo(L.latLng(b.info.lat, b.info.lon));
                    return distA - distB;
                });
                siguienteDestino = L.latLng(pendientes[0].info.lat, pendientes[0].info.lon);
                
                for(let k=1; k<pendientes.length; k++) { 
                    puntosFuturos.push(L.latLng(pendientes[k].info.lat, pendientes[k].info.lon)); 
                }
            } else {
                siguienteDestino = destinoFinal;
            }
            puntosFuturos.push(destinoFinal);

            routingActiva.setWaypoints([posicionChofer, siguienteDestino]);
            puntosFuturos.unshift(siguienteDestino);
            routingPlan.setWaypoints(puntosFuturos);
        }
    }

    function renderizarLista(paradas) {
        const container = document.getElementById('listaParadas');
        container.innerHTML = '';
        let i = 1;
        paradas.forEach(p => {
            const isDone = (p.estado_parada === 'COMPLETADO');
            const cardClass = isDone ? 'done' : 'pending';
            const numIcon = isDone ? '<i class="fa-solid fa-check"></i>' : i;
            let prods = '';
            if(p.productos) { p.productos.forEach(pr => { prods += `<li><span class="badge-qty">${pr.cantidad}x</span> ${pr.nombre}</li>`; }); }
            const html = `
                <div class="step-card ${cardClass}">
                    <div class="step-num">${numIcon}</div>
                    <div class="step-content">
                        <h3>${p.info.nombre}</h3>
                        <ul>${prods}</ul>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            i++;
        });
    }

    // --- ANIMACIONES RADAR ---
    const radarStates = [{t:"Confirmando...",d:"La tienda está revisando..."},{t:"Preparando...",d:"Empacando productos..."},{t:"Buscando...",d:"Contactando conductores..."}];
    let radarIdx = 0;
    function iniciarAnimacionRadar() {
        if(radarInterval) return;
        radarInterval = setInterval(() => {
            radarIdx = (radarIdx + 1) % radarStates.length;
            document.getElementById('radarTitle').innerText = radarStates[radarIdx].t;
            document.getElementById('radarDesc').innerText = radarStates[radarIdx].d;
        }, 3500);
    }
    function detenerAnimacionRadar() { if(radarInterval){clearInterval(radarInterval); radarInterval=null;} }
    
    function mostrarErrorRadar() {
        document.getElementById('radarTitle').innerText = "Cancelado";
        document.getElementById('radarTitle').style.color = "#ef4444";
        document.getElementById('radarDesc').innerText = "Pedido cancelado.";
        document.getElementById('radarIcon').innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
        document.getElementById('radarIcon').style.background = "#ef4444";
    }

    // --- MODALES ---
    function abrirQR() { document.getElementById('modalQr').classList.add('show'); }
    function cerrarQR() { document.getElementById('modalQr').classList.remove('show'); }
    function cerrarModalSuccess() { document.getElementById('modalSuccess').classList.remove('show'); }

    // --- POLLING PRINCIPAL ---
    iniciarAnimacionRadar();

    setInterval(() => {
        fetch(URL_POLLING)
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    procesarDatosServidor(data);
                }
            })
            .catch(err => console.log("Polling error (ignorable):", err));
    }, 2500);

</script>