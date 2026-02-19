<?php
// views/repartidor/ruta.php

// 1. DATOS
$urlVolver = ruta_accion('Auth', 'panel'); 
$urlAccion = ruta_accion('repartidor', 'cambiar_estado_ruta_ajax');
$urlEstado = ruta_accion('repartidor', 'consultar_estado_orden_ajax');

$latDestino = $orden['ord_ubicacion_lat'];
$lonDestino = $orden['ord_ubicacion_lon'];

// QR
$qrData = $orden['ord_token_qr'];
$qrImagen = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&color=000000&bgcolor=ffffff&data=" . $qrData;

// 2. AGRUPAR
$paradas = [];
$todoRecogido = true; 
foreach ($detalles as $d) {
    $idSuc = $d['suc_id'];
    if (!isset($paradas[$idSuc])) {
        $paradas[$idSuc] = [
            'info' => ['suc_id'=>$d['suc_id'], 'nombre'=>$d['suc_nombre'], 'direccion'=>$d['suc_direccion'], 'lat'=>$d['suc_latitud'], 'lon'=>$d['suc_longitud']],
            'productos' => [],
            'estado_parada' => 'COMPLETADO' 
        ];
    }
    if ($d['odet_estado'] !== 'RECOGIDO') {
        $paradas[$idSuc]['estado_parada'] = 'PENDIENTE';
        $todoRecogido = false; 
    }
    $paradas[$idSuc]['productos'][] = $d;
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

<style>
    /* 1. RESET ESTRUCTURAL (PC FULL SCREEN) */
    .page-content {
        padding: 0 !important;
        height: calc(100vh - 75px) !important;
        overflow: hidden !important;
        display: flex;
        flex-direction: column;
    }

    :root {
        --d-primary: #ff3366; 
        --d-dark: #2d3436; 
        --d-gray: #f8f9fc; 
        --d-green: #2ecc71; 
        --d-sidebar-w: 450px;
    }

    /* 2. LAYOUT PC (Panel Izquierda | Mapa Derecha) */
    .split-screen { 
        display: flex; 
        width: 100%; 
        height: 100%; 
        font-family: 'Outfit', sans-serif; 
        background: #fff;
    }

    /* PANEL IZQUIERDO */
    .order-info-panel { 
        width: var(--d-sidebar-w); 
        background: white; 
        display: flex; 
        flex-direction: column; 
        z-index: 20; 
        box-shadow: 5px 0 30px rgba(0,0,0,0.05); 
        border-right: 1px solid #eee;
        height: 100%;
    }

    .panel-header { 
        padding: 25px; 
        border-bottom: 1px solid #f0f0f0; 
        background: #fff;
        flex-shrink: 0;
    }
    
    .btn-back-link {
        display: inline-flex; align-items: center; gap: 8px;
        color: #888; text-decoration: none; font-size: 0.9rem; font-weight: 500;
        margin-bottom: 10px; transition: 0.2s;
    }
    .btn-back-link:hover { color: var(--d-primary); transform: translateX(-3px); }

    .ord-title h1 { margin: 0; font-size: 1.5rem; font-weight: 900; color: var(--d-dark); }
    .ord-subtitle { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; }
    .status-tag { background: #e6fcf5; color: var(--d-green); padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
    .price-tag { font-size: 1.1rem; font-weight: 800; color: var(--d-primary); }

    /* ZONA QR FIJA */
    .qr-fixed-zone {
        padding: 20px 25px 0 25px; 
        background: #fff; 
        flex-shrink: 0;
        z-index: 10;
    }

    /* CONTENIDO SCROLLABLE (Solo la lista) */
    .panel-scroll { 
        flex: 1; 
        overflow-y: auto; 
        padding: 10px 25px 25px 25px; 
        background: #fdfdfd;
    }

    /* TARJETAS */
    .qr-banner {
        background: var(--d-dark); color: white;
        padding: 15px 20px; border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); 
        margin-bottom: 5px;
    }
    .qr-content { display: flex; align-items: center; gap: 15px; }
    .qr-icon { font-size: 1.5rem; }
    .qr-text h4 { margin: 0; font-size: 0.95rem; font-weight: 700; }
    .qr-text p { margin: 2px 0 0; font-size: 0.75rem; opacity: 0.8; }

    .timeline-title { color: #aaa; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin: 15px 0; letter-spacing: 1px; }

    /* --- AGREGAR ESTO EN TU CSS --- */
    .step-card {
        display: flex; 
        gap: 15px; 
        margin-bottom: 25px; 
        position: relative;
        /* LA MAGIA: Transición suave de 0.5 segundos para todo */
        transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* Cuando se completa, se hace un poco transparente y cambia de borde suavemente */
    .step-card.done { 
        border-left: 4px solid var(--tl-green); 
        background-color: #f8fffb; /* Un fondo verde muy muy bajito */
        opacity: 0.6; 
    }
    
    /* Animación para el icono cuando cambia */
    .st-icon {
        transition: background-color 0.4s ease, color 0.4s ease, transform 0.4s ease;
    }
    .step-card.done .st-icon {
        transform: scale(1.1); /* Un pequeño latido al completarse */
    }
    .step-card::after { 
        content: ''; position: absolute; left: 19px; top: 40px; bottom: -30px; 
        width: 2px; background: #eee; z-index: 0; 
    }
    .step-card:last-child::after { display: none; }

    .step-num { 
        width: 40px; height: 40px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-weight: 800; font-size: 1rem; z-index: 1; flex-shrink: 0;
        background: #f1f2f6; color: #888; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .step-card.done .step-num { background: var(--d-green); color: white; }
    .step-card.pending .step-num { background: var(--d-primary); color: white; }

    .step-info { flex: 1; background: white; border: 1px solid #eee; padding: 15px; border-radius: 12px; }
    .step-info h3 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--d-dark); }
    .step-info p { margin: 3px 0 8px 0; color: #888; font-size: 0.8rem; }

    .prod-list { margin: 0; padding: 0; list-style: none; border-top: 1px dashed #eee; padding-top: 8px; }
    .prod-list li { display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px; color: #555; }
    .money-pill { background: #fff0f3; color: var(--d-primary); padding: 1px 6px; border-radius: 4px; font-weight: 700; font-size: 0.75rem; }

    /* FOOTER PC (Vertical) */
    .panel-footer { 
        padding: 20px 25px; 
        background: white; 
        border-top: 1px solid #f0f0f0; 
        flex-shrink: 0;
    }
    
    .btn-main {
        width: 100%; padding: 15px; border-radius: 12px; border: none;
        background: linear-gradient(135deg, var(--d-primary) 0%, #ff6b81 100%);
        color: white; font-weight: 800; font-size: 1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        box-shadow: 0 8px 20px rgba(255, 51, 102, 0.3); transition: 0.2s;
    }
    
    .btn-cancel {
        width: 100%; margin-top: 10px; padding: 10px;
        background: transparent; border: none; 
        color: #e17055; font-weight: 600; font-size: 0.85rem; 
        text-decoration: underline; cursor: pointer;
    }

    /* MAPA DERECHO */
    .order-map-panel { flex: 1; position: relative; background: #eee; }
    #mapa-full { width: 100%; height: 100%; }

    /* MODALES */
    .modal-overlay-custom {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.85); z-index: 3000;
        display: none; align-items: center; justify-content: center;
    }
    .modal-overlay-custom.show { display: flex; }
    .modal-box-custom { background: white; width: 90%; max-width: 350px; border-radius: 20px; padding: 30px; text-align: center; }
    .modal-qr-img { width: 220px; height: 220px; border: 5px solid white; border-radius: 15px; margin-bottom: 15px; }
    .modal-btns { display: flex; gap: 10px; margin-top: 20px; }
    .btn-m { flex: 1; padding: 12px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; }
    .btn-m.sec { background: #f1f2f6; color: #555; }
    .btn-m.danger { background: #e17055; color: white; }
    .btn-m.success { background: var(--d-green); color: white; }

    /* --- RESPONSIVE MÓVIL (CORREGIDO) --- */
    @media (max-width: 900px) {
        .page-content { height: 100vh !important; }
        .split-screen { flex-direction: column; }
        
        /* Mapa arriba */
        .order-map-panel { height: 30vh; order: 1; flex: none; }
        
        /* Panel hoja deslizante (ALTO: 70vh para ver bien el contenido) */
        .order-info-panel { 
            width: 100%; 
            height: 66vh; /* Subimos la altura para ver botones */
            order: 2; 
            border-radius: 25px 25px 0 0; 
            border-right: none;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.15);
            margin-top: -20px; 
            display: flex; flex-direction: column; overflow: hidden;
        }
        
        .panel-header { padding-top: 20px; flex-shrink: 0; }
        .panel-header::before { 
            content: ''; display: block; width: 40px; height: 5px; 
            background: #eee; border-radius: 10px; margin: 0 auto 15px auto; 
        }

        /* --- BOTONES LADO A LADO EN MÓVIL --- */
        .panel-footer {
            display: flex !important;
            gap: 10px;
            align-items: center;
            padding: 15px 20px 30px 20px; /* Padding extra abajo para móviles */
        }
        .btn-main {
            flex: 2; /* Botón verde grande */
            width: auto; margin: 0; padding: 12px;
            font-size: 0.9rem;
        }
        .btn-cancel {
            flex: 1; /* Botón rojo pequeño */
            width: auto; margin: 0; padding: 12px;
            background: #fff5f5; border: 1px solid #ffdcdc; border-radius: 12px;
            text-decoration: none; font-size: 0.8rem;
            display: flex; align-items: center; justify-content: center;
            height: 48px; /* Misma altura que el otro */
        }
    }
    
    .leaflet-routing-container { display: none !important; }


    /* PANTALLA DE CARGA DEL MAPA */
    .map-loader {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 2000; /* Por encima del mapa */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: opacity 0.5s ease;
    }
    .loader-spinner {
        width: 50px; height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid var(--d-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }
    .loader-text {
        font-weight: 700;
        color: var(--d-dark);
        font-size: 1.1rem;
        animation: pulse 1.5s infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes pulse { 0% { opacity: 0.6; } 50% { opacity: 1; } 100% { opacity: 0.6; } }
    
    /* Clase para ocultarlo */
    .map-loader.hidden {
        opacity: 0;
        pointer-events: none; /* Permite hacer clic en el mapa aunque esté transparente */
    }
</style>

<div class="split-screen">

    <div class="order-info-panel">
        
        <div class="panel-header">
            <a href="<?= $urlVolver ?>" class="btn-back-link"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            <div class="ord-title">
                <h1>Orden #<?= $orden['ord_codigo'] ?></h1>
                <div class="ord-subtitle">
                    <span class="status-tag">En Curso</span>
                    <span class="price-tag">Ganancia: $<?= number_format($orden['ord_costo_envio'], 2) ?></span>
                </div>
            </div>
        </div>

        <div class="qr-fixed-zone">
            <div id="btnQrContainer" style="<?= $todoRecogido ? 'display:none' : '' ?>">
                <div class="qr-banner" onclick="abrirModal('qr')" style="margin-bottom: 0;">
                    <div class="qr-content">
                        <div class="qr-icon"><i class="fa-solid fa-qrcode"></i></div>
                        <div class="qr-text">
                            <h4>Código de Retiro</h4>
                            <p>Toca para mostrar en caja</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <div class="panel-scroll">
            <div class="timeline-title" style="margin-top: 15px;">RUTA DE ENTREGA</div>
            <div id="listaParadas">
                </div>
        </div>

        <div class="panel-footer" id="footerActions">
            </div>

    </div>

    <div class="order-map-panel">
        
        <div id="loadingMap" class="map-loader">
            <div class="loader-spinner"></div>
            <div class="loader-text">Calculando la mejor ruta... 🛵</div>
        </div>
        <div id="mapa-full"></div>
    </div>

</div>

<div class="modal-overlay-custom" id="modalQr">
    <div class="modal-box-custom" style="background:#2d3436; color:white;">
        <h3 style="margin-top:0; font-family:'Kalam';">Código de Retiro</h3>
        <img src="<?= $qrImagen ?>" class="modal-qr-img">
        <p style="color:#aaa; margin-bottom:20px;">Orden #<?= $orden['ord_codigo'] ?></p>
        <button class="btn-m sec" style="width:100%; color:#2d3436;" onclick="cerrarModales()">Cerrar</button>
    </div>
</div>

<div class="modal-overlay-custom" id="modalConfirm">
    <div class="modal-box-custom">
        <i class="fa-solid fa-circle-question" style="font-size:3rem; color:#2d3436; margin-bottom:15px;"></i>
        <h3 id="confirmTitle" style="margin:0 0 10px 0;">¿Estás seguro?</h3>
        <p id="confirmText" style="color:#666; margin-bottom:25px;">...</p>
        <div class="modal-btns">
            <button class="btn-m sec" onclick="cerrarModales()">Volver</button>
            <button class="btn-m" id="btnConfirmAction">Confirmar</button>
        </div>
    </div>
</div>

<div class="modal-overlay-custom" id="modalScanner">
    <div class="modal-box-custom">
        <h3 style="margin:0 0 10px 0;">Escanear QR del Cliente</h3>
        <p style="color:#666; font-size:0.8rem; margin-bottom:15px;">Apunta a la pantalla del cliente</p>
        <div id="reader" style="width: 100%; border-radius: 10px; overflow: hidden;"></div>
        <div id="scannerMsg" style="margin-top:15px; font-weight:700; font-size:0.9rem; min-height:20px; transition: 0.3s;"></div>
        <button class="btn-m sec" style="margin-top:15px;" onclick="cerrarScanner()">Cancelar</button>
    </div>
</div>

<script>
    // --- 1. VARIABLES Y DATOS ---
    const ORDEN_ID = <?= $orden['ord_id'] ?>;
    const URL_ACCION = '<?= $urlAccion ?>';
    const URL_ESTADO = '<?= $urlEstado ?>';
    const URL_GPS = '<?= ruta_accion("repartidor", "actualizar_ubicacion_ajax") ?>';
    const URL_VOLVER = '<?= $urlVolver ?>';

    let qrValidado = false; 
    let html5QrCode;
    
    const CLIENTE_LAT = <?= $latDestino ?: 0 ?>;
    const CLIENTE_LON = <?= $lonDestino ?: 0 ?>;
    const destinoFinal = L.latLng(CLIENTE_LAT, CLIENTE_LON);

    // Datos Locales
    let paradasLogica = <?= json_encode(array_values($paradas)) ?>;
    let rutaYaOptimizada = false;
    let ubicacionSimulada = null; 


    // 1. Variable global para rastrear la ruta activa
    let routingControlGlobal = null;

    // --- 2. MAPA ---
    const map = L.map('mapa-full', { zoomControl: false }).setView([CLIENTE_LAT, CLIENTE_LON], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    // Iconos
    const iconStore = L.icon({
        // PHP generará la URL correcta de Render automáticamente
        iconUrl: '<?= asset("recursos/img/icono_sucursal.png") ?>', 
        iconSize: [45, 45], 
        popupAnchor: [0, -22],
        className: 'sucursal-marker' // Opcional, por si quieres darle estilos CSS luego
    });
    const iconClient = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/9356/9356230.png', iconSize: [40, 40], popupAnchor: [0, -20] });
    const iconCheck = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/190/190411.png', iconSize: [30, 30], popupAnchor: [0, -15] });
    const iconMoto = L.icon({
        // Usamos el helper asset de PHP para generar la ruta correcta
        iconUrl: '<?= asset("recursos/img/moto_reparto.png") ?>', 
        iconSize: [50, 50], // Puedes hacerlo un poco más grande si quieres
        popupAnchor: [0, -25],
        className: 'moto-marker'
    });

    L.marker(destinoFinal, {icon: iconClient}).addTo(map).bindPopup("<b>Cliente</b><br>Punto de Entrega");

    let markersTiendas = {}; 
    let marcadorMoto = null;
    let routingPlan = null;   
    let routingActiva = null; 

    // --- 3. INICIALIZACIÓN ---
    dibujarMarcadoresTiendas();
    actualizarLineaFutura();

    routingActiva = L.Routing.control({
        waypoints: [],
        routeWhileDragging: false, addWaypoints: false, draggableWaypoints: false,
        createMarker: function() { return null; },
        lineOptions: { styles: [{color: '#2ecc71', opacity: 1, weight: 7}] }, // VERDE SÓLIDO
        show: false, containerClassName: 'routing-hidden'
    }).addTo(map);

    routingActiva.on('routesfound', function(e) {
        const loader = document.getElementById('loadingMap');
        if (loader) loader.classList.add('hidden');
    });

    // --- 4. FUNCIONES MAPA ---
    function dibujarMarcadoresTiendas() {
        for (let key in markersTiendas) { map.removeLayer(markersTiendas[key]); }
        markersTiendas = {};

        paradasLogica.forEach(p => {
            let lat = parseFloat(p.info.lat);
            let lon = parseFloat(p.info.lon);
            let isDone = (p.estado_parada === 'COMPLETADO');
            markersTiendas[p.info.suc_id] = L.marker([lat, lon], { icon: isDone ? iconCheck : iconStore }).addTo(map);
        });
    }

    

    function actualizarLineaFutura(lat1, lng1, lat2, lng2) {
        // 2. LIMPIEZA PREVIA (El Parche)
        // Si ya existe un control, lo removemos del mapa antes de crear el nuevo
        if (routingControlGlobal !== null) {
            try {
                // Usamos removeControl para limpiar todo el objeto
                map.removeControl(routingControlGlobal);
            } catch (e) {
                // Si falla, es porque Leaflet ya lo borró internamente
                console.warn("No se pudo remover el control viejo, continuando...");
            }
            routingControlGlobal = null; // Reset de la variable
        }

        // 3. CREACIÓN SEGURA
        routingControlGlobal = L.Routing.control({
            waypoints: [
                L.latLng(lat1, lng1),
                L.latLng(lat2, lng2)
            ],
            // Importante: No mostrar el panel de instrucciones para ahorrar recursos
            show: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: false,
            lineOptions: {
                styles: [{ color: '#ff4757', weight: 5, opacity: 0.7 }]
            },
            // Usar un manejador de errores para que el sistema no se caiga
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1' 
            })
        }).on('routingerror', function(e) {
            console.error("Error de ruta: Servidor OSRM saturado.");
        }).addTo(map);
    }

    function actualizarVisualizacion(ubicacionActual) {
        // Mover moto
        if (marcadorMoto) marcadorMoto.setLatLng(ubicacionActual);
        else marcadorMoto = L.marker(ubicacionActual, {icon: iconMoto, zIndexOffset: 1000}).addTo(map);

        // Calcular siguiente punto VERDE (desde donde esté la moto)
        let siguienteDestino = null;
        for (let i = 0; i < paradasLogica.length; i++) {
            if (paradasLogica[i].estado_parada !== 'COMPLETADO') {
                siguienteDestino = L.latLng(paradasLogica[i].info.lat, paradasLogica[i].info.lon);
                break; 
            }
        }
        if (!siguienteDestino) siguienteDestino = destinoFinal;
        
        // Trazar línea verde
        routingActiva.setWaypoints([ubicacionActual, siguienteDestino]);
    }

    // --- 5. GPS Y ORDENAMIENTO ---
    function activarGPS() {
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition(
                (position) => {
                    const realLat = position.coords.latitude;
                    const realLon = position.coords.longitude;
                    
                    // Solo ordenamos la primera vez
                    if (!rutaYaOptimizada) optimizarOrdenParadas(realLat, realLon);
                    
                    enviarUbicacionServidor(realLat, realLon);

                    // LÓGICA DE SIMULACIÓN:
                    // Si ya hay una "ubicación simulada" (tienda completada), usamos esa.
                    // Si NO (estamos empezando), usamos el GPS real.
                    if (ubicacionSimulada) {
                        actualizarVisualizacion(ubicacionSimulada);
                    } else {
                        actualizarVisualizacion(L.latLng(realLat, realLon));
                    }
                },
                (error) => console.warn("GPS:", error.message),
                { enableHighAccuracy: true, maximumAge: 0 }
            );
        }
    }

    function optimizarOrdenParadas(lat, lon) {
        const miUbicacion = L.latLng(lat, lon);
        paradasLogica.sort((a, b) => {
            const distA = miUbicacion.distanceTo(L.latLng(a.info.lat, a.info.lon));
            const distB = miUbicacion.distanceTo(L.latLng(b.info.lat, b.info.lon));
            return distA - distB;
        });
        
        actualizarListaHTML(paradasLogica, <?= $todoRecogido ? 'true' : 'false' ?>);
        actualizarLineaFutura();
        rutaYaOptimizada = true;
        
        // Revisar inmediatamente si ya hay tiendas completadas para saltar
        recalcularPosicionSimulada();
    }

    // --- 6. SINCRONIZACIÓN Y SIMULACIÓN (CORREGIDO) ---
    function recalcularPosicionSimulada() {
        // Recorremos LA LISTA ORDENADA (paradasLogica) para ver cuál es la última completada
        let ultimaCompletada = null;
        
        for (let i = 0; i < paradasLogica.length; i++) {
            if (paradasLogica[i].estado_parada === 'COMPLETADO') {
                ultimaCompletada = L.latLng(
                    parseFloat(paradasLogica[i].info.lat), 
                    parseFloat(paradasLogica[i].info.lon)
                );
            }
        }

        // Si encontramos una, actualizamos la moto
        if (ultimaCompletada) {
            ubicacionSimulada = ultimaCompletada;
            actualizarVisualizacion(ubicacionSimulada);
        }
    }

    function verificarSimulacionYUI(dataServer) {
        let huboCambios = false;

        if(dataServer.paradas) {
            dataServer.paradas.forEach(pServer => {
                let pLocal = paradasLogica.find(p => p.info.suc_id == pServer.info.suc_id);
                
                if (pLocal && pLocal.estado_parada !== pServer.estado_parada) {
                    // Detectamos cambio
                    pLocal.estado_parada = pServer.estado_parada;
                    huboCambios = true;

                    // Actualizar UI delicadamente
                    actualizarTarjetaIndividual(pLocal);

                    // Actualizar icono tienda
                    if(pServer.estado_parada === 'COMPLETADO') {
                        if(markersTiendas[pServer.info.suc_id]) {
                            markersTiendas[pServer.info.suc_id].setIcon(iconCheck);
                        }
                    }
                }
            });
        }

        if (huboCambios) {
            // Si algo cambió, recalculamos dónde debe estar la bici
            recalcularPosicionSimulada();
            actualizarBotones(dataServer.todo_recogido);
        }
    }

    // Loop Polling
    setInterval(() => {
        if(document.querySelector('.modal-overlay-custom.show')) return;
        fetch(URL_ESTADO, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ord_id: ORDEN_ID })
        }).then(r => r.json()).then(data => {
            if(!data.success) return;
            if(data.estado_orden === 'ENTREGADO' || data.estado_orden === 'PENDIENTE') {
                window.location.href = URL_VOLVER; return;
            }
            verificarSimulacionYUI(data);
        });
    }, 2500); 

    // --- 7. DOM Y UI ---
    actualizarListaHTML(paradasLogica, <?= $todoRecogido ? 'true' : 'false' ?>);

    function actualizarListaHTML(lista, todoRecogido) {
        const container = document.getElementById('listaParadas');
        let html = '';
        let i = 1;
        lista.forEach(p => {
            const isDone = (p.estado_parada === 'COMPLETADO');
            let prodHtml = '';
            p.productos.forEach(prod => {
                prodHtml += `<li><span>${prod.odet_cantidad}x ${prod.pro_nombre}</span><span class="money-pill">$${parseFloat(prod.odet_subtotal).toFixed(2)}</span></li>`;
            });
            const cardClass = isDone ? 'done' : 'pending';
            const numIcon = isDone ? '<i class="fa-solid fa-check"></i>' : i;
            
            html += `<div class="step-card ${cardClass}" id="step-card-${p.info.suc_id}">
                        <div class="step-num" id="step-icon-${p.info.suc_id}">${numIcon}</div>
                        <div class="step-info">
                            <h3>${p.info.nombre}</h3>
                            <p>${p.info.direccion}</p>
                            <ul class="prod-list">${prodHtml}</ul>
                        </div>
                    </div>`;
            i++;
        });
        
        html += `<div class="step-card pending">
                    <div class="step-num" style="background:#2d3436; color:white; border:none;"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="step-info">
                        <h3>Punto de Entrega</h3>
                        <p>${'<?= $orden['ord_direccion_envio'] ?>'}</p>
                    </div>
                </div>`;
        container.innerHTML = html;
        actualizarBotones(todoRecogido);
    }

    function actualizarTarjetaIndividual(parada) {
        const card = document.getElementById('step-card-' + parada.info.suc_id);
        const iconDiv = document.getElementById('step-icon-' + parada.info.suc_id);
        
        if (card && parada.estado_parada === 'COMPLETADO') {
            card.classList.remove('pending');
            card.classList.add('done');
            if(iconDiv) {
                iconDiv.style.background = 'var(--d-green)';
                iconDiv.style.color = 'white';
                iconDiv.innerHTML = '<i class="fa-solid fa-check"></i>';
            }
        }
    }


    function actualizarBotones(todoRecogido) {
        const qrDiv = document.getElementById('btnQrContainer');
        if(qrDiv) qrDiv.style.display = todoRecogido ? 'none' : 'block';
        
        const footer = document.getElementById('footerActions');
        const btnCancel = `<button class="btn-cancel" onclick="abrirModal('cancelar')">Cancelar / Ayuda</button>`;
        
        if (qrValidado) {
            // Si ya escaneó con éxito, aparece el botón FINALIZAR
            footer.innerHTML = `<button class="btn-main" onclick="abrirModal('entregar')">FINALIZAR ENTREGA <i class="fa-solid fa-check"></i></button>${btnCancel}`;
        } else {
            // Si no ha validado, el botón dice Escanear QR
            // Lo dejamos habilitado solo si ya recogió todo (todoRecogido)
            let disabled = todoRecogido ? '' : 'disabled style="background:#ccc"';
            footer.innerHTML = `<button class="btn-main" ${disabled} onclick="abrirScanner()">ESCANEAR QR CLIENTE <i class="fa-solid fa-camera"></i></button>${btnCancel}`;
        }
    }

    // Funciones base
    let ultimoEnvioGPS = 0;
    function enviarUbicacionServidor(lat, lon) {
        const ahora = Date.now();
        if (ahora - ultimoEnvioGPS < 5000) return; 
        fetch(URL_GPS, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ord_id: ORDEN_ID, lat: lat, lon: lon })
        }).then(r=>r.json()).then(d=>{ if(d.success) ultimoEnvioGPS = ahora; }).catch(console.error);
    }

    function abrirModal(tipo) {
        if(tipo === 'qr') document.getElementById('modalQr').classList.add('show');
        else if (tipo === 'entregar') configurarModal('ENTREGAR', '¿Entrega Completada?', 'Confirma entrega final.', 'success');
        else if (tipo === 'cancelar') configurarModal('CANCELAR', '¿Cancelar Carrera?', 'Se liberará la orden.', 'danger');
    }
    function configurarModal(accion, titulo, texto, claseBtn) {
        document.getElementById('confirmTitle').innerText = titulo;
        document.getElementById('confirmText').innerText = texto;
        const btn = document.getElementById('btnConfirmAction');
        btn.className = 'btn-m ' + claseBtn;
        btn.onclick = () => enviarAccion(accion);
        document.getElementById('modalConfirm').classList.add('show');
    }
    function cerrarModales() { document.querySelectorAll('.modal-overlay-custom').forEach(m => m.classList.remove('show')); }
    function enviarAccion(accion) {
        const btn = document.getElementById('btnConfirmAction'); btn.disabled = true;
        fetch(URL_ACCION, {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ accion: accion, ord_id: ORDEN_ID })
        }).then(r => r.json()).then(res => {
            if(res.success) window.location.href = URL_VOLVER;
            else { alert(res.error); cerrarModales(); btn.disabled = false; }
        });
    }

    activarGPS();




    function abrirScanner() {
        const msgEl = document.getElementById('scannerMsg');
        msgEl.innerText = ""; // Limpiar mensajes previos
        
        document.getElementById('modalScanner').classList.add('show');
        html5QrCode = new Html5Qrcode("reader");

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            if (decodedText === "<?= $qrData ?>") {
                // CASO: ÉXITO
                qrValidado = true;
                msgEl.innerText = "¡CÓDIGO CORRECTO! Validando entrega...";
                msgEl.style.color = "var(--d-green)";
                
                // Detenemos la cámara inmediatamente para que no siga escaneando
                html5QrCode.stop().then(() => {
                    // Esperamos 4 segundos antes de cerrar el modal
                    setTimeout(() => {
                        document.getElementById('modalScanner').classList.remove('show');
                        actualizarBotones(true);
                    }, 4000);
                });

            } else {
                // CASO: ERROR
                msgEl.innerText = "Código Incorrecto. Intente de nuevo.";
                msgEl.style.color = "#ef4444";
                
                // Limpiar el mensaje de error después de 2 segundos para que el usuario sepa que puede reintentar
                setTimeout(() => { if(!qrValidado) msgEl.innerText = ""; }, 2000);
            }
        };

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    }

    function cerrarScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                document.getElementById('modalScanner').classList.remove('show');
            });
        } else {
            document.getElementById('modalScanner').classList.remove('show');
        }
    }
</script>