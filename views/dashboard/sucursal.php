<?php 
require_once __DIR__ . '/../../models/PedidoModelo.php';

$db = new Database();
$modeloPed = new PedidoModelo($db->getConnection());
// Validamos que exista la sesión para evitar errores
$sucId = $_SESSION['suc_id'] ?? 0;
$alertasStock = $modeloPed->obtenerAlertasBajoStock($sucId); 
?>

<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --text-dark: #0f172a;
        --danger-color: #ef4444;
        --danger-light: #fef2f2;
        --danger-border: #fecaca;
    }
    
    .panel-wrapper { 
        font-family: 'Poppins', sans-serif; 
        padding: 20px; 
        width: 100%; 
        max-width: 1200px; 
        margin: 0 auto; 
        box-sizing: border-box; 
    }
    
    .header-panel { margin-bottom: 25px; }
    .header-panel h1 { font-family: 'Kalam', cursive; font-size: 2.2rem; color: var(--text-dark); margin: 0; line-height: 1.1; }
    .header-panel h1 span { color: #e67e22; }

    /* ========================================================
       ALERTAS - DISEÑO BLINDADO (NO SE ROMPE EN PC NI MÓVIL)
       ======================================================== */
    .alerta-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--danger-light);
        padding: 15px 20px;
        border-radius: 12px;
        border: 1px solid var(--danger-border);
        margin-bottom: 20px;
    }
    
    .alerta-banner i { color: var(--danger-color); font-size: 1.5rem; }
    .alerta-banner h2 { margin: 0; font-size: 1.1rem; font-weight: 800; color: #991b1b; }

    .alertas-grid {
        display: grid;
        /* MAGIA AQUÍ: Las tarjetas miden mínimo 300px, si hay espacio se ponen lado a lado, si no, bajan solas */
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
        gap: 15px;
    }

    .card-alerta {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px;
        border: 1px solid var(--danger-border);
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.05);
        display: flex;
        flex-direction: row; /* Siempre en fila por defecto */
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        transition: transform 0.2s;
    }

    .card-alerta:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.1);
    }

    .info-alerta {
        display: flex;
        align-items: center;
        gap: 15px;
        overflow: hidden; /* Protege de textos larguísimos */
    }

    .img-alerta {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
        flex-shrink: 0; /* Prohíbe que la foto se aplaste */
    }

    .detalles-alerta { 
        display: flex; 
        flex-direction: column; 
        gap: 4px;
        min-width: 0; /* Obliga al texto a truncarse si no cabe */
    }
    
    .detalles-alerta h4 { 
        margin: 0; 
        font-size: 0.95rem; 
        color: var(--text-dark); 
        font-weight: 700; 
        white-space: nowrap; 
        overflow: hidden; 
        text-overflow: ellipsis; 
    }
    
    .stock-badge { 
        display: inline-block; 
        color: var(--danger-color); 
        font-size: 0.8rem; 
        font-weight: 800; 
    }

    .btn-solicitar {
        flex-shrink: 0; /* Prohíbe que el botón se aplaste */
        background: var(--danger-color);
        color: white;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .btn-solicitar:hover { background: #dc2626; color: white;}

    /* REGLA ESTRICTA PARA CELULARES (Máximo 480px) */
    @media (max-width: 480px) {
        .card-alerta {
            flex-direction: column; /* Se apila solo en celular */
            align-items: stretch;
        }
        .btn-solicitar {
            width: 100%;
            justify-content: center;
            padding: 12px;
        }
    }
</style>

<div class="panel-wrapper">
    <div class="header-panel">
        <h1>Panel <span>Operativo</span></h1>
    </div>

    <?php if (!empty($alertasStock)): ?>
        <div class="alerta-banner">
            <i class="fa-solid fa-triangle-exclamation fa-fade"></i>
            <h2>Atención: Tienes insumos por agotarse</h2>
        </div>
        
        <div class="alertas-grid">
            <?php foreach ($alertasStock as $alerta): ?>
                <div class="card-alerta">
                    <div class="info-alerta">
                        <img src="<?= $alerta['pro_foto'] ?: 'recursos/img/sin_foto.png' ?>" class="img-alerta" alt="Producto">
                        <div class="detalles-alerta">
                            <h4><?= htmlspecialchars($alerta['pro_nombre']) ?></h4>
                            <span class="stock-badge">Quedan: <?= floatval($alerta['ps_stock']) ?> <?= $alerta['pro_unidad'] ?>(s)</span>
                        </div>
                    </div>
                    
                    <a href="<?= ruta_accion('inventario', 'solicitar') ?>" class="btn-solicitar">
                        Reabastecer <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>