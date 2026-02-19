<?php $pageTitle = "Cierre de Caja"; ?>

<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
    /* VARIABLES DE DISEÑO */
    :root {
        --dark: #0f172a; --bg: #f8fafc; --card: #ffffff; --text: #334155; --text-light: #64748b;
        
        /* Semáforo */
        --c-efectivo-bg: #dcfce7; --c-efectivo-txt: #166534;
        --c-digital-bg: #e0f2fe; --c-digital-txt: #0369a1;
        --c-mixto-bg: #1e293b;   --c-mixto-txt: #ffffff;
        
        /* Tipos */
        --c-serv-bg: #f3e8ff; --c-serv-txt: #7e22ce;
        --c-prod-bg: #ffedd5; --c-prod-txt: #c2410c;

        /* Acento Principal (Tomado de tu sidebar rosa) */
        --brand-color: #e84393; 
    }

    body { background-color: var(--bg); font-family: 'Outfit', sans-serif; }
    .report-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }

    /* --- TIPOGRAFÍA KALAM --- */
    .font-kalam { font-family: 'Kalam', cursive; letter-spacing: -0.5px; }

    /* HEADER */
    .rep-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; flex-wrap: wrap; gap: 15px; }
    
    .rh-title h1 { 
        margin: 0; color: var(--dark); font-size: 2.8rem; line-height: 1; 
        text-shadow: 2px 2px 0px rgba(0,0,0,0.05); /* Sombra sutil */
    }
    .rh-title p { margin: 5px 0 0 0; color: var(--text-light); font-size: 1rem; font-weight: 500; }

    .date-picker { 
        padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 12px; 
        background: white; font-family: 'Outfit', sans-serif; cursor: pointer; 
        font-weight: 700; color: var(--dark); outline: none; transition: 0.2s;
    }
    .date-picker:focus { border-color: var(--brand-color); }

    /* TOTALES (CARDS) */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 40px; }
    
    .stat-card { 
        background: var(--card); padding: 25px; border-radius: 24px; 
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.06); border: 1px solid white; 
        display: flex; align-items: center; gap: 20px; transition: transform 0.2s; position: relative; overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-5px); }
    
    .sc-icon { 
        width: 65px; height: 65px; border-radius: 18px; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.8rem; flex-shrink: 0; 
    }
    .sc-info h3 { 
        margin: 0; font-size: 0.85rem; color: var(--text-light); 
        font-weight: 700; text-transform: uppercase; letter-spacing: 1px; 
    }
    /* APLICAMOS KALAM AL DINERO */
    .sc-info strong { 
        font-size: 2.2rem; color: var(--dark); display: block; 
        margin-top: 0px; line-height: 1.1; 
    }

    .bg-dark { background: var(--dark); color: white; }
    .bg-green { background: #d1fae5; color: #059669; }
    .bg-blue { background: #dbeafe; color: #2563eb; }

    /* TABLA */
    .table-card { 
        background: var(--card); border-radius: 24px; padding: 30px; 
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06); overflow-x: auto; 
    }
    .table-title { margin: 0 0 25px 0; color: var(--dark); font-size: 1.4rem; font-weight: 800; } /* Título de sección también en Kalam? Opcional */

    table { width: 100%; border-collapse: collapse; min-width: 800px; }
    th { text-align: left; padding: 15px; color: var(--text-light); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; letter-spacing: 0.5px; }
    td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; color: var(--text); font-size: 0.95rem; vertical-align: top; }
    tr:last-child td { border-bottom: none; }
    
    /* BADGES */
    .badge { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; }
    .b-serv { background: var(--c-serv-bg); color: var(--c-serv-txt); }
    .b-prod { background: var(--c-prod-bg); color: var(--c-prod-txt); }
    .b-efec { background: var(--c-efectivo-bg); color: var(--c-efectivo-txt); }
    .b-digi { background: var(--c-digital-bg); color: var(--c-digital-txt); }
    .b-mix  { background: var(--c-mixto-bg); color: var(--c-mixto-txt); }

    .ref-text { font-size: 0.8rem; color: var(--text-light); margin-top: 4px; font-family: monospace; display: flex; align-items: center; gap: 5px; }
    .price-col { font-weight: 800; color: var(--dark); font-size: 1.2rem; }
    
    /* LISTA MIXTA */
    .sub-pay-list { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; border-left: 3px solid #e2e8f0; padding-left: 12px; }
    .sub-pay-item { display: flex; justify-content: space-between; font-size: 0.9rem; color: var(--text); align-items: center; }
    .sub-meta { display: flex; flex-direction: column; }
    .sub-ref { font-size: 0.75rem; color: var(--text-light); font-family: monospace; }
    
    .empty-box { text-align: center; padding: 60px 20px; color: var(--text-light); }
</style>

<div class="report-container">
    
    <div class="rep-header">
        <div class="rh-title">
            <h1 class="font-kalam">Cierre de <span style="color: var(--brand-color);">Caja</span></h1>
            <p>Resumen financiero del día.</p>
        </div>
        <form action="index.php" method="GET">
            <input type="hidden" name="c" value="adminsucu">
            <input type="hidden" name="a" value="cierre_caja">
            <?php if(isset($_GET['token'])): ?><input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>"><?php endif; ?>
            <input type="date" name="fecha" class="date-picker" value="<?= $fecha ?>" onchange="this.form.submit()">
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="sc-icon bg-dark"><i class="fa-solid fa-cash-register"></i></div>
            <div class="sc-info">
                <h3>Total Recaudado</h3>
                <strong class="font-kalam">$<?= number_format($totalGeneral, 2) ?></strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="sc-icon bg-green"><i class="fa-solid fa-money-bill-1-wave"></i></div>
            <div class="sc-info">
                <h3>En Efectivo</h3>
                <strong class="font-kalam" style="color: var(--c-efectivo-txt);">$<?= number_format($totalEfectivo, 2) ?></strong>
            </div>
        </div>
        <div class="stat-card">
            <div class="sc-icon bg-blue"><i class="fa-solid fa-building-columns"></i></div>
            <div class="sc-info">
                <h3>Digital / Bancos</h3>
                <strong class="font-kalam" style="color: var(--c-digital-txt);">$<?= number_format($totalDigital, 2) ?></strong>
            </div>
        </div>
    </div>

    <div class="table-card">
        <h3 class="table-title font-kalam">Transacciones</h3>
        
        <?php if(empty($movimientos)): ?>
            <div class="empty-box"><i class="fa-solid fa-file-invoice-dollar fa-3x" style="margin-bottom:15px; opacity:0.5;"></i><p>No hay movimientos registrados para esta fecha.</p></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Concepto</th>
                        <th>Cliente</th>
                        <th>Detalle de Pago</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($movimientos as $m): 
                        $hora = date('H:i', strtotime($m['hora_pago']));
                        $esMixto = ($m['cantidad_pagos'] > 1);
                        
                        // PARSEO ROBUSTO (;; y ::)
                        $listaPagosRaw = explode(';;', $m['desglose_data']);
                        $pagosProcesados = [];
                        
                        foreach($listaPagosRaw as $item) {
                            if(empty(trim($item))) continue;
                            $partes = explode('::', $item);
                            if(count($partes) >= 3) {
                                $pagosProcesados[] = [
                                    'metodo' => $partes[0],
                                    'ref'    => ($partes[1] !== 'Sin Ref') ? $partes[1] : '',
                                    'monto'  => floatval($partes[2])
                                ];
                            }
                        }
                    ?>
                    <tr>
                        <td style="font-family:'Courier New', monospace; color:var(--text-light); font-weight:600;"><?= $hora ?></td>
                        
                        <td>
                            <?php if($m['tipo'] == 'SERVICIO'): ?>
                                <span class="badge b-serv"><i class="fa-solid fa-scissors"></i> Servicio</span>
                            <?php else: ?>
                                <span class="badge b-prod"><i class="fa-solid fa-bag-shopping"></i> Producto</span>
                            <?php endif; ?>
                            <div class="ref-text">Ord: <?= $m['referencia_origen'] ?></div>
                        </td>

                        <td style="font-weight:700;"><?= $m['usu_nombres'] . ' ' . $m['usu_apellidos'] ?></td>

                        <td>
                            <?php if($esMixto): ?>
                                <span class="badge b-mix"><i class="fa-solid fa-layer-group"></i> PAGO DIVIDIDO</span>
                                <div class="sub-pay-list">
                                    <?php foreach($pagosProcesados as $p): ?>
                                        <div class="sub-pay-item">
                                            <div class="sub-meta">
                                                <span style="font-weight:700; color:var(--dark);"><?= $p['metodo'] ?></span>
                                                <?php if(!empty($p['ref'])): ?>
                                                    <span class="sub-ref">#<?= $p['ref'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <strong class="font-kalam" style="font-size:1rem;">$<?= number_format($p['monto'], 2) ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: 
                                // CASO B: ÚNICO
                                $unico = !empty($pagosProcesados) ? $pagosProcesados[0] : ['metodo'=>'Desconocido', 'ref'=>'', 'monto'=>0];
                                $cssBadge = (stripos($unico['metodo'], 'Efectivo') !== false) ? 'b-efec' : 'b-digi';
                            ?>
                                <span class="badge <?= $cssBadge ?>">
                                    <?= $unico['metodo'] ?>
                                </span>
                                <?php if(!empty($unico['ref'])): ?>
                                    <div class="ref-text"><i class="fa-solid fa-receipt"></i> <?= $unico['ref'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <td class="price-col font-kalam">$<?= number_format($m['monto_total'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>