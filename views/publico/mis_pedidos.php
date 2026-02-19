<?php
// 1. OBTENER NOMBRE REAL DE LA SESIÓN
if (session_status() === PHP_SESSION_NONE) session_start();

$nombreClienteFull = 'Cliente'; // Valor por defecto

// Verificamos si existen las variables de sesión (Ajusta si tus variables se llaman diferente, ej: 'nombres')
if (isset($_SESSION['usuario_nombre'])) {
    $nombreClienteFull = $_SESSION['usuario_nombre'];
    if (isset($_SESSION['usuario_apellido'])) {
        $nombreClienteFull .= ' ' . $_SESSION['usuario_apellido'];
    }
}

$urlDetalle  = ruta_accion('publico', 'orden_detalle');
$urlCancelar = ruta_accion('publico', 'cancelar_pedido_ajax');
$urlFactura  = ruta_accion('publico', 'consultar_estado_ruta_ajax');
$urlCalificar = ruta_accion('publico', 'procesar_resena_ajax'); // <--- AGREGA ESTA
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    :root {
        --bg-light: #f8f9fa;
        --text-dark: #2d3436;
        --text-muted: #636e72;
        --border-color: #dfe6e9;
        --primary-soft: #ffecef; /* Un tono suave de tu color primario */
    }

    /* --- LAYOUT PRINCIPAL --- */
    .orders-wrapper {
        max-width: 1200px; margin: 40px auto; padding: 0 25px;
        font-family: 'Outfit', sans-serif;
    }

    /* --- ENCABEZADO PREMIUM (SOLO ESTO CAMBIA) --- */
    .page-header-main {
        background: white;
        padding: 30px 40px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05); /* Sombra elegante */
        margin-bottom: 40px;
        border: 1px solid rgba(0,0,0,0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 30px;
        position: relative;
        overflow: hidden;
    }

    /* Decoración de fondo sutil (Círculos) */
    .page-header-main::before {
        content: ''; position: absolute; top: -50px; right: -50px;
        width: 200px; height: 200px; background: var(--primary-soft);
        border-radius: 50%; opacity: 0.5; z-index: 0;
    }

    .ph-content { position: relative; z-index: 1; flex: 1; }

    .ph-title { 
        font-family: 'Kalam', cursive; 
        font-size: 3rem; 
        line-height: 1; 
        color: var(--text-dark); 
        margin: 0 0 10px 0;
    }
    .ph-title span { color: var(--primary); } /* La parte rosa */

    .ph-desc {
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        color: #666;
        max-width: 600px;
        line-height: 1.6;
        margin: 0;
    }

    /* Cajas de Estadísticas a la derecha */
    .ph-stats { 
        position: relative; z-index: 1; 
        display: flex; gap: 15px; 
    }

    .stat-box {
        background: #f8f9fa;
        padding: 15px 25px;
        border-radius: 15px;
        text-align: center;
        border: 1px solid #eee;
        transition: 0.3s;
    }
    .stat-box:hover { transform: translateY(-3px); border-color: var(--primary); background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

    .stat-number { display: block; font-size: 1.8rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
    .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 700; }

    /* Ajuste Móvil */
    @media (max-width: 768px) {
        .page-header-main { flex-direction: column; align-items: flex-start; padding: 25px; }
        .ph-stats { width: 100%; justify-content: space-between; }
        .stat-box { flex: 1; }
    }

    /* --- PESTAÑAS DISEÑO PREMIUM (CÁPSULA) --- */
    .tabs-modern { 
        display: inline-flex; /* Se ajusta al contenido */
        background: white; 
        padding: 8px; 
        border-radius: 50px; /* Forma redondeada completa */
        width: auto; 
        box-shadow: 0 5px 20px rgba(0,0,0,0.04); /* Sombra suave igual al header */
        border: 1px solid rgba(0,0,0,0.03);
        margin-bottom: 35px;
        gap: 5px; /* Espacio entre botones */
    }

    .tab-m-btn {
        padding: 12px 35px; 
        border: none; 
        background: transparent; 
        color: #888; /* Gris suave inactivo */
        font-weight: 700; 
        border-radius: 40px; 
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); 
        font-size: 0.95rem;
        font-family: 'Outfit', sans-serif;
        letter-spacing: 0.5px;
    }

    /* Efecto Hover */
    .tab-m-btn:hover { 
        background: var(--bg-light); 
        color: var(--primary); 
    }

    /* ESTADO ACTIVO (El cambio fuerte) */
    .tab-m-btn.active { 
        background: var(--text-dark); /* Fondo Negro elegante */
        color: white; 
        box-shadow: 0 4px 15px rgba(45, 52, 54, 0.3); 
        transform: translateY(-1px); /* Se eleva un poquito */
    }
    
    .tab-content { display: none; animation: slideUp 0.4s ease; }
    .tab-content.active { display: block; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* --- GRID DE TARJETAS MEJORADO --- */
    .orders-grid {
        display: grid; 
        /* CAMBIO AQUÍ: Poner 320px asegura que quepan 3 en PC */
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
        gap: 30px;
    }

    .order-card-new {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--border-color);
        transition: transform 0.3s, box-shadow 0.3s; display: flex; flex-direction: column;
    }
    .order-card-new:hover { transform: translateY(-7px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); border-color: var(--primary); }

    /* Encabezado de Tarjeta con Estado */
    .ocn-header {
        padding: 20px; display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid var(--bg-light); position: relative; overflow: hidden;
    }
    /* Barra de estado lateral */
    .ocn-header::before { content:''; position:absolute; left:0; top:0; bottom:0; width:6px; }
    .st-pendiente .ocn-header::before { background: #f1c40f; }
    .st-en_camino .ocn-header::before { background: #3498db; }
    .st-completado .ocn-header::before { background: #2ecc71; }
    .st-cancelado .ocn-header::before { background: #ff4757; }

    .ocn-code { font-size: 1.1rem; font-weight: 800; color: var(--text-dark); }
    .ocn-date { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 5px; }
    .ocn-status-badge {
        padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .st-pendiente .ocn-status-badge { background: #fff3cd; color: #856404; }
    .st-en_camino .ocn-status-badge { background: #d1ecf1; color: #0c5460; }
    .st-completado .ocn-status-badge { background: #d4edda; color: #155724; }
    .st-cancelado .ocn-status-badge { background: #f8d7da; color: #721c24; }

    /* Cuerpo de Tarjeta */
    .ocn-body { padding: 25px 20px; flex: 1; display: flex; align-items: center; justify-content: space-between; }
    .ocn-total-label { font-size: 0.9rem; color: var(--text-muted); display: block; margin-bottom: 5px; }
    .ocn-total-value { font-size: 1.8rem; font-weight: 900; color: var(--text-dark); }
    .ocn-items { display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--text-muted); background: var(--bg-light); padding: 8px 15px; border-radius: 10px; }

    /* Pie de Tarjeta (Acciones) */
    .ocn-footer { padding: 15px 20px; background: var(--bg-light); display: flex; gap: 15px; }
    .btn-ocn {
        flex: 1; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.9rem;
        display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; transition: 0.2s; text-decoration: none; border: none;
    }
    .btn-ocn-track { background: var(--text-dark); color: white; }
    .btn-ocn-track:hover { background: var(--primary); }
    .btn-ocn-details { background: white; color: var(--text-muted); border: 2px solid var(--border-color); }
    .btn-ocn-details:hover { border-color: var(--text-dark); color: var(--text-dark); }
    .btn-ocn-receipt { background: var(--primary-soft); color: var(--primary); }
    .btn-ocn-receipt:hover { background: var(--primary); color: white; }
    
    .btn-ocn-cancel {
        width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;
        border-radius: 12px; background: white; color: #ff4757; border: 2px solid #ff4757; cursor: pointer; transition: 0.2s;
    }
    .btn-ocn-cancel:hover { background: #ff4757; color: white; }


    /* --- NUEVO RECIBO LATERAL (SIDE PANEL) --- */
    .side-panel-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 9999; opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .side-panel {
        position: fixed; top: 0; right: 0; height: 100%;
        width: 50%; /* PC DEFAULT */
        max-width: 600px; background: white; box-shadow: -5px 0 30px rgba(0,0,0,0.1);
        transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: flex; flex-direction: column; z-index: 10000;
    }
    .side-panel-overlay.active { opacity: 1; visibility: visible; }
    .side-panel-overlay.active .side-panel { transform: translateX(0); }

    /* Estructura interna del panel */
    .sp-header {
        padding: 25px 30px; display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid var(--border-color);
    }
    .sp-title { font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    .sp-close { font-size: 1.5rem; color: var(--text-muted); cursor: pointer; padding: 5px; }

    .sp-content-scroll {
        flex: 1; overflow-y: auto; padding: 30px; /* Scroll en el cuerpo */
    }
    
    /* Diseño del Recibo dentro del panel */
    .receipt-box { background: white; }
    .rec-header { text-align: center; margin-bottom: 40px; }
    .rec-logo { font-family: 'Kalam', cursive; font-size: 2.5rem; color: var(--text-dark); display: block; margin-bottom: 10px; }
    .rec-meta-ord { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); background: var(--bg-light); padding: 10px 20px; border-radius: 50px; display: inline-block; margin: 15px 0; }
    
    .rec-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; background: var(--bg-light); padding: 20px; border-radius: 15px; }
    .rec-label { font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 5px; }
    .rec-value { font-size: 1.05rem; font-weight: 600; color: var(--text-dark); }

    .rec-store-group { margin-bottom: 25px; }
    .rec-store-header { background: var(--text-dark); color: white; padding: 10px 15px; border-radius: 8px 8px 0 0; font-weight: 700; font-size: 0.9rem; display: flex; justify-content: space-between; }
    .rec-item-row { display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid var(--border-color); }
    .rec-item-name { font-weight: 600; color: var(--text-dark); margin-bottom: 5px; }
    .rec-item-meta { font-size: 0.9rem; color: var(--text-muted); }
    .rec-item-total { font-weight: 800; font-size: 1.1rem; color: var(--text-dark); }

    .rec-grand-total {
        display: flex; justify-content: space-between; align-items: center;
        padding: 25px; background: var(--primary-soft); border-radius: 15px; margin-top: 30px;
    }
    .rgt-label { font-size: 1.2rem; font-weight: 800; color: var(--primary); }
    .rgt-value { font-size: 2rem; font-weight: 900; color: var(--primary); }

    .sp-footer { padding: 25px 30px; border-top: 1px solid var(--border-color); }
    .btn-download-full { 
        width: 100%; padding: 18px; background: var(--text-dark); color: white; border: none; border-radius: 15px;
        font-size: 1.1rem; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.2s;
    }
    .btn-download-full:hover { background: var(--primary); }


    /* --- MODAL GENÉRICO (Reemplazo de Alerts) --- */
    .generic-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999;
        background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .generic-modal-overlay.active { opacity: 1; visibility: visible; }
    .generic-modal-box {
        background: white; width: 90%; max-width: 450px; border-radius: 25px; padding: 35px;
        text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.2); transform: scale(0.8); transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .generic-modal-overlay.active .generic-modal-box { transform: scale(1); }

    .gm-icon { font-size: 3.5rem; margin-bottom: 20px; }
    .gm-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-bottom: 10px; }
    .gm-desc { font-size: 1rem; color: var(--text-muted); margin-bottom: 30px; line-height: 1.5; }
    .gm-actions { display: flex; gap: 15px; justify-content: center; }
    .btn-gm { padding: 12px 30px; border-radius: 50px; font-weight: 700; border: none; cursor: pointer; font-size: 1rem; transition: 0.2s; }
    .btn-gm-secondary { background: var(--bg-light); color: var(--text-muted); }
    .btn-gm-primary { background: var(--primary); color: white; }
    .btn-gm-danger { background: #ff4757; color: white; }



    /* --- ESTILOS CALIFICACIÓN MEJORADOS --- */
    .rate-card {
        background: #fff; border: 1px solid #eee; border-radius: 12px;
        margin-bottom: 25px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.03);
    }
    
    .rate-card-header {
        background: #fcfcfc; padding: 15px 20px; border-bottom: 1px solid #eee;
        display: flex; align-items: center; justify-content: space-between;
    }
    .rate-biz-info { display: flex; align-items: center; gap: 12px; }
    .rate-biz-logo { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd; }
    .rate-biz-name { font-weight: 800; color: var(--text-dark); font-size: 1rem; }
    
    .rate-section-title { 
        font-size: 0.8rem; text-align: center; color: #aaa; text-transform: uppercase; 
        font-weight: 700; margin: 15px 0 5px 0; letter-spacing: 1px;
    }

    .rate-prod-row { 
        display: flex; align-items: center; justify-content: space-between; 
        padding: 12px 20px; border-bottom: 1px solid #f9f9f9; 
    }
    .rate-prod-left { display: flex; align-items: center; gap: 12px; }
    .rate-prod-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid #eee; }
    .rate-prod-name { font-size: 0.9rem; font-weight: 600; color: #444; max-width: 180px; }

    /* Estrellas centradas */
    .stars-centered { display: flex; flex-direction: row-reverse; gap: 4px; }
    .stars-centered input { display: none; }
    .stars-centered label { font-size: 1.5rem; color: #ddd; cursor: pointer; transition: 0.2s; }
    .stars-centered label:hover, .stars-centered label:hover ~ label, .stars-centered input:checked ~ label { color: #f1c40f; }


    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
        .orders-grid { grid-template-columns: 1fr; } /* 1 columna en móvil */
        .side-panel { width: 100%; } /* Recibo 100% ancho en móvil */
        .page-header-main { flex-direction: column; align-items: flex-start; gap: 15px; }
        .rec-info-grid { grid-template-columns: 1fr; }
        .ocn-body { flex-direction: column; align-items: flex-start; gap: 15px; }
        .ocn-items { width: 100%; }
    }
</style>

<div class="dashboard-wrapper">
    <div class="orders-wrapper">
        <div class="page-header-main">
            <div class="ph-content">
                <h1 class="ph-title">Mis <span>Pedidos</span> 📦</h1>
                <p class="ph-desc">
                    Bienvenido a tu historial de compras. Aquí puedes <strong>rastrear la ubicación</strong> de tus productos en tiempo real, gestionar devoluciones o descargar tus <strong>recibos oficiales</strong> de forma instantánea.
                </p>
            </div>

            <div class="ph-stats">
                <div class="stat-box">
                    <span class="stat-number" style="color:#f1c40f"><?= count($activos) ?></span>
                    <span class="stat-label">En Curso</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number" style="color:#2ecc71"><?= count($historial) ?></span>
                    <span class="stat-label">Finalizados</span>
                </div>
            </div>
        </div>

        <div class="tabs-modern">
            <button class="tab-m-btn active" onclick="switchTab('activos')">En Curso (<?= count($activos) ?>)</button>
            <button class="tab-m-btn" onclick="switchTab('historial')">Historial (<?= count($historial) ?>)</button>
        </div>

        <div id="tab-activos" class="tab-content active">
            <?php if (empty($activos)): ?>
                <div style="text-align:center; padding: 60px; color: var(--text-muted);">
                    <i class="fa-solid fa-wind" style="font-size:3rem; margin-bottom:20px; opacity:0.5;"></i>
                    <h3>No tienes pedidos en curso.</h3>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($activos as $p): $stClass = strtolower($p['ord_estado']); ?>
                        <div class="order-card-new st-<?= $stClass ?>">
                            <div class="ocn-header">
                                <div>
                                    <div class="ocn-code"><?= $p['ord_codigo'] ?></div>
                                    <div class="ocn-date"><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y h:i A', strtotime($p['ord_fecha'])) ?></div>
                                </div>
                                <div class="ocn-status-badge"><?= str_replace('_', ' ', $p['ord_estado']) ?></div>
                            </div>
                            <div class="ocn-body">
                                <div>
                                    <span class="ocn-total-label">Total a Pagar</span>
                                    <span class="ocn-total-value">
                                        <?php if(floatval($p['total_dinero_real']) > 0): ?>
                                            $<?= number_format($p['total_dinero_real'], 2) ?>
                                        <?php endif; ?>
                                        
                                        <?php if(intval($p['total_puntos_orden']) > 0): ?>
                                            <?= (floatval($p['total_dinero_real']) > 0) ? ' <small>+</small> ' : '' ?>
                                            <span style="color:#0984e3;"><i class="fa-solid fa-coins"></i> <?= $p['total_puntos_orden'] ?> pts</span>
                                        <?php endif; ?>

                                        <?php if(floatval($p['total_dinero_real']) <= 0 && intval($p['total_puntos_orden']) <= 0): ?>
                                            $0.00
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="ocn-items"><i class="fa-solid fa-layer-group"></i> <?= $p['items'] ?> Productos</div>
                            </div>
                            <div class="ocn-footer">
                                <a href="<?= $urlDetalle ?>&id=<?= $p['ord_id'] ?>" class="btn-ocn btn-ocn-track">
                                    <i class="fa-solid fa-map-location-dot"></i> Rastrear Ahora
                                </a>
                                <?php if ($p['ord_estado'] === 'PENDIENTE'): ?>
                                    <div class="btn-ocn-cancel" onclick="confirmarCancelar(<?= $p['ord_id'] ?>)" title="Cancelar Pedido">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="tab-historial" class="tab-content">
            <?php if (empty($historial)): ?>
                 <div style="text-align:center; padding: 60px; color: var(--text-muted);">
                    <i class="fa-solid fa-box-archive" style="font-size:3rem; margin-bottom:20px; opacity:0.5;"></i>
                    <h3>Tu historial está vacío.</h3>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($historial as $h): 
                        $esCompletado = in_array($h['ord_estado'], ['COMPLETADO', 'ENTREGADO']);
                        $stClass = strtolower($h['ord_estado']);
                    ?>
                        <div class="order-card-new st-<?= $stClass ?>" style="opacity: 0.9;">
                            <div class="ocn-header">
                                <div>
                                    <div class="ocn-code"><?= $h['ord_codigo'] ?></div>
                                    <div class="ocn-date"><?= date('d/m/Y', strtotime($h['ord_fecha'])) ?></div>
                                </div>
                                <div class="ocn-status-badge" style="background:#eee; color:#666;"><?= $h['ord_estado'] ?></div>
                            </div>
                            <div class="ocn-body">
                                <div>
                                    <span class="ocn-total-label">Total Pagado</span>
                                    <span class="ocn-total-value">
                                        <?php if(floatval($h['total_dinero_real']) > 0): ?>
                                            $<?= number_format($h['total_dinero_real'], 2) ?>
                                        <?php endif; ?>
                                        
                                        <?php if(intval($h['total_puntos_orden']) > 0): ?>
                                            <?= (floatval($h['total_dinero_real']) > 0) ? ' <small>+</small> ' : '' ?>
                                            <span style="color:#0984e3;"><i class="fa-solid fa-coins"></i> <?= $h['total_puntos_orden'] ?> pts</span>
                                        <?php endif; ?>

                                        <?php if(floatval($h['total_dinero_real']) <= 0 && intval($h['total_puntos_orden']) <= 0): ?>
                                            $0.00
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="ocn-items"><i class="fa-solid fa-check"></i> <?= $h['items'] ?> ítems</div>
                            </div>
                            <div class="ocn-footer">
                                
                                <?php if($esCompletado): ?>
                                    <button class="btn-ocn btn-ocn-receipt" onclick="abrirReciboLateral(<?= $h['ord_id'] ?>)">
                                        <i class="fa-solid fa-receipt"></i> Recibo
                                    </button>

                                    <?php if($h['tiene_calificacion'] == 0): ?>
                                        <button class="btn-ocn" style="background:#f1c40f; color:white;" onclick="abrirCalificacion(<?= $h['ord_id'] ?>)">
                                            <i class="fa-solid fa-star"></i> Calificar
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-ocn" style="background:#eee; color:#aaa; cursor:default;">
                                            <i class="fa-solid fa-star"></i> Listo
                                        </button>
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="side-panel-overlay" id="sideReceiptPanel">
    <div class="side-panel">
        <div class="sp-header">
            <h3 class="sp-title">Detalle del Recibo</h3>
            <div class="sp-close" onclick="cerrarReciboLateral()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        
        <div class="sp-content-scroll" id="receiptContentToPrint">
            <div class="receipt-box" id="receiptDynamicBody">
                <div style="text-align:center; padding:50px;"><i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color:var(--primary)"></i></div>
            </div>
        </div>

        <div class="sp-footer">
            <button class="btn-download-full" onclick="descargarPDFLateral()">
                <i class="fa-solid fa-file-pdf"></i> Descargar factura
            </button>
        </div>
    </div>
</div>

<div class="side-panel-overlay" id="sideRatingPanel">
    <div class="side-panel">
        <div class="sp-header">
            <h3 class="sp-title">Calificar Compra</h3>
            <div class="sp-close" onclick="cerrarCalificacion()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        
        <div class="sp-content-scroll">
            <div id="ratingDynamicBody">
                </div>
        </div>

        <div class="sp-footer">
            <button class="btn-download-full" onclick="enviarCalificacion()" id="btnEnviarResena" style="background:var(--primary);">
                Enviar Reseña
            </button>
        </div>
    </div>
</div>


<div id="genericModal" class="generic-modal-overlay">
    <div class="generic-modal-box">
        <div id="gmIcon" class="gm-icon"></div>
        <h3 id="gmTitle" class="gm-title"></h3>
        <p id="gmDesc" class="gm-desc"></p>
        <div id="gmActions" class="gm-actions">
            </div>
    </div>
</div>

<script>
    // Variable global con el nombre del cliente desde PHP
    const clienteNombreSesion = "<?= htmlspecialchars($nombreClienteFull) ?>";

    // --- TABS ---
    function switchTab(tabName) {
        document.querySelectorAll('.tab-m-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        const btns = document.querySelectorAll('.tab-m-btn');
        if(tabName === 'activos') btns[0].classList.add('active');
        if(tabName === 'historial') btns[1].classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    // --- LÓGICA DEL RECIBO LATERAL ---
    function abrirReciboLateral(idOrden) {
        const panel = document.getElementById('sideReceiptPanel');
        const container = document.getElementById('receiptDynamicBody');
        panel.classList.add('active');
        container.innerHTML = '<div style="text-align:center; padding:50px;"><i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color:var(--primary-soft)"></i></div>';

        fetch('<?= $urlFactura ?>&id=' + idOrden)
        .then(r => r.json())
        .then(data => {
            if(!data.success) throw new Error(data.error || "Error");

            const fechaOrdenTexto = new Date(data.fecha_orden.replace(/-/g, '/')).toLocaleString();
            let htmlContent = `
                <div class="rec-header">
                    <span class="rec-logo">TuLook<span style="color:var(--primary)">360</span></span>
                    <div class="rec-meta-ord">${data.codigo_orden}</div>
                </div>
                <div class="rec-info-grid">
                    <div><span class="rec-label">Cliente</span><span class="rec-value">${clienteNombreSesion}</span></div>
                    <div style="text-align: right;"><span class="rec-label">Fecha</span><span class="rec-value">${fechaOrdenTexto}</span></div>
                </div>`;

            let granTotalDinero = 0;
            let granTotalPuntos = 0;

            Object.values(data.paradas).forEach(sucursal => {
                htmlContent += `<div class="rec-store-group"><div class="rec-store-header"><span>${sucursal.info.nombre}</span></div>`;
                
                sucursal.productos.forEach(prod => {
                    const subDinero = parseFloat(prod.odet_precio_unitario || 0) * parseInt(prod.odet_cantidad);
                    const pts = parseInt(prod.odet_puntos_canje || 0);
                    granTotalDinero += subDinero;
                    granTotalPuntos += pts;

                    htmlContent += `
                        <div class="rec-item-row">
                            <div><div class="rec-item-name">${prod.pro_nombre}</div><div class="rec-item-meta">${prod.odet_cantidad} unidades</div></div>
                            <div class="rec-item-total" style="text-align:right;">
                                ${pts > 0 ? `<span style="color:#0984e3; font-weight:800;">${pts} pts</span>` : ''}
                                ${(pts > 0 && subDinero > 0) ? '<br>' : ''}
                                ${subDinero > 0 ? `$${subDinero.toFixed(2)}` : (pts <= 0 ? '$0.00' : '')}
                            </div>
                        </div>`;
                });

                // Pagos
                if (sucursal.pagos && sucursal.pagos.length > 0) {
                    htmlContent += `<div style="background:#f8f9fa; padding:10px 15px;">`;
                    sucursal.pagos.forEach(p => {
                        htmlContent += `<div style="display:flex; justify-content:space-between; font-size:0.8rem;"><span>${p.mp_nombre}</span><strong>$${parseFloat(p.pago_monto).toFixed(2)}</strong></div>`;
                    });
                    htmlContent += `</div>`;
                } else if (granTotalPuntos > 0 && granTotalDinero <= 0) {
                    htmlContent += `<div style="background:#e3f2fd; color:#0d47a1; padding:8px; font-size:0.8rem; text-align:center;">Pago con Puntos</div>`;
                } else {
                    htmlContent += `<div style="background:#fff3cd; padding:8px; font-size:0.8rem; text-align:center;">Pago Pendiente</div>`;
                }
                htmlContent += `</div>`;
            });

            // COSTO DE ENVÍO (CORREGIDO: Usamos granTotalDinero)
            if (data.costo_envio && parseFloat(data.costo_envio) > 0) {
                const envio = parseFloat(data.costo_envio);
                granTotalDinero += envio; // <--- AQUÍ ESTABA EL ERROR ReferenceError
                htmlContent += `<div style="display:flex; justify-content:space-between; padding:15px 25px; border-top:1px dashed #eee;"><span>Envío</span><strong>$${envio.toFixed(2)}</strong></div>`;
            }

            let totalLabel = `$${granTotalDinero.toFixed(2)}`;
            if(granTotalPuntos > 0) totalLabel += ` <span style="color:#0984e3;">+ ${granTotalPuntos} pts</span>`;

            htmlContent += `
                <div class="rec-grand-total">
                    <span class="rgt-label">TOTAL PAGADO</span>
                    <span class="rgt-value">${totalLabel}</span>
                </div>`;

            container.innerHTML = htmlContent;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `<div style="color:red; text-align:center; padding:20px;">Error: ${err.message}</div>`;
        });
    }

    function cerrarReciboLateral() {
        document.getElementById('sideReceiptPanel').classList.remove('active');
    }

    function descargarPDFLateral() {
        const element = document.getElementById('receiptContentToPrint');
        const btn = document.querySelector('.btn-download-full');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...';
        btn.disabled = true;

        const opt = {
            margin: [10, 10, 10, 10],
            filename: `Recibo_TuLook360_${new Date().getTime()}.pdf`,
            image: { type: 'jpeg', quality: 0.99 },
            html2canvas: { scale: 2, scrollY: 0 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(err => {
            mostrarModal('Error PDF', 'No se pudo generar el archivo. Intenta de nuevo.', 'error');
             btn.innerHTML = originalText;
             btn.disabled = false;
        });
    }


    // --- SISTEMA DE MODALES GENÉRICOS (NO ALERTS) ---
    const modalGen = document.getElementById('genericModal');
    const gmTitle = document.getElementById('gmTitle');
    const gmDesc = document.getElementById('gmDesc');
    const gmIcon = document.getElementById('gmIcon');
    const gmActions = document.getElementById('gmActions');

    function mostrarModal(titulo, mensaje, tipo, accionConfirmar = null) {
        gmTitle.innerText = titulo;
        gmDesc.innerText = mensaje;
        gmActions.innerHTML = ''; // Limpiar botones

        if (tipo === 'error') {
            gmIcon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:#ff4757;"></i>';
            gmActions.innerHTML = '<button class="btn-gm btn-gm-secondary" onclick="cerrarModalGen()">Cerrar</button>';
        } else if (tipo === 'success') {
            gmIcon.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#2ecc71;"></i>';
            gmActions.innerHTML = '<button class="btn-gm btn-gm-primary" onclick="cerrarModalGen()">Aceptar</button>';
        } else if (tipo === 'confirm') {
            gmIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#f1c40f;"></i>';
            
            const btnCancel = document.createElement('button');
            btnCancel.className = 'btn-gm btn-gm-secondary';
            btnCancel.innerText = 'Cancelar';
            btnCancel.onclick = cerrarModalGen;
            
            const btnOk = document.createElement('button');
            btnOk.className = 'btn-gm btn-gm-danger';
            btnOk.innerText = 'Sí, Confirmar';
            btnOk.onclick = () => { 
                btnOk.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...'; btnOk.disabled = true;
                if(accionConfirmar) accionConfirmar(); 
            };
            
            gmActions.appendChild(btnCancel);
            gmActions.appendChild(btnOk);
        }
        modalGen.classList.add('active');
    }
    function cerrarModalGen() { modalGen.classList.remove('active'); }

    // --- CANCELACIÓN USANDO EL NUEVO MODAL ---
    function confirmarCancelar(idOrden) {
        mostrarModal(
            '¿Cancelar Pedido?',
            'El stock será devuelto a la tienda y la orden se anulará irreversiblemente.',
            'confirm',
            () => ejecutarCancelacionReal(idOrden)
        );
    }

    function ejecutarCancelacionReal(id) {
        fetch('<?= $urlCancelar ?>', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ord_id: id})
        }).then(r => r.json()).then(res => {
            cerrarModalGen();
            if(res.success) {
                mostrarModal('Pedido Cancelado', 'La orden ha sido anulada correctamente.', 'success');
                 setTimeout(() => location.reload(), 1500); // Recargar tras éxito
            } else {
                mostrarModal('Error', res.error || "No se pudo cancelar.", 'error');
            }
        }).catch(() => {
             cerrarModalGen();
             mostrarModal('Error de Conexión', 'Inténtalo más tarde.', 'error');
        });
    }



    let ordenIdParaCalificar = 0;
    // Ahora guardamos objetos más completos { id_negocio, id_sucursal }
    let listaNegociosCalificar = []; 
    let listaProductosCalificar = []; 

    function abrirCalificacion(idOrden) {
        ordenIdParaCalificar = idOrden;
        const panel = document.getElementById('sideRatingPanel');
        const container = document.getElementById('ratingDynamicBody');
        
        panel.classList.add('active');
        container.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fa-solid fa-circle-notch fa-spin fa-3x" style="color:var(--primary-soft)"></i><p style="margin-top:15px; font-weight:600; color:var(--text-muted);">Cargando...</p></div>';

        fetch('<?= $urlFactura ?>&id=' + idOrden)
        .then(r => r.json())
        .then(data => {
            if(!data.success) throw new Error("Error cargando productos");

            let html = `<p style="text-align:center; color:#666; margin-bottom:20px; font-size:0.9rem;">Califica tu experiencia por cada establecimiento.</p>`;
            
            listaNegociosCalificar = [];
            listaProductosCalificar = [];

            // Obtenemos las paradas (sucursales)
            const paradas = Object.values(data.paradas);

            // IMPORTANTE: Ya no necesitamos agrupar manualmente porque 'paradas' YA SON las sucursales únicas.
            // Si hay 2 sucursales del mismo negocio, se califican por separado (lo cual es correcto logísticamente).
            
            paradas.forEach(suc => {
                const negocioId = suc.info.neg_id;
                const sucursalId = suc.suc_id; // <--- ESTE ES EL DATO CLAVE QUE FALTABA
                const logo = suc.info.logo || 'recursos/img/sin_foto.png';
                
                // Guardamos referencia: Negocio + Sucursal
                // Usamos un ID compuesto para el input name para que sea único: rn_NEGID_SUCID
                const uniqueId = `${negocioId}_${sucursalId}`;

                listaNegociosCalificar.push({ 
                    neg_id: negocioId, 
                    suc_id: sucursalId, 
                    unique_id: uniqueId 
                });

                html += `
                    <div class="rate-card">
                        <div class="rate-card-header">
                            <div class="rate-biz-info">
                                <img src="${logo}" class="rate-biz-logo">
                                <div>
                                    <div class="rate-biz-name">${suc.info.neg_nombre}</div>
                                    <div style="font-size:0.75rem; color:#888;">${suc.info.nombre}</div>
                                </div>
                            </div>
                        </div>

                        <div style="padding:15px; text-align:center; background:#fdfdfd; border-bottom:1px solid #eee;">
                            <div style="font-size:0.8rem; font-weight:700; color:#555; margin-bottom:5px;">Califica esta sucursal</div>
                            <div class="stars-centered">
                                <input type="radio" name="rn_${uniqueId}" id="rn_${uniqueId}_5" value="5"><label for="rn_${uniqueId}_5">★</label>
                                <input type="radio" name="rn_${uniqueId}" id="rn_${uniqueId}_4" value="4"><label for="rn_${uniqueId}_4">★</label>
                                <input type="radio" name="rn_${uniqueId}" id="rn_${uniqueId}_3" value="3"><label for="rn_${uniqueId}_3">★</label>
                                <input type="radio" name="rn_${uniqueId}" id="rn_${uniqueId}_2" value="2"><label for="rn_${uniqueId}_2">★</label>
                                <input type="radio" name="rn_${uniqueId}" id="rn_${uniqueId}_1" value="1"><label for="rn_${uniqueId}_1">★</label>
                            </div>
                        </div>

                        <div class="rate-section-title">Productos Recibidos</div>
                `;

                suc.productos.forEach(prod => {
                    // Guardamos referencia del producto + negocio + sucursal
                    listaProductosCalificar.push({ 
                        pro_id: prod.pro_id, 
                        neg_id: negocioId, 
                        suc_id: sucursalId 
                    });
                    
                    const imgProd = prod.imagen || 'recursos/img/sin_foto.png';

                    html += `
                        <div class="rate-prod-row">
                            <div class="rate-prod-left">
                                <img src="${imgProd}" class="rate-prod-img">
                                <div class="rate-prod-name">${prod.pro_nombre}</div>
                            </div>
                            <div class="stars-centered" style="font-size:0.8rem;">
                                <input type="radio" name="rp_${prod.pro_id}" id="rp_${prod.pro_id}_5" value="5"><label for="rp_${prod.pro_id}_5" style="font-size:1.2rem;">★</label>
                                <input type="radio" name="rp_${prod.pro_id}" id="rp_${prod.pro_id}_4" value="4"><label for="rp_${prod.pro_id}_4" style="font-size:1.2rem;">★</label>
                                <input type="radio" name="rp_${prod.pro_id}" id="rp_${prod.pro_id}_3" value="3"><label for="rp_${prod.pro_id}_3" style="font-size:1.2rem;">★</label>
                                <input type="radio" name="rp_${prod.pro_id}" id="rp_${prod.pro_id}_2" value="2"><label for="rp_${prod.pro_id}_2" style="font-size:1.2rem;">★</label>
                                <input type="radio" name="rp_${prod.pro_id}" id="rp_${prod.pro_id}_1" value="1"><label for="rp_${prod.pro_id}_1" style="font-size:1.2rem;">★</label>
                            </div>
                        </div>
                    `;
                });

                html += `</div>`; // Fin Card
            });

            html += `
                <div style="margin-top:20px;">
                    <label style="font-weight:700; font-size:0.9rem;">Comentario General</label>
                    <textarea id="txtComentario" style="width:100%; border:1px solid #ddd; border-radius:10px; padding:15px; margin-top:5px; resize:none; font-family:inherit;" rows="3" placeholder="¿Algo más que quieras contarnos?"></textarea>
                </div>
            `;

            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = "Error al cargar.";
        });
    }

    function enviarCalificacion() {
        // 1. Recolectar Calificaciones de SUCURSALES
        let califNegocios = [];
        let faltaNegocio = false;

        listaNegociosCalificar.forEach(item => {
            // Buscamos el input con el ID único compuesto
            const val = document.querySelector(`input[name="rn_${item.unique_id}"]:checked`)?.value;
            if(val) {
                califNegocios.push({ 
                    id: item.neg_id,       // ID Negocio
                    suc_id: item.suc_id,   // ID Sucursal (IMPORTANTE)
                    rating: val 
                });
            } else {
                faltaNegocio = true;
            }
        });

        if(faltaNegocio) {
            mostrarModal('Falta Información', 'Por favor califica a todos los establecimientos.', 'error');
            return;
        }

        // 2. Recolectar Calificaciones de Productos
        let califProductos = [];
        listaProductosCalificar.forEach(item => {
            const val = document.querySelector(`input[name="rp_${item.pro_id}"]:checked`)?.value || 0;
            califProductos.push({ 
                pro_id: item.pro_id, 
                neg_id: item.neg_id, 
                suc_id: item.suc_id,  // ID Sucursal (IMPORTANTE)
                rating: val 
            });
        });

        const comentario = document.getElementById('txtComentario').value;

        // 3. Enviar
        const btn = document.getElementById('btnEnviarResena');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
        btn.disabled = true;

        fetch('<?= $urlCalificar ?>', { // Usando la variable PHP correcta
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                ord_id: ordenIdParaCalificar,
                comentario: comentario,
                negocios: califNegocios, // Array de tiendas con suc_id
                productos: califProductos // Array de productos con suc_id
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                mostrarModal('¡Gracias!', 'Tus reseñas se han guardado.', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarModal('Error', res.error || 'No se pudo guardar', 'error');
                btn.innerHTML = 'Enviar Reseña';
                btn.disabled = false;
            }
        })
        .catch(() => {
            mostrarModal('Error', 'Error de conexión', 'error');
            btn.innerHTML = 'Enviar Reseña';
            btn.disabled = false;
        });
    }


    function cerrarCalificacion() {
        document.getElementById('sideRatingPanel').classList.remove('active');
    }

</script>