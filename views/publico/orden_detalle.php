<?php
// 1. GENERAR URL DE VOLVER SEGURA
$urlVolver = ruta_accion('publico', 'mis_pedidos');
if (empty($urlVolver)) $urlVolver = "index.php?c=publico&a=mis_pedidos";

// 2. PREPARACIÓN DE DATOS
$esRetiro = ($orden['ord_tipo_entrega'] === 'RETIRO');
$latCliente = $orden['ord_ubicacion_lat'] ?: -0.180653; 
$lonCliente = $orden['ord_ubicacion_lon'] ?: -78.467834;

// 3. AGRUPAR PARADAS Y DETECTAR ESTADO
$paradas = [];
foreach ($detalles as $d) {
    $idSuc = $d['suc_id'];
    if (!isset($paradas[$idSuc])) {
        // Calcular distancia desde el cliente (inicialmente) para ordenar
        $dist = sqrt(pow($d['suc_latitud'] - $latCliente, 2) + pow($d['suc_longitud'] - $lonCliente, 2));
        
        $paradas[$idSuc] = [
            'info' => [
                'suc_id'    => $d['suc_id'],
                'nombre' => $d['suc_nombre'], 
                'direccion' => $d['suc_direccion'], 
                'lat' => $d['suc_latitud'], 
                'lon' => $d['suc_longitud'], 
                'distancia' => $dist
            ],
            'productos' => [],
            'estado_parada' => 'COMPLETADO' // Asumimos completado y si hallamos un pendiente, lo cambiamos
        ];
    }
    
    // Si AL MENOS UN producto está pendiente, la parada no está lista
    if ($d['odet_estado'] !== 'RECOGIDO') {
        $paradas[$idSuc]['estado_parada'] = 'PENDIENTE';
    }
    
    $paradas[$idSuc]['productos'][] = $d;
}

// Ordenar paradas por cercanía lógica
usort($paradas, function($a, $b) { return $a['info']['distancia'] <=> $b['info']['distancia']; });

// 4. DETERMINAR PUNTO DE INICIO DINÁMICO ("ESTOY AQUÍ")
// Por defecto, arrancamos donde el cliente pidió (Casa/Oficina)
$latInicioRuta = $latCliente;
$lonInicioRuta = $lonCliente;
$textoInicio = "Tu Ubicación (Inicio)";
$iconoInicio = "user"; // Icono base

// Recorremos para ver dónde fue la última recogida
$paradasPendientes = [];
foreach ($paradas as $suc) {
    if ($suc['estado_parada'] === 'COMPLETADO') {
        // Si ya recogí aquí, AHORA ESTOY AQUÍ. Mi ruta hacia el siguiente empieza desde esta tienda.
        $latInicioRuta = $suc['info']['lat'];
        $lonInicioRuta = $suc['info']['lon'];
        $textoInicio = "Estás aquí: " . $suc['info']['nombre'];
        $iconoInicio = "shop_done"; // Cambiamos icono a tienda validada
    } else {
        // Si está pendiente, la agregamos a la cola de la ruta
        $paradasPendientes[] = $suc;
    }
}

// API QR
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&color=000000&bgcolor=ffffff&data=" . $orden['ord_token_qr'];
$esCancelado = ($orden['ord_estado'] === 'CANCELADO');
$urlGps = ruta_accion('publico', 'consultar_estado_ruta_ajax');
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>



<style>
    :root {
        --d-primary: #ff3366; --d-dark: #2d3436; --d-gray: #f8f9fc; --d-green: #2ecc71; --d-sidebar-w: 420px;
    }
    body { margin: 0; overflow-x: hidden; background: #fff; }

    /* LAYOUT */
    .split-screen { display: flex; height: calc(100vh - 60px); font-family: 'Outfit', sans-serif; position: relative; }

    /* PANEL IZQUIERDO */
    .order-info-panel { width: var(--d-sidebar-w); background: white; display: flex; flex-direction: column; z-index: 20; box-shadow: 5px 0 30px rgba(0,0,0,0.05); overflow-y: auto; }
    .panel-header { padding: 25px; border-bottom: 1px solid #f0f0f0; }
    .btn-back { display: inline-flex; align-items: center; gap: 10px; color: var(--d-dark); text-decoration: none; font-weight: 800; background: #f1f2f6; padding: 10px 20px; border-radius: 30px; margin-bottom: 20px; transition: 0.2s; font-size: 0.9rem; }
    .btn-back:hover { background: #e1e2e6; }
    .ord-title h1 { margin: 0; font-size: 1.6rem; font-weight: 900; color: var(--d-dark); }
    .ord-subtitle { color: #888; font-size: 0.9rem; margin-top: 5px; }

    /* QR */
    .qr-container-inline { background: #fff0f3; padding: 20px; border-radius: 16px; margin: 25px; text-align: center; border: 2px dashed var(--d-primary); }
    .qr-img { width: 160px; height: 160px; mix-blend-mode: multiply; border-radius: 8px; }
    .qr-txt { color: var(--d-primary); font-weight: 800; margin-top: 10px; display: block; }

    /* LISTA PASOS */
    .steps-list { padding: 0 25px 25px 25px; }
    .step-card { display: flex; gap: 15px; margin-bottom: 20px; position: relative; transition:0.3s; }
    .step-card.completed { opacity: 0.5; filter: grayscale(1); } /* Estilo apagado si ya fue */
    
    .step-card::after { content: ''; position: absolute; left: 19px; top: 45px; bottom: -30px; width: 2px; background: #eee; z-index: 0; }
    .step-card:last-child::after { display: none; }

    .step-num { width: 40px; height: 40px; background: var(--d-dark); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; z-index: 1; flex-shrink: 0; }
    .step-num.user-loc { background: #3498db; }
    .step-num.done { background: var(--d-green); } /* Verde si ya completó */

    .step-info h3 { margin: 0; font-size: 1rem; font-weight: 700; }
    .step-info p { margin: 4px 0 8px 0; color: #888; font-size: 0.85rem; }
    .pickup-list { background: #f8f9fa; padding: 10px 15px; border-radius: 10px; font-size: 0.85rem; color: #555; list-style: none; margin: 0; }
    .pickup-list li { margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
    
    /* PANEL DERECHO */
    .order-map-panel { flex: 1; position: relative; background: #eee; }
    #mapa-full { width: 100%; height: 100%; z-index: 1; }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .split-screen { flex-direction: column; height: 100vh; }
        .order-map-panel { height: 40vh; order: 1; flex: none; }
        .order-info-panel { width: 100%; height: 60vh; order: 2; border-radius: 25px 25px 0 0; margin-top: -25px; box-shadow: 0 -5px 20px rgba(0,0,0,0.1); }
        .btn-back { position: absolute; top: 15px; left: 15px; z-index: 999; margin: 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); background: white; }
        .step-card::after { left: 19px; }
    }
</style>

<div class="split-screen">

    <div class="order-info-panel">
        <div class="panel-header">
            <a href="<?= $urlVolver ?>" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver</a>
            <div class="ord-title">
                <h1>Orden #<?= htmlspecialchars($orden['ord_codigo']) ?></h1>
                <div class="ord-subtitle">
                    <span style="background: #eee; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 0.8rem; color: #555;"><?= str_replace('_', ' ', $orden['ord_estado']) ?></span>
                </div>
            </div>
        </div>

        <?php if ($esRetiro && !$esCancelado): ?>
            <div class="qr-container-inline">
                <div style="font-size:0.9rem; margin-bottom:10px; color:#555;">Presenta este código en caja:</div>
                <img src="<?= $qrUrl ?>" class="qr-img">
                <span class="qr-txt">ESCANEAR AQUÍ</span>
            </div>
        <?php endif; ?>

        <div class="steps-list">
            <h3 style="color:#aaa; font-size:0.85rem; font-weight:800; text-transform:uppercase; margin-bottom:20px;">
                <i class="fa-solid fa-route"></i> Itinerario de Viaje
            </h3>

            <div class="step-card">
                <div class="step-num user-loc"><i class="fa-solid fa-location-crosshairs"></i></div>
                <div class="step-info">
                    <h3><?= $textoInicio ?></h3>
                    <p>Estás aquí (Punto de Partida Actual)</p>
                </div>
            </div>

            <?php if ($esRetiro): ?>
                <?php $i=1; foreach($paradas as $suc): 
                    $isDone = ($suc['estado_parada'] === 'COMPLETADO');
                    $classDone = $isDone ? 'completed' : '';
                    $classNum = $isDone ? 'done' : '';
                    $iconNum = $isDone ? '<i class="fa-solid fa-check"></i>' : $i;
                ?>
                    <div id="card-suc-<?= $suc['info']['suc_id'] ?>" class="step-card <?= $classDone ?>">
                        <div class="step-num <?= $classNum ?>"><?= $iconNum ?></div>
                        <div class="step-info">
                            <h3><?= $suc['info']['nombre'] ?></h3>
                            <p><?= $suc['info']['direccion'] ?></p>
                            
                            <ul class="pickup-list">
                                <?php foreach ($suc['productos'] as $prod): ?>
                                    <li>
                                        <?php if($prod['odet_estado']=='RECOGIDO'): ?>
                                            <i class="fa-solid fa-check-circle" style="color:var(--d-green)"></i> 
                                            <span style="text-decoration:line-through; color:#aaa;">
                                                <?= $prod['odet_cantidad'] ?>x <?= $prod['pro_nombre'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span><?= $prod['odet_cantidad'] ?>x <?= $prod['pro_nombre'] ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php $i++; endforeach; ?>
            <?php else: ?>
                <div class="step-card">
                    <div class="step-num" style="background:#27ae60;"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="step-info">
                        <h3>Destino de Entrega</h3>
                        <p><?= $orden['ord_direccion_envio'] ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="order-map-panel">
        <div id="mapa-full"></div>
    </div>
</div>

<?php if (!$esCancelado): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. CONFIGURACIÓN INICIAL DEL MAPA ---
        let latStart = <?= $latInicioRuta ?>;
        let lonStart = <?= $lonInicioRuta ?>;
        
        const map = L.map('mapa-full', { zoomControl: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Iconos
        const iconUser = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/9131/9131546.png', iconSize: [40, 40], popupAnchor: [0, -20] });
        const iconShop = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/7877/7877084.png', iconSize: [45, 45], popupAnchor: [0, -25] });
        const iconDone = L.icon({ iconUrl: 'https://cdn-icons-png.flaticon.com/512/190/190411.png', iconSize: [35, 35], popupAnchor: [0, -20] });

        // Variables Globales
        let markerInicio = null;
        let routeControl = null;
        let mapMarkers = {}; // Para guardar referencias a los marcadores de tiendas

        // --- FUNCIÓN PARA DIBUJAR EL MAPA (Se llama al inicio y al actualizar) ---
        function renderMap(inicio, ruta, paradasEstado) {
            // 1. Actualizar Marcador de "Estás Aquí"
            if (markerInicio) {
                markerInicio.setLatLng([inicio.lat, inicio.lon]); 
                // Opcional: Centrar mapa si se movió mucho
                // map.panTo([inicio.lat, inicio.lon]);
            } else {
                markerInicio = L.marker([inicio.lat, inicio.lon], {icon: iconUser}).addTo(map).bindPopup("<b>Estás Aquí</b>");
                map.setView([inicio.lat, inicio.lon], 15);
            }

            // 2. RUTAS (Routing Machine)
            if (routeControl) {
                map.removeControl(routeControl); // Borrar línea anterior
            }

            if (ruta.length > 0) {
                // Construir puntos: Inicio -> Tienda A -> Tienda B...
                let waypoints = [L.latLng(inicio.lat, inicio.lon)];
                ruta.forEach(p => waypoints.push(L.latLng(p.lat, p.lon)));

                routeControl = L.Routing.control({
                    // [CORRECCION] ESTA LINEA ARREGLA EL ERROR ROJO EN CONSOLA
                    serviceUrl: 'https://router.project-osrm.org/route/v1',
                    waypoints: waypoints,
                    routeWhileDragging: false, 
                    draggableWaypoints: false, 
                    addWaypoints: false,
                    createMarker: function() { return null; },
                    lineOptions: { styles: [{color: '#ff3366', opacity: 0.8, weight: 6}] },
                    show: false, 
                    containerClassName: 'routing-hidden'
                }).addTo(map);
            }
            
            // 3. Actualizar Iconos de Tiendas (Verde si completado)
            if(paradasEstado && paradasEstado.length > 0) {
                paradasEstado.forEach(p => {
                    // Si ya existe el marcador y cambió a completado
                    if(mapMarkers[p.suc_id] && p.estado_parada === 'COMPLETADO') {
                        mapMarkers[p.suc_id].setIcon(iconDone);
                    }
                });
            }
        }

        // --- CARGA INICIAL (Usando datos PHP) ---
        let rutaInit = [];
        <?php if($esRetiro): foreach($paradas as $suc): ?>
            // Guardamos coordenadas para iniciar
            var pLat = <?= $suc['info']['lat'] ?>;
            var pLon = <?= $suc['info']['lon'] ?>;
            var sucId = <?= $suc['info']['suc_id'] ?>;
            var isDone = <?= ($suc['estado_parada'] === 'COMPLETADO') ? 'true' : 'false' ?>;

            // Dibujar marcador inicial
            var m = L.marker([pLat, pLon], {icon: isDone ? iconDone : iconShop}).addTo(map);
            mapMarkers[sucId] = m; // Guardar referencia

            // Si está pendiente, agregarlo a la ruta inicial
            if(!isDone) {
                rutaInit.push({lat: pLat, lon: pLon});
            }
        <?php endforeach; endif; ?>
        
        renderMap({lat: latStart, lon: lonStart}, rutaInit, []);


        // ================================================================
        // --- MOTOR INTELIGENTE (AJAX LOOP) ---
        // ================================================================
        const ordenId = <?= $orden['ord_id'] ?>;
        const urlEstado = '<?= $urlGps ?>&id=' + ordenId;

        setInterval(() => {
            fetch(urlEstado)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    // --- NUEVO: SI LA ORDEN YA TERMINÓ, NOS VAMOS ---
                    if (data.estado_orden === 'COMPLETADO' || data.estado_orden === 'ENTREGADO') {
                        // Usamos la variable PHP $urlVolver que ya tienes arriba
                        window.location.href = '<?= $urlVolver ?>';
                        return;
                    }

                    // A. REDIBUJAR MAPA (Solo si cambió algo en la ruta)
                    renderMap(data.inicio, data.ruta_pendiente, data.paradas);

                    // B. ACTUALIZAR LISTA VISUAL (Pintar de gris/verde)
                    // Como el array de paradas viene ordenado, recorremos:
                    // Nota: data.paradas es un objeto con claves numéricas, usamos Object.values o forEach directo si es array
                    Object.values(data.paradas).forEach(p => {
                        const card = document.getElementById('card-suc-' + p.suc_id);
                        if (card) {
                            const numBadge = card.querySelector('.step-num');
                            
                            if (p.estado_parada === 'COMPLETADO') {
                                // 1. Apagar la tarjeta (Gris)
                                if (!card.classList.contains('completed')) {
                                    card.classList.add('completed');
                                }
                                // 2. Cambiar número por Check Verde
                                if (!numBadge.classList.contains('done')) {
                                    numBadge.classList.add('done');
                                    numBadge.innerHTML = '<i class="fa-solid fa-check"></i>';
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error("Sync error:", err));
        }, 10000); // REVISAR CADA 10 SEGUNDOS

    });
</script>
<style> .leaflet-routing-container { display: none !important; } </style>
<?php endif; ?>