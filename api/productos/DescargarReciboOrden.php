<?php
// api/productos/DescargarReciboOrden.php
require_once __DIR__ . '/../../config/database.php';
date_default_timezone_set('America/Guayaquil');

$qr_token = $_GET['qr'] ?? null;

if (!$qr_token) {
    die("<h2 style='color:red; text-align:center; padding:50px;'>Acceso Denegado.</h2>");
}

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // 1. OBTENER CABECERA
    $sqlOrd = "SELECT o.ord_id, o.ord_codigo, o.ord_fecha, o.ord_tipo_entrega, o.ord_direccion_envio,
                      u.usu_nombres, u.usu_apellidos, o.ord_total, o.ord_costo_envio,
                      (SELECT IFNULL(SUM(odet_puntos_canje), 0) FROM tbl_orden_detalle WHERE ord_id = o.ord_id) as total_puntos
               FROM tbl_orden o
               INNER JOIN tbl_usuario u ON o.cli_id = u.usu_id
               WHERE o.ord_token_qr = ?";
               
    $stmt = $pdo->prepare($sqlOrd);
    $stmt->execute([$qr_token]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) { die("<h2 style='text-align:center; padding:50px;'>Comprobante no encontrado.</h2>"); }

    // 2. OBTENER DETALLES
    $sqlDet = "SELECT d.odet_cantidad, d.odet_precio_unitario, d.odet_puntos_canje,
                      p.pro_nombre, n.neg_nombre
               FROM tbl_orden_detalle d
               INNER JOIN tbl_producto p ON d.pro_id = p.pro_id
               INNER JOIN tbl_negocio n ON p.neg_id = n.neg_id
               WHERE d.ord_id = ?";
    $stmtDet = $pdo->prepare($sqlDet);
    $stmtDet->execute([$orden['ord_id']]);
    $productos = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error de sistema: " . $e->getMessage());
}

$fecha = date('d/m/Y h:i A', strtotime($orden['ord_fecha']));
$cliente = $orden['usu_nombres'] . ' ' . $orden['usu_apellidos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Recibo_<?= $orden['ord_codigo'] ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { 
            background: #f1f2f6; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            margin: 0; 
            padding: 10px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
        }

        /* Contenedor principal adaptable */
        .receipt-box { 
            background: white; 
            padding: 25px; 
            width: 100%; 
            max-width: 500px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .receipt-head { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .logo-text { font-size: 1.8rem; font-weight: 900; color: #2d3436; }
        .logo-text span { color: #e84393; }
        .order-badge { background: #f8f9fa; padding: 6px 12px; border-radius: 50px; font-weight: 800; font-size: 0.85rem; color: #636e72; display: inline-block; margin-top: 5px; }
        
        .section-title { font-size: 0.7rem; color: #b2bec3; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 10px 0; }
        
        .info-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; }
        .info-label { color: #636e72; }
        .info-value { color: #2d3436; font-weight: 600; text-align: right; }

        .item-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f6fa; }
        .item-name { font-weight: 600; color: #2d3436; font-size: 0.9rem; margin-bottom: 2px; }
        .item-meta { font-size: 0.75rem; color: #a4b0be; }
        .item-price { font-weight: 700; color: #2d3436; font-size: 0.9rem; text-align: right; }

        .total-box { 
            background: #fff0f5; 
            padding: 15px; 
            border-radius: 10px; 
            margin-top: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .total-label { font-size: 1rem; font-weight: 800; color: #e84393; }
        .total-value { font-size: 1.3rem; font-weight: 900; color: #e84393; text-align: right; }

        /* Botón estilo App */
        .btn-descargar { 
            background: #2d3436; 
            color: white; 
            border: none; 
            padding: 16px; 
            width: 100%; 
            max-width: 500px; 
            margin-top: 15px; 
            border-radius: 12px; 
            font-size: 1rem; 
            font-weight: 800; 
            cursor: pointer; 
            display: none; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            text-transform: uppercase;
        }

        #qrC { margin-top: 20px; display: inline-block; padding: 10px; background: white; border: 1px solid #eee; border-radius: 8px; }
        #estado { margin-top: 15px; font-weight: 600; color: #10ac84; font-size: 0.9rem; text-align: center; }
    </style>
</head>
<body>

    <div id="printArea" class="receipt-box">
        <div class="receipt-head">
            <div class="logo-text">TuLook<span>360</span></div>
            <div class="order-badge">ORDEN: <?= $orden['ord_codigo'] ?></div>
        </div>
        
        <div class="section-title">Información General</div>
        <div class="info-row">
            <span class="info-label">Fecha y Hora</span>
            <span class="info-value"><?= $fecha ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Cliente</span>
            <span class="info-value"><?= $cliente ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Método Entrega</span>
            <span class="info-value"><?= $orden['ord_tipo_entrega'] ?></span>
        </div>

        <div class="section-title">Detalle de Productos</div>
        <?php foreach($productos as $p): ?>
            <div class="item-row">
                <div style="flex: 1;">
                    <div class="item-name"><?= $p['pro_nombre'] ?></div>
                    <div class="item-meta"><?= $p['odet_cantidad'] ?> x <?= $p['neg_nombre'] ?></div>
                </div>
                <div class="item-price">
                    <?php if($p['odet_precio_unitario'] > 0) echo '$'.number_format($p['odet_precio_unitario'] * $p['odet_cantidad'], 2); ?>
                    <?php if($p['odet_puntos_canje'] > 0) echo ($p['odet_precio_unitario'] > 0 ? ' + ' : '').$p['odet_puntos_canje'].' pts'; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if($orden['ord_costo_envio'] > 0): ?>
            <div class="info-row" style="margin-top:10px;"><span class="info-label">Costo Envío:</span><span class="info-value">$<?= number_format($orden['ord_costo_envio'], 2) ?></span></div>
        <?php endif; ?>

        <div class="total-box">
            <span class="total-label">TOTAL PAGADO</span>
            <div class="total-value">
                $<?= number_format($orden['ord_total'], 2) ?>
                <?php if($orden['total_puntos'] > 0): ?>
                    <br><small style="font-size:0.75rem; opacity:0.8;">+ <?= $orden['total_puntos'] ?> pts</small>
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align:center;">
            <div id="qrC"></div>
            <div style="margin-top:10px; font-family:monospace; font-weight:bold; letter-spacing:2px; color:#b2bec3;"><?= $orden['ord_codigo'] ?></div>
        </div>
    </div>

    <div id="estado">⏳ Generando recibo...</div>
    <button id="btnReDescargar" class="btn-descargar" onclick="ejecutarDescarga()">📥 Descargar PDF</button>

    <script>
        // Dibujar QR
        new QRCode(document.getElementById("qrC"), { text: "<?= $orden['ord_codigo'] ?>", width: 130, height: 130 });

        function ejecutarDescarga() {
            const opt = {
                margin: 5,
                filename: 'Recibo_<?= $orden['ord_codigo'] ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 3, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            return html2pdf().set(opt).from(document.getElementById('printArea')).save();
        }

        // Auto-descarga con feedback visual
        setTimeout(() => {
            ejecutarDescarga().then(() => {
                document.getElementById('estado').innerHTML = "✅ ¡Recibo generado con éxito!";
                document.getElementById('btnReDescargar').style.display = "block";
            });
        }, 1200);
    </script>
</body>
</html>