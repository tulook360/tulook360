<?php
// helpers para formatear moneda
function formatoDinero($valor) {
    return '$' . number_format($valor, 2);
}
?>

<style>
    /* --- AJUSTES ESTRUCTURALES --- */
    .dashboard-wrapper-perfil {
        margin-top: -85px; /* Sube detrás del Navbar transparente */
        position: relative;
        z-index: 1;
        background-color: #f4f6f9;
        min-height: 100vh;
        padding-bottom: 60px;
    }

    :root { --primary: #ff3366; --dark: #1e272e; --text-muted: #636e72; }

    /* --- HERO HEADER (PORTADA) --- */
    .biz-header {
        height: 55vh; 
        position: relative;
        background-color: var(--dark);
        display: flex; align-items: flex-end; justify-content: center;
        overflow: hidden;
        border-radius: 0 0 50px 50px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }

    .biz-cover-bg {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        object-fit: cover; opacity: 0.5; filter: blur(3px); 
        transform: scale(1.05); transition: 0.5s;
    }
    
    .biz-header:hover .biz-cover-bg { transform: scale(1.0); filter: blur(0px); }

    .biz-overlay-gradient {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.1) 60%, rgba(0,0,0,0.4) 100%);
    }

    /* --- CONTENIDO DEL HEADER --- */
    .biz-header-content {
        position: relative; z-index: 10;
        text-align: center;
        bottom: -50px; /* Logo flota mitad adentro mitad afuera */
        width: 100%;
    }

    .biz-logo-big {
        width: 140px; height: 140px;
        border-radius: 35px;
        border: 5px solid #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        background: white; object-fit: cover;
    }

    .biz-title {
        color: white; font-size: 3rem; font-weight: 900;
        font-family: 'Outfit', sans-serif; margin: 0; 
        text-shadow: 0 4px 15px rgba(0,0,0,0.5);
        line-height: 1.1; margin-bottom: 15px; letter-spacing: -1px;
    }

    /* --- BOTÓN VOLVER (FLOTANTE) --- */
    .btn-back-floating {
        position: fixed; top: 100px; left: 30px; z-index: 999;
        background: white; color: var(--dark);
        width: 45px; height: 45px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        text-decoration: none; transition: 0.3s;
        border: 1px solid #eee; cursor: pointer;
    }
    .btn-back-floating:hover { 
        background: var(--primary); color: white; transform: scale(1.1) rotate(-10deg); 
    }

    /* --- CUERPO --- */
    .biz-container {
        max-width: 1100px; margin: 0 auto; padding: 80px 20px 40px 20px;
    }

    /* STATS */
    .stats-wrapper {
        display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-bottom: 50px;
    }
    .stat-pill {
        background: white; padding: 10px 25px; border-radius: 50px;
        display: flex; align-items: center; gap: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03); color: var(--text-muted); font-weight: 600;
    }
    .stat-pill i { color: var(--primary); }
    .stat-pill b { color: var(--dark); font-size: 1.1rem; }

    /* TITULOS DE SECCIÓN */
    .section-title {
        font-size: 1.5rem; font-weight: 800; color: var(--dark);
        margin-bottom: 25px; display: flex; align-items: center; gap: 10px;
    }
    .section-title::after {
        content: ''; flex: 1; height: 2px; background: #eee; margin-left: 10px;
    }

    /* --- GRID DE TARJETAS (MISMO DISEÑO) --- */
    .profile-grid {
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
        gap: 25px; margin-bottom: 50px;
    }

    .p-card {
        background: white; border-radius: 20px; overflow: hidden;
        border: 1px solid #f0f0f0; transition: 0.3s;
        display: flex; flex-direction: column;
    }
    .p-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: var(--primary); }
    
    .p-img-box { height: 160px; overflow: hidden; position: relative; }
    .p-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .p-card:hover .p-img { transform: scale(1.1); }
    
    .p-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
    .p-title { font-weight: 800; color: var(--dark); margin: 0 0 5px 0; font-size: 1rem; }
    .p-desc { font-size: 0.85rem; color: #888; margin-bottom: 15px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
    .p-footer { margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #eee; padding-top: 10px; }
    .p-price { font-weight: 900; color: var(--primary); font-size: 1.1rem; }
    
    /* BOTON RESERVAR (NUEVO) */
    .btn-reservar-perfil {
        background: var(--dark); color: white;
        border: none; padding: 8px 20px;
        border-radius: 50px; font-weight: 700; font-size: 0.8rem;
        cursor: pointer; transition: 0.3s;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        display: flex; align-items: center; gap: 8px;
    }
    .btn-reservar-perfil:hover { 
        background: var(--primary); 
        transform: translateY(-2px); 
    }

    .btn-action {
        width: 35px; height: 35px; border-radius: 50%; border: none;
        background: var(--dark); color: white; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: 0.2s;
    }
    .p-card:hover .btn-action { background: var(--primary); }

</style>

<div class="dashboard-wrapper-perfil">

    <a href="javascript:history.back()" class="btn-back-floating" title="Regresar al Dashboard">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <?php 
        $portada = 'recursos/img/sin_foto.png'; 
        if(!empty($perfil['sucursales']) && !empty($perfil['sucursales'][0]['suc_foto'])){
            $portada = $perfil['sucursales'][0]['suc_foto'];
        }
        $logo = $perfil['info']['neg_logo'] ?: 'recursos/img/sin_foto.png';
    ?>

    <div class="biz-header">
        <img src="<?= $portada ?>" class="biz-cover-bg">
        <div class="biz-overlay-gradient"></div>
        
        <div class="biz-header-content">
            <h1 class="biz-title"><?= $perfil['info']['neg_nombre'] ?></h1>
            <img src="<?= $logo ?>" class="biz-logo-big">
        </div>
    </div>

    <div class="biz-container">
        
        <div class="stats-wrapper">
            <div class="stat-pill">
                <i class="fa-solid fa-scissors"></i> 
                <span><b><?= count($perfil['servicios']) ?></b> Servicios</span>
            </div>
            <div class="stat-pill">
                <i class="fa-solid fa-bag-shopping"></i> 
                <span><b><?= count($perfil['productos']) ?></b> Productos</span>
            </div>
            <div class="stat-pill">
                <i class="fa-solid fa-location-dot"></i> 
                <span><b><?= count($perfil['sucursales']) ?></b> Sucursales</span>
            </div>
            <div class="stat-pill">
                <i class="fa-regular fa-calendar-check"></i> 
                <span>Desde <b><?= date('Y', strtotime($perfil['info']['neg_fundacion'])) ?></b></span>
            </div>
        </div>

        <?php if(!empty($perfil['servicios'])): ?>
            <h2 class="section-title"><i class="fa-solid fa-spa" style="color:var(--primary)"></i> Servicios Destacados</h2>
            <div class="profile-grid">
                <?php foreach($perfil['servicios'] as $serv): ?>
                    <?php 
                        // --- CORRECCIÓN DE IMAGEN ---
                        // Intentamos obtener la imagen de donde sea que venga (array 'imagenes' o string 'imagen')
                        $imgS = 'recursos/img/sin_foto.png';
                        
                        if (!empty($serv['imagenes']) && is_array($serv['imagenes']) && count($serv['imagenes']) > 0) {
                            $imgS = $serv['imagenes'][0]; // Prioridad 1: Array de imágenes
                        } elseif (!empty($serv['imagen'])) {
                            // Prioridad 2: Campo 'imagen' directo (si es string)
                            $imgS = is_array($serv['imagen']) ? ($serv['imagen'][0] ?? $imgS) : $serv['imagen'];
                        }
                    ?>
                    <div class="p-card">
                        <div class="p-img-box"><img src="<?= $imgS ?>" class="p-img"></div>
                        <div class="p-body">
                            <h4 class="p-title"><?= $serv['serv_nombre'] ?></h4>
                            <p class="p-desc"><?= strip_tags($serv['serv_descripcion']) ?: 'Sin descripción detallada.' ?></p>
                            <div class="p-footer">
                                <span class="p-price"><?= formatoDinero($serv['serv_precio']) ?></span>
                                
                                <button class="btn-reservar-perfil" onclick="abrirModalServicio(<?= $serv['serv_id'] ?>)">
                                    Reservar <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($perfil['productos'])): ?>
            <h2 class="section-title"><i class="fa-solid fa-box-open" style="color:var(--primary)"></i> Productos en Venta</h2>
            <div class="profile-grid">
                <?php foreach($perfil['productos'] as $prod): ?>
                    <?php 
                        // --- CORRECCIÓN DE IMAGEN PRODUCTO ---
                        $imgP = 'recursos/img/sin_foto.png';
                        if (!empty($prod['imagenes']) && is_array($prod['imagenes']) && count($prod['imagenes']) > 0) {
                            $imgP = $prod['imagenes'][0];
                        } elseif (!empty($prod['imagen'])) {
                            $imgP = is_array($prod['imagen']) ? ($prod['imagen'][0] ?? $imgP) : $prod['imagen'];
                        }
                    ?>
                    <div class="p-card">
                        <div class="p-img-box"><img src="<?= $imgP ?>" class="p-img"></div>
                        <div class="p-body">
                            <h4 class="p-title"><?= $prod['pro_nombre'] ?></h4>
                            <p class="p-desc"><?= strip_tags($prod['pro_descripcion']) ?: 'Sin descripción disponible.' ?></p>
                            <div class="p-footer">
                                <span class="p-price"><?= formatoDinero($prod['pro_precio']) ?></span>
                                <button class="btn-action"><i class="fa-solid fa-cart-plus"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</div>