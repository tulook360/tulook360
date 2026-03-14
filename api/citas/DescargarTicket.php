<?php
// api/citas/DescargarTicket.php
require_once __DIR__ . '/../../config/database.php';
date_default_timezone_set('America/Guayaquil');

$qr_token = $_GET['qr'] ?? null;

if (!$qr_token) {
    die("<h2 style='color:red; text-align:center; padding:50px;'>Acceso Denegado. Falta el código de seguridad.</h2>");
}

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Buscamos la cita usando el QR como llave de seguridad
    $sql = "SELECT c.cita_id, c.cita_qr_token, n.neg_nombre, suc.suc_nombre, s.serv_nombre, 
                   u.usu_nombres as esp_nombre, u.usu_apellidos as esp_apellido, d.det_precio, d.det_ini
            FROM tbl_cita c
            INNER JOIN tbl_negocio n ON c.neg_id = n.neg_id
            INNER JOIN tbl_sucursal suc ON c.suc_id = suc.suc_id
            INNER JOIN tbl_cita_det d ON c.cita_id = d.cita_id
            INNER JOIN tbl_servicio s ON d.serv_id = s.serv_id
            INNER JOIN tbl_usuario u ON d.usu_id = u.usu_id
            WHERE c.cita_qr_token = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$qr_token]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cita) { 
        die("<h2 style='text-align:center; padding:50px;'>Comprobante no encontrado o código inválido.</h2>"); 
    }

    // Preparamos los datos
    $fecha = date('d/m/Y', strtotime($cita['det_ini']));
    $hora = date('h:i A', strtotime($cita['det_ini']));
    $especialista = $cita['esp_nombre'] . ' ' . $cita['esp_apellido'];
    $precio = number_format($cita['det_precio'], 2);
    $token = $cita['cita_qr_token'];
    $negocio = htmlspecialchars($cita['neg_nombre']);
    $servicio = htmlspecialchars($cita['serv_nombre']);
    $sucursal = htmlspecialchars($cita['suc_nombre']);

} catch (Exception $e) {
    die("Error de sistema: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante <?= $token ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { background: #f8f9fa; font-family: sans-serif; display: flex; flex-direction: column; align-items: center; padding: 20px; }
        .ticket-box { background: white; padding: 30px; border: 1px solid #eee; width: 100%; max-width: 350px; border-radius:10px; }
        .ticket-head { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .logo-text { font-size: 1.8rem; font-weight: 900; color: #333; }
        .logo-text span { color: #e84393; }
        .t-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.95rem; }
        .t-label { color: #888; font-weight: 500; }
        .t-value { color: #333; font-weight: 700; text-align: right; }
        .t-total { border-top: 2px dashed #eee; padding-top: 20px; margin-top: 20px; font-size: 1.4rem; font-weight: 900; display: flex; justify-content: space-between; color: #333; }
        #estado { text-align: center; margin-top: 20px; font-weight: bold; color: #e84393; font-size: 1.1rem; }
        
        /* NUEVO: Estilo para el botón de respaldo */
        .btn-descargar { 
            background: #2d3436; 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%; 
            max-width: 350px; 
            margin-top: 15px; 
            border-radius: 10px; 
            font-size: 1rem; 
            font-weight: bold; 
            cursor: pointer; 
            display: none; /* Inicia oculto hasta que pase el intento automático */
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-descargar:active { transform: scale(0.98); }
    </style>
</head>
<body>

    <div id="printArea" class="ticket-box">
        <div class="ticket-head">
            <div class="logo-text">TuLook<span>360</span></div>
            <div style="font-size:1.1rem; font-weight:800; margin-top:5px;"><?= $negocio ?></div>
            <div style="font-size:0.7rem; color:#888; letter-spacing:2px; font-weight:700; margin-top:5px;">COMPROBANTE DE RESERVA</div>
        </div>
        
        <div class="t-row"><span class="t-label">Fecha:</span><span class="t-value"><?= $fecha ?></span></div>
        <div class="t-row"><span class="t-label">Hora:</span><span class="t-value"><?= $hora ?></span></div>
        <div class="t-row"><span class="t-label">Especialista:</span><span class="t-value"><?= $especialista ?></span></div>
        <div class="t-row"><span class="t-label">Servicio:</span><span class="t-value"><?= $servicio ?></span></div>
        <div class="t-row"><span class="t-label">Sucursal:</span><span class="t-value"><?= $sucursal ?></span></div>
        
        <div class="t-total">
            <span>TOTAL</span>
            <span>$<?= $precio ?></span>
        </div>

        <div style="text-align:center; margin-top:30px;">
            <div id="qrC" style="display:inline-block; padding:10px; background:white; border:1px solid #eee;"></div>
            <div style="font-weight:800; font-family:monospace; margin-top:10px; font-size:1.1rem; letter-spacing:2px;"><?= $token ?></div>
        </div>
    </div>

    <div id="estado">⏳ Generando tu comprobante en PDF...</div>
    
    <button id="btnReDescargar" class="btn-descargar" onclick="descargarPDFManual()">📥 Volver a descargar</button>

    <script>
        // 1. Dibujamos el código QR
        new QRCode(document.getElementById("qrC"), { text: "<?= $token ?>", width: 120, height: 120 });

        // 2. Función que hace el trabajo de descargar
        function ejecutarDescarga() {
            const opt = {
                margin: 10,
                filename: 'Ticket_<?= $token ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
            };

            return html2pdf().set(opt).from(document.getElementById('printArea')).save();
        }

        // 3. Descarga Automática (El primer intento)
        setTimeout(() => {
            ejecutarDescarga().then(() => {
                document.getElementById('estado').innerHTML = "✅ ¡PDF Generado!<br><br><span style='color:#666;font-size:0.85rem'>¿No viste la descarga? Usa el botón de abajo.</span>";
                // Mostramos el botón por si falló o lo cerró por error
                document.getElementById('btnReDescargar').style.display = "block";
            });
        }, 1000);

        // 4. Descarga Manual (Si presionan el botón)
        function descargarPDFManual() {
            const btn = document.getElementById('btnReDescargar');
            btn.innerText = "⏳ Generando...";
            btn.disabled = true;

            ejecutarDescarga().then(() => {
                btn.innerText = "✅ Descargado de nuevo";
                btn.disabled = false;
                
                // Regresamos el texto a su estado normal luego de 3 segundos
                setTimeout(() => {
                    btn.innerText = "📥 Volver a descargar";
                }, 3000);
            });
        }
    </script>
</body>
</html>