<?php 
$pageTitle = "Mis Citas"; 
$urlCalificar = ruta_accion('publico', 'guardar_calificacion_ajax');

// Calculamos estadísticas rápidas
$totalProximas = count($citas_activas) + count($citas_proximas);
$totalPasadas = count($citas_historial);
$urlCancelarCita = ruta_accion('publico', 'cancelar_cita_ajax');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #e84393;
        --bg-light: #f8f9fa;
        --text-dark: #2d3436;
        --text-muted: #636e72;
        --border-color: #dfe6e9;
        --primary-soft: #fff0f6;
        
        /* Estados */
        --st-ok: #00b894;   /* Confirmado */
        --st-pend: #0984e3; /* Reservado */
        --st-proc: #6c5ce7; /* En Atención */
        --st-fin: #2d3436;  /* Finalizado */
        --st-err: #d63031;  /* Perdida */
    }

    .orders-wrapper { max-width: 1200px; margin: 40px auto; padding: 0 25px; font-family: 'Outfit', sans-serif; }

    /* --- ENCABEZADO PREMIUM --- */
    .page-header-main {
        background: white; padding: 30px 40px; border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05); margin-bottom: 40px;
        border: 1px solid rgba(0,0,0,0.03); display: flex;
        justify-content: space-between; align-items: center; gap: 30px;
        position: relative; overflow: hidden;
    }
    .page-header-main::before {
        content: ''; position: absolute; top: -50px; right: -50px;
        width: 200px; height: 200px; background: var(--primary-soft);
        border-radius: 50%; opacity: 0.5; z-index: 0;
    }
    .ph-content { position: relative; z-index: 1; flex: 1; }
    .ph-title { font-size: 2.5rem; line-height: 1; color: var(--text-dark); margin: 0 0 10px 0; font-weight: 800; }
    .ph-title span { color: var(--primary); }
    .ph-desc { font-size: 1rem; color: #666; max-width: 600px; margin: 0; }

    .ph-stats { position: relative; z-index: 1; display: flex; gap: 15px; }
    .stat-box {
        background: #f8f9fa; padding: 15px 25px; border-radius: 15px;
        text-align: center; border: 1px solid #eee; transition: 0.3s;
    }
    .stat-number { display: block; font-size: 1.8rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
    .stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 700; }

    /* --- PESTAÑAS CÁPSULA --- */
    .tabs-modern { 
        display: inline-flex; background: white; padding: 8px; border-radius: 50px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.03);
        margin-bottom: 35px; gap: 5px;
    }
    .tab-m-btn {
        padding: 12px 30px; border: none; background: transparent; color: #888;
        font-weight: 700; border-radius: 40px; cursor: pointer; transition: 0.3s;
        font-size: 0.9rem;
    }
    .tab-m-btn.active { background: var(--text-dark); color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

    .tab-content { display: none; animation: slideUp 0.4s ease; }
    .tab-content.active { display: block; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* --- GRID DE TARJETAS --- */
    .orders-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }

    .order-card-new {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--border-color);
        transition: 0.3s; display: flex; flex-direction: column; position: relative;
    }
    .order-card-new:hover { transform: translateY(-5px); border-color: var(--primary); }

    /* Franja de estado */
    .order-card-new::before { content:''; position:absolute; left:0; top:0; bottom:0; width:6px; }
    .st-CONFIRMADO::before { background: var(--st-ok); }
    .st-RESERVADO::before { background: var(--st-pend); }
    .st-EN_ATENCION::before { background: var(--st-proc); }
    .st-FINALIZADO::before { background: var(--st-fin); }
    .st-PERDIDA::before, .st-NO_ASISTIO::before { background: var(--st-err); }

    .ocn-header { padding: 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--bg-light); }
    .ocn-date { font-size: 0.85rem; font-weight: 700; color: var(--text-dark); }
    .ocn-status-badge { padding: 5px 12px; border-radius: 30px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: var(--bg-light); }

    .ocn-body { padding: 20px; flex: 1; }
    .ocn-title { font-size: 1.1rem; font-weight: 800; margin: 0 0 12px; color: var(--text-dark); }
    
    .ocn-meta { display: flex; align-items: center; gap: 12px; margin-top: 15px; }
    .meta-img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .meta-info h4 { margin: 0; font-size: 0.85rem; font-weight: 700; }
    .meta-info p { margin: 0; font-size: 0.75rem; color: var(--text-muted); }

    .ocn-footer { padding: 15px 20px; background: var(--bg-light); display: flex; gap: 10px; }
    .btn-ocn {
        flex: 1; padding: 10px; border-radius: 12px; font-weight: 700; font-size: 0.85rem;
        display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; border: none; transition: 0.2s;
    }
    .btn-ocn-details { background: white; color: var(--text-dark); border: 1px solid var(--border-color); }
    .btn-ocn-rate { background: #f1c40f; color: white; }

    /* --- SIDE PANEL (DRAWER) --- */
    .side-panel-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 9999; opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .side-panel {
        position: fixed; top: 0; right: 0; height: 100%; width: 100%; max-width: 450px;
        background: white; transform: translateX(100%); transition: 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: flex; flex-direction: column; z-index: 10000;
    }
    .side-panel-overlay.active { opacity: 1; visibility: visible; }
    .side-panel-overlay.active .side-panel { transform: translateX(0); }

    .sp-header { padding: 25px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
    .sp-content { flex: 1; overflow-y: auto; padding: 30px; background: var(--bg-light); }
    
    /* Ticket */
    /* DISEÑO DE TICKET MEJORADO */
    .ticket-box { 
        background: white; 
        padding: 40px; 
        padding-bottom: 60px; /* Agregamos espacio extra al final para que no se corte el total */
        border: 1px solid #eee; 
        width: 100%;
        max-width: 500px; /* Lo hacemos un poquito más ancho */
        margin: 0 auto;
        overflow: visible !important; /* Forzamos a que todo el contenido sea visible */
    }
    .ticket-head { 
        text-align: center; 
        border-bottom: 2px solid var(--text-dark); 
        padding-bottom: 25px; 
        margin-bottom: 25px; 
    }
    .ticket-head .logo-text { font-size: 1.8rem; font-weight: 900; color: var(--text-dark); }
    .ticket-head .logo-text span { color: var(--primary); }
    
    .t-row { 
        display: flex; 
        justify-content: space-between; 
        margin-bottom: 12px; 
        font-size: 0.95rem; 
    }
    .t-label { color: #888; font-weight: 500; }
    .t-value { color: var(--text-dark); font-weight: 700; text-align: right; }
    
    .t-total { 
        border-top: 2px dashed #eee; 
        padding-top: 20px; 
        margin-top: 20px; 
        font-size: 1.4rem; 
        font-weight: 900; 
        display: flex; 
        justify-content: space-between;
        color: var(--text-dark);
    }
    
    /* Stars para el Panel lateral */
    .stars-centered { display: flex; flex-direction: row-reverse; gap: 5px; justify-content: center; margin: 15px 0; }
    .stars-centered input { display: none; }
    .stars-centered label { font-size: 1.8rem; color: #dfe6e9; cursor: pointer; transition: 0.2s; }
    .stars-centered label:hover, .stars-centered label:hover ~ label, .stars-centered input:checked ~ label { color: #f1c40f; }

    @media (max-width: 768px) {
        .page-header-main { flex-direction: column; padding: 25px; text-align: center; }
        .ph-stats { width: 100%; justify-content: center; }
        .side-panel { width: 100%; }
    }


    /* --- ESTILOS DEL MODAL GENÉRICO --- */
.generic-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; visibility: hidden; transition: 0.3s;
}
.generic-modal-overlay.active { opacity: 1; visibility: visible; }
.generic-modal-box {
    background: white; width: 90%; max-width: 400px; border-radius: 25px; padding: 35px;
    text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.2); transform: scale(0.8); transition: 0.3s;
}
.generic-modal-overlay.active .generic-modal-box { transform: scale(1); }
.gm-icon { font-size: 3.5rem; margin-bottom: 15px; }
.gm-title { font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin: 0 0 10px; }
.gm-desc { font-size: 0.95rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 25px; }
.gm-actions { display: flex; gap: 12px; justify-content: center; }
.btn-gm { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; }
.btn-gm-secondary { background: #eee; color: #666; }
.btn-gm-danger { background: #ff4757; color: white; }
.btn-gm-primary { background: var(--primary); color: white; }
</style>

<div class="orders-wrapper">
    
    <div class="page-header-main">
        <div class="ph-content">
            <h1 class="ph-title">Mis <span>Citas</span> 🗓️</h1>
            <p class="ph-desc">Gestiona tus reservas y consulta tu historial de servicios de belleza y bienestar.</p>
        </div>
        <div class="ph-stats">
            <div class="stat-box">
                <span class="stat-number" style="color:var(--st-pend)"><?= $totalProximas ?></span>
                <span class="stat-label">Pendientes</span>
            </div>
            <div class="stat-box">
                <span class="stat-number" style="color:var(--st-fin)"><?= $totalPasadas ?></span>
                <span class="stat-label">Realizadas</span>
            </div>
        </div>
    </div>

    <div class="tabs-modern">
        <button class="tab-m-btn active" onclick="switchTab('agenda', this)">Agenda</button>
        <button class="tab-m-btn" onclick="switchTab('historial', this)">Historial</button>
    </div>

    <div id="view-agenda" class="tab-content active">
        <div class="orders-grid">
            <?php 
            $hay = false;
            foreach([$citas_activas, $citas_proximas] as $g) {
                foreach($g as $c) { $hay=true; renderCitaCard($c, false); }
            }
            if(!$hay) echo '<div class="empty-msg" style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-muted);"><h3>No hay citas programadas.</h3></div>';
            ?>
        </div>
    </div>

    <div id="view-historial" class="tab-content">
        <div class="orders-grid">
            <?php if(empty($citas_historial)): ?>
                <div class="empty-msg" style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text-muted);"><h3>Tu historial está vacío.</h3></div>
            <?php else: ?>
                <?php foreach($citas_historial as $c) {
                    $puede = ($c['det_estado'] == 'FINALIZADO' && $c['ya_calificado'] == 0);
                    renderCitaCard($c, $puede);
                } ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="side-panel-overlay" id="drawerOverlay">
    <div class="side-panel">
        <div class="sp-header">
            <h3 style="margin:0; font-weight:800;">Detalle de la Cita</h3>
            <button style="border:none; background:none; font-size:1.5rem; cursor:pointer;" onclick="closeDrawer()">&times;</button>
        </div>
        <div class="sp-content" id="drawerBody"></div>
        <div class="sp-footer" style="padding:20px; background:white;" id="drawerFooter"></div>
    </div>
</div>

<div class="side-panel-overlay" id="rateOverlay">
    <div class="side-panel">
        <div class="sp-header">
            <h3 style="margin:0; font-weight:800;">Calificar Servicio</h3>
            <button style="border:none; background:none; font-size:1.5rem; cursor:pointer;" onclick="closeRate()">&times;</button>
        </div>
        <div class="sp-content">
            <div style="background:white; padding:25px; border-radius:15px; border:1px solid var(--border-color);">
                <input type="hidden" id="r_id"><input type="hidden" id="r_neg"><input type="hidden" id="r_suc">
                
                <p style="text-align:center; color:var(--text-muted); font-size:0.9rem; margin-bottom:20px;">Tu opinión nos ayuda a mejorar.</p>

                <div style="margin-bottom:20px;">
                    <label style="font-weight:700; font-size:0.85rem; color:var(--text-dark);">1. ¿Qué tal el servicio?</label>
                    <div class="stars-centered"><?= stars_centered('s1') ?></div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-weight:700; font-size:0.85rem; color:var(--text-dark);">2. ¿Y el especialista?</label>
                    <div class="stars-centered"><?= stars_centered('s2') ?></div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-weight:700; font-size:0.85rem; color:var(--text-dark);">3. ¿Las instalaciones?</label>
                    <div class="stars-centered"><?= stars_centered('s3') ?></div>
                </div>

                <label style="font-weight:700; font-size:0.85rem; color:var(--text-dark);">Comentario adicional</label>
                <textarea id="r_msg" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--border-color); margin-top:8px; font-family:inherit;" rows="3" placeholder="Escribe aquí..."></textarea>
                
                <button class="btn-ocn" id="btnSend" onclick="sendRate()" style="background:var(--primary); color:white; width:100%; margin-top:20px; padding:15px;">Enviar Calificación</button>
            </div>
        </div>
    </div>
</div>


<div id="genericModal" class="generic-modal-overlay">
    <div class="generic-modal-box">
        <div id="gmIcon" class="gm-icon"></div>
        <h3 id="gmTitle" class="gm-title"></h3>
        <p id="gmDesc" class="gm-desc"></p>
        <div id="gmActions" class="gm-actions"></div>
    </div>
</div>

<?php
function renderCitaCard($c, $puedeCalificar) {
    $f = new DateTime($c['det_ini']);
    $fecha = $f->format('d/m/Y');
    $hora = $f->format('h:i A');
    $foto = $c['esp_foto'] ?: 'https://ui-avatars.com/api/?background=random&name='.$c['esp_nombre'];
    $json = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
    $st = $c['det_estado'];
?>
    <div class="order-card-new st-<?= $st ?>">
        <div class="ocn-header">
            <span class="ocn-date"><?= $fecha ?></span>
            <span class="ocn-status-badge"><?= ($st == 'RESERVADO' ? 'Pendiente' : str_replace('_',' ',$st)) ?></span>
        </div>
        <div class="ocn-body">
            <h3 class="ocn-title"><?= $c['serv_nombre'] ?></h3>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin-bottom:15px;">
                $<?= number_format($c['det_precio'], 2) ?>
            </div>
            
            <div class="ocn-meta">
                <img src="<?= $foto ?>" class="meta-img">
                <div class="meta-info">
                    <h4><?= $c['esp_nombre'] ?></h4>
                    <p><i class="fa-regular fa-clock"></i> <?= $hora ?></p>
                </div>
            </div>
        </div>
        <div class="ocn-footer">
            <button class="btn-ocn btn-ocn-details" onclick='openDrawer(<?= $json ?>)'>
                <i class="fa-solid fa-receipt"></i> Ver Ticket
            </button>
            
            <?php if(in_array($st, ['RESERVADO', 'CONFIRMADO'])): ?>
                <button class="btn-ocn" style="flex:0; background:white; color:#ff4757; border:1px solid #ff4757; width:45px;" 
                        onclick="confirmarCancelarCita(<?= $c['cita_id'] ?>)" title="Cancelar Cita">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            <?php endif; ?>

            <?php if($puedeCalificar): ?>
                <button class="btn-ocn btn-ocn-rate" onclick='openRate(<?= $json ?>)'>
                    <i class="fa-solid fa-star"></i> Calificar
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php }

function stars_centered($n) {
    $s=''; for($i=5;$i>=1;$i--) $s.='<input type="radio" name="'.$n.'" id="'.$n.'_'.$i.'" value="'.$i.'"><label for="'.$n.'_'.$i.'">★</label>'; return $s;
}
?>

<script>
    function switchTab(id, btn) {
        document.querySelectorAll('.tab-m-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(g => g.classList.remove('active'));
        document.getElementById('view-' + id).classList.add('active');
    }

    // --- DRAWER (DETALLE) ---
    function openDrawer(d) {
        const body = document.getElementById('drawerBody');
        const footer = document.getElementById('drawerFooter');
        const date = new Date(d.det_ini.replace(/-/g, '/')); 
        
        let html = `
            <div id="printArea" class="ticket-box">
                <div class="ticket-head">
                    <div class="logo-text">TuLook<span>360</span></div>
                    <div style="font-size:1.1rem; font-weight:800; margin-top:5px;">${d.neg_nombre}</div>
                    <div style="font-size:0.7rem; color:#888; letter-spacing:2px; font-weight:700; margin-top:5px;">COMPROBANTE DE RESERVA</div>
                </div>
                
                <div class="t-row"><span class="t-label">Fecha:</span><span class="t-value">${date.toLocaleDateString()}</span></div>
                <div class="t-row"><span class="t-label">Hora:</span><span class="t-value">${date.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}</span></div>
                <div class="t-row"><span class="t-label">Especialista:</span><span class="t-value">${d.esp_nombre}</span></div>
                <div class="t-row"><span class="t-label">Servicio:</span><span class="t-value">${d.serv_nombre}</span></div>
                <div class="t-row"><span class="t-label">Sucursal:</span><span class="t-value">${d.suc_nombre}</span></div>
                
                <div class="t-total">
                    <span>TOTAL</span>
                    <span>$${parseFloat(d.det_precio).toFixed(2)}</span>
                </div>
        `;

        if(d.det_estado === 'FINALIZADO') {
            html += `<div style="text-align:center; margin-top:20px; font-family:monospace; font-size:0.7rem; color:#aaa;">ID: ${d.cita_qr_token}</div></div>`;
            footer.innerHTML = `<button class="btn-ocn" id="dlBtn" onclick="downloadT()" style="background:var(--text-dark); color:white; width:100%;">
                    <i class="fa-solid fa-download"></i> Descargar Ticket (PDF)</button>`;
        } else {
            html += `
                <div style="text-align:center; margin-top:30px;">
                    <div id="qrC" style="display:inline-block; padding:10px; background:white; border:1px solid #eee;"></div>
                    <div style="font-weight:800; font-family:monospace; margin-top:10px; font-size:1.1rem; letter-spacing:2px;">${d.cita_qr_token}</div>
                    <p style="font-size:0.7rem; color:var(--text-muted); margin-top:5px;">Presenta este código al llegar al local</p>
                </div>
            </div>`;
            footer.innerHTML = '';
        }

        body.innerHTML = html;
        if(document.getElementById('qrC')) new QRCode(document.getElementById("qrC"), {text:d.cita_qr_token, width:150, height:150});

        document.getElementById('drawerOverlay').classList.add('active');
    }

    function closeDrawer() { document.getElementById('drawerOverlay').classList.remove('active'); }

    // --- CALIFICACIÓN ---
    function openRate(d) {
        document.getElementById('r_id').value = d.cita_id; 
        document.getElementById('r_neg').value = d.neg_id; 
        document.getElementById('r_suc').value = d.suc_id;
        document.querySelectorAll('input[type="radio"]').forEach(r => r.checked=false); 
        document.getElementById('r_msg').value='';
        document.getElementById('rateOverlay').classList.add('active');
    }
    function closeRate() { document.getElementById('rateOverlay').classList.remove('active'); }

    function downloadT() {
        const element = document.getElementById('printArea');
        const btn = document.getElementById('dlBtn'); 
        
        if (!btn) return;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...';
        btn.disabled = true;

        // Configuramos opciones optimizadas
        const opt = {
            margin: [10, 0, 10, 0], // Margen arriba/abajo, pero no a los lados para centrar mejor
            filename: `Ticket_Cita_${new Date().getTime()}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 3, // Alta calidad
                letterRendering: true, 
                useCORS: true,
                scrollY: 0, // SOLUCIÓN: Ignora el scroll actual para evitar cortes
                windowWidth: document.documentElement.offsetWidth // Captura el ancho real
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Generar y Guardar
        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }).catch(err => {
            console.error(err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert("Error al generar el documento");
        });
    }

    function sendRate() {
        const gV=(n)=>document.querySelector(`input[name="${n}"]:checked`)?.value||0;
        const d = {
            cita_id:document.getElementById('r_id').value, 
            neg_id:document.getElementById('r_neg').value, 
            suc_id:document.getElementById('r_suc').value,
            voto_servicio:gV('s1'), voto_especialista:gV('s2'), voto_negocio:gV('s3'), 
            comentario:document.getElementById('r_msg').value
        };
        
        const btn=document.getElementById('btnSend');
        btn.innerHTML='Enviando...'; btn.disabled=true;

        fetch('<?= $urlCalificar ?>', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(d) })
        .then(r=>r.json()).then(res=>{
            if(res.success){ closeRate(); location.reload(); }
            else{ alert('Error al guardar'); btn.innerHTML='Enviar'; btn.disabled=false; }
        });
    }



    const API_CANCELAR_CITA = '<?= $urlCancelarCita ?>';

    function confirmarCancelarCita(id) {
        mostrarModal(
            '¿Cancelar Reserva?',
            'Tu turno será liberado.',
            'confirm',
            () => ejecutarCancelacionCitaReal(id)
        );
    }

    function ejecutarCancelacionCitaReal(id) {
        fetch(API_CANCELAR_CITA, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ cita_id: id })
        })
        .then(r => r.json())
        .then(res => {
            cerrarModalGen();
            if(res.success) {
                mostrarModal('Cancelado', 'La cita ha sido cancelada y los puntos devueltos.', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                mostrarModal('Error', res.error, 'error');
            }
        });
    }

    // Asegúrate de tener estas funciones de apoyo (o usarlas si ya las tienes)
    function mostrarModal(titulo, mensaje, tipo, accionConfirmar = null) {
        document.getElementById('gmTitle').innerText = titulo;
        document.getElementById('gmDesc').innerText = mensaje;
        const actions = document.getElementById('gmActions');
        const icon = document.getElementById('gmIcon');
        actions.innerHTML = '';
        
        if(tipo === 'confirm') {
            icon.innerHTML = '<i class="fa-solid fa-circle-exclamation" style="color:#f1c40f"></i>';
            actions.innerHTML = `
                <button class="btn-gm btn-gm-secondary" onclick="cerrarModalGen()">Atrás</button>
                <button class="btn-gm btn-gm-danger" id="btnConfirmReal">Confirmar</button>`;
            document.getElementById('btnConfirmReal').onclick = accionConfirmar;
        } else if(tipo === 'success') {
            icon.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#00b894"></i>';
            actions.innerHTML = `<button class="btn-gm btn-gm-primary" onclick="cerrarModalGen()">Aceptar</button>`;
        } else if(tipo === 'error') {
            icon.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color:#d63031"></i>';
            actions.innerHTML = `<button class="btn-gm btn-gm-secondary" onclick="cerrarModalGen()">Cerrar</button>`;
        }
        
        document.getElementById('genericModal').classList.add('active');
    }
    function cerrarModalGen() { document.getElementById('genericModal').classList.remove('active'); }
</script>