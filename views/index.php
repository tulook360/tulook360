<?php
// =============================================================================
// SECCIÓN 0: LÓGICA Y DATOS (PHP)
// =============================================================================
date_default_timezone_set('America/Guayaquil');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PublicoModelo.php';
require_once __DIR__ . '/../nucleo/helpers.php';

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // Aleatoriedad para la vitrina (RAND)
    $servicios = $modelo->obtenerServiciosHome('') ?: [];
    $servicios = array_slice($servicios, 0, 6); 

    $productos = $modelo->obtenerProductosHome('') ?: [];
    $productos = array_slice($productos, 0, 6); 

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuLook360 | Reserva tu estilo</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Kalam:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root { 
            --primary: #ff3366; 
            --dark: #1e272e; 
            --gray-bg: #f8f9fc;
            --text: #2d3436;
            --white: #ffffff;
            --radius: 16px;
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--gray-bg); color: var(--text); overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }

        /* --- 1.1 NAVBAR (PC) --- */
        .navbar { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 20px 5%; position: absolute; top: 0; left: 0; width: 100%; 
            z-index: 200;
        }
        .brand { font-family: 'Kalam'; font-size: 1.8rem; color: var(--white); text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .brand span { color: var(--primary); }
        
        .nav-actions a { 
            color: var(--white); font-weight: 600; margin-left: 20px; font-size: 0.95rem; transition:0.3s;
        }
        .btn-register {
            background: var(--white); color: var(--dark) !important; 
            padding: 10px 25px; border-radius: 50px; font-weight: 700 !important;
        }
        .btn-register:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(255,255,255,0.2); }

        /* --- 1.2 HERO (PORTADA PC) --- */
        .hero { 
            height: 75vh; 
            background: linear-gradient(135deg, rgba(30,39,46,0.9) 0%, rgba(30,39,46,0.6) 100%), 
                        url('https://images.unsplash.com/photo-1633681926022-84c23e8cb2d6?q=80&w=2070&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 0 20px; color: var(--white);
            border-bottom-right-radius: 50px; border-bottom-left-radius: 50px;
            position: relative; 
            /* Quitamos overflow hidden para que la burbuja pueda salir si es necesario */
            /* overflow: hidden;  <-- ELIMINADO */
            transition: 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .hero-content-wrapper {
            transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            width: 100%; max-width: 800px; z-index: 10;
            display: flex; flex-direction: column; align-items: center;
            position: relative; /* Para que la burbuja se posicione respecto a esto */
        }

        /* ESTADO BUSCANDO (PC) */
        .hero.searching .hero-content-wrapper {
            transform: translateX(-22vw);
            width: 45%; 
        }

        .hero h1 { font-size: 3.5rem; line-height: 1.1; margin-bottom: 15px; font-weight: 800; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .hero p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 40px; max-width: 600px; font-weight: 300; }

        /* BUSCADOR */
        .search-container {
            background: var(--white); padding: 6px; border-radius: 50px; 
            display: flex; width: 100%; max-width: 600px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3); transform: translateY(0); transition: 0.3s;
            position: relative;
            z-index: 20; /* Encima de la burbuja al inicio */
        }
        .search-container:focus-within { transform: translateY(-5px); box-shadow: 0 25px 60px rgba(255, 51, 102, 0.2); }
        
        .search-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #aaa; z-index: 1; }
        
        .search-input { 
            flex: 1; border: none; padding: 15px 20px 15px 45px; font-size: 1.1rem; 
            outline: none; border-radius: 50px; color: var(--dark); min-width: 0; 
        }
        
        .search-clear {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: #eee; border: none; width: 30px; height: 30px; border-radius: 50%;
            cursor: pointer; color: #666; display: none; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .search-clear:hover { background: var(--primary); color: white; }

        /* --- 1.3 PANEL DE RESULTADOS (PC - LATERAL) --- */
        .live-results-panel {
            position: absolute; 
            top: 80px; right: -50%; width: 45%; height: calc(100% - 100px);
            background: transparent; 
            padding: 25px; overflow-y: auto; z-index: 5; 
            opacity: 0; transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            display: flex; flex-direction: column; gap: 15px; margin-right: 5%;
        }
        .hero.searching .live-results-panel { right: 0; opacity: 1; }
        .live-results-panel h3 { color: #ffffff !important; text-shadow: 0 2px 10px rgba(0,0,0,0.8); font-weight: 700; }
        .live-results-panel #sinResultados { color: #fff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.5); }

        /* Grid de 3 columnas para resultados */
        /* 1. Cuadrícula de 3 columnas para los resultados */
        .results-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 Columnas */
            gap: 15px;
            padding: 10px;
        }

        /* 2. Tarjeta con diseño vertical */
        .mini-card {
            display: flex; 
            flex-direction: column; /* Imagen arriba, texto abajo */
            background: #fff; 
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            border: 1px solid #eee;
            cursor: pointer; 
            transition: 0.3s;
            animation: slideInUp 0.4s ease forwards;
            opacity: 0; transform: translateY(20px);
            text-align: center; /* Todo el texto centrado */
        }
        .mini-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        @keyframes slideInUp { to { opacity: 1; transform: translateY(0); } }

        /* 3. Contenedor de imagen (Fondo blanco para los espacios sobrantes) */
        .mini-img-wrapper { 
            position: relative; 
            width: 100%; 
            height: 150px; /* Altura fija para que todas las tarjetas midan lo mismo */
            background: #fcfcfc; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mini-img { 
            max-width: 100%; 
            max-height: 100%; 
            object-fit: contain; /* SE VE TODA LA IMAGEN SIN RECORTAR */
        }
        
        /* 4. Precio en la esquina superior derecha de la foto */
        .mini-price-tag {
            position: absolute; 
            top: 10px; 
            right: 10px;
            background: var(--primary); 
            color: #fff;
            padding: 5px 10px; 
            border-radius: 8px;
            font-weight: 800; 
            font-size: 0.85rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* 5. Información del nombre y negocio */
        .mini-info { padding: 12px; }
        .mini-info h4 { 
            font-size: 0.95rem; 
            margin: 0 0 6px; 
            color: var(--dark); 
            font-weight: 800; 
            line-height: 1.2;
        }
        .mini-biz-info { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 5px;
            font-size: 0.7rem; 
            color: #999; 
            text-transform: uppercase; 
            font-weight: 700; 
        }

        /* --- 1.4 RESTO DEL SITIO --- */
        .categories-wrapper { margin-top: -30px; position: relative; z-index: 10; padding: 0 5%; }
        .cat-scroll { display: flex; gap: 15px; overflow-x: auto; padding-bottom: 20px; justify-content: center; scrollbar-width: none; }
        .cat-scroll::-webkit-scrollbar { display: none; }
        .cat-pill { 
            background: var(--white); padding: 12px 25px; border-radius: 50px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px;
            cursor: pointer; transition: 0.3s; font-weight: 700; color: var(--dark); border: 1px solid transparent;
        }
        .cat-pill i { color: var(--primary); font-size: 1.1rem; }
        .cat-pill:hover { transform: translateY(-5px); border-color: var(--primary); color: var(--primary); }

        .section-box { padding: 50px 5%; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .section-title { font-size: 1.8rem; font-weight: 800; color: var(--dark); margin: 0; }
        .section-link { color: var(--primary); font-weight: 700; font-size: 0.9rem; }
        .card-scroller { display: flex; overflow-x: auto; gap: 20px; padding: 10px 5px 40px 5px; scroll-snap-type: x mandatory; }
        .card { flex: 0 0 280px; background: var(--white); border-radius: var(--radius); box-shadow: 0 10px 25px rgba(0,0,0,0.06); transition: 0.3s; scroll-snap-align: start; position: relative; overflow: hidden; border: 1px solid #eee; cursor: pointer; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
        .card-img-wrap { height: 180px; position: relative; overflow: hidden; background: #f8f9fa; }
        .fade-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.6s ease-in-out; z-index: 1; }
        .fade-img.visible { opacity: 1; z-index: 2; }
        .card-img-wrap.contain .fade-img { object-fit: contain; padding: 15px; }
        .carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 32px; height: 32px; border-radius: 50%; background: rgba(255, 255, 255, 0.95); color: var(--dark); border: 1px solid rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; opacity: 1; transition: 0.2s; font-size: 0.8rem; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .carousel-arrow:active, .carousel-arrow:hover { background: var(--primary); color: white; }
        .arrow-prev { left: 10px; } .arrow-next { right: 10px; }
        .card-badge { position: absolute; top: 10px; left: 10px; z-index: 10; background: rgba(255,255,255,0.95); padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-content { padding: 15px; }
        .card-biz { font-size: 0.75rem; color: #999; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .card-name { font-size: 1.1rem; font-weight: 800; color: var(--dark); margin: 0 0 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #eee; }
        .card-price { font-size: 1.2rem; font-weight: 900; color: var(--primary); }
        .btn-card { background: var(--dark); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; font-size: 0.8rem; }
        .card:hover .btn-card { background: var(--primary); transform: rotate(90deg); }


        /* Estilo para el botón de registro de cliente en el Hero */
        .btn-cta-user {
            background: var(--primary);
            color: white !important;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 25px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(255, 51, 102, 0.3);
        }
        .btn-cta-user:hover { 
            transform: scale(1.05); 
            box-shadow: 0 15px 30px rgba(255, 51, 102, 0.5); 
        }

        /* =============================================================================
           SECCIÓN RESPONSIVE (MÓVIL) - ESTILO "BURBUJA DE PENSAMIENTO"
           ============================================================================= */
        @media (max-width: 900px) {
            /* 1. AJUSTES BÁSICOS */
            .navbar { padding: 15px 20px; z-index: 200; }
            .nav-actions { display: none; }
            .hero { height: 60vh; border-radius: 0 0 30px 30px; padding: 0 15px; position: relative; }
            .mobile-login { display: block !important; }
            
            /* 2. CERO MOVIMIENTO: DESACTIVAR ANIMACIONES DEL HERO EN MÓVIL */
            /* El texto NO desaparece, el buscador NO se mueve. Todo quieto. */
            .hero.searching .hero-content-wrapper {
                transform: none !important;
                width: 100% !important;
            }
            .hero.searching .hero-content-wrapper > * {
                opacity: 1 !important;
                visibility: visible !important;
                display: block !important;
            }

            /* 3. EL BUSCADOR: SE QUEDA DONDE ESTÁ */
            /* No usamos fixed ni absolute raros. Se queda en su flujo natural. */
            .search-container {
                position: relative;
                z-index: 100 !important; /* Alto para que se pueda clickear */
                width: 100%; /* Ancho completo del contenedor padre */
                transform: none !important; /* Asegurar que no se mueva */
            }

            /* 4. LA BURBUJA DE RESULTADOS (POPOVER) */
            /* Nace DESDE el buscador */
            .live-results-panel {
                /* Configuración de "Burbuja Flotante" */
                position: absolute !important;
                top: 5% !important; /* Justo debajo del buscador */
                left: 0 !important;
                width: 100% !important;
                height: auto !important;
                max-height: 390px; /* Altura máxima con scroll */
                
                /* Estilo Cajita Blanca */
                background: #ffffff !important;
                border-radius: 15px !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
                border: 1px solid #eee;
                
                padding: 15px !important;
                margin-top: 15px !important; /* Separación pequeña del buscador */
                z-index: 200 !important; /* Encima del buscador y todo lo demás */
                
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px); /* Efecto de salir suave */
                transition: all 0.3s ease;
                
                /* Resetear estilos de PC */
                right: auto !important;
            }

            /* FLECHITA DE LA BURBUJA (Opcional, estilo "bocadillo") */
            .live-results-panel::before {
                content: '';
                position: absolute;
                top: -8px; left: 50%; transform: translateX(-50%);
                border-left: 8px solid transparent;
                border-right: 8px solid transparent;
                border-bottom: 8px solid #ffffff;
            }

            /* MOSTRAR LA BURBUJA */
            .hero.searching .live-results-panel {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            /* ESTILOS DENTRO DE LA BURBUJA */
            .live-results-panel h3 { 
                text-align: center; color: #2d3436 !important; 
                font-size: 1rem; margin-bottom: 10px;
                text-shadow: none !important;
            }

            /* Agrega esto dentro de tu @media (max-width: 900px) */
            /* REEMPLAZO EXACTO DE CUADRÍCULA Y TARJETA */
            .results-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important; /* 2 Columnas */
                gap: 10px !important;
            }
            .mini-card {
                background: #fff !important;
                padding: 0 !important;
                border: 1px solid #eee !important;
                border-radius: 12px !important;
                flex-direction: column !important; /* Imagen arriba */
                text-align: center !important;
                overflow: hidden !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
            }
            .mini-img-wrapper {
                position: relative !important;
                width: 100% !important;
                height: 100px !important;
                background: #fcfcfc !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .mini-img {
                max-width: 100% !important;
                max-height: 100% !important;
                object-fit: contain !important; /* Imagen completa */
            }
            .mini-price-tag {
                position: absolute !important;
                top: 5px !important;
                right: 5px !important;
                background: var(--primary) !important;
                color: #fff !important;
                padding: 2px 6px !important;
                border-radius: 6px !important;
                font-weight: 800 !important;
                font-size: 0.7rem !important;
            }
            .mini-info { padding: 8px !important; }
            .mini-info h4 { font-size: 0.8rem !important; margin: 0 !important; line-height: 1.1 !important; }
            .mini-biz-info { 
                display: flex !important; align-items: center !important; justify-content: center !important; 
                gap: 3px !important; font-size: 0.6rem !important; margin-top: 4px !important; color: #888 !important; 
            }
            
            /* SCROLLBAR MÓVIL DENTRO DE LA BURBUJA */
            .live-results-panel::-webkit-scrollbar { display: none; }
            
            /* Ajustes Generales */
            .cat-scroll { justify-content: flex-start; padding-left: 20px; }
            .categories-wrapper { margin-top: -25px; padding: 0; position:relative; z-index: 50; }
            .card { flex: 0 0 240px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="brand"><i class="fa-solid fa-scissors" style="color:var(--primary)"></i> TuLook<span>360</span></a>
        <div class="nav-actions">
            <a href="<?= ruta_vista('login.php', [], false) ?>">Iniciar Sesión</a> 
            <a href="<?= ruta_accion('auth', 'registroCliente', [], false) ?>" style="margin-left:20px; font-weight:600; color:white;">Crear Cuenta</a> 
            <a href="<?= ruta_accion('auth', 'registro', [], false) ?>" class="btn-register">Registrar Negocio</a> </div>
        </div>
        <a href="<?= ruta_vista('login.php', [], false) ?>" style="color:white; font-size:1.5rem; display:none;" class="mobile-login"><i class="fa-solid fa-circle-user"></i></a>
    </nav>

    <header class="hero" id="hero-section">
        <div class="hero-content-wrapper">
            <span style="text-transform:uppercase; letter-spacing:2px; font-size:0.8rem; font-weight:700; color:var(--primary); margin-bottom:10px;">La Plataforma #1 de Belleza</span>
            <h1>Reserva tu próximo <br>Look Perfecto</h1>
            <p>Encuentra barberías, salones y spas cerca de ti.</p>
            <a href="<?= ruta_accion('auth', 'registroCliente', [], false) ?>" class="btn-cta-user">¡Únete ahora y reserva! ✨</a>
            
            <div class="search-container" id="search-box">
                <i class="fa-solid fa-search search-icon"></i>
                <input type="text" id="inputBusqueda" class="search-input" placeholder="¿Qué buscas? (Ej: Corte, Uñas...)" autocomplete="off">
                <button type="button" id="btnBorrarBusqueda" class="search-clear"><i class="fa-solid fa-xmark"></i></button>
                
                </div>
        </div>

        <div class="live-results-panel" id="panelResultados">
            <h3 style="margin-top:0; font-size:1.2rem; color:var(--dark);">Resultados</h3>
            <div class="results-grid" id="gridResultados"></div>
            <div id="sinResultados" style="display:none; text-align:center; padding:20px; color:#888;">
                No encontramos coincidencias. 😔
            </div>
        </div>
    </header>

    <div class="categories-wrapper">
        <div class="cat-scroll">
            <div class="cat-pill"><i class="fa-solid fa-scissors"></i> Barbería</div>
            <div class="cat-pill"><i class="fa-solid fa-wand-magic-sparkles"></i> Peluquería</div>
            <div class="cat-pill"><i class="fa-solid fa-hand-holding-heart"></i> Manicure</div>
            <div class="cat-pill"><i class="fa-solid fa-spa"></i> Spa & Relax</div>
            <div class="cat-pill"><i class="fa-solid fa-eye"></i> Cejas</div>
        </div>
    </div>

    <section class="section-box">
        <div class="section-header">
            <h2 class="section-title">Recomendados 🔥</h2>
            <a href="<?= ruta_vista('login.php') ?>" class="section-link">Ver todo</a>
        </div>
        <div class="card-scroller">
            <?php foreach($servicios as $s): ?>
            <div class="card fade-carousel-card" onclick="location.href='<?= ruta_vista('login.php') ?>'">
                <div class="card-img-wrap contain"> 
                    <?php if (empty($s['imagenes'])): ?>
                        <img src="https://via.placeholder.com/300x200?text=Sin+Foto" class="fade-img visible">
                    <?php else: ?>
                        <?php foreach ($s['imagenes'] as $i => $img): ?>
                            <img src="<?= $img ?>" class="fade-img <?= $i === 0 ? 'visible' : '' ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (count($s['imagenes'] ?? []) > 1): ?>
                        <button class="carousel-arrow arrow-prev"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="carousel-arrow arrow-next"><i class="fa-solid fa-chevron-right"></i></button>
                    <?php endif; ?>
                    <div class="card-badge"><i class="fa-regular fa-clock"></i> <?= $s['serv_duracion'] ?> min</div>
                </div>
                <div class="card-content">
                    <div class="card-biz"><?= htmlspecialchars($s['neg_nombre']) ?></div>
                    <h3 class="card-name"><?= htmlspecialchars($s['serv_nombre']) ?></h3>
                    <div class="card-footer">
                        <span class="card-price">$<?= number_format($s['serv_precio'], 2) ?></span>
                        <div class="btn-card"><i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section-box" style="padding-top:0;">
        <div class="section-header">
            <h2 class="section-title">Tienda Online 🛍️</h2>
            <a href="<?= ruta_vista('login.php') ?>" class="section-link">Ver productos</a>
        </div>
        <div class="card-scroller">
            <?php foreach($productos as $p): ?>
            <div class="card fade-carousel-card" onclick="location.href='<?= ruta_vista('login.php') ?>'">
                <div class="card-img-wrap contain">
                    <?php if (empty($p['imagenes'])): ?>
                        <img src="https://via.placeholder.com/300x200?text=Sin+Foto" class="fade-img visible">
                    <?php else: ?>
                        <?php foreach ($p['imagenes'] as $i => $img): ?>
                            <img src="<?= $img ?>" class="fade-img <?= $i === 0 ? 'visible' : '' ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (count($p['imagenes'] ?? []) > 1): ?>
                        <button class="carousel-arrow arrow-prev"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="carousel-arrow arrow-next"><i class="fa-solid fa-chevron-right"></i></button>
                    <?php endif; ?>
                    <div class="card-badge" style="background:#e1ffe1; color:#00b894;">En Stock</div>
                </div>
                <div class="card-content">
                    <div class="card-biz"><?= htmlspecialchars($p['neg_nombre']) ?></div>
                    <h3 class="card-name"><?= htmlspecialchars($p['pro_nombre']) ?></h3>
                    <div class="card-footer">
                        <span class="card-price">$<?= number_format($p['pro_precio'], 2) ?></span>
                        <div class="btn-card"><i class="fa-solid fa-cart-plus"></i></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="b2b-banner" style="margin:40px 5%; background:var(--dark); border-radius:30px; padding:50px; text-align:center; color:white;">
        <h2 style="font-family:'Kalam'; font-size:2.5rem; color:var(--primary);">¿Tienes un negocio?</h2>
        <p>Gestiona tu agenda, clientes e inventario con TuLook360.</p>
        <a href="<?= ruta_accion('auth', 'registro') ?>" class="btn-register" style="display:inline-block; margin-top:20px;">Prueba Gratis Ahora</a>
    </div>

    <footer style="text-align:center; padding:30px; color:#888; font-size:0.8rem; background:white;">
        <p>&copy; <?= date('Y') ?> TuLook360 Inc.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. CARRUSEL FADE (Igual que antes)
            const cards = document.querySelectorAll('.fade-carousel-card');
            cards.forEach(card => {
                const images = card.querySelectorAll('.fade-img');
                const btnPrev = card.querySelector('.arrow-prev');
                const btnNext = card.querySelector('.arrow-next');
                if (images.length <= 1) return;
                let currentIndex = 0; let autoInterval;
                const showImage = (index) => {
                    images.forEach(img => img.classList.remove('visible'));
                    images[index].classList.add('visible');
                };
                const nextImage = () => { currentIndex = (currentIndex + 1) % images.length; showImage(currentIndex); };
                const prevImage = () => { currentIndex = (currentIndex - 1 + images.length) % images.length; showImage(currentIndex); };
                const handleArrowClick = (e, action) => {
                    e.preventDefault(); e.stopImmediatePropagation(); e.stopPropagation(); 
                    action(); resetTimer();
                };
                if (btnNext && btnPrev) {
                    btnNext.addEventListener('click', (e) => handleArrowClick(e, nextImage));
                    btnPrev.addEventListener('click', (e) => handleArrowClick(e, prevImage));
                }
                const startAuto = () => { autoInterval = setInterval(nextImage, 4000); };
                const stopAuto = () => clearInterval(autoInterval);
                const resetTimer = () => { stopAuto(); startAuto(); };
                setTimeout(startAuto, Math.floor(Math.random() * 3000));
                card.addEventListener('mouseenter', stopAuto);
                card.addEventListener('mouseleave', startAuto);
                card.addEventListener('touchstart', stopAuto, {passive: true});
            });

            // 2. BUSCADOR VIVO
            const hero = document.getElementById('hero-section');
            const input = document.getElementById('inputBusqueda');
            const btnClear = document.getElementById('btnBorrarBusqueda');
            const grid = document.getElementById('gridResultados');
            const noResults = document.getElementById('sinResultados');
            
            // AGREGADO: MOVER PANEL EN MOVIL PARA QUE SEA HIJO DE LA CAJA DE TEXTO (TRUCO DE POSICIONAMIENTO)
            const panel = document.getElementById('panelResultados');
            const wrapper = document.querySelector('.hero-content-wrapper');
            
            // Función para reubicar el panel en móvil (para que la burbuja nazca del wrapper)
            const checkMobile = () => {
                if (window.innerWidth <= 900) {
                    wrapper.appendChild(panel); // Mover dentro del wrapper en móvil
                } else {
                    document.getElementById('hero-section').appendChild(panel); // Volver al hero en PC
                }
            };
            window.addEventListener('resize', checkMobile);
            checkMobile(); // Ejecutar al inicio

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
                    fetch(`index.php?c=publico&a=buscar_ajax&q=${encodeURIComponent(val)}`)
                        .then(r => r.json())
                        .then(data => {
                            grid.innerHTML = '';
                            if (data.length === 0) {
                                noResults.style.display = 'block';
                            } else {
                                noResults.style.display = 'none';
                                data.forEach((item, index) => {
                                    const card = document.createElement('div');
                                    card.className = 'mini-card';
                                    card.style.animationDelay = `${index * 0.1}s`;
                                    card.onclick = () => window.location.href = 'index.php?c=auth&a=login';
                                    
                                    const meta = item.tipo === 'servicio' 
                                        ? `<i class="fa-regular fa-clock"></i> ${item.meta} min` 
                                        : `<i class="fa-solid fa-box"></i> Stock`;

                                    card.innerHTML = `
                                        <div class="mini-img-wrapper">
                                            <img src="${item.imagen}" class="mini-img">
                                            <span class="mini-price-tag">$${parseFloat(item.precio).toFixed(2)}</span>
                                        </div>
                                        <div class="mini-info">
                                            <h4>${item.titulo}</h4>
                                            <div class="mini-biz-info">
                                                <i class="fa-solid fa-store" style="font-size:0.5rem;"></i>
                                                <span>${item.negocio}</span>
                                            </div>
                                            <div style="font-size: 0.6rem; color: #bbb; margin-top: 3px;">
                                                ${meta}
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
        });
    </script>
</body>
</html>