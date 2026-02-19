<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../nucleo/Menu.php';
require_once __DIR__ . '/../../nucleo/helpers.php';

// Datos Sesión
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Invitado';
$usuarioFoto   = $_SESSION['usuario_foto'] ?? null;
$menuItems     = Menu::obtener(4); // Rol 4 = Cliente
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'TuLook360' ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Kalam:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root { 
            --primary: #ff3366; 
            --dark: #1e272e; 
            --gray-bg: #f8f9fc;
            --text: #2d3436;
            --glass: rgba(30, 39, 46, 0.95); 
        }
        
        * { box-sizing: border-box; }
        body { 
            margin: 0; font-family: 'Outfit', sans-serif; 
            background: var(--gray-bg); color: var(--text); 
            padding-top: 80px; 
        }
        a { text-decoration: none; color: inherit; transition: 0.2s; }

        /* --- NAVBAR SEMI-TRANSPARENTE --- */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%; height: 75px;
            background: var(--glass); backdrop-filter: blur(10px);
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 5%; z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .nav-left { display: flex; align-items: center; gap: 20px; }
        
        .btn-menu {
            background: rgba(255,255,255,0.1); border: none; color: white;
            width: 45px; height: 45px; border-radius: 12px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
            transition: 0.3s;
        }
        .btn-menu:hover { background: var(--primary); transform: scale(1.05); }

        .brand { font-family: 'Kalam'; font-size: 1.8rem; color: white; }
        .brand span { color: var(--primary); }

        /* ZONA USUARIO DERECHA (CORREGIDA) */
        .user-zone { display: flex; align-items: center; gap: 15px; }
        
        /* Botón Perfil (Tuerca) */
        .btn-profile {
            width: 40px; height: 40px; border-radius: 50%; 
            background: rgba(255,255,255,0.15); color: white;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s; cursor: pointer; font-size: 1.1rem;
        }
        .btn-profile:hover { background: var(--primary); transform: rotate(90deg); }

        .user-pill {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.08); padding: 5px 20px 5px 5px;
            border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);
        }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid white; }
        .user-info { line-height: 1.1; }
        .user-name { color: white; font-weight: 700; font-size: 0.95rem; display: block; }
        .user-role { color: var(--primary); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

        /* --- SIDEBAR --- */
        .sidebar-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
            z-index: 1500; opacity: 0; visibility: hidden; transition: 0.3s;
        }
        .sidebar-overlay.active { opacity: 1; visibility: visible; }

        .sidebar {
            position: fixed; top: 0; left: -320px; width: 300px; height: 100vh;
            background: var(--dark); z-index: 1600;
            display: flex; flex-direction: column;
            transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
            box-shadow: 10px 0 40px rgba(0,0,0,0.4);
        }
        .sidebar.active { left: 0; }

        .sidebar-header {
            padding: 40px 20px 30px; text-align: center;
            background: linear-gradient(to bottom, #252f38, var(--dark));
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-avatar {
            width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
            border: 3px solid var(--primary); padding: 3px; background: var(--dark);
            margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .sidebar-nav { flex: 1; padding: 20px; overflow-y: auto; }
        
        .menu-link {
            display: flex; align-items: center; gap: 15px;
            padding: 14px 18px; margin-bottom: 8px;
            color: rgba(255,255,255,0.8); border-radius: 12px;
            transition: all 0.3s ease; font-weight: 500; font-size: 0.95rem;
        }
        .menu-link i { width: 24px; text-align: center; font-size: 1.1rem; color: rgba(255,255,255,0.5); }
        .menu-link:hover { background: rgba(255, 51, 102, 0.1); color: white; padding-left: 25px; }
        .menu-link:hover i { color: var(--primary); transform: scale(1.1); }

        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .btn-logout {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 12px; border-radius: 12px;
            background: rgba(220, 53, 69, 0.1); color: #ff7675; font-weight: 700; transition: 0.3s;
        }
        .btn-logout:hover { background: #ff7675; color: white; }

        /* --- RESPONSIVE (MÓVIL) --- */
        @media (max-width: 768px) {
            /* Solo ocultamos el texto (Nombre y Rol) para ahorrar espacio */
            .user-info { display: none; } 
            
            /* Quitamos el borde/fondo de la píldora para que solo se vea la foto */
            .user-pill { padding: 0; border: none; background: transparent; }
            
            /* Ajustamos tamaño de logo */
            .brand { font-size: 1.4rem; }
            
            /* NOTA: Ya NO ocultamos .btn-profile, por eso aparecerá la tuerca */
        }


        /* --- BADGE DEL CARRITO --- */
        .cart-badge {
            position: absolute; top: -5px; right: -5px;
            background: #ff4757; color: white;
            font-size: 0.7rem; font-weight: 800;
            width: 18px; height: 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--glass); /* Borde para separar del icono */
        }

    /* --- MODAL CARRITO (SIDEBAR DERECHO) --- */
    .cart-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); backdrop-filter: blur(3px);
        z-index: 2000; opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .cart-overlay.active { opacity: 1; visibility: visible; }

    /* --- SIDEBAR CARRITO RESPONSIVO --- */
    .cart-sidebar {
        position: fixed; top: 0; 
        height: 100vh;
        background: #f8f9fa; /* Fondo gris muy suave (App Style) */
        z-index: 2001;
        transition: 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        box-shadow: -10px 0 50px rgba(0,0,0,0.15); /* Sombra más difusa */
        display: flex; flex-direction: column;
        width: 100%;
        right: -100%;
    }
    .cart-sidebar.active { right: 0; }

    @media (min-width: 768px) {
        .cart-sidebar { width: 450px; right: -450px; } /* Ancho fijo elegante en PC */
    }

    /* HEADER */
    .cart-header {
        padding: 25px; 
        background: #fff;
        display: flex; justify-content: space-between; align-items: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); /* Separación sutil */
        z-index: 10;
    }
    .cart-title { font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0; letter-spacing: -0.5px; }
    .btn-close-cart {
        background: #f1f2f6; border: none; width: 36px; height: 36px; border-radius: 12px;
        cursor: pointer; display: flex; align-items: center; justify-content: center; color: #2d3436;
        transition: 0.2s;
    }
    .btn-close-cart:hover { background: #e2e6ea; transform: scale(1.05); }

        /* --- ESTILOS CARRITO (REDISEÑO) --- */
    .cart-body { flex: 1; overflow-y: auto; padding: 20px; }

    /* TARJETA DE PRODUCTO (NUEVO DISEÑO) */
    .cart-item {
        background: white;
        border-radius: 16px;
        padding: 12px;
        margin-bottom: 15px;
        display: flex; align-items: center; gap: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .cart-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        border-color: rgba(255, 51, 102, 0.2); /* Borde rosa sutil al hover */
    }
    
    .cart-img-box {
        width: 75px; height: 75px; border-radius: 12px; 
        background: #f4f6f8; display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .cart-img-box img { width: 100%; height: 100%; object-fit: contain; padding: 5px; }

    .cart-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
    .cart-biz { font-size: 0.6rem; color: #b2bec3; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .cart-name { font-size: 0.95rem; font-weight: 700; color: var(--dark); line-height: 1.2; margin: 0; }
    .cart-price { font-size: 0.9rem; font-weight: 800; color: var(--primary); }

    /* CONTROLES (CÁPSULA) */
    .cart-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }

    /* Botón Basurero */
    .btn-del-item {
        width: 30px; height: 30px; border-radius: 50%; border: none;
        background: #fff0f3; color: var(--primary); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
    }
    .btn-del-item:hover { background: var(--primary); color: white; transform: scale(1.1); }

    /* Cápsula de Cantidad (+ / -) */
    .qty-capsule {
        display: flex; align-items: center; 
        background: #f1f2f6; 
        border-radius: 10px; /* Bordes menos redondos (más modernos) */
        padding: 3px; height: 32px; gap: 3px;
    }
    .btn-qty-mini {
        width: 26px; height: 26px; border-radius: 8px; border: none;
        background: white; color: var(--dark); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 800; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: 0.2s;
    }
    .btn-qty-mini:hover:not(:disabled) { color: var(--primary); transform: scale(1.05); }
    .qty-val { width: 22px; text-align: center; font-size: 0.85rem; font-weight: 800; color: var(--dark); }

    /* Botón Eliminar (Sutil) */
    .btn-del-item {
        width: 28px; height: 28px; border-radius: 8px; border: none;
        background: transparent; color: #b2bec3; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
    }
    .btn-del-item:hover { background: #fff0f3; color: var(--primary); }

    /* FOOTER */
    .cart-footer { 
        padding: 25px; 
        background: white; 
        box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
        z-index: 10;
    }
    .total-row { display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center; }
    .lbl-total { font-size: 1rem; color: #636e72; font-weight: 600; }
    .amount-total { font-size: 1.4rem; font-weight: 900; color: var(--dark); }

    .btn-checkout {
        width: 100%; padding: 16px; 
        background: linear-gradient(135deg, var(--primary) 0%, #ff5e78 100%); /* Degradado */
        color: white; border: none; border-radius: 16px; 
        font-weight: 800; font-size: 1rem; letter-spacing: 0.5px;
        cursor: pointer; transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(255, 51, 102, 0.3); /* Glow */
    }
    .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 51, 102, 0.4); }

    /* EMPTY STATE */
    .cart-empty { text-align: center; margin-top: 60px; color: #b2bec3; }
    .cart-empty i { font-size: 4rem; opacity: 0.3; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="overlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="<?= $usuarioFoto ?: 'https://ui-avatars.com/api/?name='.$usuarioNombre ?>" class="sidebar-avatar">
        <h3 style="margin:0; color:white; font-size:1.1rem;"><?= explode(' ', $usuarioNombre)[0] ?></h3>
        <p style="margin:5px 0 0; color:rgba(255,255,255,0.5); font-size:0.8rem;">Bienvenido</p>
    </div>

    <nav class="sidebar-nav">
        <?php if (!empty($menuItems['sueltas'])): ?>
            <?php foreach($menuItems['sueltas'] as $m): ?>
                <a href="<?= ruta_accion(explode('/', $m['url'])[0], explode('/', $m['url'])[1]) ?>" class="menu-link">
                    <i class="fa-solid <?= $m['icono'] ?>"></i> <?= $m['titulo'] ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= ruta_accion('auth', 'logout') ?>" class="btn-logout">
            <i class="fa-solid fa-power-off"></i> Cerrar Sesión
        </a>
    </div>
</aside>

<nav class="navbar">
    <div class="nav-left">
        <button class="btn-menu" id="btnMenu"><i class="fa-solid fa-bars-staggered"></i></button>
        <div class="brand">TuLook<span>360</span></div>
    </div>

    <div class="user-zone">
        
        <button onclick="abrirCarrito()" class="btn-profile" style="position:relative;" title="Mi Carrito">
            <i class="fa-solid fa-cart-shopping"></i>
            <span id="cartCountBadge" class="cart-badge" style="display:none;">0</span>
        </button>

        <?php if (tiene_permiso('usuario', 'perfil')): ?>
            <a href="<?= ruta_accion('usuario', 'perfil') ?>" class="btn-profile" title="Configuración">
                <i class="fa-solid fa-gear"></i>
            </a>
        <?php endif; ?>

        <div class="user-pill">
            <img src="<?= $usuarioFoto ?: 'https://ui-avatars.com/api/?name='.$usuarioNombre ?>" class="user-avatar">
            <div class="user-info">
                <span class="user-name"><?= explode(' ', $usuarioNombre)[0] ?></span>
                <span class="user-role">CLIENTE VIP</span>
            </div>
        </div>
    </div>
</nav>

<div class="main-container" style="min-height: 80vh;">



<div class="cart-overlay" id="cartOverlay" onclick="cerrarCarrito()"></div>
<aside class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3 class="cart-title">Mi Carrito <span style="color:var(--primary);">.</span></h3>
        <button class="btn-close-cart" onclick="cerrarCarrito()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    
    <div class="cart-body" id="cartBody">
        <div class="cart-empty">
            <i class="fa-solid fa-basket-shopping"></i>
            <p>Tu carrito está vacío</p>
        </div>
    </div>

    <div class="cart-footer">
        <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-weight:800; font-size:1.1rem; color:var(--dark);">
            <span>Total</span>
            <span id="cartTotalGlobal">$0.00</span>
        </div>
        
        <button class="btn-checkout" onclick="window.location.href='<?= ruta_accion('publico', 'checkout') ?>'">
            Procesar Compra <i class="fa-solid fa-arrow-right"></i>
        </button>
    </div>
</aside>

<script>
    // --- CONFIGURACIÓN API (SEGURIDAD) ---
    const API_CARRITO = {
        ver:        '<?= str_replace("+", "%2B", ruta_accion("publico", "ver_carrito_ajax")) ?>',
        actualizar: '<?= str_replace("+", "%2B", ruta_accion("publico", "actualizar_carrito_ajax")) ?>',
        eliminar:   '<?= str_replace("+", "%2B", ruta_accion("publico", "eliminar_carrito_ajax")) ?>'
    };
    
    // URL para obtener datos
    const URL_VER_CARRITO = API_CARRITO.ver;

    document.addEventListener('DOMContentLoaded', () => {
        cargarCarrito(false); 
    });

    // 1. ABRIR Y CARGAR
    function abrirCarrito() {
        document.getElementById('cartOverlay').classList.add('active');
        document.getElementById('cartSidebar').classList.add('active');
        cargarCarrito(true); // Cargar lista completa y renderizar
    }

    // 2. CERRAR
    function cerrarCarrito() {
        document.getElementById('cartOverlay').classList.remove('active');
        document.getElementById('cartSidebar').classList.remove('active');
    }

    // 3. FUNCIÓN PRINCIPAL DE CARGA
    function cargarCarrito(renderizarLista = false) {
        const badge = document.getElementById('cartCountBadge');
        const container = document.getElementById('cartBody');
        const totalLbl = document.getElementById('cartTotalGlobal');

        fetch(URL_VER_CARRITO)
            .then(r => r.json())
            .then(resp => {
                if(resp.success) {
                    // A. Actualizar Badge
                    if(resp.count > 0) {
                        badge.style.display = 'flex';
                        badge.innerText = resp.count;
                    } else {
                        badge.style.display = 'none';
                    }

                    if(renderizarLista) {
                        if(resp.items.length === 0) {
                            container.innerHTML = `<div class="cart-empty"><i class="fa-solid fa-basket-shopping"></i><p>Tu carrito está vacío</p></div>`;
                            totalLbl.innerText = '$0.00';
                            document.querySelector('.btn-checkout').disabled = true;
                            return;
                        }

                        // --- INICIO LÓGICA DE VALIDACIÓN DE PUNTOS ---
                        let totalPuntosGlobal = 0;
                        let puntosPorNegocio = {}; 
                        let saldoPorNegocio = {};   
                        let faltaSaldoGlobal = false;
                        
                        // Primer paso: Calcular necesidades por negocio
                        resp.items.forEach(item => {
                            const esPromo = (parseInt(item.prom_id) > 0);
                            if (esPromo && (item.prom_modalidad === 'PUNTOS' || item.prom_modalidad === 'MIXTO')) {
                                const nid = item.neg_id;
                                const ptsRequeridos = (parseInt(item.puntos_necesarios) || 0) * parseInt(item.car_cantidad);
                                puntosPorNegocio[nid] = (puntosPorNegocio[nid] || 0) + ptsRequeridos;
                                saldoPorNegocio[nid] = parseInt(item.saldo_puntos_actual) || 0;
                            }
                        });

                        // Segundo paso: Generar HTML
                        let html = '';
                        resp.items.forEach(item => {
                            const esPromo = (parseInt(item.prom_id) > 0);
                            const cantidad = parseInt(item.car_cantidad);
                            const nid = item.neg_id;
                            
                            // Multiplicar puntos de la fila
                            const ptsUnitarios = parseInt(item.puntos_necesarios) || 0;
                            const ptsFila = ptsUnitarios * cantidad;
                            
                            let htmlPrecio = '';
                            let errorPuntosHtml = '';

                            if (esPromo) {
                                if (item.prom_modalidad === 'PUNTOS' || item.prom_modalidad === 'MIXTO') {
                                    totalPuntosGlobal += ptsFila;
                                    // Validar si el acumulado del negocio supera el saldo
                                    if (puntosPorNegocio[nid] > saldoPorNegocio[nid]) {
                                        faltaSaldoGlobal = true;
                                        errorPuntosHtml = `<div style="color:#ff4757; font-size:0.7rem; font-weight:800; margin-top:5px;"><i class="fa-solid fa-circle-exclamation"></i> Saldo insuficiente (Tienes ${saldoPorNegocio[nid]} pts)</div>`;
                                    }
                                }

                                // Pintar precio según modalidad
                                if (item.prom_modalidad === 'PUNTOS') {
                                    htmlPrecio = `<span style="color:#0984e3; font-weight:900;"><i class="fa-solid fa-coins"></i> ${ptsFila} pts</span>`;
                                } else if (item.prom_modalidad === 'MIXTO') {
                                    htmlPrecio = `<span style="color:var(--primary); font-weight:800;">$${parseFloat(item.prom_precio_oferta).toFixed(2)}</span> <span style="font-size:0.75rem; color:#0984e3;">+ ${ptsFila} pts</span>`;
                                } else {
                                    htmlPrecio = `<span style="color:var(--primary); font-weight:800;">$${parseFloat(item.prom_precio_oferta).toFixed(2)}</span>`;
                                }
                            } else {
                                htmlPrecio = `<span class="cart-price">$${parseFloat(item.pro_precio).toFixed(2)}</span>`;
                            }

                            // Controles (Bloqueados si es promo)
                            const controlesHtml = esPromo ? 
                                `<span style="background:#f1f2f6; padding:4px 10px; border-radius:10px; font-size:0.7rem; font-weight:800; color:#888;">Cantidad: ${cantidad} (Oferta)</span>` :
                                `<div class="qty-capsule">
                                    <button class="btn-qty-mini" onclick="cambiarCantidadItem(${item.car_id}, -1)" ${(cantidad <= 1) ? 'disabled' : ''}><i class="fa-solid fa-minus"></i></button>
                                    <span class="qty-val">${cantidad}</span>
                                    <button class="btn-qty-mini" onclick="cambiarCantidadItem(${item.car_id}, 1)"><i class="fa-solid fa-plus"></i></button>
                                </div>`;

                            html += `
                                <div class="cart-item" style="${errorPuntosHtml ? 'border-color:#ff7675; background:#fff8f8;' : ''}">
                                    <div class="cart-img-box"><img src="${item.imagen}"></div>
                                    <div class="cart-info">
                                        <div class="cart-biz">${item.neg_nombre} ${esPromo ? '<span style="color:#ff7675; font-weight:900;">[PROMO]</span>' : ''}</div>
                                        <h4 class="cart-name">${item.pro_nombre}</h4>
                                        <div class="cart-price">${htmlPrecio}</div>
                                        ${errorPuntosHtml}
                                    </div>
                                    <div class="cart-actions">
                                        ${controlesHtml}
                                        <button class="btn-del-item" onclick="eliminarDelCarrito(${item.car_id})"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </div>`;
                        });
                        
                        container.innerHTML = html;

                        // Actualizar Footer Total
                        let totalTexto = '$' + parseFloat(resp.total).toFixed(2);
                        if (totalPuntosGlobal > 0) totalTexto += ` <span style="color:#0984e3;">+ <i class="fa-solid fa-coins"></i> ${totalPuntosGlobal} pts</span>`;
                        totalLbl.innerHTML = totalTexto;

                        // Bloqueo del Botón de Compra
                        const btnCheckout = document.querySelector('.btn-checkout');
                        if (faltaSaldoGlobal) {
                            btnCheckout.disabled = true;
                            btnCheckout.style.opacity = '0.5';
                            btnCheckout.innerHTML = 'Puntos Insuficientes <i class="fa-solid fa-lock"></i>';
                        } else {
                            btnCheckout.disabled = false;
                            btnCheckout.style.opacity = '1';
                            btnCheckout.innerHTML = 'Procesar Compra <i class="fa-solid fa-arrow-right"></i>';
                        }
                    }
                }
            })
            .catch(err => console.error("Error carrito:", err));
    }

    // 4. CAMBIAR CANTIDAD (+1 o -1)
    function cambiarCantidadItem(carId, cambio) {
        fetch(API_CARRITO.actualizar, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ car_id: carId, cambio: cambio })
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                cargarCarrito(true); // Recargar para ver cambios y actualizar precio total
            } else {
                console.error("Error actualizando cantidad");
            }
        })
        .catch(err => console.error(err));
    }

    // 5. ELIMINAR ITEM
    function eliminarDelCarrito(id) {
        // Ejecutamos directamente sin preguntar
        fetch(API_CARRITO.eliminar, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ car_id: id })
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                cargarCarrito(true); // Recargar lista visualmente
            } else {
                console.error("Error al eliminar");
            }
        })
        .catch(err => console.error(err));
    }
</script>