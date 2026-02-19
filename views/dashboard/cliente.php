<?php
// =============================================================================
// SECTOR 1: LÓGICA BLINDADA (Con corrección de caracteres especiales)
// =============================================================================
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';
require_once __DIR__ . '/../../nucleo/helpers.php'; 

$db = new Database();
$modeloPublico = new PublicoModelo($db->getConnection());

// Semilla para aleatoriedad
$semilla = uniqid(); 
$_SESSION['semilla_servicios'] = $semilla; 

$listaServicios = $modeloPublico->obtenerServiciosDashboard(0, 8, $semilla);
$listaProductos = $modeloPublico->obtenerProductosDashboard(0, 8, $semilla);
$promociones = $modeloPublico->obtenerPromocionesVigentes(15);

// --- FUNCIÓN DE PARCHE (ESTO ES LO QUE ARREGLA EL ERROR 400) ---
if (!function_exists('url_segura')) {
    function url_segura($c, $a) {
        // Cambiamos el '+' por '%2B' para que no se rompa al viajar por internet
        return str_replace('+', '%2B', ruta_accion($c, $a));
    }
}

// Generamos URLs que NUNCA fallan
$urlListarNegocios = str_replace('+', '%2B', ruta_accion('publico', 'listar_negocios_ajax'));
$urlVerPerfil           = url_segura('publico', 'ver_perfil_negocio_ajax');
$urlVisitarNegocio      = url_segura('publico', 'negocio');
$urlVerServicio         = url_segura('publico', 'ver_detalle_servicio_ajax');
$urlCargarMas           = url_segura('publico', 'cargar_mas_servicios_ajax');
$urlCargarMasProductos  = url_segura('publico', 'cargar_mas_productos_ajax');
$urlBuscar = str_replace('+', '%2B', ruta_accion('publico', 'buscar_ajax'));
?>


<style>
    /* Ajuste para que el Hero suba detrás del Navbar */
    .dashboard-wrapper { margin-top: -80px; }

    /* --- 1. HERO (FONDO Y ESTRUCTURA) --- */
    .hero { 
        height: 75vh; 
        background: linear-gradient(135deg, rgba(30,39,46,0.9) 0%, rgba(30,39,46,0.6) 100%), 
                    url('https://images.unsplash.com/photo-1633681926022-84c23e8cb2d6?q=80&w=2070&auto=format&fit=crop');
        background-size: cover; background-position: center;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        text-align: center; padding: 0 20px; color: #ffffff;
        border-bottom-right-radius: 50px; border-bottom-left-radius: 50px;
        position: relative; 
        overflow: hidden;
        transition: 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .hero-content-wrapper {
        transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        width: 100%; max-width: 800px; z-index: 10;
        display: flex; flex-direction: column; align-items: center;
        position: relative; 
    }

    /* ANIMACIÓN AL BUSCAR (SOLO PC) */
    .hero.searching .hero-content-wrapper {
        transform: translateX(-22vw);
        width: 45%; 
    }

    .hero h1 { font-family: 'Outfit', sans-serif; font-size: 3.5rem; line-height: 1.1; margin-bottom: 15px; font-weight: 800; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
    .hero p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 40px; max-width: 600px; font-weight: 300; }

    /* --- 2. BUSCADOR --- */
    .search-container {
        background: #ffffff; padding: 6px; border-radius: 50px; 
        display: flex; width: 100%; max-width: 600px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3); 
        transform: translateY(0); transition: 0.3s;
        position: relative; z-index: 20;
    }
    
    .search-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #aaa; z-index: 1; }
    
    .search-input { 
        flex: 1; border: none; padding: 15px 20px 15px 45px; font-size: 1.1rem; 
        outline: none; border-radius: 50px; color: #1e272e; min-width: 0; background: transparent;
    }
    
    .search-clear {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: #eee; border: none; width: 30px; height: 30px; border-radius: 50%;
        cursor: pointer; color: #666; display: none; align-items: center; justify-content: center;
        transition: 0.2s;
    }
    .search-clear:hover { background: #ff3366; color: white; }

    /* --- 3. PANEL DE RESULTADOS (PC) --- */
    .live-results-panel {
        position: absolute; 
        top: 95px; /* Separación del buscador */
        right: -50%; width: 45%; 
        height: 475px; /* Alto fijo */
        background: transparent; 
        padding: 10px 25px 25px 25px; 
        overflow-y: auto; z-index: 5; 
        opacity: 0; transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        display: flex; flex-direction: column; gap: 15px; margin-right: 5%;
    }

    /* SCROLLBAR PERSONALIZADO */
    .live-results-panel::-webkit-scrollbar { width: 6px; }
    .live-results-panel::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .live-results-panel::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.5); border-radius: 10px; }
    .live-results-panel::-webkit-scrollbar-thumb:hover { background: #ff3366; }
    
    .hero.searching .live-results-panel { right: 0; opacity: 1; }
    .live-results-panel h3 { color: #ffffff !important; text-shadow: 0 2px 10px rgba(0,0,0,0.8); font-weight: 700; margin-top:0; }

    /* TARJETAS DE RESULTADOS (DISEÑO VERTICAL) */
    .results-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 columnas en PC */
        gap: 15px;
        padding: 10px;
    }

    .mini-card {
        display: flex; flex-direction: column; 
        background: #fff; border-radius: 16px; overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #eee;
        cursor: pointer; transition: 0.3s; 
        animation: slideInUp 0.4s ease forwards; opacity: 0;
        text-align: center;
        min-height: 260px; /* <--- AGREGA ESTA LÍNEA */
    }
    .mini-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
    @keyframes slideInUp { to { opacity: 1; transform: translateY(0); } }

    .mini-img-wrapper { 
        position: relative; width: 100%; height: 120px; 
        background: #fcfcfc; display: flex; align-items: center; justify-content: center; 
    }
    .mini-img { max-width: 100%; max-height: 100%; object-fit: contain; }
    
    .mini-price-tag { 
        position: absolute; top: 10px; right: 10px; 
        background: var(--primary); color: #fff; padding: 5px 10px; 
        border-radius: 8px; font-weight: 800; font-size: 0.85rem; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.2); 
    }
    
    /* Aumentamos el padding inferior para que el botón no se corte */
    .mini-info { 
        padding: 12px 12px 20px 12px; /* <--- CAMBIO AQUÍ: 20px abajo */
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Para empujar el botón al fondo si sobra espacio */
        flex: 1; /* Ocupa el espacio restante */
    }
    .mini-info h4 { font-size: 0.9rem; margin: 0 0 6px; color: #1e272e; font-weight: 800; line-height: 1.2; }
    .mini-biz-info { 
        display: flex; align-items: center; justify-content: center; gap: 5px; 
        font-size: 0.7rem; color: #999; text-transform: uppercase; font-weight: 700; 
    }

    /* =============================================================================
    SECTOR 3: RESPONSIVE (MÓVIL)
    ============================================================================= */
    @media (max-width: 900px) {
        .hero { 
            height: 60vh; 
            border-radius: 0 0 30px 30px; 
            transition: all 0.3s ease;
        }
        
        /* ALINEACIÓN AL TOPE AL BUSCAR */
        .hero.searching {
            justify-content: flex-start !important; 
            padding-top: 80px !important; 
            overflow: visible !important; /* <--- AGREGA ESTA LÍNEA (ESTO ARREGLA EL SCROLL) */
        }

        /* GRID DE 2 COLUMNAS EN MÓVIL */
        .results-grid { 
            display: grid !important; 
            grid-template-columns: repeat(2, 1fr) !important; 
            gap: 10px !important; 
        }

        /* PANEL FLOTANTE EN MÓVIL */
        .live-results-panel {
            position: absolute !important; 
            top: 60px !important; 
            left: 0 !important;
            width: 100% !important; 
            height: 400px !important; 
            
            /* CAMBIO AQUÍ: Usamos viewport units (vh) para asegurar que quepa en pantalla */
            max-height: 70vh !important; 
            
            background: #ffffff !important; 
            border-radius: 15px !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
            padding: 10px !important; 
            
            /* Aseguramos que el scroll funcione */
            overflow-y: auto !important; 
            -webkit-overflow-scrolling: touch; /* Suavidad en iPhone */
            
            opacity: 0; visibility: hidden; display: none;
            right: auto !important;
            z-index: 9999 !important; /* Z-index muy alto */
        }

        .hero.searching .live-results-panel { 
            opacity: 1; visibility: visible; display: block; transform: translateY(0); 
        }
        
        .live-results-panel h3 { color: #333 !important; text-align: center; text-shadow: none !important; margin: 10px 0; }
        
        /* Ajustes menores */
        .mini-img-wrapper { height: 100px !important; }
        .mini-card { padding: 5px !important; }

        /* Ocultar textos al buscar */
        .hero.searching .hero-content-wrapper { width: 100% !important; transform: none !important; }
        /* Usamos '>' para ocultar SOLO los textos directos del banner, NO los del buscador */
        .hero.searching .hero-content-wrapper > h1, 
        .hero.searching .hero-content-wrapper > p, 
        .hero.searching .hero-content-wrapper > span { 
            display: none !important; 
        }
    }



    /* =============================================================================
    NUEVA SECCIÓN: DIRECTORIO DE NEGOCIOS (Split Layout)
    ============================================================================= */
    .business-section {
        display: flex;
        gap: 50px;
        padding: 60px 5%;
        align-items: flex-start; /* Para que el sticky funcione */
        position: relative;
    }

    /* COLUMNA IZQUIERDA (FIJA/STICKY) */
    .biz-left {
        width: 35%;
        position: sticky;
        top: 100px; /* Se queda pegada al bajar */
    }
    .biz-left h2 { font-family: 'Outfit'; font-size: 2.5rem; line-height: 1.1; margin-bottom: 15px; color: var(--dark); }
    .biz-left p { color: #666; margin-bottom: 30px; font-size: 1rem; }
    
    /* Buscador Local de Negocios */
    .biz-search {
        background: white; border: 1px solid #eee; padding: 15px 20px; border-radius: 15px;
        width: 100%; font-size: 1rem; outline: none; transition: 0.3s;
        box-shadow: 0 5px 15px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 10px;
    }
    .biz-search:focus-within { border-color: var(--primary); box-shadow: 0 10px 25px rgba(255, 51, 102, 0.15); }
    .biz-search input { border: none; width: 100%; outline: none; color: var(--text); }

    /* COLUMNA DERECHA (GRID DE LOGOS) */
    .biz-right {
        width: 65%;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); /* Responsive automático */
        gap: 20px;
    }

    /* TARJETA DE NEGOCIO (LOGO) */
    .biz-card {
        background: white; border-radius: 20px; padding: 20px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; cursor: pointer; 
        border: 1px solid transparent; box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        
        /* Estado inicial para la animación */
        opacity: 0; 
        transform: scale(0.5) translateY(50px); 
    }

    .biz-card:hover {
        transform: translateY(-5px) scale(1.05) !important; /* El !important sobreescribe la animación final */
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        border-color: var(--primary);
    }
    .biz-logo-img {
        width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
        margin-bottom: 10px; border: 2px solid #f0f0f0; transition: 0.3s;
    }
    .biz-card:hover .biz-logo-img { border-color: var(--primary); }
    .biz-name { font-weight: 700; font-size: 0.9rem; color: var(--dark); line-height: 1.2; }

    /* Animación "Pez Saltando" (Pop Up con rebote) */
    @keyframes popFish {
        0% { opacity: 0; transform: scale(0.3) translateY(50px); }
        60% { opacity: 1; transform: scale(1.1) translateY(-10px); } /* Sube un poco más */
        80% { transform: scale(0.95) translateY(0); } /* Rebota abajo */
        100% { opacity: 1; transform: scale(1) translateY(0); } /* Se queda quieto */
    }

    /* ANIMACIÓN 2: PASARELA FLOTANTE (Para el inicio - Estado reposo) */
    @keyframes floatPass {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    /* Clase para modo pasarela */
    .biz-card.floating {
        animation: fadeIn 0.8s ease forwards, floatPass 6s ease-in-out infinite;
        opacity: 0; /* Empieza invisible y el JS le da el delay */
    }

    /* --- MODAL LATERAL (OFF-CANVAS) --- */
    .biz-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);
        z-index: 2000; opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .biz-modal-overlay.active { opacity: 1; visibility: visible; }

    .biz-drawer {
        position: fixed; top: 0; 
        /* CAMBIO 1: Ocultarlo totalmente a la derecha */
        right: -50%; 
        /* CAMBIO 2: Ancho del 50% de la pantalla */
        width: 50%; 
        height: 100%;
        background: white; z-index: 2001; transition: 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        box-shadow: -10px 0 40px rgba(0,0,0,0.2);
        display: flex; flex-direction: column;
    }
    .biz-drawer.active { right: 0; }

    /* --- CARRETE DE FOTOS EN CABECERA --- */
    .header-slideshow {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        z-index: 0; overflow: hidden;
        border-radius: 0 0 0 0;
    }
    
    /* Overlay oscuro para que el texto/iconos blancos resalten sobre la foto */
    .header-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.6));
        z-index: 1; pointer-events: none;
    }

    .slide-img {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        object-fit: cover;
        opacity: 0; /* Invisibles por defecto */
        transition: opacity 1.5s ease-in-out; /* Transición suave de 1.5s */
        transform: scale(1.05); /* Un poquito de zoom para efecto cinemático */
    }

    .slide-img.active { opacity: 1; }

    /* --- CABECERA PREMIUM DEL MODAL --- */
    .drawer-header {
        height: 280px; 
        /* Fondo degradado moderno */
        background: linear-gradient(135deg, var(--dark) 0%, #2d3436 100%);
        position: relative;
        display: flex; 
        align-items: flex-end; 
        padding: 0;
        margin-bottom: 25px; /* Espacio para que el logo flote */
        border-radius: 0 0 0 0; /* Recto arriba */
    }

    /* Patrón decorativo sutil en el fondo */
    .drawer-header::before {
        content: '';
        position: absolute; top:0; left:0; width:100%; height:100%;
        background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.3;
    }

    /* Botón cerrar flotante */
    .btn-close-modal {
        position: absolute; top: 20px; right: 20px; left: auto; /* A la derecha es más estándar */
        background: rgba(255,255,255,0.2); 
        color: white;
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; backdrop-filter: blur(5px);
        transition: 0.2s; z-index: 20; border: 1px solid rgba(255,255,255,0.3);
    }
    .btn-close-modal:hover { background: white; color: var(--dark); transform: rotate(90deg); }

    /* Contenedor del Logo Flotante */
    .drawer-logo-wrapper {
        position: absolute; 
        bottom: -40px; 
        left: 30px; 
        width: 110px; height: 110px;
        background: white;
        padding: 4px; /* Borde blanco interno */
        border-radius: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        z-index: 10;
    }

    .drawer-logo { 
        width: 100%; height: 100%; 
        border-radius: 21px; 
        object-fit: cover; 
        background: #f8f9fa;
    }

    /* INFO DEL NEGOCIO (Debajo del header) */
    .drawer-info-container {
        padding: 0 30px;
        position: relative;
    }

    .drawer-title { 
        font-size: 2rem; 
        font-weight: 800; 
        margin: 0 0 5px 0; 
        color: var(--dark);
        line-height: 1.1;
        padding-left: 125px; /* Espacio para no chocar con el logo */
    }

    .drawer-meta {
        padding-left: 125px; 
        display: flex;
        flex-direction: column;
        gap: 2px; /* Menos espacio entre "Desde" y "Teléfono" */
        margin-bottom: 5px; /* <--- REDUCIDO AL MÍNIMO */
    }

    .badge-fundacion {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 0.75rem; color: #666; font-weight: 600;
        background: #f0f2f5; padding: 4px 10px; border-radius: 20px;
        width: fit-content;
    }

    .btn-telefono {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--primary); font-weight: 700; font-size: 0.9rem;
        cursor: pointer; text-decoration: none; margin-top: 5px;
        transition: 0.2s;
    }
    .btn-telefono:hover { transform: translateX(5px); }

    /* --- PESTAÑAS PREMIUM (ESTILO CÁPSULA) --- */
    .drawer-tabs { 
        display: flex; 
        justify-content: center; /* Centradas para que se vean mejor */
        gap: 10px; /* Espacio entre botones */
        padding: 0 20px; 
        margin-top: 15px; /* Un poco de aire arriba */
        margin-bottom: 20px; /* Aire abajo antes del contenido */
        border-bottom: none; /* Quitamos la línea fea */
    }

    .tab-btn {
        padding: 10px 20px; 
        font-weight: 700; 
        font-size: 0.85rem; 
        color: #888; 
        cursor: pointer;
        background: #f1f2f6; /* Fondo gris suave por defecto */
        border-radius: 50px; /* Redonditas */
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: flex; align-items: center; gap: 8px; /* Para alinear iconos */
        border: 1px solid transparent;
    }

    .tab-btn:hover {
        background: #e2e6ea;
        transform: translateY(-2px); /* Flotan un poquito al pasar el mouse */
    }

    .tab-btn.active { 
        background: var(--primary); 
        color: white; 
        box-shadow: 0 5px 15px rgba(255, 51, 102, 0.3); /* Sombra "Glow" */
        border-color: var(--primary);
    }

    /* Contenido del Modal */
    .drawer-body { flex: 1; overflow-y: auto; padding: 20px; background: #fafafa; }
    .tab-content { display: none; animation: fadeIn 0.3s; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* RESPONSIVE DE ESTA SECCIÓN */
    @media (max-width: 900px) {
        .business-section { flex-direction: column; gap: 30px; padding: 40px 20px; }
        .biz-left { width: 100%; position: relative; top: 0; text-align: center; }
        .biz-right { width: 100%; grid-template-columns: repeat(2, 1fr); }
        .biz-drawer { width: 100%; right: -100%; } /* Modal ocupa toda la pantalla en móvil */
    }

    /* --- MODO ACUARIO (Pasarela Viva) --- */
    .biz-right { 
        position: relative; 
        min-height: 320px; /* REDUCIDO: Antes era 400px. Se ve más compacto */
        overflow: hidden; /* Importante para que no se salgan */
    }

    /* Tarjeta en modo flotante */
    .biz-card.floating-random {
        position: absolute;
        transition: all 0.8s ease-in-out; /* Suaviza el cambio de posición y opacidad */
        opacity: 0; /* Empiezan invisibles */
        z-index: 1;
        width: 130px; /* Tamaño fijo para calcular bien las posiciones */
    }
    
    .biz-card.floating-random.visible {
        opacity: 1;
        z-index: 10;
        transform: scale(1.05); /* Un pequeño zoom al aparecer */
    }

    /* --- MODO BÚSQUEDA (Ordenado y Centrado) --- */
    .biz-right.searching-mode {
        display: flex; 
        flex-wrap: wrap; 
        justify-content: center; 
        align-content: flex-start;
        gap: 20px;
        min-height: auto; /* Se ajusta al contenido */
    }
    
    .biz-right.searching-mode .biz-card {
        position: relative !important; 
        top: auto !important; left: auto !important;
        opacity: 0;
        animation: popFish 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        width: auto; /* Tamaño automático en lista */
    }

    /* --- MODO BÚSQUEDA (Ordenado y Centrado) --- */
    .biz-right.searching-mode {
        display: flex; 
        flex-wrap: wrap; 
        justify-content: center; /* CENTRADO HORIZONTAL */
        align-content: flex-start;
        gap: 20px;
    }
    
    .biz-right.searching-mode .biz-card {
        position: relative !important; /* Vuelven al flujo normal */
        top: auto !important; left: auto !important;
        animation: popFish 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        opacity: 0;
    }


    /* --- GRID DE PRODUCTOS Y SERVICIOS (ESTILO E-COMMERCE) --- */
    /* --- GRID DE PRODUCTOS Y SERVICIOS --- */
    .items-grid {
        display: grid;
        /* EN MÓVIL: Forzamos 2 columnas exactas */
        grid-template-columns: repeat(2, 1fr); 
        gap: 12px; /* Espacio un poco más cerrado */
        padding-bottom: 80px; /* Mucho espacio abajo para que no se corte al final */
    }

    /* EN PC: Usamos auto-ajuste para que entren 3 o 4 según el espacio */
    @media (min-width: 768px) {
        .items-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }
    }

    .item-card {
        background: white;
        border: 1px solid #f0f0f0;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .item-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: var(--primary);
    }

    .item-img-container {
        width: 100%;
        /* ANTES: 150px -> AHORA: 110px (Más bajito para que se vea el resto) */
        height: 110px; 
        background: #f8f9fa;
        position: relative;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center; /* Centrar imagen */
    }

    .item-img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* <--- MUESTRA TODO SIN CORTAR */
        transition: 0.5s;
        padding: 5px; /* Un pequeño margen para que no toque los bordes */
        background: white; /* Fondo blanco para que se vea limpio */
    }

    .item-card:hover .item-img { transform: scale(1.1); }

    .item-body {
        /* ANTES: 15px -> AHORA: 10px (Para ganar espacio) */
        padding: 10px; 
        display: flex;
        flex-direction: column;
        flex: 1; 
    }

    .item-title {
        font-size: 1rem; font-weight: 800; color: var(--dark);
        margin: 0 0 5px 0;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; /* Max 2 lineas */
    }

    .item-desc {
        font-size: 0.8rem; color: #888; margin-bottom: 10px;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        min-height: 2.4em; /* Reserva espacio para 2 líneas */
    }

    .item-footer {
        margin-top: auto; /* Empuja esto al fondo */
        display: flex; justify-content: space-between; align-items: center;
        padding-top: 10px; border-top: 1px dashed #eee;
    }

    .item-price { font-size: 1.1rem; font-weight: 900; color: var(--primary); }

    .btn-add {
        background: var(--dark); color: white;
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; transition: 0.2s;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .item-card:hover .btn-add { background: var(--primary); transform: scale(1.1); }


    /* --- TARJETAS DE SUCURSALES (INFO & MAPA) --- */
    .branch-card {
        display: flex; 
        gap: 15px;
        background: white; 
        border: 1px solid #f0f0f0;
        border-radius: 16px; 
        padding: 15px;
        margin-bottom: 15px; 
        transition: all 0.3s ease;
        align-items: center;
    }

    .branch-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: var(--primary);
    }

    .branch-img {
        width: 100px; 
        height: 100px; 
        border-radius: 12px; 
        object-fit: cover;
        background: #f8f9fa;
        flex-shrink: 0; /* Evita que la imagen se aplaste */
    }

    .branch-info {
        flex: 1;
        display: flex; 
        flex-direction: column;
        justify-content: center;
    }

    .branch-name { font-size: 1rem; font-weight: 800; color: var(--dark); margin-bottom: 5px; }
    
    .branch-detail { 
        font-size: 0.85rem; color: #666; margin-bottom: 4px; 
        display: flex; align-items: flex-start; gap: 8px;
    }
    .branch-detail i { color: #aaa; margin-top: 3px; font-size: 0.8rem; }

    .btn-map {
        display: inline-flex; align-items: center; gap: 6px;
        margin-top: 8px; padding: 6px 15px;
        background: #e3f2fd; color: #1976d2; /* Azul Maps */
        font-size: 0.8rem; font-weight: 700; border-radius: 50px;
        text-decoration: none; width: fit-content; transition: 0.2s;
    }
    .btn-map:hover { background: #1976d2; color: white; }



    /* Botón "Visitar Negocio" al final de las listas */
    .btn-ver-mas-container {
        grid-column: 1 / -1; /* Ocupa todo el ancho del grid */
        margin-top: 10px;
        text-align: center;
    }

    .btn-ver-mas {
        display: inline-block;
        padding: 12px 30px;
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
        border-radius: 50px;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .btn-ver-mas:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(255, 51, 102, 0.2);
    }


    /* ============================================================== */
    /* ESTILOS SECCIÓN SERVICIOS (DISEÑO PREMIUM) */
    /* ============================================================== */
    .services-section-wrapper {
        padding: 40px 5% 80px 5%;
        background: linear-gradient(to bottom, #ffffff 0%, #f8f9fc 100%);
    }

    .services-header-text { margin-bottom: 35px; }
    .services-header-text h2 { font-size: 2rem; color: var(--dark); font-weight: 800; margin: 0; }
    .services-header-text p { color: #888; font-size: 1rem; margin-top: 5px; }

    /* GRID SYSTEM (4 Columnas) */
    .services-grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }

    /* TARJETA LIMPIA (Estilo "Nails") */
    .service-card-item {
        background: white;
        border-radius: 16px; /* Bordes más suaves */
        overflow: hidden;
        border: 1px solid #f0f0f0; /* Borde muy sutil */
        display: flex; flex-direction: column;
        transition: all 0.3s ease;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); /* Sombra muy suave inicial */
    }

    /* --- NUEVO: ETIQUETA DE CALIFICACIÓN (Esquina Superior Derecha) --- */
    .serv-rating-badge {
        position: absolute; 
        top: 8px; 
        right: 8px;
        padding: 5px 10px; 
        border-radius: 20px;
        font-size: 0.75rem; 
        font-weight: 800; 
        z-index: 25; /* Encima de todo */
        display: flex; 
        align-items: center; 
        gap: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        backdrop-filter: blur(4px);
        transition: transform 0.2s;
    }

    .service-card-item:hover .serv-rating-badge {
        transform: scale(1.05); /* Efecto pop al pasar el mouse */
    }

    /* ESTILO 1: TIENE VOTOS (Blanco y Dorado) */
    .serv-rating-badge.has-votes {
        background: rgba(255, 255, 255, 0.95);
        color: #2d3436;
    }
    .serv-rating-badge.has-votes i { 
        color: #f1c40f; /* Amarillo Estrella */
    }

    /* ESTILO 2: SÉ EL PRIMERO (Oscuro y Trofeo) */
    .serv-rating-badge.be-first {
        background: var(--dark); 
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .serv-rating-badge.be-first i {
        color: #00b894; /* Verde Trofeo o Turquesa */
    }

    /* TARJETA */
    
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .service-card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border-color: var(--primary); /* Toque de color al hover */
    }

    /* FOTO PORTADA */
    /* --- CARGA ESTOS ESTILOS NUEVOS --- */
    
    /* FOTO PORTADA (CARRUSEL) */
    .serv-img-box {
        width: 100%; height: 160px;
        position: relative;
        background: #f8f9fa;
        overflow: hidden;
    }

    /* IMAGEN (Modo Carrusel) */
    .serv-img { 
        width: 100%; height: 100%; 
        object-fit: contain; 
        position: absolute; top: 0; left: 0;
        opacity: 0; transition: opacity 0.5s ease-in-out;
        z-index: 1;
        background: #fff; 
    }
    
    /* CLASE PARA MOSTRAR LA FOTO ACTUAL */
    .serv-img.active { opacity: 1; z-index: 2; }

    /* FLECHAS DEL CARRUSEL */
    .slider-btn {
        position: absolute; top: 50%; transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.9); border: 1px solid #eee;
        width: 28px; height: 28px; border-radius: 50%;
        cursor: pointer; z-index: 10;
        display: flex; align-items: center; justify-content: center;
        color: var(--dark); font-size: 0.8rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: 0.2s;
        /* opacity: 0;  <-- ELIMINADO para que siempre se vean */
    }
    .service-card-item:hover .slider-btn { opacity: 1; } /* Aparecen al hover */
    .slider-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
    .slider-btn.prev { left: 8px; }
    .slider-btn.next { right: 8px; }

    /* LOGO DEL NEGOCIO (Aseguramos que esté por encima de todo) */
    .serv-biz-logo { z-index: 20; }
    .serv-badge-time { z-index: 20; }
    .service-card-item:hover .serv-img { transform: scale(1.08); }

    /* BADGE DURACIÓN */
    .serv-badge-time {
        position: absolute; bottom: 8px; right: 8px;
        background: rgba(30, 39, 46, 0.8); color: white;
        padding: 3px 8px; border-radius: 8px;
        font-size: 0.65rem; font-weight: 600; z-index: 20;
    }

    /* LOGO FLOTANTE */
    .serv-biz-logo {
        position: absolute; top: 8px; left: 8px;
        width: 38px; height: 38px;
        background: white; padding: 2px; border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 20;
    }
    .serv-biz-logo img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

    /* CUERPO */
    .serv-body { padding: 12px 15px; flex: 1; display: flex; flex-direction: column; }
    
    .serv-biz-name { font-size: 0.7rem; color: #999; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .serv-title { font-size: 1rem; font-weight: 800; color: var(--dark); margin: 0 0 6px 0; line-height: 1.2; height: 2.4em; overflow: hidden; }
    .serv-desc { font-size: 0.8rem; color: #777; margin: 0 0 10px 0; height: 2.4em; overflow: hidden; }

    /* FOOTER PRECIO/BOTÓN */
    .serv-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
    .serv-price { font-size: 1.1rem; font-weight: 800; color: var(--primary); }
    .serv-price small { font-size: 0.65rem; color: #aaa; font-weight: 600; text-transform: uppercase; margin-bottom: 2px; }
    .serv-price { font-size: 1.2rem; font-weight: 800; color: var(--primary); }

    .btn-reservar-card {
        background: var(--dark); color: white; border: none;
        padding: 6px 18px; border-radius: 50px;
        font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.2s;
    }
    .btn-reservar-card:hover { background: var(--primary); transform: scale(1.05); }

    /* BOTÓN VER MÁS */
    /* --- ESTILOS BOTONES DE CARGA (MEJORADOS) --- */
    
    /* Contenedor Flex para ponerlos lado a lado */
    .load-more-container { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        gap: 20px; /* Espacio entre los dos botones */
        margin-top: 40px; 
        margin-bottom: 20px;
    }

    /* Estilo Base para ambos botones */
    .btn-load-more {
        padding: 12px 35px; 
        border-radius: 50px;
        font-weight: 800; 
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-size: 0.9rem; 
        display: inline-flex; 
        align-items: center; 
        gap: 10px;
        border: 2px solid transparent; /* Borde base */
    }

    /* 1. Botón "VER MÁS" (Oscuro Sólido -> Hover Primary) */
    #btnLoadMoreServices {
        background: var(--dark); 
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    #btnLoadMoreServices:hover {
        background: var(--primary); 
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 51, 102, 0.3);
    }

    /* 2. Botón "OCULTAR TODO" (Blanco Delineado -> Hover Rojo Suave) */
    #btnShowLess {
        background: white;
        color: var(--primary); 
        border-color: var(--primary);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    #btnShowLess:hover {
        background: #fff0f5; /* Fondo rosita muy claro */
        transform: translateY(-3px);
    }

    /* RESPONSIVE: En celular que se pongan uno arriba del otro si no caben */
    @media (max-width: 500px) {
        .load-more-container { flex-direction: column; gap: 15px; }
        .btn-load-more { width: 80%; justify-content: center; }
    }
    .btn-load-more:hover {
        border-color: var(--primary); color: var(--primary); background: #fff0f3;
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) { 
        .services-grid-container { grid-template-columns: repeat(3, 1fr); } 
    }
    
    @media (max-width: 800px) { 
        .services-grid-container { grid-template-columns: repeat(2, 1fr); gap: 15px; } 
    }
    
    /* MÓVIL: AQUÍ ESTÁ EL CAMBIO CLAVE (Antes era 1fr, ahora repeat(2, 1fr)) */
    @media (max-width: 500px) { 
        .services-grid-container { 
            grid-template-columns: repeat(2, 1fr); /* 2 Columnas */
            gap: 10px; /* Menos espacio entre ellas */
            margin-bottom: 20px;
        } 
        
        .serv-img-box { 
            height: 120px; /* Foto más bajita para compensar */
        }

        /* Ajustes para que el texto no se vea gigante en tarjetas pequeñas */
        .service-card-item { border-radius: 12px; }
        .serv-body { padding: 8px 10px; }
        .serv-title { font-size: 0.85rem; margin-bottom: 4px; }
        .serv-biz-name { font-size: 0.6rem; }
        .serv-desc { display: none; } /* Ocultamos descripción en móvil para ahorrar espacio (Opcional) */
        
        .serv-footer { padding-top: 8px; }
        .serv-price { font-size: 0.95rem; }
        
        .btn-reservar-card { 
            padding: 4px 10px; 
            font-size: 0.7rem; 
        }
        
        /* Ajuste de flechas en móvil */
        .slider-btn { width: 22px; height: 22px; font-size: 0.6rem; }
    }


    /* ETIQUETA DE RATING PARA EL BUSCADOR (MINI) */
    .mini-rating-badge {
        position: absolute; top: 10px; left: 10px; z-index: 10;
        padding: 4px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 3px;
    }
    .mini-rating-badge.has-votes { background: rgba(255,255,255,0.95); color: #2d3436; }
    .mini-rating-badge.has-votes i { color: #f1c40f; }
    
    .mini-rating-badge.be-first { background: var(--dark); color: white; border: 1px solid rgba(255,255,255,0.2); }
    .mini-rating-badge.be-first i { color: #00b894; }

    /* --- BOTÓN ANIMADO DEL BUSCADOR VIVO --- */
    .btn-live-action {
        background: var(--primary);
        color: white;
        border: none;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px; /* Espacio entre texto e icono */
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Efecto rebote */
        box-shadow: 0 4px 10px rgba(255, 51, 102, 0.2);
    }

    .btn-live-action:hover {
        transform: translateY(-3px) scale(1.05); /* Sube y crece un poco */
        box-shadow: 0 8px 20px rgba(255, 51, 102, 0.4);
        background: var(--dark); /* Cambio de color elegante */
    }

    /* Ajuste para el contenedor de puntos en móvil */
    @media (max-width: 768px) {
        /* Stackea la info y los puntos verticalmente */
        .drawer-info-container {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 10px !important;
        }

        /* El contenedor de puntos ocupa todo el ancho y se alinea al borde */
        #drawerPointsContainer {
            width: 100% !important;
            margin-right: 0 !important;
            padding: 0 !important;
            margin-top: 10px !important;
        }
        
        /* Ajuste visual para la tarjeta de puntos en móvil */
        #drawerPointsContainer > div {
            margin: 0 !important;
            display: flex !important;
            flex-direction: row !important; /* En móvil mejor horizontal para ahorrar alto */
            justify-content: space-between !important;
            align-items: center !important;
            text-align: left !important;
            padding: 15px 20px !important;
        }

        /* Reduce el margen del icono en móvil */
        #drawerPointsContainer i {
            margin-bottom: 0 !important;
            font-size: 1.5rem !important;
        }
    }

    /* =============================================================================
       ESTILOS PREMIUM: CINTA DE OFERTAS "SUPER CARD"
       ============================================================================= */
    .promo-section {
        width: 100%;
        overflow: hidden;
        padding: 50px 0; /* Más espacio vertical */
        background: linear-gradient(to bottom, #ffffff, #fdfdfd);
        border-bottom: 1px solid #f0f0f0;
        margin-bottom: 20px;
    }

    /* TÍTULO DE SECCIÓN (Igual al resto del dashboard) */
    .promo-section-header {
        padding: 0 5%;
        margin-bottom: 30px;
    }

    /* EL RIEL (CINTA) - VERSIÓN JS CONTROLADA */
    .promo-track {
        display: flex;
        width: max-content;
        gap: 30px;
        padding: 15px 0;
        /* Quitamos la animación CSS porque ahora lo moverá el JS */
        /* animation: scrollPromos 60s linear infinite;  <-- ELIMINADO */
        
        /* Cursor para indicar que se puede deslizar */
        cursor: grab; 
        cursor: -webkit-grab;
        
        /* Importante para el rendimiento del movimiento */
        will-change: transform; 
        transform: translateX(0);
    }
    
    .promo-track:active {
        cursor: grabbing;
        cursor: -webkit-grabbing;
    }

    /* --- DISEÑO DE TARJETA SUPERIOR --- */
    .super-card {
        width: 420px; /* Más ancha para que quepa todo */
        height: 210px; /* Más alta para lucir la foto */
        background: #fff;
        border-radius: 20px;
        display: flex;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        border: 1px solid #f1f2f6;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
        text-decoration: none; /* Quitar subrayado de link */
        flex-shrink: 0;
    }

    .super-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(232, 67, 147, 0.15); /* Sombra Rosa */
        border-color: rgba(232, 67, 147, 0.3);
    }

    /* IZQUIERDA: FOTO DEL ÍTEM (CORREGIDO PARA VER TODO) */
    .sc-img-box {
        width: 35%; 
        height: 100%;
        position: relative;
        overflow: hidden;
        /* Fondo suave para rellenar los huecos si la imagen no encaja perfecto */
        background: #fdfdfd; 
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sc-img {
        width: 100%; 
        height: 100%;
        /* EL CAMBIO CLAVE: 'contain' muestra la foto entera sin cortar nada */
        object-fit: contain; 
        transition: transform 0.5s ease;
        /* Un pequeño padding para que la imagen no toque los bordes */
        padding: 5px; 
    }
    .super-card:hover .sc-img { transform: scale(1.05); }

    /* ETIQUETA TIPO (Servicio/Producto) SOBRE LA FOTO */
    .sc-type-tag {
        position: absolute; top: 10px; left: 10px;
        background: rgba(0,0,0,0.6); color: #fff;
        font-size: 0.6rem; font-weight: 700;
        padding: 3px 8px; border-radius: 4px;
        text-transform: uppercase;
        backdrop-filter: blur(2px);
    }

    /* DERECHA: CONTENIDO */
    .sc-content {
        width: 65%;
        padding: 12px 15px;
        display: flex;
        flex-direction: column;
        /* Quitamos space-between y usamos flex-start */
        justify-content: flex-start; 
        position: relative;
    }

    /* CABECERA DE LA TARJETA (Negocio + Badge Descuento) */
    .sc-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        margin-bottom: 5px;
    }
    .sc-biz {
        display: flex; align-items: center; gap: 6px;
    }
    .sc-biz-logo {
        width: 24px; height: 24px; border-radius: 50%; object-fit: cover;
        border: 1px solid #eee;
    }
    .sc-biz-name {
        font-size: 0.7rem; color: #636e72; font-weight: 700; text-transform: uppercase;
        max-width: 110px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    .sc-badge-off {
        background: #ff7675; color: white;
        font-size: 0.75rem; font-weight: 800;
        padding: 2px 8px; border-radius: 6px;
        box-shadow: 0 2px 5px rgba(255,118,117,0.3);
    }

    /* TÍTULO Y DESCRIPCIÓN */
    .sc-title {
        font-size: 0.95rem; font-weight: 800; color: #2d3436;
        line-height: 1.2; margin-bottom: 2px;
        /* Forzar a 1 sola línea */
        display: -webkit-box;
        -webkit-line-clamp: 1; 
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
    }

    /* PEGAR ESTO ARRIBA DE .sc-desc */
    .sc-item-real-name {
        font-size: 0.7rem;         /* Más pequeño que el título */
        color: #b2bec3;           /* Gris claro */
        font-weight: 600;
        display: block;
        margin-top: -2px;          /* Pegadito al título */
        margin-bottom: 4px;
        font-style: italic;        /* Cursiva para diferenciarlo */
    }

    /* PEGAR ESTO DEBAJO DE .sc-title */
    .sc-desc {
        font-size: 0.75rem; 
        color: #7f8c8d; 
        margin-top: 4px;      /* Separación del título */
        margin-bottom: 10px;  /* Separación de los precios */
        line-height: 1.3;
        
        /* Cortar texto sobrante */
        display: -webkit-box;
        -webkit-line-clamp: 2; /* Máximo 2 líneas */
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    /* PRECIOS */
    .sc-prices {
        display: flex; align-items: baseline; gap: 8px;
        margin-top: auto; /* Empuja hacia abajo */
    }
    .sc-price-old {
        font-size: 0.8rem; color: #b2bec3; text-decoration: line-through; font-weight: 600;
    }
    .sc-price-new {
        font-size: 1.4rem; font-weight: 900; color: #e84393; letter-spacing: -0.5px;
    }
    
    /* 3. ACTUALIZA ESTA CLASE */
    .sc-meta-bar {
        margin-top: auto; /* <--- MAGIA: Empuja esto al fondo de la tarjeta siempre */
        padding-top: 10px;
        border-top: 1px dashed #eee;
        
        display: flex; 
        align-items: center; /* Centrado vertical para que el botón no se vea raro */
        justify-content: space-between;
        gap: 15px; 
    }

    /* Contenedor para que la barra/fecha ocupe el espacio sobrante */
    .meta-left-col {
        flex: 1; /* Ocupa todo el espacio posible */
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-bottom: 2px; /* Pequeño ajuste visual */
    }

    /* EL NUEVO BOTÓN DE ACCIÓN */
    .btn-card-action {
        background: #2d3436; /* Oscuro elegante */
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        white-space: nowrap; /* Que el texto no se rompa */
        display: flex; 
        align-items: center; 
        gap: 6px;
    }
    .btn-card-action:hover {
        background: #e84393; /* Rosa al pasar el mouse */
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(232, 67, 147, 0.3);
    }
    
    /* Barra de progreso */
    .progress-container {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 0.65rem; color: #636e72; font-weight: 700; margin-bottom: 3px;
    }
    .progress-track {
        width: 100%; height: 5px; background: #f1f2f6; border-radius: 10px; overflow: hidden;
    }
    .progress-fill {
        height: 100%; background: linear-gradient(90deg, #fd79a8, #e84393); border-radius: 10px;
    }
    
    /* Info de Fecha */
    .date-info {
        font-size: 0.7rem; color: #0984e3; font-weight: 600; display: flex; align-items: center; gap: 5px;
    }

    /* RESPONSIVE */
    /* RESPONSIVE (MÓVIL) */
    @media (max-width: 768px) {
        /* 1. AUMENTAMOS LA ALTURA: De 160px a 225px */
        /* 2. AJUSTAMOS ANCHO: 310px para que entre en cualquier celular */
        .super-card { 
            width: 310px; 
            height: 225px; 
        }
        
        /* Ajustamos textos para que no se vean gigantes */
        .sc-title { font-size: 0.9rem; }
        .sc-price-new { font-size: 1.2rem; }
        
        /* Forzamos que la imagen ocupe un poco menos de ancho en móvil */
        .sc-img-box { width: 30%; }
        .sc-content { width: 70%; padding: 10px; }
    }


</style>

<div class="dashboard-wrapper">
    
    <div class="hero" id="hero-section">
        <div class="hero-content-wrapper">
            <span style="text-transform:uppercase; letter-spacing:2px; font-size:0.8rem; font-weight:700; color:var(--primary); margin-bottom:10px;">
                PANEL PERSONAL
            </span>
            <h1>Hola, <?= explode(' ', $_SESSION['usuario_nombre'])[0] ?> 👋</h1>
            <p>¿Qué servicio deseas reservar hoy?</p>
            
            <div class="search-container" id="search-box">
                <i class="fa-solid fa-search search-icon"></i>
                <input type="text" id="inputBusqueda" class="search-input" placeholder="¿Qué buscas? (Ej: Corte, Uñas...)" autocomplete="off">
                <button type="button" id="btnBorrarBusqueda" class="search-clear"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <div class="live-results-panel" id="panelResultados">
            <h3>Resultados</h3>
            <div class="results-grid" id="gridResultados"></div>
            <div id="sinResultados" style="display:none; text-align:center; padding:20px; color:#ddd;">
                No hay coincidencias.
            </div>
        </div>
    </div>


    <!-- INICIO SECCION CARRUCEL DE PROMOCIONES -->

    <?php if (!empty($promociones)): ?>
            <div class="promo-section">
                
                <div class="services-header-text" style="padding: 0 5%; margin-bottom: 20px;">
                    <h2>Ofertas <span style="color:var(--primary)">Imperdibles</span></h2>
                    <p>Descuentos exclusivos y canjes por puntos. ¡Solo por tiempo limitado!</p>
                </div>

                <div class="promo-track">
                    <?php 
                    // FUNCIÓN PARA DIBUJAR LA SUPER TARJETA
                    $dibujarSuperCard = function($p) {
                        // 1. DATOS BÁSICOS
                        $logoNegocio = !empty($p['neg_logo']) ? $p['neg_logo'] : 'recursos/img/sin_foto.png';
                        $fotoItem    = !empty($p['foto_item']) ? $p['foto_item'] : 'recursos/img/sin_foto.png';
                        $tipoItem    = $p['tipo_item']; // 'SERVICIO' o 'PRODUCTO'
                        
                        // 2. CÁLCULOS DE PRECIO
                        $precioReal   = floatval($p['precio_real']);
                        $precioOferta = floatval($p['prom_precio_oferta']);
                        $puntosReq    = intval($p['puntos_necesarios']);
                        $limite = intval($p['prom_limite_usos']);
                        $usados = intval($p['total_usos']);
                        $quedanPromo = ($limite > 0) ? ($limite - $usados) : 999999;
                        $esLimitadaJs = ($limite > 0) ? 'true' : 'false';
                        
                        // Porcentaje OFF
                        $pct = 0;
                        if($p['prom_modalidad'] === 'PRECIO' || $p['prom_modalidad'] === 'MIXTO') {
                            if($precioReal > 0 && $precioOferta < $precioReal) {
                                $pct = round((($precioReal - $precioOferta) / $precioReal) * 100);
                            }
                        }

                        // 3. CÁLCULOS DE STOCK / VIGENCIA
                        $esPorCupos = ($p['prom_limite_usos'] > 0);
                        $usados     = intval($p['total_usos']);
                        $totalCupos = intval($p['prom_limite_usos']);
                        $porcentajeUso = 0;
                        if ($esPorCupos) {
                            $porcentajeUso = ($usados / $totalCupos) * 100;
                        }
                        
                        // Link al negocio
                        $linkAction = "javascript:abrirModalNegocio(" . $p['neg_id'] . ")";
                        ?>
                        
                        <div class="super-card">
                            <div class="sc-img-box">
                                <img src="<?= $fotoItem ?>" class="sc-img">
                                <div class="sc-type-tag"><?= $tipoItem ?></div>
                            </div>

                            <div class="sc-content">
                                
                                <div class="sc-header">
                                    <div class="sc-biz">
                                        <img src="<?= $logoNegocio ?>" class="sc-biz-logo">
                                        <span class="sc-biz-name"><?= htmlspecialchars($p['neg_nombre']) ?></span>
                                    </div>
                                    <?php if($pct > 0): ?>
                                        <div class="sc-badge-off">-<?= $pct ?>%</div>
                                    <?php elseif($p['prom_modalidad'] === 'PUNTOS'): ?>
                                        <div class="sc-badge-off" style="background:#74b9ff;">CANJE</div>
                                    <?php endif; ?>
                                </div>

                                <div class="sc-main-info">
                                    <div class="sc-title"><?= htmlspecialchars($p['prom_nombre']) ?></div>
                                    
                                    <span class="sc-item-real-name">
                                        (<?= htmlspecialchars($p['nombre_item']) ?>)
                                    </span>

                                    <?php if (!empty($p['prom_desc'])): ?>
                                        <div class="sc-desc">
                                            <?= htmlspecialchars(strip_tags($p['prom_desc'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="sc-prices">
                                    <?php if($p['prom_modalidad'] === 'PUNTOS'): ?>
                                        <span class="sc-price-new" style="color:#0984e3; font-size:1.2rem;">
                                            <i class="fa-solid fa-coins"></i> <?= number_format($puntosReq) ?> pts
                                        </span>
                                        <?php if($precioReal > 0): ?>
                                            <span class="sc-price-old" style="font-size:0.7rem;">(Valor $<?= number_format($precioReal, 2) ?>)</span>
                                        <?php endif; ?>

                                    <?php elseif($p['prom_modalidad'] === 'MIXTO'): ?>
                                        <?php if($precioReal > $precioOferta): ?>
                                            <span class="sc-price-old">$<?= number_format($precioReal, 2) ?></span>
                                        <?php endif; ?>
                                        <span class="sc-price-new">$<?= number_format($precioOferta, 2) ?></span>                            
                                        <span style="font-weight:800; color:#0984e3; font-size:0.9rem;">+ <?= $puntosReq ?> pts</span>

                                    <?php else: ?>
                                        <?php if($precioReal > $precioOferta): ?>
                                            <span class="sc-price-old">$<?= number_format($precioReal, 2) ?></span>
                                        <?php endif; ?>
                                        <span class="sc-price-new">$<?= number_format($precioOferta, 2) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="sc-meta-bar">
                                    
                                    <div class="meta-left-col">
                                        <?php if($esPorCupos): ?>
                                            <div class="progress-container">
                                                <span>¡Quedan <?= $totalCupos - $usados ?>!</span>
                                                <span><?= intval($porcentajeUso) ?>%</span>
                                            </div>
                                            <div class="progress-track">
                                                <div class="progress-fill" style="width: <?= $porcentajeUso ?>%;"></div>
                                            </div>
                                        <?php else: ?>
                                            <div class="date-info">
                                                <i class="fa-regular fa-calendar"></i> 
                                                <?php if($p['prom_fin'] && $p['prom_fin'] !== '0000-00-00 00:00:00'): ?>
                                                    Hasta <?= date('d/m/Y', strtotime($p['prom_fin'])) ?>
                                                <?php else: ?>
                                                    Tiempo limitado
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($tipoItem === 'SERVICIO'): ?>
                                        <button class="btn-card-action" 
                                            onclick="event.stopPropagation(); abrirModalServicio(<?= $p['serv_id'] ?>, <?= $p['prom_id'] ?>, '<?= $p['prom_modalidad'] ?>', <?= $precioOferta ?>, <?= $puntosReq ?>)">
                                            Reservar <i class="fa-solid fa-calendar-check"></i>
                                        </button>

                                    <?php /* --- AGREGA ESTE BLOQUE PARA LOS PRODUCTOS --- */ ?>
                                    <?php elseif($tipoItem === 'PRODUCTO'): ?>
                                        <button class="btn-card-action" style="background: var(--primary);"
                                            onclick="event.stopPropagation(); abrirModalCompra(<?= $p['pro_id'] ?>, <?= $p['prom_id'] ?>, '<?= $p['prom_modalidad'] ?>', <?= $precioOferta ?>, <?= $puntosReq ?>, <?= $quedanPromo ?>, <?= $esLimitadaJs ?>)">
                                            Comprar <i class="fa-solid fa-cart-shopping"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                        <?php
                    };
                    ?>

                    <?php foreach($promociones as $promo) { $dibujarSuperCard($promo); } ?>
                    <?php foreach($promociones as $promo) { $dibujarSuperCard($promo); } ?>
                    
                    <?php if(count($promociones) < 4): ?>
                        <?php foreach($promociones as $promo) { $dibujarSuperCard($promo); } ?>
                        <?php foreach($promociones as $promo) { $dibujarSuperCard($promo); } ?>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>

    <!-- FIN SECCION CARRUCEL DE PROMOCIONES -->

    <!-- SECCION DE BUSQUEDA DE NEGOCIO -->


    <div class="business-section">
        
        <div class="biz-left">
            <h2>Descubre <span style="color:var(--primary)">Negocios</span> <br>Increíbles</h2>
            <p>Explora salones, barberías y spas registrados. <br>¡Encuentra tu favorito!</p>
            
            <div class="biz-search" style="position:relative;"> <i class="fa-solid fa-magnifying-glass" style="color:#aaa;"></i>
                <input type="text" id="inputNegocios" placeholder="Filtrar por nombre..." autocomplete="off" style="padding-right:30px;">
                <i class="fa-solid fa-xmark" id="btnBorrarNegocios" 
                style="position:absolute; right:15px; color:#999; cursor:pointer; display:none;"></i>
            </div>
        </div>

        <div class="biz-right" id="gridNegocios">
            <div style="text-align:center; grid-column: 1/-1; color:#999; padding:40px;">
                <i class="fa-solid fa-circle-notch fa-spin fa-2x"></i>
                <p style="margin-top:10px;">Cargando directorio...</p>
            </div>
        </div>

    </div>

    <div class="biz-modal-overlay" id="bizModalOverlay"></div>
    
    <div class="biz-drawer" id="bizDrawer">
        <div class="drawer-header" id="drawerHeaderBg">
            <div class="header-slideshow" id="headerSlideshow">
                </div>
            <div class="header-overlay"></div>
            <div class="btn-close-modal" id="btnCloseDrawer"><i class="fa-solid fa-xmark"></i></div>
            
            <div class="drawer-logo-wrapper">
                <img src="" id="drawerLogo" class="drawer-logo">
            </div>
        </div>
        
        <div class="drawer-info-container" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; min-height: 100px;">
            <div style="flex: 1;">
                <h1 class="drawer-title" id="drawerTitle" style="padding-left: 125px; margin-bottom: 5px;">Nombre Negocio</h1>
                
                <div class="drawer-meta" style="padding-left: 125px;">
                    <div class="badge-fundacion">
                        <i class="fa-regular fa-calendar-check"></i> 
                        Desde <span id="drawerFundacion">--</span>
                    </div>
                    
                    <div id="drawerBizInfoRating" style="display:none; align-items:center; gap:5px; font-size:0.8rem; font-weight:700; margin-top:3px;"></div>
                    
                    <a href="#" class="btn-telefono" id="drawerTelLink">
                        <i class="fa-solid fa-phone-volume"></i> 
                        <span id="drawerTel">--</span>
                    </a>
                </div>
            </div>

            <div id="drawerPointsContainer" style="margin-right: 20px; margin-top: 5px; width: 160px; flex-shrink: 0; display: none;"></div>
        </div>

        <div class="drawer-tabs">
            <div class="tab-btn active" onclick="cambiarTab('servicios')">
                <i class="fa-solid fa-spa"></i> Servicios
            </div>
            <div class="tab-btn" onclick="cambiarTab('productos')">
                <i class="fa-solid fa-bag-shopping"></i> Productos
            </div>
            <div class="tab-btn" onclick="cambiarTab('info')">
                <i class="fa-solid fa-map-location-dot"></i> Info
            </div>
        </div>

        <div class="drawer-body">
            <div id="tab-servicios" class="tab-content active">
                <div id="listaServicios"></div> </div>

            <div id="tab-productos" class="tab-content">
                <div id="listaProductos"></div> </div>

            <div id="tab-info" class="tab-content">
                <div id="listaSucursales"></div> </div>
        </div>
    </div>

    <!-- INICIO DE LA SECCION DE SERVICIOS -->

    <div class="services-section-wrapper" id="seccionServicios">
        
        <div class="services-header-text">
            <h2>Explora <span style="color:var(--primary)">Servicios Top</span></h2>
            <p>Las mejores opciones seleccionadas para ti.</p>
        </div>

        <div class="services-grid-container" id="servicesGrid">
            
            <?php if (!empty($listaServicios)): ?>
                <?php foreach ($listaServicios as $serv): ?>
                    <?php 
                        // 1. Preparar Datos Básicos
                        $fotoServ = !empty($serv['imagenes']) ? $serv['imagenes'][0] : 'recursos/img/sin_foto.png';
                        $logoNeg  = !empty($serv['neg_logo']) ? $serv['neg_logo'] : 'recursos/img/sin_foto.png';
                        
                        // 2. Lógica de Calificación (NUEVO)
                        $votos    = intval($serv['votos_total'] ?? 0);
                        $promedio = floatval($serv['rating_promedio'] ?? 0);
                    ?>
                    
                    <div class="service-card-item">
                        
                        <div class="serv-img-box">
                            <?php 
                                $imgs = !empty($serv['imagenes']) ? $serv['imagenes'] : ['recursos/img/sin_foto.png'];
                            ?>

                            <?php foreach($imgs as $k => $imgUrl): ?>
                                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                                    class="serv-img <?= ($k === 0) ? 'active' : '' ?>">
                            <?php endforeach; ?>

                            <?php if(count($imgs) > 1): ?>
                                <button class="slider-btn prev" onclick="cambiarSlide(this, -1)">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button class="slider-btn next" onclick="cambiarSlide(this, 1)">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($votos > 0): ?>
                                <div class="serv-rating-badge has-votes" title="<?= $votos ?> opiniones">
                                    <i class="fa-solid fa-star"></i> <?= number_format($promedio, 1) ?>
                                </div>
                            <?php else: ?>
                                <div class="serv-rating-badge be-first">
                                    <i class="fa-solid fa-trophy"></i> Sé el 1ro
                                </div>
                            <?php endif; ?>
                            <span class="serv-badge-time">
                                <i class="fa-regular fa-clock"></i> <?= $serv['serv_duracion'] ?> min
                            </span>

                            <div class="serv-biz-logo" title="<?= htmlspecialchars($serv['neg_nombre']) ?>">
                                <img src="<?= htmlspecialchars($logoNeg) ?>">
                            </div>
                        </div>

                        <div class="serv-body">
                            <div class="serv-biz-name" style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: 4px;">
                                <span><i class="fa-solid fa-shop"></i> <?= htmlspecialchars($serv['neg_nombre']) ?></span>
                                
                                <?php if (!empty($serv['puntos_ganados']) && $serv['puntos_ganados'] > 0): ?>
                                    <span style="color: #00b894; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; gap: 4px;" title="Ganas <?= $serv['puntos_ganados'] ?> puntos">
                                        <i class="fa-solid fa-coins"></i> +<?= $serv['puntos_ganados'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="serv-title"><?= htmlspecialchars($serv['serv_nombre']) ?></h3>
                            <p class="serv-desc">
                                <?= mb_strimwidth(strip_tags($serv['serv_descripcion']), 0, 55, "...") ?>
                            </p>
                            
                            <div class="serv-footer">
                                <div class="serv-price">
                                    <small>Precio</small>
                                    $<?= number_format($serv['serv_precio'], 2) ?>
                                </div>
                                <button class="btn-reservar-card" onclick="abrirModalServicio(<?= $serv['serv_id'] ?>)">
                                    Reservar <i class="fa-solid fa-calendar-check" style="margin-left:5px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-services" style="grid-column: 1/-1; text-align:center; padding: 40px; color:#999;">
                    <i class="fa-regular fa-calendar-xmark fa-2x"></i>
                    <p>No hay servicios disponibles por el momento.</p>
                </div>
            <?php endif; ?>

        </div>

        <div class="load-more-container">
            <button id="btnLoadMoreServices" class="btn-load-more">
                <span id="txtBtnMore">Ver Más Servicios</span>
                <i class="fa-solid fa-chevron-down" id="iconBtnMore"></i>
                <i class="fa-solid fa-circle-notch fa-spin" id="loaderBtnMore" style="display:none;"></i>
            </button>

            <button id="btnShowLess" class="btn-load-more" style="display:none; margin-left: 10px; border-color: var(--primary); color: var(--primary);">
                Ocultar Todo <i class="fa-solid fa-chevron-up"></i>
            </button>
        </div>

    </div>


    <!-- FIN DE LA SECCION DE SERVICIOS -->


    <!-- INICIO DE LA SECCION DE PRODUCTOS -->


    <div class="services-section-wrapper" id="seccionProductos" style="background: #fff;"> <div class="services-header-text">
            <h2>Productos <span style="color:var(--primary)">Recomendados</span></h2>
            <p>Artículos de belleza y cuidado personal listos para ti.</p>
        </div>

        <div class="services-grid-container" id="productsGrid">
            
            <?php if (!empty($listaProductos)): ?>
                <?php foreach ($listaProductos as $prod): ?>
                    <?php 
                        // Preparar Datos Visuales
                        $fotoProd = !empty($prod['imagenes']) ? $prod['imagenes'][0] : 'recursos/img/sin_foto.png';
                        $logoNeg  = !empty($prod['neg_logo']) ? $prod['neg_logo'] : 'recursos/img/sin_foto.png';
                        
                        // Formatear Presentación
                        $presentacion = floatval($prod['pro_contenido']) . ' ' . $prod['pro_unidad_consumo'];
                        if($prod['pro_unidad'] !== 'Unidad') {
                            $presentacion = $prod['pro_unidad'] . ' de ' . $presentacion;
                        }

                        // --- LÓGICA DE CALIFICACIÓN (PRODUCTOS) ---
                        $votosP = intval($prod['votos_total'] ?? 0);
                        $promP  = floatval($prod['rating_promedio'] ?? 0);
                    ?>
                    
                    <div class="service-card-item">
                        
                        <div class="serv-img-box">
                            <?php 
                                $imgs = !empty($prod['imagenes']) ? $prod['imagenes'] : ['recursos/img/sin_foto.png'];
                            ?>

                            <?php foreach($imgs as $k => $imgUrl): ?>
                                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                                        class="serv-img <?= ($k === 0) ? 'active' : '' ?>">
                            <?php endforeach; ?>

                            <?php if(count($imgs) > 1): ?>
                                <button class="slider-btn prev" onclick="cambiarSlide(this, -1)">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button class="slider-btn next" onclick="cambiarSlide(this, 1)">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($votosP > 0): ?>
                                <div class="serv-rating-badge has-votes" title="<?= $votosP ?> opiniones">
                                    <i class="fa-solid fa-star"></i> <?= number_format($promP, 1) ?>
                                </div>
                            <?php else: ?>
                                <div class="serv-rating-badge be-first">
                                    <i class="fa-solid fa-trophy"></i> Sé el 1ro
                                </div>
                            <?php endif; ?>

                            <span class="serv-badge-time" style="background: var(--primary);">
                                <i class="fa-solid fa-box-open"></i> <?= $presentacion ?>
                            </span>

                            <div class="serv-biz-logo" title="<?= htmlspecialchars($prod['neg_nombre']) ?>">
                                <img src="<?= htmlspecialchars($logoNeg) ?>">
                            </div>
                        </div>

                        <div class="serv-body">
                        <div class="serv-biz-name" style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: 4px;">
                            <span><i class="fa-solid fa-shop"></i> <?= htmlspecialchars($prod['neg_nombre']) ?></span>
                            
                            <?php if (!empty($prod['puntos_ganados']) && $prod['puntos_ganados'] > 0): ?>
                                <span style="color: #00b894; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; gap: 4px;" title="Ganas <?= $prod['puntos_ganados'] ?> puntos">
                                    <i class="fa-solid fa-coins"></i> +<?= $prod['puntos_ganados'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 class="serv-title"><?= htmlspecialchars($prod['pro_nombre']) ?></h3>
                            <p class="serv-desc">
                                <?= mb_strimwidth(strip_tags($prod['pro_descripcion']), 0, 55, "...") ?>
                            </p>
                            <div class="serv-footer">
                                <div class="serv-price">
                                    <small>Precio</small>
                                    $<?= number_format($prod['pro_precio'], 2) ?>
                                </div>
                                
                                <button class="btn-reservar-card" style="background: var(--primary);" onclick="abrirModalCompra(<?= $prod['pro_id'] ?>)">
                                    Comprar <i class="fa-solid fa-cart-shopping"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-services" style="grid-column: 1/-1; text-align:center; padding: 40px; color:#999;">
                    <i class="fa-solid fa-box-open fa-2x"></i>
                    <p>No hay productos disponibles por el momento.</p>
                </div>
            <?php endif; ?>

        </div>

        <div class="load-more-container">
            <button id="btnLoadMoreProducts" class="btn-load-more" style="border-color: var(--primary);; color: var(--primary);; background:white;">
                <span id="txtBtnMorePro">Ver Más Productos</span>
                <i class="fa-solid fa-chevron-down" id="iconBtnMorePro"></i>
                <i class="fa-solid fa-circle-notch fa-spin" id="loaderBtnMorePro" style="display:none;"></i>
            </button>

            <button id="btnShowLessPro" class="btn-load-more" style="display:none; margin-left: 10px; border-color: var(--primary);; color: var(--primary);; background:white;">
                Ocultar Todo <i class="fa-solid fa-chevron-up"></i>
            </button>
        </div>

    </div>


    <!-- FIN DE LA SECCION DE PRODUCTOS -->
    

</div>

<script>
    const RUTA_SIN_FOTO = 'recursos/img/sin_foto.png';

    window.cambiarSlide = function(btn, direction) {
    if(event) { event.preventDefault(); event.stopPropagation(); } // Evita clicks raros
    
    const container = btn.closest('.serv-img-box');
    const images = container.querySelectorAll('.serv-img');
    
    if(images.length <= 1) return; 

    let activeIndex = 0;
    images.forEach((img, index) => {
        if (img.classList.contains('active')) {
            activeIndex = index;
            img.classList.remove('active');
        }
    });

    let newIndex = (activeIndex + direction + images.length) % images.length;
    images[newIndex].classList.add('active');
};


document.addEventListener('DOMContentLoaded', () => {
    // Referencias DOM
    const hero = document.getElementById('hero-section');
    const input = document.getElementById('inputBusqueda');
    const btnClear = document.getElementById('btnBorrarBusqueda');
    const grid = document.getElementById('gridResultados');
    const noResults = document.getElementById('sinResultados');
    
    // --- LÓGICA MÓVIL: POSICIONAMIENTO PANEL ---
    const panel = document.getElementById('panelResultados');
    const wrapper = document.querySelector('.hero-content-wrapper');
    
    const checkMobile = () => {
        if (window.innerWidth <= 900) {
            wrapper.appendChild(panel); 
        } else {
            document.getElementById('hero-section').appendChild(panel); 
        }
    };
    window.addEventListener('resize', checkMobile);
    checkMobile(); 

    // --- BUSCADOR PRINCIPAL (ARRIBA) ---
    let debounceTimer;
    const resetSearch = () => {
        input.value = '';
        hero.classList.remove('searching');
        btnClear.style.display = 'none';
        setTimeout(() => { grid.innerHTML = ''; }, 500); 
    };

    btnClear.addEventListener('click', resetSearch);

    input.addEventListener('input', (e) => {
        const val = e.target.value.trim();
        btnClear.style.display = val.length > 0 ? 'flex' : 'none';
        
        if (val.length < 2) {
            if(val.length === 0) resetSearch();
            return; 
        }
        
        hero.classList.add('searching');
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            // NOTA: Para el buscador de arriba, si también quieres usar token, 
            // deberías generar una variable PHP similar. 
            // Por ahora asumimos que este usa el que definimos arriba o se mantiene.
            // Si el buscador de arriba te falla, avísame para generar su token también.
            fetch(`index.php?c=publico&a=buscar_ajax&q=${encodeURIComponent(val)}`) // <--- OJO AQUI SI FALLA
                .then(r => r.json())
                .then(data => {
                    grid.innerHTML = '';
                    if (data.length === 0) {
                        noResults.style.display = 'block';
                    } else {
                        noResults.style.display = 'none';
                        // --- DENTRO DEL FETCH DEL BUSCADOR VIVO ---
                        data.forEach((item, index) => {
                            const card = document.createElement('div');
                            card.className = 'mini-card';
                            card.style.animationDelay = `${index * 0.05}s`;
                            
                            card.onclick = () => {
                                if (item.tipo === 'servicio') abrirModalServicio(item.id);
                                else abrirModalCompra(item.id);
                            };

                            // 1. Preparar el HTML de Puntos con el icono de monedas
                            const puntos = parseInt(item.puntos_ganados) || 0;
                            let puntosHtml = '';
                            let containerStyle = 'justify-content: center;'; // Por defecto nombre centrado
                            
                            if (puntos > 0) {
                                // Si hay puntos: icono fa-coins, color turquesa y alineación a los extremos
                                puntosHtml = `<span style="color: #00b894; font-weight: 800;"><i class="fa-solid fa-coins"></i> +${puntos}</span>`;
                                containerStyle = 'justify-content: space-between;';
                            }

                            // 2. Rating Badge (Estrellas)
                            const votos = parseInt(item.votos_total) || 0;
                            const prom  = parseFloat(item.rating_promedio) || 0.0;
                            let badgeHtml = (votos > 0) 
                                ? `<div class="mini-rating-badge has-votes"><i class="fa-solid fa-star"></i> ${prom.toFixed(1)}</div>`
                                : `<div class="mini-rating-badge be-first"><i class="fa-solid fa-trophy"></i> Nuevo</div>`;

                            // 3. Meta info e icono del botón
                            let metaHtml = (item.tipo === 'servicio') 
                                ? `<i class="fa-regular fa-clock"></i> ${item.meta} min` 
                                : `<i class="fa-solid fa-box"></i> Stock`;

                            let btnContent = (item.tipo === 'servicio') ? 'Reservar' : 'Comprar';

                            // Plantilla de la tarjeta actualizada
                            card.innerHTML = `
                                <div class="mini-img-wrapper" style="position:relative;">
                                    ${badgeHtml}
                                    <img src="${item.imagen}" class="mini-img">
                                    <span class="mini-price-tag">$${parseFloat(item.precio).toFixed(2)}</span>
                                </div>
                                <div class="mini-info">
                                    <div style="display: flex; align-items: center; ${containerStyle} width: 100%; font-size:0.65rem; color:#999; text-transform:uppercase; font-weight:700; margin-bottom:2px;">
                                        <span><i class="fa-solid fa-shop"></i> ${item.negocio}</span>
                                        ${puntosHtml}
                                    </div>

                                    <h4 style="font-size:0.9rem; margin-bottom:4px; line-height:1.2; color:var(--dark);">${item.titulo}</h4>
                                    
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto;">
                                        <span style="font-size:0.7rem; color:#666;">${metaHtml}</span>
                                        <button class="btn-live-action">
                                            ${btnContent}
                                        </button>
                                    </div>
                                </div>
                            `;
                            grid.appendChild(card);
                        });
                    }
                })
                .catch(err => console.error(err));
        }, 300);
    });

    // =========================================================
    // 2. LÓGICA DIRECTORIO DE NEGOCIOS (MODO ACUARIO INTELIGENTE)
    // =========================================================
    const gridNegocios  = document.getElementById('gridNegocios');
    const inputNegocios = document.getElementById('inputNegocios');
    const btnBorrarBiz  = document.getElementById('btnBorrarNegocios');
    let allNegocios = []; 
    let activeTimeouts = []; 
    let slideInterval;

    // --- CORRECCIÓN DE RUTA DE IMAGEN ---
    const RUTA_SIN_FOTO = 'recursos/img/sin_foto.png';

    // --- CONFIGURACIÓN DE LA GRILLA (Para evitar superposiciones) ---
    const COLS = 4; // 4 Columnas
    const ROWS = 2; // 2 Filas
    // Total = 8 espacios. Si mostramos 5 tarjetas, siempre habrá huecos libres.
    let occupiedSlots = new Set(); // Aquí guardaremos qué espacios están ocupados

    // Función: Limpiar todo (resetear grilla y timers)
    const limpiarAnimaciones = () => {
        activeTimeouts.forEach(t => clearTimeout(t));
        activeTimeouts = [];
        occupiedSlots.clear(); // Liberar todos los espacios
        gridNegocios.innerHTML = ''; 
    };

    // Función: Obtener una posición LIBRE en la grilla
    const getSmartPos = () => {
        let available = [];
        
        // 1. Buscar qué celdas (slots) están vacías
        for (let r = 0; r < ROWS; r++) {
            for (let c = 0; c < COLS; c++) {
                let slotId = `${r}-${c}`;
                if (!occupiedSlots.has(slotId)) {
                    available.push({ r, c, id: slotId });
                }
            }
        }

        // Si por alguna razón rara no hay espacio, forzamos uno aleatorio
        if (available.length === 0) return { top: '10%', left: '10%', slotId: null };

        // 2. Elegir una celda vacía al azar
        const choice = available[Math.floor(Math.random() * available.length)];

        // 3. Calcular porcentajes base (Ej: Fila 0 es 0%, Fila 1 es 50%)
        const baseTop  = (choice.r / ROWS) * 100;
        const baseLeft = (choice.c / COLS) * 100;

        // 4. Agregar "Jitter" (Aleatoriedad controlada dentro de la celda)
        // Para que no se vea cuadriculado, le sumamos un random entre 2% y 15%
        const jitterTop  = 2 + Math.random() * 15; 
        const jitterLeft = 2 + Math.random() * 10;

        return {
            top: (baseTop + jitterTop) + '%',
            left: (baseLeft + jitterLeft) + '%',
            slotId: choice.id
        };
    };

    // Función: Ciclo de vida de una tarjeta
    const iniciarCicloTarjeta = (cardElement, oldSlotId = null) => {
        // Validación de seguridad
        if (!document.body.contains(cardElement)) return; 
        if (allNegocios.length === 0) return;

        // 1. Liberar el espacio anterior (si tenía uno)
        if (oldSlotId) {
            occupiedSlots.delete(oldSlotId);
        }

        // 2. Escoger nuevo negocio al azar
        const randomNeg = allNegocios[Math.floor(Math.random() * allNegocios.length)];
        const logoUrl = randomNeg.neg_logo ? randomNeg.neg_logo : RUTA_SIN_FOTO;

        cardElement.innerHTML = `<img src="${logoUrl}" class="biz-logo-img"><div class="biz-name">${randomNeg.neg_nombre}</div>`;
        cardElement.onclick = () => abrirModalNegocio(randomNeg.neg_id);

        // 3. Buscar NUEVA posición inteligente
        const pos = getSmartPos();
        
        // 4. Ocupar el nuevo espacio (Si obtuvimos uno válido)
        if (pos.slotId) {
            occupiedSlots.add(pos.slotId);
        }

        // 5. Mover tarjeta
        cardElement.style.top = pos.top;
        cardElement.style.left = pos.left;

        // 6. Aparecer
        let t1 = setTimeout(() => { cardElement.classList.add('visible'); }, 100);
        activeTimeouts.push(t1);

        // 7. Calcular vida útil (tiempo visible)
        const vidaUtil = 3500 + Math.random() * 3000;

        // 8. Programar desaparición y renacimiento
        let t2 = setTimeout(() => {
            cardElement.classList.remove('visible'); // Fade Out
            
            // Esperar transición CSS (0.8s) y reiniciar ciclo pasando el slot a liberar
            let t3 = setTimeout(() => {
                iniciarCicloTarjeta(cardElement, pos.slotId); 
            }, 800); 
            activeTimeouts.push(t3);

        }, vidaUtil);
        activeTimeouts.push(t2);
    };

    const renderizarNegocios = (filtro = '') => {
        limpiarAnimaciones(); 
        
        const esBusqueda = filtro.length > 0;

        if (esBusqueda) {
            // --- MODO BÚSQUEDA (Ordenado) ---
            gridNegocios.classList.add('searching-mode');
            const filtrados = allNegocios.filter(n => n.neg_nombre.toLowerCase().includes(filtro.toLowerCase()));

            if (filtrados.length === 0) {
                gridNegocios.innerHTML = '<div style="text-align:center; width:100%; padding:40px; color:#999;"><i class="fa-regular fa-face-frown fa-2x"></i><br>No encontramos coincidencias.</div>';
                return;
            }

            filtrados.forEach((neg, i) => {
                const card = document.createElement('div');
                card.className = 'biz-card';
                card.style.animationDelay = `${i * 0.05}s`;
                
                const logoUrl = neg.neg_logo ? neg.neg_logo : RUTA_SIN_FOTO;
                
                card.innerHTML = `<img src="${logoUrl}" class="biz-logo-img"><div class="biz-name">${neg.neg_nombre}</div>`;
                card.onclick = () => abrirModalNegocio(neg.neg_id);
                gridNegocios.appendChild(card);
            });

        } else {
            // --- MODO ACUARIO (ALEATORIO INTELIGENTE) ---
            gridNegocios.classList.remove('searching-mode');
            
            // Cantidad de tarjetas simultáneas (Menor que 8 para dejar huecos)
            const cantidadFlotantes = 5; 

            for (let i = 0; i < cantidadFlotantes; i++) {
                const card = document.createElement('div');
                card.className = 'biz-card floating-random';
                gridNegocios.appendChild(card);
                
                // Delay inicial en escalera para que no aparezcan todos de golpe
                let tInit = setTimeout(() => {
                    iniciarCicloTarjeta(card);
                }, i * 1000); 
                activeTimeouts.push(tInit);
            }
        }
    };

    // Cargar datos
    // Cargar datos
    const cargarDirectorioInicial = () => {
        // 1. Usamos la variable PHP, pero aseguramos que el navegador no la codifique doble
        // Nota: PHP ya genera la URL completa. 
        const urlRaw = '<?= $urlListarNegocios ?>'; 
        
        fetch(urlRaw, {
            headers: {
                'Accept': 'application/json', // <--- ESTO ES CRUCIAL PARA EL PASO 1
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            // Verificamos si la respuesta es válida antes de convertir a JSON
            if (!r.ok) throw new Error("Error en el servidor: " + r.status);
            return r.json();
        })
        .then(resp => {
            if (resp.success) {
                allNegocios = resp.data;
                renderizarNegocios(''); 
            } else {
                console.warn("Advertencia del servidor:", resp.error);
                // Opcional: mostrar mensaje en el grid
                gridNegocios.innerHTML = '<div style="padding:20px; text-align:center">No se pudo verificar la sesión. Recarga la página.</div>';
            }
        })
        .catch(err => {
            console.error("Error cargando directorio:", err);
            // Evita que se quede el spinner girando infinitamente
            gridNegocios.innerHTML = '<div style="padding:20px; text-align:center; color:red;">Error de conexión. Intenta recargar.</div>';
        });
    };

    cargarDirectorioInicial();

    // Listeners
    if(inputNegocios) {
        inputNegocios.addEventListener('input', (e) => {
            const val = e.target.value.trim();
            btnBorrarBiz.style.display = val.length > 0 ? 'block' : 'none';
            renderizarNegocios(val);
        });
    }

    if(btnBorrarBiz) {
        btnBorrarBiz.addEventListener('click', () => {
            inputNegocios.value = '';
            btnBorrarBiz.style.display = 'none';
            renderizarNegocios(''); 
        });
    }

    // =========================================================
    // 3. LÓGICA DEL MODAL (OFF-CANVAS)
    // =========================================================
    const drawer        = document.getElementById('bizDrawer');
    const overlay       = document.getElementById('bizModalOverlay');
    const btnClose      = document.getElementById('btnCloseDrawer');
    
    // Referencias internas del Modal
    const uiTitulo      = document.getElementById('drawerTitle');
    const uiLogo        = document.getElementById('drawerLogo');
    const uiTel         = document.getElementById('drawerTel');
    const contServicios = document.getElementById('listaServicios');
    const contProductos = document.getElementById('listaProductos');
    const contSucursales= document.getElementById('listaSucursales');

    // FUNCIÓN: Cerrar Modal
    const cerrarModal = () => {
        drawer.classList.remove('active');
        overlay.classList.remove('active');
        if(slideInterval) clearInterval(slideInterval); // DETENER SLIDESHOW AL CERRAR
    };

    if(btnClose) btnClose.onclick = cerrarModal;
    if(overlay) overlay.onclick = cerrarModal;

    // FUNCIÓN: Cambiar de Pestaña
    window.cambiarTab = (tabName) => {
        // 1. Quitar activo a todos los botones y contenidos
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // 2. Activar el seleccionado (buscando por texto o índice, aquí simplificado)
        // Nota: En el HTML los botones llaman a cambiarTab('servicios'), etc.
        // Vamos a buscar el botón que corresponda visualmente:
        const botones = document.querySelectorAll('.tab-btn');
        if(tabName === 'servicios') botones[0].classList.add('active');
        if(tabName === 'productos') botones[1].classList.add('active');
        if(tabName === 'info')      botones[2].classList.add('active');

        // 3. Mostrar contenido
        document.getElementById(`tab-${tabName}`).classList.add('active');
    };

    // Función auxiliar para desordenar array (Aleatorio)
    const mezclarArray = (array) => {
        return array.sort(() => Math.random() - 0.5);
    };

    // FUNCIÓN PRINCIPAL: Abrir y Cargar Negocio
    window.abrirModalNegocio = (id) => {
        // --- FUNCIÓN INTERNA PARA RENDERIZAR LAS PESTAÑAS (CORRECCIÓN) ---
        const procesarBloque = (items, tipo, negocioId) => {
            if (!items || items.length === 0) {
                return `<div style="text-align:center; padding:40px; color:#999;">No hay ${tipo} disponibles.</div>`;
            }

            let itemsMix = items.sort(() => Math.random() - 0.5);
            let itemsShow = itemsMix.slice(0, 6);
            let html = '<div class="items-grid">';
            
            itemsShow.forEach(item => {
                let img, titulo, desc, footerHtml;
                let badgeHtml = ''; 
                let pointsBadge = '';

                if(tipo === 'servicio') {
                    img = item.imagen || RUTA_SIN_FOTO;
                    titulo = item.serv_nombre;
                    desc = item.serv_descripcion || 'Sin descripción';
                    const pts = parseInt(item.puntos_ganados) || 0;
                    if(pts > 0) {
                        pointsBadge = `<div style="position: absolute; top: 8px; left: 8px; background: rgba(255, 255, 255, 0.9); color: #00b894; padding: 4px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; z-index: 25; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"><i class="fa-solid fa-coins"></i> +${pts}</div>`;
                    }
                    const prom = parseFloat(item.rating_promedio) || 0.0;
                    badgeHtml = prom > 0 ? `<div class="serv-rating-badge has-votes"><i class="fa-solid fa-star"></i> ${prom.toFixed(1)}</div>` : `<div class="serv-rating-badge be-first"><i class="fa-solid fa-trophy"></i> Nuevo</div>`;
                    footerHtml = `<div style="display:flex; flex-direction:column;"><span style="font-size:0.7rem; color:#aaa;">${item.serv_duracion} min</span><span class="item-price">$${parseFloat(item.serv_precio).toFixed(2)}</span></div><button onclick="abrirModalServicio(${item.serv_id})" class="btn-reservar-card">Reservar <i class="fa-solid fa-calendar-check" style="margin-left:5px;"></i></button>`;
                } 
                else if (tipo === 'producto') {
                    img = item.imagen || RUTA_SIN_FOTO;
                    titulo = item.pro_nombre;
                    desc = item.pro_descripcion || 'Sin descripción';
                    const pts = parseInt(item.puntos_ganados) || 0;
                    if(pts > 0) {
                        pointsBadge = `<div style="position: absolute; top: 8px; left: 8px; background: rgba(255, 255, 255, 0.9); color: #00b894; padding: 4px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; z-index: 25; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"><i class="fa-solid fa-coins"></i> +${pts}</div>`;
                    }
                    const promP = parseFloat(item.rating_producto) || 0.0;
                    badgeHtml = promP > 0 ? `<div class="serv-rating-badge has-votes"><i class="fa-solid fa-star"></i> ${promP.toFixed(1)}</div>` : `<div class="serv-rating-badge be-first"><i class="fa-solid fa-trophy"></i> Nuevo</div>`;
                    footerHtml = `<span class="item-price">$${parseFloat(item.pro_precio).toFixed(2)}</span><button onclick="abrirModalCompra(${item.pro_id})" class="btn-reservar-card" style="background: var(--primary);">Comprar <i class="fa-solid fa-cart-shopping"></i></button>`;
                } 
                else if (tipo === 'sucursal') {
                    img = item.suc_foto || RUTA_SIN_FOTO;
                    titulo = item.suc_nombre;
                    desc = `<i class="fa-solid fa-location-dot" style="color:var(--primary);"></i> ${item.suc_direccion}`;
                    let btnMapa = item.suc_latitud ? `<a href="https://www.google.com/maps?q=${item.suc_latitud},${item.suc_longitud}" target="_blank" class="btn-add" style="display:flex; text-decoration:none;"><i class="fa-solid fa-map-location-dot"></i></a>` : '';
                    footerHtml = `<span class="item-price" style="font-size:0.9rem;"><i class="fa-solid fa-phone"></i> ${item.suc_telefono || '--'}</span> ${btnMapa}`;
                }

                html += `
                    <div class="item-card">
                        <div class="item-img-container">
                            ${pointsBadge} ${badgeHtml}
                            <img src="${img}" class="item-img">
                        </div>
                        <div class="item-body">
                            <h4 class="item-title">${titulo}</h4>
                            <p class="item-desc">${desc}</p>
                            <div class="item-footer">${footerHtml}</div>
                        </div>
                    </div>`;
            });

            const baseUrl = '<?= $urlVisitarNegocio ?>'; 
            html += `<div class="btn-ver-mas-container"><a href="${baseUrl}&id=${negocioId}" class="btn-ver-mas">Visitar Negocio <i class="fa-solid fa-arrow-right-long" style="margin-left:5px;"></i></a></div></div>`;
            return html;
        };
        // 1. Mostrar Modal
        drawer.classList.add('active');
        overlay.classList.add('active');
        
        // Resetear visualmente
        uiTitulo.innerText = "Cargando...";
        uiLogo.src= RUTA_SIN_FOTO;
        contServicios.innerHTML  = '<div style="padding:20px; text-align:center; color:#999"><i class="fa-solid fa-circle-notch fa-spin"></i> Cargando...</div>';
        contProductos.innerHTML  = '';
        contSucursales.innerHTML = '';
        
        // Resetear Pestaña
        window.cambiarTab('servicios');

        // 0. RESETEAR SLIDESHOW (Del paso anterior)
        if(slideInterval) clearInterval(slideInterval);
        const contenedorSlides = document.getElementById('headerSlideshow');
        contenedorSlides.innerHTML = ''; 

        // 2. Pedir datos
        const urlPerfil = '<?= $urlVerPerfil ?>'; 

        fetch(`${urlPerfil}&id=${id}`)
        .then(r => r.json())
        .then(resp => {
            if (resp.success) {
                const data = resp.data;
                
                // 1. ASIGNACIÓN DE DATOS BÁSICOS
                uiTitulo.innerText = data.info.neg_nombre;
                uiLogo.src         = data.info.neg_logo ? data.info.neg_logo : RUTA_SIN_FOTO;
                
                const fundacion = data.info.neg_fundacion ? new Date(data.info.neg_fundacion).getFullYear() : '----';
                document.getElementById('drawerFundacion').innerText = fundacion;
                
                let telefono = 'No registrado';
                if (data.sucursales && data.sucursales.length > 0) {
                    telefono = data.sucursales[0].suc_telefono || 'No registrado';
                }
                uiTel.innerText = telefono;
                document.getElementById('drawerTelLink').href = telefono !== 'No registrado' ? `tel:${telefono}` : '#';

                // 2. LÓGICA DE CALIFICACIÓN (ESTRELLAS)
                const boxRating = document.getElementById('drawerBizInfoRating');
                const ratingN   = parseFloat(data.info.rating_negocio) || 0;
                const votosN    = parseInt(data.info.votos_negocio) || 0;

                if (votosN > 0) {
                    boxRating.style.display = 'flex';
                    boxRating.innerHTML = `
                        <i class="fa-solid fa-star" style="color:#f1c40f;"></i> 
                        <span style="color:#2d3436;">${ratingN.toFixed(1)} / 5</span>
                        <span style="color:#aaa; font-weight:400; font-size:0.8rem;">(${votosN} opiniones)</span>
                    `;
                } else {
                    boxRating.style.display = 'flex';
                    boxRating.innerHTML = `
                        <span style="background:#e0fcf6; color:#00b894; padding:2px 8px; border-radius:10px; font-size:0.75rem;">
                            <i class="fa-solid fa-trophy"></i> Nuevo
                        </span>
                    `;
                }

                // 3. CONTROL DEL SWITCH DE FIDELIDAD (SIN DUPLICADOS)
                const pointsContainer = document.getElementById('drawerPointsContainer');
                pointsContainer.innerHTML = ''; // Limpiar siempre al abrir
                pointsContainer.style.display = 'none'; // Oculto por defecto

                if (parseInt(data.info.fidelidad_activa) === 1) {
                    pointsContainer.style.display = 'block'; 
                    const puntosUsuario = parseInt(data.puntos_cliente) || 0;

                    if (puntosUsuario > 0) {
                        pointsContainer.innerHTML = `
                            <div style="background: #e0fcf6; border: 1px solid #b3f5e1; padding: 10px; border-radius: 15px; text-align: center; box-shadow: 0 4px 10px rgba(0,184,148,0.05);">
                                <div style="color: #00b894; font-size: 1.2rem; margin-bottom: 2px;">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <p style="margin:0; font-size: 0.65rem; color: #666; font-weight: 700; text-transform: uppercase;">Tu Saldo</p>
                                <p style="margin:0; font-size: 1.1rem; font-weight: 900; color: #2d3436;">${puntosUsuario} <span style="font-size:0.7rem; font-weight:500;">pts</span></p>
                            </div>
                        `;
                    } else {
                        pointsContainer.innerHTML = `
                            <div style="background: #fff; border: 1px dashed #fd79a8; padding: 10px; border-radius: 15px; text-align: center;">
                                <div style="color: var(--primary); font-size: 1.1rem; margin-bottom: 2px;">
                                    <i class="fa-solid fa-gift"></i>
                                </div>
                                <p style="margin:0; font-size: 0.7rem; font-weight: 800; color: var(--dark); line-height:1.1;">¡Gana premios!</p>
                                <p style="margin:0; font-size: 0.6rem; color: #888; line-height: 1.2;">Reserva servicios con <i class="fa-solid fa-coins" style="color:#00b894;"></i></p>
                            </div>
                        `;
                    }
                }

                // 4. SLIDESHOW Y CARGA DE PESTAÑAS (Mantenemos igual)
                let fotos = [];
                if(data.sucursales && data.sucursales.length > 0){
                    fotos = data.sucursales.map(s => s.suc_foto).filter(f => f != null && f !== '');
                }
                if(fotos.length > 0) {
                    fotos.forEach((foto, index) => {
                        const img = document.createElement('img');
                        img.src = (foto && foto !== 'undefined') ? foto : RUTA_SIN_FOTO;
                        img.className = index === 0 ? 'slide-img active' : 'slide-img';
                        contenedorSlides.appendChild(img);
                    });
                    if(fotos.length > 1) {
                        let currentSlide = 0;
                        const slides = contenedorSlides.querySelectorAll('.slide-img');
                        slideInterval = setInterval(() => {
                            slides[currentSlide].classList.remove('active');
                            currentSlide = (currentSlide + 1) % slides.length;
                            slides[currentSlide].classList.add('active');
                        }, 4000);
                    } else {
                        const imgDefault = document.createElement('img');
                        imgDefault.src = RUTA_SIN_FOTO;
                        imgDefault.className = 'slide-img active';
                        contenedorSlides.appendChild(imgDefault);
                    }
                }

                // 5. RENDERIZAR CONTENIDO DE PESTAÑAS
                contServicios.innerHTML  = procesarBloque(data.servicios, 'servicio', data.info.neg_id);
                contProductos.innerHTML  = procesarBloque(data.productos, 'producto', data.info.neg_id);
                contSucursales.innerHTML = procesarBloque(data.sucursales, 'sucursal', data.info.neg_id);

            } else {
                alert("Error al cargar datos.");
                cerrarModal();
            }
        })
        .catch(err => {
            console.error(err);
            cerrarModal();
        });
    };



    // --- FUNCIÓN CARRUSEL AUTOMÁTICO ---
    function iniciarCarruselAutomatico() {
        if(window.autoPlayInterval) clearInterval(window.autoPlayInterval); // Limpiar anterior

        window.autoPlayInterval = setInterval(() => {
            const cards = document.querySelectorAll('.service-card-item');
            cards.forEach(card => {
                // Si el mouse está encima, NO mover la foto
                if(card.matches(':hover')) return;

                const images = card.querySelectorAll('.serv-img');
                if (images.length > 1) {
                    let activeIndex = 0;
                    images.forEach((img, index) => {
                        if (img.classList.contains('active')) activeIndex = index;
                    });
                    
                    images[activeIndex].classList.remove('active');
                    // Mover al siguiente
                    let nextIndex = (activeIndex + 1) % images.length;
                    images[nextIndex].classList.add('active');
                }
            });
        }, 3500); // Velocidad: 3.5 segundos
    }


    // ==============================================================
    // LÓGICA DE CARGA INFINITA DE SERVICIOS (AJAX)
    // ==============================================================
    let offsetActual = 8; // Ya cargamos 16 con PHP
    const limitBatch = 8;
    const btnLoad = document.getElementById('btnLoadMoreServices');
    const btnLess = document.getElementById('btnShowLess');
    const gridServ = document.getElementById('servicesGrid'); // OJO: Usar ID diferente al gridResultados
    const txtBtn = document.getElementById('txtBtnMore');
    const iconBtn = document.getElementById('iconBtnMore');
    const loaderBtn = document.getElementById('loaderBtnMore');

    // URL Segura generada en PHP (Asegúrate de haberla creado arriba en PHP)
    const urlCargarMas = '<?= isset($urlCargarMas) ? $urlCargarMas : "" ?>'; 

    
    // --- Botón OCULTAR TODO (CORREGIDO) ---
    if(btnLess) {
        btnLess.addEventListener('click', () => {
            // A. Eliminar tarjetas AJAX
            document.querySelectorAll('.ajax-loaded').forEach(el => el.remove());
            
            // B. Resetear variables
            offsetActual = 8; 

            // C. Gestionar botones
            btnLess.style.display = 'none';        // Ocultar el botón "Ocultar"
            
            // D. --- AQUÍ ESTABA EL FALLO: HAY QUE RESETEAR EL BOTÓN VER MÁS ---
            btnLoad.style.display = 'inline-flex'; // 1. Hacerlo visible
            btnLoad.disabled = false;              // 2. Reactivarlo
            txtBtn.innerText = 'Ver Más Servicios';// 3. Poner el texto original
            iconBtn.style.display = 'inline-block';// 4. Mostrar flecha
            loaderBtn.style.display = 'none';      // 5. Ocultar ruedita de carga
            
            // E. SCROLL CON OFFSET (CORREGIDO)
            const section = document.getElementById('seccionServicios');
            const y = section.getBoundingClientRect().top + window.scrollY - 60; 
            window.scrollTo({top: y, behavior: 'smooth'});
        });
    }

    // --- Botón VER MÁS (CORREGIDO LÓGICA FINAL) ---
    if(btnLoad && urlCargarMas) {
        btnLoad.addEventListener('click', () => {
            // Estado de carga
            btnLoad.disabled = true;
            txtBtn.innerText = 'Cargando...';
            iconBtn.style.display = 'none';
            loaderBtn.style.display = 'inline-block';

            fetch(`${urlCargarMas}&offset=${offsetActual}`)
                .then(r => r.json())
                .then(resp => {
                    // CASO A: Si trae datos
                    if(resp.success && resp.data.length > 0) {
                        resp.data.forEach(serv => {
                            gridServ.insertAdjacentHTML('beforeend', crearTarjetaServicioHTML(serv));
                        });
                        
                        offsetActual += limitBatch;
                        
                        // Mostrar botón ocultar apenas cargamos algo
                        btnLess.style.display = 'inline-flex';

                        // SI TRAJO MENOS DE 8, ES EL FINAL -> OCULTAR BOTÓN CARGAR
                        if(resp.data.length < limitBatch) {
                            btnLoad.style.display = 'none';
                        }
                        
                        iniciarCarruselAutomatico(); 
                    } 
                    // CASO B: Si NO trae datos (Llegamos al final exacto) -> OCULTAR BOTÓN
                    else {
                        btnLoad.style.display = 'none';
                    }
                })
                .catch(err => { 
                    console.error("Error:", err); 
                })
                .finally(() => {
                    // Solo restaurar el botón si NO lo ocultamos por llegar al final
                    if(btnLoad.style.display !== 'none'){
                        btnLoad.disabled = false;
                        txtBtn.innerText = 'Ver Más Servicios';
                        iconBtn.style.display = 'inline-block';
                        loaderBtn.style.display = 'none';
                    }
                });
        });
    }

    

    // --- ACTUALIZACIÓN DE LA PLANTILLA JS (Ver Más) ---
    
    function crearTarjetaServicioHTML(s) {
        const logo = s.neg_logo ? s.neg_logo : 'recursos/img/sin_foto.png';
        let desc = s.serv_descripcion ? s.serv_descripcion.replace(/(<([^>]+)>)/gi, "") : 'Sin descripción';
        if(desc.length > 55) desc = desc.substring(0, 55) + '...';
        const precio = parseFloat(s.serv_precio).toFixed(2);

        // 1. LÓGICA DE PUNTOS (Icono de monedas y color turquesa)
        let puntosHtml = '';
        if (s.puntos_ganados && parseInt(s.puntos_ganados) > 0) {
            puntosHtml = `
                <span style="color: #00b894; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; gap: 4px;" title="Ganas ${s.puntos_ganados} puntos">
                    <i class="fa-solid fa-coins"></i> +${s.puntos_ganados}
                </span>
            `;
        }

        // 2. LÓGICA DE RATING
        const votos = parseInt(s.votos_total) || 0;
        const promedio = parseFloat(s.rating_promedio) || 0.0;
        let htmlRating = (votos > 0) 
            ? `<div class="serv-rating-badge has-votes" title="${votos} opiniones"><i class="fa-solid fa-star"></i> ${promedio.toFixed(1)}</div>`
            : `<div class="serv-rating-badge be-first"><i class="fa-solid fa-trophy"></i> Sé el 1ro</div>`;

        // 3. IMÁGENES
        let imagenesHTML = '';
        const listaFotos = (s.imagenes && s.imagenes.length > 0) ? s.imagenes : ['recursos/img/sin_foto.png'];
        listaFotos.forEach((url, idx) => {
            imagenesHTML += `<img src="${url}" class="serv-img ${idx === 0 ? 'active' : ''}">`;
        });

        let controlsHTML = (listaFotos.length > 1) 
            ? `<button class="slider-btn prev" onclick="cambiarSlide(this, -1)"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-btn next" onclick="cambiarSlide(this, 1)"><i class="fa-solid fa-chevron-right"></i></button>`
            : '';

        return `
        <div class="service-card-item ajax-loaded">
            <div class="serv-img-box">
                ${imagenesHTML} ${controlsHTML} ${htmlRating} 
                <span class="serv-badge-time"><i class="fa-regular fa-clock"></i> ${s.serv_duracion} min</span>
                <div class="serv-biz-logo"><img src="${logo}"></div>
            </div>
            <div class="serv-body">
                <div class="serv-biz-name" style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: 8px;">
                    <span><i class="fa-solid fa-shop"></i> ${s.neg_nombre}</span>
                    ${puntosHtml}
                </div>
                <h3 class="serv-title">${s.serv_title || s.serv_nombre}</h3>
                <p class="serv-desc">${desc}</p>
                <div class="serv-footer">
                    <div class="serv-price">
                        <small>Precio</small>
                        $${precio}
                    </div>
                    <button class="btn-reservar-card" onclick="abrirModalServicio(${s.serv_id})">
                        Reservar <i class="fa-solid fa-calendar-check" style="margin-left:5px;"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }
    iniciarCarruselAutomatico();


    // ==============================================================
    // LÓGICA CARGA INFINITA PRODUCTOS
    // ==============================================================
    let offsetPro = 8;
    const btnLoadPro = document.getElementById('btnLoadMoreProducts');
    const btnLessPro = document.getElementById('btnShowLessPro');
    const gridPro    = document.getElementById('productsGrid');
    const urlCargarMasPro = '<?= isset($urlCargarMasProductos) ? $urlCargarMasProductos : "" ?>';

    // Función Plantilla JS para Productos
    function crearTarjetaProductoHTML(p) {
        const logo = p.neg_logo ? p.neg_logo : 'recursos/img/sin_foto.png';
        let desc = p.pro_descripcion ? p.pro_descripcion.replace(/(<([^>]+)>)/gi, "") : 'Sin descripción';
        if(desc.length > 55) desc = desc.substring(0, 55) + '...';
        
        // Formatear presentación
        let presentacion = parseFloat(p.pro_contenido) + ' ' + p.pro_unit_consumo; // Ajustado a tus datos
        if(p.pro_unidad !== 'Unidad') presentacion = p.pro_unidad + ' de ' + presentacion;

        // 1. LÓGICA DE PUNTOS
        let puntosHtml = '';
        if (p.puntos_ganados && parseInt(p.puntos_ganados) > 0) {
            puntosHtml = `
                <span style="color: #00b894; font-weight: 800; font-size: 0.75rem; display: flex; align-items: center; gap: 4px;" title="Ganas ${p.puntos_ganados} puntos">
                    <i class="fa-solid fa-coins"></i> +${p.puntos_ganados}
                </span>
            `;
        }

        // 2. LÓGICA RATING
        const votos = parseInt(p.votos_total) || 0;
        const prom = parseFloat(p.rating_promedio) || 0.0;
        let badgeHtml = (votos > 0) 
            ? `<div class="serv-rating-badge has-votes"><i class="fa-solid fa-star"></i> ${prom.toFixed(1)}</div>`
            : `<div class="serv-rating-badge be-first"><i class="fa-solid fa-trophy"></i> Nuevo</div>`;

        // 3. FOTOS (CARRUSEL)
        let imagenesHTML = '';
        const listaFotos = (p.imagenes && p.imagenes.length > 0) ? p.imagenes : ['recursos/img/sin_foto.png'];
        listaFotos.forEach((url, idx) => {
            imagenesHTML += `<img src="${url}" class="serv-img ${idx === 0 ? 'active' : ''}">`;
        });

        let controlsHTML = (listaFotos.length > 1) 
            ? `<button class="slider-btn prev" onclick="cambiarSlide(this, -1)"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-btn next" onclick="cambiarSlide(this, 1)"><i class="fa-solid fa-chevron-right"></i></button>`
            : '';

        return `
        <div class="service-card-item ajax-loaded-pro">
            <div class="serv-img-box">
                ${imagenesHTML} ${controlsHTML}
                ${badgeHtml} 
                <span class="serv-badge-time" style="background: var(--primary);">
                    <i class="fa-solid fa-box-open"></i> ${presentacion}
                </span>
                <div class="serv-biz-logo"><img src="${logo}"></div>
            </div>
            <div class="serv-body">
                <div class="serv-biz-name" style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: 8px;">
                    <span><i class="fa-solid fa-shop"></i> ${p.neg_nombre}</span>
                    ${puntosHtml}
                </div>
                <h3 class="serv-title">${p.pro_nombre}</h3>
                <p class="serv-desc">${desc}</p>
                <div class="serv-footer">
                    <div class="serv-price"><small>Precio</small>$${parseFloat(p.pro_precio).toFixed(2)}</div>
                    <button class="btn-reservar-card" onclick="abrirModalCompra(${p.pro_id})" style="background: var(--primary);">
                        Comprar <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }

    if(btnLoadPro && urlCargarMasPro) {
        btnLoadPro.addEventListener('click', () => {
            // UI Loading
            btnLoadPro.disabled = true;
            document.getElementById('txtBtnMorePro').innerText = 'Cargando...';
            document.getElementById('iconBtnMorePro').style.display = 'none';
            document.getElementById('loaderBtnMorePro').style.display = 'inline-block';

            fetch(`${urlCargarMasPro}&offset=${offsetPro}`)
                .then(r => r.json())
                .then(resp => {
                    if(resp.success && resp.data.length > 0) {
                        resp.data.forEach(prod => {
                            gridPro.insertAdjacentHTML('beforeend', crearTarjetaProductoHTML(prod));
                        });
                        offsetPro += 8;
                        btnLessPro.style.display = 'inline-flex';
                        if(resp.data.length < 8) btnLoadPro.style.display = 'none';
                        iniciarCarruselAutomatico(); // Reactivar sliders
                    } else {
                        btnLoadPro.style.display = 'none';
                    }
                })
                .catch(console.error)
                .finally(() => {
                    if(btnLoadPro.style.display !== 'none'){
                        btnLoadPro.disabled = false;
                        document.getElementById('txtBtnMorePro').innerText = 'Ver Más Productos';
                        document.getElementById('iconBtnMorePro').style.display = 'inline-block';
                        document.getElementById('loaderBtnMorePro').style.display = 'none';
                    }
                });
        });

        // Botón Ocultar (Productos)
        // Botón Ocultar (Productos)
        btnLessPro.addEventListener('click', () => {
            document.querySelectorAll('.ajax-loaded-pro').forEach(el => el.remove());
            offsetPro = 8;
            btnLessPro.style.display = 'none';
            
            // --- REINICIAR ESTADO DEL BOTÓN VER MÁS ---
            btnLoadPro.style.display = 'inline-flex';
            btnLoadPro.disabled = false;
            document.getElementById('txtBtnMorePro').innerText = 'Ver Más Productos'; // Restaurar texto
            document.getElementById('iconBtnMorePro').style.display = 'inline-block'; // Mostrar flecha
            document.getElementById('loaderBtnMorePro').style.display = 'none';       // Ocultar spinner
            
            // Scroll suave con offset
            const section = document.getElementById('seccionProductos');
            const y = section.getBoundingClientRect().top + window.scrollY - 60; 
            window.scrollTo({top: y, behavior: 'smooth'});
        });
    }



}); // Fin del DOMContentLoaded



// =========================================================
// LÓGICA DE LA CINTA DE OFERTAS (SWIPE + AUTOPLAY)
// =========================================================
document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.promo-track');
    
    if (track) {
        let currentX = 0;          // Posición actual
        let isDragging = false;    // ¿Está el usuario tocando?
        let startX = 0;            // Dónde empezó el toque
        let previousTranslate = 0; // Posición antes de arrastrar
        let animationID;           // ID del frame de animación
        let isPaused = false;      // Pausa lógica
        let pauseTimeout;          // Temporizador de los 4 segundos
        
        // Velocidad automática (ajustar si es muy rápido/lento)
        const speed = 0.5; 

        // 1. FUNCIÓN DE MOVIMIENTO AUTOMÁTICO
        const autoPlay = () => {
            if (!isDragging && !isPaused) {
                currentX -= speed;
                
                // Lógica Infinita:
                // Como tenemos el contenido duplicado (x2), si llegamos a la mitad, 
                // reseteamos a 0 sin que el usuario se de cuenta.
                const maxScroll = track.scrollWidth / 2;
                
                if (Math.abs(currentX) >= maxScroll) {
                    currentX = 0; 
                }
                
                track.style.transform = `translateX(${currentX}px)`;
            }
            animationID = requestAnimationFrame(autoPlay);
        };

        // Iniciar movimiento
        animationID = requestAnimationFrame(autoPlay);

        // 2. DETECCIÓN DE TOQUE / MOUSE (SWIPE)
        
        // --- INICIO DEL TOQUE ---
        const touchStart = (index) => {
            isDragging = true;
            isPaused = true; // Pausar inmediatamente
            
            // Limpiar cualquier temporizador de reanudación anterior
            if(pauseTimeout) clearTimeout(pauseTimeout);

            startX = getPositionX(index);
            previousTranslate = currentX;
            
            // Efecto visual de agarre
            track.style.transition = 'none'; 
        }

        // --- MOVIENDO EL DEDO ---
        const touchMove = (index) => {
            if (isDragging) {
                const currentPosition = getPositionX(index);
                const diff = currentPosition - startX;
                currentX = previousTranslate + diff;
                track.style.transform = `translateX(${currentX}px)`;
            }
        }

        // --- SOLTAR EL DEDO ---
        const touchEnd = () => {
            isDragging = false;
            
            // Programar reanudación en 4 SEGUNDOS
            pauseTimeout = setTimeout(() => {
                isPaused = false;
            }, 4000);
        }

        // Helpers para obtener X (sea Mouse o Touch)
        const getPositionX = (event) => {
            return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
        }

        // EVENT LISTENERS (Mouse y Touch)
        track.addEventListener('touchstart', (e) => touchStart(e), {passive: true});
        track.addEventListener('touchend', touchEnd);
        track.addEventListener('touchmove', (e) => touchMove(e), {passive: true});

        track.addEventListener('mousedown', (e) => touchStart(e));
        track.addEventListener('mouseup', touchEnd);
        track.addEventListener('mouseleave', () => {
            if(isDragging) touchEnd();
        });
        track.addEventListener('mousemove', (e) => touchMove(e));

        // Evitar menú contextual al mantener presionado
        track.oncontextmenu = function(event) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        };
    }
});
</script>