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
    
    // --- ESTA ES LA LÍNEA MÁGICA: PEDIMOS LOS LOGOS DIRECTAMENTE ---
    $logosNegocios = $modelo->obtenerLogosNegocios() ?: []; 
    
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

        .hero-content-wrapper {
            transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            width: 100%; max-width: 800px; z-index: 10;
            display: flex; flex-direction: column; align-items: center;
            position: relative; /* Para que la burbuja se posicione respecto a esto */
        }

        /* 1. Mejorar el Hero con un degradado más profundo para que el texto resalte */
        .hero { 
            height: 75vh; 
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(15, 23, 42, 0.4) 100%), 
                        url('https://images.unsplash.com/photo-1633681926022-84c23e8cb2d6?q=80&w=2070&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 0 20px; color: var(--white);
            border-bottom-right-radius: 60px; /* Curva más moderna */
            border-bottom-left-radius: 60px;
            position: relative; transition: 0.6s;
        }

        /* 2. Etiqueta "La Plataforma #1" con estilo de cristal */
        .hero-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            padding: 8px 20px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            display: inline-block;
            animation: fadeInDown 0.8s ease-out;
        }

        /* 3. Título con sombra de texto elegante */
        .hero h1 { 
            font-size: clamp(2.5rem, 5vw, 3.8rem); 
            line-height: 1.1; 
            margin-bottom: 20px; 
            font-weight: 800; 
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: fadeInUp 1s ease-out;
        }

        /* 4. Subtítulo con retraso en la animación */
        .hero p { 
            font-size: 1.2rem; opacity: 0.9; margin-bottom: 35px; 
            max-width: 550px; font-weight: 300; 
            animation: fadeInUp 1.2s ease-out;
        }

        /* 5. Botón con efecto de brillo */
        .btn-cta-user {
            background: var(--primary);
            color: white !important;
            padding: 16px 45px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(255, 51, 102, 0.4);
            transition: 0.4s;
            animation: fadeInUp 1.4s ease-out;
        }

        .btn-cta-user:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 35px rgba(255, 51, 102, 0.6);
        }

        /* 6. Estrellas de confianza (Nuevo) */
        .hero-trust {
            margin-top: 25px;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            gap: 8px;
            animation: fadeIn 2s ease-in;
        }
        .hero-trust i { color: #f1c40f; }

        /* Animaciones */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 901px) {
            #menuMovil {
                display: none !important;
            }
        }


        /* =============================================================================
           SECCIÓN HISTORIA Y ALIADOS (PC PREMIUM)
           ============================================================================= */
        .seccion-historia {
            padding: 100px 5%; background-color: #ffffff; 
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            position: relative;
        }
        .historia-container {
            display: flex; max-width: 1200px; width: 100%; gap: 80px; align-items: center;
        }

        /* Lado Derecho: Textos */
        .historia-texto { flex: 1; padding-right: 20px; }
        .historia-badge {
            background: rgba(255, 51, 102, 0.1); color: var(--primary);
            padding: 8px 16px; border-radius: 50px; font-size: 0.8rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px; display: inline-block; margin-bottom: 20px;
        }
        .historia-texto h2 { font-size: 3rem; color: var(--dark); margin-top: 0; margin-bottom: 20px; line-height: 1.1; font-weight: 800; letter-spacing: -1px; }
        .historia-texto p { font-size: 1.15rem; color: #555; line-height: 1.8; margin-bottom: 20px; font-weight: 400; }

        /* Lado Izquierdo: Muro de Doble Columna */
        .historia-logos-wrap {
            flex: 1; height: 480px; overflow: hidden; position: relative;
            display: flex; justify-content: center; gap: 20px;
            /* Máscara de desvanecimiento suave (difumina arriba y abajo) */
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
        }

        .logos-column {
            display: flex; flex-direction: column; gap: 20px;
            width: max-content;
        }
        
        /* Direcciones opuestas */
        .column-up { animation: scrollVerticalUp 40s linear infinite; }
        .column-down { animation: scrollVerticalDown 40s linear infinite; }

        /* Pausar ambas al pasar el mouse */
        .historia-logos-wrap:hover .logos-column { animation-play-state: paused; }

        @keyframes scrollVerticalUp {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); } 
        }
        @keyframes scrollVerticalDown {
            0% { transform: translateY(-50%); }
            100% { transform: translateY(0); } 
        }

        /* Diseño de Píldoras Refinado */
        .logo-pill {
            display: flex; align-items: center; gap: 15px;
            background: #ffffff; padding: 8px 20px 8px 8px;
            border-radius: 50px; border: 1px solid #f0f2f5;
            box-shadow: 0 10px 25px rgba(0,0,0,0.04); 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            width: 250px; /* Ancho fijo para alineación perfecta */
        }
        .logo-pill:hover {
            transform: scale(1.05); 
            box-shadow: 0 15px 35px rgba(255, 51, 102, 0.15);
            border-color: rgba(255, 51, 102, 0.3);
        }
        .pill-img {
            width: 50px; height: 50px; border-radius: 50%;
            background: #fff; display: flex; align-items: center; justify-content: center;
            overflow: hidden; border: 1px solid #f0f0f0; flex-shrink: 0; padding: 4px;
        }
        .pill-img img { width: 100%; height: 100%; object-fit: contain; }
        .pill-name { 
            font-weight: 800; font-size: 0.95rem; color: var(--dark); 
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        /* =============================================================================
        SECCIÓN RESPONSIVE (MÓVIL) - OPTIMIZADO PARA IPHONE 12 PRO / MÓVILES
        ============================================================================= */
        @media (max-width: 900px) {
            /* 1. NAVBAR MÓVIL: Más compacta */
            .navbar { padding: 15px 5%; }
            .nav-actions { display: none; } /* Ocultamos botones de PC */
            .mobile-login { display: block !important; }

            /* 2. HERO MÓVIL: Más alto y con contenido centrado hacia abajo */
            .hero { 
                height: 75vh !important; /* Hacemos que crezca verticalmente */
                border-radius: 0 0 40px 40px; /* Redondeado más suave en móvil */
                padding: 0 20px;
                /* Empujamos el contenido hacia abajo para que no choque con el logo */
                justify-content: flex-end; 
                padding-bottom: 80px; 
            }

            /* 3. AJUSTE DE TEXTOS: Para que no se amontonen */
            .hero-content-wrapper {
                width: 100%;
                max-width: 100%;
            }

            .hero h1 { 
                font-size: 2.6rem !important; /* Tamaño ideal para lectura en mano */
                line-height: 1.1;
                margin-bottom: 15px;
            }

            .hero p { 
                font-size: 1.1rem !important; 
                margin-bottom: 30px; 
                padding: 0 10px; /* Evita que el texto toque los bordes laterales */
            }

            /* 4. BOTÓN MÓVIL: Un poco más grande para el pulgar */
            .btn-cta-user {
                width: 100%; /* El botón ocupa todo el ancho disponible */
                max-width: 300px;
                padding: 18px 20px;
                font-size: 1rem;
            }

            /* 5. ETIQUETA SUPERIOR (Badge) */
            .hero-badge {
                font-size: 0.65rem;
                padding: 6px 15px;
                margin-bottom: 15px;
            }

            /* 6. ESTRELLAS DE CONFIANZA */
            .hero-trust {
                margin-top: 20px;
                justify-content: center;
                font-size: 0.8rem;
            }


            /* --- MENÚ FULLSCREEN ESTILO APP PREMIUM (SOLO MÓVIL) --- */
            .menu-fullscreen {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100vh;
                background: rgba(15, 23, 42, 0.75); /* Transparente para que se vea el fondo */
                backdrop-filter: blur(20px); /* Desenfoque fuerte (Efecto cristal real) */
                -webkit-backdrop-filter: blur(20px); /* Soporte para iPhone */
                z-index: 9999;
                display: flex;
                flex-direction: column;
                padding: 30px 25px;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-20px);
                transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            }

            .menu-fullscreen.activo {
                opacity: 1; visibility: visible; transform: translateY(0);
            }

            /* Cabecera del menú */
            .menu-header {
                display: flex; justify-content: space-between; align-items: center;
                width: 100%; margin-bottom: 50px; border-bottom: 1px solid rgba(255,255,255,0.1);
                padding-bottom: 20px;
            }

            .cerrar-menu {
                background: rgba(255,255,255,0.1); border: none; color: var(--white);
                width: 45px; height: 45px; border-radius: 50%;
                font-size: 1.5rem; display: flex; align-items: center; justify-content: center;
                cursor: pointer; transition: 0.3s;
            }

            /* Tarjetas de opciones */
            .menu-links {
                display: flex; flex-direction: column; gap: 15px; width: 100%;
            }

            .menu-links a {
                background: rgba(255, 255, 255, 0.05); /* Fondo de tarjeta sutil */
                border: 1px solid rgba(255, 255, 255, 0.1);
                padding: 20px; border-radius: 20px;
                color: var(--white) !important; font-size: 1.2rem; font-weight: 600;
                text-decoration: none; display: flex !important; align-items: center; gap: 15px;
            }

            .menu-links a i { font-size: 1.4rem; opacity: 0.8; }

            /* Destacar la opción de Negocio */
            .menu-links .link-destacado {
                background: rgba(255, 51, 102, 0.1) !important; /* Fondo rosado muy suave */
                border-color: rgba(255, 51, 102, 0.4) !important;
                color: var(--primary) !important;
            }
            .menu-links .link-destacado i { color: var(--primary); opacity: 1; }


            
            /* =============================================================================
               SECCIÓN HISTORIA (MÓVIL)
               ============================================================================= */
            .seccion-historia { padding: 50px 0 20px 0; }
            .historia-container { flex-direction: column-reverse; gap: 40px; }
            
            .historia-texto { padding: 0 25px; text-align: center; }
            .historia-texto h2 { font-size: 2.2rem; }
            .historia-badge { margin-bottom: 15px; font-size: 0.75rem; }

            /* Convertimos las columnas verticales en 2 filas horizontales */
            .historia-logos-wrap {
                height: auto; width: 100%; flex-direction: column; gap: 15px;
                -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
                mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
            }
            .logos-column {
                flex-direction: row; width: max-content;
            }
            .column-up { animation: scrollHorizontalLeft 40s linear infinite; }
            .column-down { animation: scrollHorizontalRight 40s linear infinite; }

            @keyframes scrollHorizontalLeft {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            @keyframes scrollHorizontalRight {
                0% { transform: translateX(-50%); }
                100% { transform: translateX(0); }
            }

            /* Ajustes tamaño móvil */
            .logo-pill { width: 220px; padding: 5px 15px 5px 5px; } 
            .pill-img { width: 40px; height: 40px; }
            .pill-name { font-size: 0.85rem; }
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

        <a href="javascript:void(0);" style="color:white; font-size:1.5rem; display:none;" class="mobile-login" id="btnMenuMovil"><i class="fa-solid fa-bars"></i></a>
    </nav>

    <header class="hero" id="hero-section">
        <div class="hero-content-wrapper">
            <div class="hero-badge">La Plataforma #1 de Belleza</div>
            
            <h1>Encuentra tu estilo <br> Reserva tu momento</h1>
            
            <p>Los mejores profesionales de la ciudad listos para transformar tu look con un solo clic.</p>
            
            <a href="<?= ruta_accion('auth', 'registroCliente', [], false) ?>" class="btn-cta-user">
                ¡Únete ahora y reserva! ✨
            </a>

            <div class="hero-trust">
                <div>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <span>+500 servicios reservados hoy</span>
            </div>
        </div>
    </header>


    <div class="menu-fullscreen" id="menuMovil">
        
        <div class="menu-header">
            <a href="#" class="brand"><i class="fa-solid fa-scissors" style="color:var(--primary)"></i> TuLook<span>360</span></a>
            <button class="cerrar-menu" id="btnCerrarMenu"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="menu-links">
            <a href="<?= ruta_vista('login.php', [], false) ?>">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Iniciar Sesión
            </a> 
            <a href="<?= ruta_accion('auth', 'registroCliente', [], false) ?>">
                <i class="fa-solid fa-user-plus"></i> Crear Cuenta
            </a> 
            <a href="<?= ruta_accion('auth', 'registro', [], false) ?>" class="link-destacado">
                <i class="fa-solid fa-store"></i> Registrar Negocio
            </a>
        </div>
        
    </div>


    <!-- INICIO SECCION HISTORIO -->

    <section class="seccion-historia">
        <div class="historia-container">
            
            <div class="historia-logos-wrap">
                
                <div class="logos-column column-up">
                    <?php if (!empty($logosNegocios)): ?>
                        <?php 
                        // Multiplicamos por 6 para asegurar la ilusión perfecta infinita
                        for($i=0; $i<6; $i++): 
                            foreach($logosNegocios as $negocio): 
                                $logoUrl = !empty($negocio['neg_logo']) ? $negocio['neg_logo'] : 'recursos/img/sin_foto.png';
                        ?>
                            <div class="logo-pill" title="<?= htmlspecialchars($negocio['neg_nombre']) ?>">
                                <div class="pill-img"><img src="<?= $logoUrl ?>" alt="" loading="lazy"></div>
                                <span class="pill-name"><?= htmlspecialchars($negocio['neg_nombre']) ?></span>
                            </div>
                        <?php endforeach; endfor; ?>
                    <?php endif; ?>
                </div>

                <div class="logos-column column-down">
                    <?php if (!empty($logosNegocios)): ?>
                        <?php 
                        for($i=0; $i<6; $i++): 
                            // array_reverse para que la segunda columna tenga un orden distinto
                            foreach(array_reverse($logosNegocios) as $negocio): 
                                $logoUrl = !empty($negocio['neg_logo']) ? $negocio['neg_logo'] : 'recursos/img/sin_foto.png';
                        ?>
                            <div class="logo-pill" title="<?= htmlspecialchars($negocio['neg_nombre']) ?>">
                                <div class="pill-img"><img src="<?= $logoUrl ?>" alt="" loading="lazy"></div>
                                <span class="pill-name"><?= htmlspecialchars($negocio['neg_nombre']) ?></span>
                            </div>
                        <?php endforeach; endfor; ?>
                    <?php endif; ?>
                </div>

            </div>

            <div class="historia-texto">
                <span class="historia-badge">Nuestra Red de Aliados</span>
                <h2>Nacimos para <span style="color: var(--primary);">conectar</span> tu estilo.</h2>
                <p>Todo empezó con una frustración común: perder horas llamando a salones o esperando turnos sin saber a qué hora nos atenderían.</p>
                <p>Sabemos que los profesionales de la belleza son artistas increíbles. Por eso, nos asociamos con las mejores marcas y barberías de la ciudad.</p>
                <p><strong>TuLook360</strong> es el puente directo entre tú y estos expertos. Ahora, reservar tu próximo tratamiento es tan fácil como pedir un taxi.</p>
            </div>

        </div>
    </section>
    <!-- FIN SECCION HISTORIA -->

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Lógica para el menú móvil de Pantalla Completa
            const btnMenu = document.getElementById('btnMenuMovil');
            const btnCerrar = document.getElementById('btnCerrarMenu');
            const menu = document.getElementById('menuMovil');

            if(btnMenu && btnCerrar && menu) {
                btnMenu.addEventListener('click', () => {
                    menu.classList.add('activo');
                    document.body.style.overflow = 'hidden'; // Evita scroll de fondo
                });
                btnCerrar.addEventListener('click', () => {
                    menu.classList.remove('activo');
                    document.body.style.overflow = ''; // Restaura scroll
                });
            }
        });


        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. Obtener el contenedor
            const carruselLogos = document.getElementById('carruselLogosHistoria');
            if (!carruselLogos) return;

            // --- EL CAMBIO ESTÁ AQUÍ 1: Usamos la URL segura con Token que ya tienes arriba ---
            const urlTraerLogos = '<?= str_replace("+", "%2B", ruta_accion("publico", "listar_negocios_ajax", [], false)) ?>';
            const rutaSinFoto = 'recursos/img/sin_foto.png';

            // 3. Hacer la petición AJAX
            fetch(urlTraerLogos, {
                headers: { 
                    'Accept': 'application/json',
                    // --- EL CAMBIO ESTÁ AQUÍ 2: Cabecera para que el Router sepa que es AJAX ---
                    'X-Requested-With': 'XMLHttpRequest' 
                }
            })
            .then(r => r.json())
            .then(resp => {
                // Si la respuesta es exitosa y hay datos...
                if (resp.success && resp.data && resp.data.length > 0) {
                    
                    let htmlLogos = '';
                    
                    // DIBUJAR LOS LOGOS (LO HACEMOS DOS VECES PARA EL BUCLE INFINITO)
                    for (let i = 0; i < 2; i++) {
                        resp.data.forEach(negocio => {
                            const logoUrl = negocio.neg_logo ? negocio.neg_logo : rutaSinFoto;
                            
                            htmlLogos += `
                                <div class="logo-box" title="${negocio.neg_nombre}">
                                    <img src="${logoUrl}" alt="${negocio.neg_nombre}" loading="lazy">
                                </div>
                            `;
                        });
                    }

                    // Inyectar en el HTML
                    carruselLogos.innerHTML = htmlLogos;

                } else {
                    // Solo si la BD está vacía mostramos el respaldo
                    carruselLogos.innerHTML = `
                        <div class="logo-box"><i class="fa-solid fa-store" style="font-size:2.5rem; color:#ddd;"></i></div>
                        <div class="logo-box"><i class="fa-solid fa-scissors" style="font-size:2.5rem; color:#ddd;"></i></div>
                        <div class="logo-box"><i class="fa-solid fa-spa" style="font-size:2.5rem; color:#ddd;"></i></div>
                        <div class="logo-box"><i class="fa-solid fa-store" style="font-size:2.5rem; color:#ddd;"></i></div>
                    `;
                }
            })
            .catch(err => {
                console.error("Error al cargar logos para historia:", err);
            });
        });
    </script>


</body>
</html>