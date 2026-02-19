<?php
// URLs para JS
$urlAceptar = ruta_accion('repartidor', 'aceptar_oferta_ajax');
$urlConsultar = ruta_accion('repartidor', 'consultar_ofertas_json');
$urlMapa = ruta_accion('repartidor', 'ruta') . "&id=";
?>

<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&display=swap" rel="stylesheet">

<style>
    /* --- ESTILOS BASE --- */
    .dashboard-repartidor {
        font-family: 'Outfit', sans-serif;
        background-color: #ffffff;
        min-height: 90vh;
        padding: 40px 5%;
    }

    .dash-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px; }
    .dash-title h1 { font-size: 2rem; color: #111; margin: 0; letter-spacing: -1px; }
    .dash-title p { color: #888; margin: 5px 0 0 0; }
    .live-badge { background: #e6fcf5; color: #0ca678; padding: 8px 16px; border-radius: 30px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
    .live-dot { width: 10px; height: 10px; background: #20c997; border-radius: 50%; animation: pulse 2s infinite; }

    /* --- GRID --- */
    .offers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    /* --- TARJETA --- */
    .offer-card { background: #fff; border: 1px solid #eee; border-radius: 24px; display: flex; flex-direction: column; overflow: hidden; transition: 0.3s; }
    .offer-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.06); }

    .card-header {
        /* Aumentamos el padding superior a 35px para dejar sitio a la etiqueta */
        padding: 35px 25px 20px 25px; 
        background: #fafafa;
        border-bottom: 1px dashed #e0e0e0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        position: relative; /* Necesario para que la etiqueta se posicione respecto a esto */
    }

    .earnings-box { 
        display: flex; flex-direction: column; 
        border-right: 1px solid #eee; /* Línea divisoria */
    }
    .earn-label { font-size: 0.7rem; text-transform: uppercase; color: #aaa; font-weight: 700; letter-spacing: 0.5px; }
    .earn-amount { font-size: 1.6rem; font-weight: 800; color: #00b894; letter-spacing: -1px; } /* Verde */

    /* CAJA DE INVERSIÓN (DERECHA - NUEVA) */
    .investment-box { 
        display: flex; flex-direction: column; 
        justify-content: center;
        /* Alineamos a la derecha para que se vea ordenado bajo la etiqueta */
        align-items: flex-end; 
        text-align: right;
    }
    .inv-label { 
        font-size: 0.7rem; text-transform: uppercase; color: #e17055; font-weight: 700; letter-spacing: 0.5px; 
        display: flex; align-items: center; gap: 4px;
    }
    .inv-amount { font-size: 1.4rem; font-weight: 800; color: #2d3436; letter-spacing: -0.5px; }
    
    /* Etiqueta de distancia flotante (La movemos al cuerpo o la dejamos pequeña) */
    .dist-badge-mini {
        position: absolute; 
        top: 12px;        /* Pegada arriba */
        right: 12px;      /* Pegada a la derecha */
        background: #fff; 
        border: 1px solid #dcdcdc; 
        padding: 4px 12px;
        border-radius: 20px; 
        font-size: 0.75rem; 
        font-weight: 800; 
        color: #555;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        z-index: 100;     /* ¡IMPORTANTE! Esto asegura que siempre se vea, no solo en hover */
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .earn-label { font-size: 0.75rem; text-transform: uppercase; color: #aaa; font-weight: 700; }
    .earn-amount { font-size: 1.8rem; font-weight: 800; color: #111; letter-spacing: -1px; }
    .dist-pill { background: #fff; border: 1px solid #ddd; padding: 6px 12px; border-radius: 10px; font-weight: 600; font-size: 0.85rem; color: #555; }

    .card-body { padding: 25px; flex: 1; }
    
    /* LÍNEA DE TIEMPO */
    .timeline { position: relative; padding-left: 20px; }
    .timeline::before { content: ''; position: absolute; left: 6px; top: 8px; bottom: 0; width: 2px; background: #f0f0f0; border-radius: 2px; }
    
    .tl-point { position: relative; margin-bottom: 25px; }
    .tl-point:last-child { margin-bottom: 0; }
    .tl-dot { position: absolute; left: -20px; top: 6px; width: 14px; height: 14px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 1px #ddd; background: #fff; z-index: 2; }
    .tl-point.pickup .tl-dot { background: #3498db; box-shadow: 0 0 0 1px #3498db; }
    .tl-point.dropoff .tl-dot { background: #ff3366; box-shadow: 0 0 0 1px #ff3366; }

    /* --- LISTA DE TIENDAS CON AUTO-SCROLL --- */
    
    /* --- LISTA DE TIENDAS CON AUTO-SCROLL (CORREGIDO) --- */
    
    /* 1. La "Ventana" visible */
    .shop-scroll-mask {
        /* Aumentamos un poco la altura para que se vea mejor */
        height: 90px; 
        overflow: hidden;
        position: relative;
        /* Suavizamos los bordes con un gradiente (Fade in/out) */
        mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%);
        -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%);
        
        /* Asegura que el contenido se posicione bien */
        display: flex;
        align-items: flex-start; 
    }

    /* 2. El "Tren" que se mueve */
    .shop-track {
        width: 100%;
        /* Importante: Sin márgenes ni paddings extraños aquí */
        margin: 0;
        padding: 0;
        display: block; /* Block se comporta mejor para alturas exactas que Flex en este caso */
    }

    /* 3. La Animación */
    .shop-track.animating {
        /* Ajusta el tiempo (12s) para que sea más lento y legible */
        animation: scroll-vertical 15s linear infinite;
        /* Will-change ayuda al navegador a renderizarlo suave */
        will-change: transform;
    }

    /* Pausar al pasar el mouse (UX Vital) */
    .shop-scroll-mask:hover .shop-track {
        animation-play-state: paused;
    }

    /* 4. EL ÍTEM INDIVIDUAL (CLAVE DEL ARREGLO) */
    .shop-item { 
        /* Usamos padding en vez de margin para evitar saltos de cálculo */
        padding: 10px 0; 
        border-bottom: 1px dashed #f0f0f0;
        box-sizing: border-box; /* Asegura que el padding cuente en la altura */
    }
    
    /* Quitamos el borde del último ítem visual para limpieza */
    .shop-item:last-child { border-bottom: none; }

    .shop-item h4 { margin: 0; font-size: 0.95rem; color: #111; font-weight: 700; line-height: 1.2; }
    .shop-item p { margin: 2px 0 0; font-size: 0.8rem; color: #777; line-height: 1.2; }

    /* Keyframes: Mover hacia arriba EXACTAMENTE el 50% */
    /* Como duplicamos la lista (A + A), al mover el 50% llegamos justo al inicio de la segunda A */
    @keyframes scroll-vertical {
        0% { transform: translateY(0); }
        100% { transform: translateY(-50%); } 
    }

    .tl-content h4 { margin: 0; font-size: 1rem; color: #111; font-weight: 700; }
    .tl-content p { margin: 4px 0 0; font-size: 0.9rem; color: #777; }
    .tl-meta { font-size: 0.8rem; color: #aaa; margin-top: 4px; display: block; font-weight: 500; }

    .card-footer { padding: 20px 25px; border-top: 1px solid #f5f5f5; background: #fff; }
    .btn-take-order { width: 100%; background: #111; color: #fff; border: none; padding: 14px; border-radius: 14px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .btn-take-order:hover { background: #333; transform: scale(1.02); }

    /* SECCIÓN ACTIVOS */
    .active-strip { background: linear-gradient(135deg, #0984e3 0%, #00cec9 100%); border-radius: 20px; padding: 20px 30px; color: white; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; box-shadow: 0 10px 25px rgba(9, 132, 227, 0.25); }
    .btn-resume-map { background: white; color: #0984e3; padding: 10px 25px; border-radius: 50px; font-weight: 800; text-decoration: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

    .empty-state-pro { text-align: center; padding: 80px 20px; border: 2px dashed #eee; border-radius: 30px; margin-top: 20px; }
    
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
    @media (max-width: 768px) { .dashboard-repartidor { padding: 20px 15px; } .dash-header { flex-direction: column; align-items: flex-start; } .active-strip { flex-direction: column; text-align: center; gap: 15px; } }

    /* --- ESTILOS MODALES PERSONALIZADOS --- */
    .custom-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.4); backdrop-filter: blur(8px);
        display: none; align-items: center; justify-content: center;
        z-index: 9999; padding: 20px; animation: fadeIn 0.3s ease;
    }

    .custom-modal-box {
        background: #fff; border-radius: 28px; width: 100%; max-width: 400px;
        padding: 35px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        transform: scale(0.9); animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    .modal-icon-header {
        width: 60px; height: 60px; background: #e6fcf5; color: #0ca678;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px; font-size: 1.5rem;
    }
    .modal-icon-header.alert-icon { background: #fff0f0; color: #ff3366; }

    .custom-modal-box h3 { font-size: 1.5rem; margin: 0 0 10px; color: #111; }
    .custom-modal-box p { color: #666; line-height: 1.5; font-size: 0.95rem; margin-bottom: 25px; }

    .modal-actions { display: flex; gap: 12px; }
    .btn-modal-primary, .btn-modal-secondary {
        flex: 1; padding: 14px; border-radius: 16px; font-weight: 700; cursor: pointer; transition: 0.2s; border: none; font-family: 'Outfit';
    }
    .btn-modal-primary { background: #111; color: #fff; }
    .btn-modal-primary:hover { background: #333; transform: translateY(-2px); }
    
    .btn-modal-secondary { background: #f0f0f0; color: #666; }
    .btn-modal-secondary:hover { background: #e0e0e0; }

    @keyframes modalPop { to { transform: scale(1); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<div class="dashboard-repartidor">
    
    <div class="dash-header">
        <div class="dash-title">
            <h1>Zona de Repartos</h1>
            <p>Hola <?= explode(' ', $_SESSION['usuario_nombre'])[0] ?>, selecciona una orden.</p>
        </div>
        <div class="live-badge"><div class="live-dot"></div> En vivo</div>
    </div>

    <div id="active-container"></div>
    <div id="offers-container" class="offers-grid"></div>
    
    <div id="initial-loader" class="empty-state-pro">
        <i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color:#ddd;"></i>
        <p style="color:#aaa; margin-top:15px;">Conectando con satélite...</p>
    </div>

</div>


<div id="modalConfirm" class="custom-modal-overlay">
    <div class="custom-modal-box">
        <div class="modal-icon-header">
            <i class="fa-solid fa-route"></i>
        </div>
        <h3>¿Aceptar esta carrera?</h3>
        <p>Al confirmar, te comprometes a recoger los productos y entregarlos al cliente en el menor tiempo posible.</p>
        <div class="modal-actions">
            <button id="btnCancelModal" class="btn-modal-secondary">Cancelar</button>
            <button id="btnConfirmModal" class="btn-modal-primary">¡Sí, vamos! <i class="fa-solid fa-bolt"></i></button>
        </div>
    </div>
</div>

<div id="modalAlert" class="custom-modal-overlay">
    <div class="custom-modal-box">
        <div class="modal-icon-header alert-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 id="alertTitle">¡Ups!</h3>
        <p id="alertMsg">No se pudo asignar la carrera.</p>
        <div class="modal-actions">
            <button onclick="cerrarModalAlert()" class="btn-modal-primary">Entendido</button>
        </div>
    </div>
</div>

<script>
    const URL_CONSULTA = '<?= $urlConsultar ?>';
    const URL_ACEPTAR  = '<?= $urlAceptar ?>';
    const URL_MAPA     = '<?= $urlMapa ?>';

    // VARIABLES DE ESTADO (MEMORIA)
    // Guardamos aquí la "versión anterior" de los datos para comparar
    let estadoOfertasPrevio = null;
    let estadoActivosPrevio = null;

    // FUNCIÓN MAESTRA: Cargar Datos y Pintar HTML
    function cargarDatos() {
        fetch(URL_CONSULTA)
            .then(r => r.json())
            .then(data => {
                if(!data.success) return;

                // 1. VERIFICAR Y RENDERIZAR PEDIDOS ACTIVOS
                // Convertimos a texto para comparar rápido si algo cambió
                const jsonActivos = JSON.stringify(data.mis_pedidos);
                
                if (jsonActivos !== estadoActivosPrevio) {
                    // Solo si son diferentes, redibujamos
                    renderActivos(data.mis_pedidos);
                    estadoActivosPrevio = jsonActivos; // Actualizamos la memoria
                    console.log("🔄 Actualizando lista de activos...");
                }

                // 2. VERIFICAR Y RENDERIZAR NUEVAS OFERTAS
                const jsonOfertas = JSON.stringify(data.ofertas);

                if (jsonOfertas !== estadoOfertasPrevio) {
                    // Solo si hay cambios reales (nuevas órdenes o alguien tomó una)
                    renderOfertas(data.ofertas);
                    estadoOfertasPrevio = jsonOfertas; // Actualizamos la memoria
                    console.log("🔔 Actualizando panel de ofertas...");
                } else {
                    // Si son iguales, NO HACEMOS NADA. La animación sigue fluida.
                    // console.log("zzz... Sin cambios");
                }

                // Quitar loader inicial si existe
                const loader = document.getElementById('initial-loader');
                if(loader && loader.style.display !== 'none') loader.style.display = 'none';
            })
            .catch(err => console.error("Error polling:", err));
    }

    // Renderizar Pedidos En Curso
    function renderActivos(pedidos) {
        const container = document.getElementById('active-container');
        let html = '';
        
        if(pedidos.length > 0) {
            pedidos.forEach(p => {
                html += `
                <div class="active-strip">
                    <div>
                        <h3 style="margin:0; font-size:1.3rem;">Orden #${p.ord_codigo} en curso</h3>
                        <p style="margin:5px 0 0; opacity:0.9;">Estado: <strong>${p.ord_estado.replace('_', ' ')}</strong></p>
                    </div>
                    <a href="${URL_MAPA}${p.ord_id}" class="btn-resume-map">
                        Abrir Mapa GPS <i class="fa-solid fa-location-arrow"></i>
                    </a>
                </div>`;
            });
        }
        container.innerHTML = html;
    }

    // Renderizar Ofertas (GRID)
    function renderOfertas(ofertas) {
        const container = document.getElementById('offers-container');
        
        if(ofertas.length === 0) {
            container.innerHTML = '';
            // Mostrar mensaje de vacío solo si ya quitamos el loader
            if(!document.getElementById('initial-loader') || document.getElementById('initial-loader').style.display === 'none') {
                container.innerHTML = `
                <div class="empty-state-pro" style="grid-column: 1 / -1;">
                    <i class="fa-solid fa-satellite-dish fa-spin" style="font-size:3rem; color:#ddd;"></i>
                    <h3 style="color:#aaa;">Buscando pedidos...</h3>
                </div>`;
            }
            return;
        }

        let html = '';
        ofertas.forEach(of => {
            // LÓGICA DE AUTO-SCROLL
            let tiendasHtml = '';
            let itemsBase = ''; 
            let needsAnimation = false;

            if(of.lista_tiendas && of.lista_tiendas.length > 0) {
                // HTML Base
                of.lista_tiendas.forEach(tienda => {
                    itemsBase += `
                    <div class="shop-item">
                        <h4><i class="fa-solid fa-store" style="color:#aaa; margin-right:5px;"></i> ${tienda.neg_nombre}</h4>
                        <p>${tienda.suc_direccion || 'Dirección no disponible'}</p>
                    </div>`;
                });

                // Si hay más de 1, duplicamos para loop
                if (of.lista_tiendas.length > 1) {
                    needsAnimation = true;
                    tiendasHtml = itemsBase + itemsBase; 
                } else {
                    tiendasHtml = itemsBase;
                }
            } else {
                tiendasHtml = '<div class="shop-item"><h4>Tienda Desconocida</h4></div>';
            }

            const animClass = needsAnimation ? 'animating' : '';

            // Referencia visual
            let refHtml = of.ord_referencia ? 
                `<span class="tl-meta" style="color:#e67e22;"><i class="fa-solid fa-triangle-exclamation"></i> ${of.ord_referencia}</span>` : '';

            let ganancia = parseFloat(of.ord_costo_envio).toFixed(2);
            let pagoTienda = parseFloat(of.capital_necesario).toFixed(2);

            html += `
            <div class="offer-card" id="card-${of.ord_id}">
                
                <div class="card-header">
                    <div class="dist-badge-mini">
                        <i class="fa-solid fa-route"></i> ${of.distancia_real_km} km
                    </div>

                    <div class="earnings-box">
                        <span class="earn-label">Tu Ganancia</span>
                        <span class="earn-amount">$${ganancia}</span>
                    </div>

                    <div class="investment-box">
                        <span class="inv-label"><i class="fa-solid fa-wallet"></i> Pagar en Local</span>
                        <span class="inv-amount" style="color:#2d3436;">$${pagoTienda}</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="timeline">
                        <div class="tl-point pickup">
                            <div class="tl-dot"></div>
                            <div class="tl-content">
                                <div class="shop-scroll-mask">
                                    <div class="shop-track ${animClass}">
                                        ${tiendasHtml}
                                    </div>
                                </div>
                                <span class="tl-meta" style="margin-top:8px; color:#3498db;">Recoger ${of.total_items} productos</span>
                            </div>
                        </div>
                        <div class="tl-point dropoff">
                            <div class="tl-dot"></div>
                            <div class="tl-content">
                                <h4>Cliente</h4>
                                <p class="text-truncate">${of.ord_direccion_envio}</p>
                                ${refHtml}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn-take-order" onclick="aceptarPedido(${of.ord_id})">
                        Aceptar Carrera <i class="fa-solid fa-bolt"></i>
                    </button>
                </div>
            </div>`;
        });

        container.innerHTML = html;
    }

    // LÓGICA ACEPTAR
    let idOrdenPendiente = null;

    // Abrir Modal de Confirmación
    function aceptarPedido(idOrden) {
        idOrdenPendiente = idOrden;
        document.getElementById('modalConfirm').style.display = 'flex';
    }

    // Cerrar Modal Confirmación
    document.getElementById('btnCancelModal').onclick = () => {
        document.getElementById('modalConfirm').style.display = 'none';
        idOrdenPendiente = null;
    };

    // Al darle a "Sí, vamos" en el Modal
    document.getElementById('btnConfirmModal').onclick = () => {
        const idOrden = idOrdenPendiente;
        if(!idOrden) return;

        // Cerramos el modal de confirmación
        document.getElementById('modalConfirm').style.display = 'none';

        // Iniciamos proceso visual en la tarjeta
        const btnCard = document.querySelector(`#card-${idOrden} .btn-take-order`);
        const originalText = btnCard.innerHTML;
        btnCard.disabled = true;
        btnCard.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Asignando...';

        fetch(URL_ACEPTAR, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ord_id: idOrden })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                // Redirección directa al mapa GPS
                window.location.href = URL_MAPA + idOrden;
            } else {
                // Mostrar Alerta Personalizada en lugar de alert()
                mostrarAlerta("Error", res.error);
                btnCard.disabled = false;
                btnCard.innerHTML = originalText;
            }
        })
        .catch(err => {
            console.error(err);
            mostrarAlerta("Error de Red", "No se pudo conectar con el servidor.");
            btnCard.disabled = false;
            btnCard.innerHTML = originalText;
        });
    };

    function mostrarAlerta(titulo, mensaje) {
        document.getElementById('alertTitle').innerText = titulo;
        document.getElementById('alertMsg').innerText = mensaje;
        document.getElementById('modalAlert').style.display = 'flex';
    }

    function cerrarModalAlert() {
        document.getElementById('modalAlert').style.display = 'none';
    }

    // INICIAR EL LOOP
    cargarDatos(); 
    // Intervalo de 5 segundos. Ahora es 100% silencioso si no hay cambios.
    setInterval(cargarDatos, 5000); 

</script>