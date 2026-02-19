<?php 
$pageTitle = "Mi Agenda"; 
$mesesES = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
$mesActual = $mesesES[date('n') - 1];
?>

<style>
    /* VARIABLES */
    :root {
        --primary: #ff3366; --dark: #0f172a; --bg: #f8fafc;
        --blue: #3b82f6; --orange: #f59e0b; --green: #10b981; --gray: #64748b; --red: #ef4444;
    }
    body { background-color: var(--bg); font-family: 'Outfit', sans-serif; }
    .agenda-container { max-width: 900px; margin: 0 auto; padding: 30px 20px 100px 20px; }

    /* TOAST */
    #toast-box { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
    .toast { background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); font-weight: 600; min-width: 280px; display: flex; align-items: center; gap: 12px; animation: slideIn 0.3s; border-left: 5px solid #ccc; }
    .t-success { border-color: var(--green); color: var(--green); }
    .t-error { border-color: var(--red); color: var(--red); }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* MODAL CONFIRMACION */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 5000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
    .modal-card { background: white; width: 90%; max-width: 400px; padding: 30px; border-radius: 20px; text-align: center; transform: translateY(20px); transition: transform 0.3s; }
    .modal-overlay.active { opacity: 1; }
    .modal-overlay.active .modal-card { transform: translateY(0); }
    .modal-icon { font-size: 3rem; margin-bottom: 15px; color: var(--dark); }
    .modal-btns { display: flex; gap: 10px; margin-top: 25px; }
    .btn-modal { flex: 1; padding: 12px; border-radius: 10px; border: none; font-weight: 700; cursor: pointer; }
    .btn-cancel { background: #f1f5f9; color: var(--gray); }
    .btn-ok { background: var(--dark); color: white; }

    /* HEADER & TABS */
    .agenda-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 20px; }
    .ah-title h1 { font-size: 2rem; font-weight: 800; color: var(--dark); margin: 0; line-height: 1.1; }
    .ah-title p { color: var(--gray); margin: 5px 0 0 0; }
    .ah-date { text-align: right; background: white; padding: 10px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .ah-day { font-size: 1.8rem; font-weight: 800; color: var(--primary); line-height: 1; display: block; }
    .ah-month { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--dark); letter-spacing: 1px; }

    .tabs-pills { display: flex; gap: 10px; margin-bottom: 30px; }
    .pill-btn { padding: 10px 25px; border: none; background: transparent; border-radius: 8px; font-weight: 700; color: var(--gray); cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
    .pill-btn.active { background: white; color: var(--dark); box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .pill-badge { background: var(--dark); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; }
    .pill-btn.active .pill-badge { background: var(--primary); }

    .view-content { display: none; animation: fadeUp 0.4s ease; }
    .view-content.active { display: block; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* TIMELINE */
    .timeline-wrapper { position: relative; padding-left: 20px; }
    .timeline-wrapper::before { content: ''; position: absolute; left: 7px; top: 10px; bottom: 10px; width: 2px; background: #cbd5e1; z-index: 0; }
    .timeline-item { position: relative; margin-bottom: 25px; z-index: 1; }
    .tl-dot { position: absolute; left: -18px; top: 20px; width: 14px; height: 14px; border-radius: 50%; background: white; border: 3px solid var(--gray); z-index: 2; box-shadow: 0 0 0 4px var(--bg); }
    
    .st-CONFIRMADO .tl-dot { border-color: var(--blue); } /* Azul: Listo */
    .st-RESERVADO .tl-dot { border-color: var(--gray); background: #eee; } /* Gris: Pendiente Pago */
    .st-EN_ATENCION .tl-dot { border-color: var(--orange); background: var(--orange); }
    .st-FINALIZADO .tl-dot { border-color: var(--green); background: var(--green); }
    .st-FUTURO .tl-dot { border-color: var(--gray); }

    /* TARJETA */
    .tl-card { background: var(--card-bg); border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid white; transition: all 0.3s ease; overflow: hidden; cursor: pointer; }
    .tl-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    .tl-card.open { border-color: var(--primary); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }

    .card-summary { display: grid; grid-template-columns: 80px 1fr auto; padding: 0; }
    .tl-time-col { background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; border-right: 1px solid var(--border); padding: 15px 5px; }
    .time-start { font-weight: 800; color: var(--dark); font-size: 1.1rem; }
    .time-end { font-size: 0.75rem; color: var(--gray); margin-top: 2px; }

    .tl-info-col { padding: 15px 20px; display: flex; flex-direction: column; justify-content: center; }
    .serv-name { font-size: 1.1rem; font-weight: 800; color: var(--dark); margin: 0 0 5px 0; }
    .client-row { display: flex; align-items: center; gap: 10px; }
    .client-av { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; }
    .client-txt { font-size: 0.9rem; color: var(--text-main); font-weight: 500; }

    .tl-arrow-col { padding: 15px; display: flex; align-items: center; justify-content: center; color: #cbd5e1; }
    .chevron-icon { transition: transform 0.3s; }
    .tl-card.open .chevron-icon { transform: rotate(180deg); color: var(--primary); }

    /* DETALLES */
    .card-details { max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out; background: #fff; border-top: 1px dashed #f1f5f9; }
    .tl-card.open .card-details { max-height: 800px; }
    .details-padding { padding: 20px; }

    .recipe-box { background: #f8fafc; border-radius: 12px; padding: 15px; margin-bottom: 20px; border: 1px solid #eee; }
    .recipe-header { font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: block; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    
    .recipe-item { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(0,0,0,0.03); }
    .recipe-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .ri-info { display: flex; align-items: center; gap: 12px; }
    .ri-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid #eee; background: white; }
    .ri-name { font-weight: 600; font-size: 0.9rem; color: var(--dark); }
    .ri-qty { background: white; padding: 5px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; color: var(--dark); border: 1px solid #eee; }

    /* BOTONES ACCIÓN */
    .btn-act { width: 100%; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .btn-start { background: var(--dark); color: white; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2); }
    .btn-start:hover { background: var(--primary); transform: translateY(-2px); }
    
    .btn-end { background: var(--orange); color: white; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2); }
    .btn-end:hover { background: #d97706; transform: translateY(-2px); }
    
    .btn-disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; box-shadow: none; border: 1px solid #eee; }
    .btn-pending { background: #fff7ed; color: #c2410c; border: 1px dashed #fb923c; cursor: not-allowed; } /* Estilo para "Pendiente de Pago" */

    .note-alert { margin-top: 15px; padding: 10px; background: #fffbeb; border-left: 3px solid var(--orange); color: #b45309; font-size: 0.85rem; display: flex; gap: 8px; align-items: flex-start; border-radius: 0 6px 6px 0; }
    .empty-box { text-align: center; padding: 60px 20px; color: #cbd5e1; }

    @media (max-width: 600px) { .card-summary { grid-template-columns: 70px 1fr 30px; } .ah-title h1 { font-size: 1.5rem; } }
</style>

<div id="toast-box"></div>

<div class="modal-overlay" id="confirmModal">
    <div class="modal-card">
        <div class="modal-icon" id="mIcon"><i class="fa-solid fa-circle-question"></i></div>
        <h3 id="mTitle" style="margin:0 0 10px 0; color:var(--dark);">Confirmar Acción</h3>
        <p id="mText" style="color:#64748b; font-size:0.9rem; margin:0;">¿Estás seguro?</p>
        <div class="modal-btns">
            <button class="btn-modal btn-cancel" onclick="closeModal()">Cancelar</button>
            <button class="btn-modal btn-ok" id="mBtnOk">Confirmar</button>
        </div>
    </div>
</div>

<div class="agenda-container">
    <div class="agenda-header">
        <div class="ah-title">
            <h1>Hola, <?= explode(' ', $_SESSION['usuario_nombre'])[0] ?></h1>
            <p>Aquí tienes tu cronograma.</p>
        </div>
        <div class="ah-date">
            <span class="ah-day"><?= date('d') ?></span>
            <span class="ah-month"><?= $mesActual ?></span>
        </div>
    </div>

    <div class="tabs-pills">
        <button class="pill-btn active" onclick="switchView('hoy', this)">HOY <span class="pill-badge"><?= count($citasHoy) ?></span></button>
        <button class="pill-btn" onclick="switchView('futuras', this)">PRÓXIMAS <span class="pill-badge"><?= count($citasFuturas) ?></span></button>
    </div>

    <div id="view-hoy" class="view-content active">
        <?php if(empty($citasHoy)): ?>
            <div class="empty-box"><i class="fa-solid fa-check-circle fa-4x" style="margin-bottom:15px; opacity:0.5;"></i><h3>Todo al día</h3><p>No tienes citas pendientes para hoy.</p></div>
        <?php else: ?>
            <div class="timeline-wrapper">
                <?php foreach($citasHoy as $c): 
                    $horaIni = date('H:i', strtotime($c['det_ini']));
                    $horaFin = date('H:i', strtotime($c['det_fin']));
                    $fotoCli = $c['usu_foto'] ?: "https://ui-avatars.com/api/?name=".$c['usu_nombres'];
                    $estado = $c['det_estado'];
                ?>
                <div class="timeline-item st-<?= $estado ?>" id="card-<?= $c['det_id'] ?>">
                    <div class="tl-dot"></div>
                    <div class="tl-card">
                        <div class="card-summary" onclick="toggleCard(this)">
                            <div class="tl-time-col">
                                <span class="time-start"><?= $horaIni ?></span>
                                <span class="time-end"><?= $horaFin ?></span>
                            </div>
                            <div class="tl-info-col">
                                <h3 class="serv-name"><?= $c['serv_nombre'] ?></h3>
                                <div class="client-row">
                                    <img src="<?= $fotoCli ?>" class="client-av">
                                    <span class="client-txt"><?= $c['usu_nombres'] . ' ' . $c['usu_apellidos'] ?></span>
                                </div>
                            </div>
                            <div class="tl-arrow-col"><i class="fa-solid fa-chevron-down chevron-icon"></i></div>
                        </div>

                        <div class="card-details">
                            <div class="details-padding">
                                <div class="recipe-box">
                                    <span class="recipe-header"><i class="fa-solid fa-list-check"></i> Insumos a preparar</span>
                                    <?php if(empty($c['receta'])): ?>
                                        <div style="text-align:center; color:#94a3b8; font-size:0.85rem; padding:10px;">No requiere insumos específicos.</div>
                                    <?php else: ?>
                                        <?php foreach($c['receta'] as $r): 
                                            $cantidadReal = floatval($r['si_cantidad']) * floatval($r['pro_contenido']);
                                            $cantStr = (floor($cantidadReal) == $cantidadReal) ? number_format($cantidadReal, 0) : number_format($cantidadReal, 2);
                                            $fotoProd = $r['pro_foto'] ?: 'recursos/img/sin_foto.png';
                                        ?>
                                        <div class="recipe-item">
                                            <div class="ri-info"><img src="<?= $fotoProd ?>" class="ri-img"><span class="ri-name"><?= $r['pro_nombre'] ?></span></div>
                                            <div class="ri-qty"><?= $cantStr ?> <?= strtolower($r['pro_unidad_consumo']) ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if($c['cita_notas']): ?>
                                        <div class="note-alert"><i class="fa-solid fa-circle-exclamation" style="margin-top:2px;"></i><span>Nota: <?= $c['cita_notas'] ?></span></div>
                                    <?php endif; ?>
                                </div>

                                <?php if($estado == 'CONFIRMADO'): ?>
                                    <?php
                                    // --- NUEVA LÓGICA DE VERIFICACIÓN DE STOCK ---
                                    $allInStock = true;
                                    if (!empty($c['receta'])) {
                                        foreach ($c['receta'] as $r) {
                                            // Si el stock es 0 o menor, marcamos como falso y salimos del bucle
                                            if ($r['ps_stock'] <= 0) {
                                                $allInStock = false;
                                                break;
                                            }
                                        }
                                    }
                                    ?>

                                    <?php if ($allInStock): ?>
                                        <button class="btn-act btn-start" onclick="askAction(<?= $c['det_id'] ?>, 'start', 'Iniciar Atención', '¿Confirmas que el cliente está listo y tienes los insumos?')">
                                            <i class="fa-solid fa-play"></i> INICIAR ATENCIÓN
                                        </button>
                                    <?php else: ?>
                                        <div class="alert-pill" style="background-color: #fee2e2; color: #991b1b; border: 1px solid #f87171; justify-content: center; font-weight: 700;">
                                            <i class="fa-solid fa-triangle-exclamation"></i> No hay insumos en la sucursal, acérquese al administrador.
                                        </div>
                                    <?php endif; ?>
                                
                                <?php elseif($estado == 'RESERVADO'): ?>
                                    <button class="btn-act btn-pending" disabled>
                                        <i class="fa-solid fa-money-bill-wave"></i> PENDIENTE DE PAGO
                                    </button>
                                    <p style="text-align:center; font-size:0.8rem; color:#94a3b8; margin:5px 0 0 0;">
                                        El cliente debe pasar por caja para confirmar.
                                    </p>

                                <?php elseif($estado == 'EN_ATENCION'): ?>
                                    <button class="btn-act btn-end" onclick="askAction(<?= $c['det_id'] ?>, 'finish', 'Finalizar Servicio', '¿El servicio ha concluido exitosamente?')">
                                        <i class="fa-solid fa-flag-checkered"></i> FINALIZAR SERVICIO
                                    </button>
                                
                                <?php else: ?>
                                    <button class="btn-act btn-disabled" disabled><i class="fa-solid fa-lock"></i> COMPLETADO</button>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="view-futuras" class="view-content">
        <?php if(empty($citasFuturas)): ?>
            <div class="empty-box"><i class="fa-regular fa-calendar fa-4x" style="margin-bottom:15px; opacity:0.5;"></i><h3>Calendario limpio</h3><p>No tienes citas programadas próximamente.</p></div>
        <?php else: ?>
            <div class="timeline-wrapper">
                <?php foreach($citasFuturas as $f): 
                    $fechaObj = new DateTime($f['det_ini']);
                    $diaSemana = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"][$fechaObj->format('w')];
                    $mesTexto = $mesesES[$fechaObj->format('n') - 1];
                    $fechaTexto = $diaSemana . ' ' . $fechaObj->format('d') . ' ' . $mesTexto;
                    $horaTexto = $fechaObj->format('H:i');
                    $fotoCli = $f['usu_foto'] ?: "https://ui-avatars.com/api/?name=".$f['usu_nombres'];
                ?>
                <div class="timeline-item st-FUTURO">
                    <div class="tl-dot"></div>
                    <div class="tl-card" style="opacity:0.85;">
                        <div class="card-summary" onclick="toggleCard(this)">
                            <div class="tl-time-col" style="background:white;">
                                <span class="time-start" style="font-size:0.8rem; text-transform:uppercase; color:var(--primary); text-align:center;"><?= $fechaTexto ?></span>
                                <span class="time-end"><?= $horaTexto ?></span>
                            </div>
                            <div class="tl-info-col">
                                <h3 class="serv-name"><?= $f['serv_nombre'] ?></h3>
                                <div class="client-row"><img src="<?= $fotoCli ?>" class="client-av" style="filter:grayscale(100%);"><span class="client-txt"><?= $f['usu_nombres'] ?></span></div>
                            </div>
                            <div class="tl-arrow-col"><i class="fa-solid fa-chevron-down chevron-icon"></i></div>
                        </div>
                        <div class="card-details">
                            <div class="details-padding">
                                <div class="recipe-box" style="margin-bottom:0;">
                                    <span class="recipe-header" style="color:#64748b;"><i class="fa-solid fa-clipboard-list"></i> Previsión de material</span>
                                    <?php if(empty($f['receta'])): ?>
                                        <div style="text-align:center; color:#94a3b8; font-size:0.85rem;">Sin insumos especiales.</div>
                                    <?php else: ?>
                                        <?php foreach($f['receta'] as $r): 
                                            $cantidadReal = floatval($r['si_cantidad']) * floatval($r['pro_contenido']);
                                            $cantStr = (floor($cantidadReal) == $cantidadReal) ? number_format($cantidadReal, 0) : number_format($cantidadReal, 2);
                                            $fotoProd = $r['pro_foto'] ?: 'recursos/img/sin_foto.png';
                                        ?>
                                        <div class="recipe-item">
                                            <div class="ri-info"><img src="<?= $fotoProd ?>" class="ri-img"><span class="ri-name"><?= $r['pro_nombre'] ?></span></div>
                                            <div class="ri-qty"><?= $cantStr ?> <?= strtolower($r['pro_unidad_consumo']) ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const URL_API = '<?= $urlCambio ?>';

    function toggleCard(header) {
        header.closest('.tl-card').classList.toggle('open');
    }

    function switchView(viewId, btn) {
        document.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.view-content').forEach(c => c.classList.remove('active'));
        document.getElementById('view-' + viewId).classList.add('active');
    }

    // MODAL HANDLER
    let currentAction = null;
    function askAction(id, type, title, text) {
        currentAction = { id: id, type: type };
        document.getElementById('mTitle').innerText = title;
        document.getElementById('mText').innerText = text;
        
        const icon = document.getElementById('mIcon');
        icon.innerHTML = (type === 'start') ? '<i class="fa-solid fa-rocket" style="color:var(--dark)"></i>' : '<i class="fa-solid fa-flag-checkered" style="color:var(--orange)"></i>';
        
        const modal = document.getElementById('confirmModal');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeModal() {
        const modal = document.getElementById('confirmModal');
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    document.getElementById('mBtnOk').addEventListener('click', () => {
        if(currentAction) procesarCita(currentAction.id, currentAction.type);
        closeModal();
    });

    function procesarCita(id, accion) {
        // 1. Definir variables en el scope principal de la función
        const btn = document.querySelector(`#card-${id} button`);
        let originalText = ''; // Usamos let para poder asignarle valor

        if(btn) {
            originalText = btn.innerHTML; // Guardamos el texto original
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...';
            btn.disabled = true;
        }

        fetch(URL_API, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id, accion: accion })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                showToast("Acción completada con éxito", "success");
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(res.error, "error");
                // Restaurar botón
                if(btn) { 
                    btn.innerHTML = originalText; 
                    btn.disabled = false; 
                }
            }
        })
        .catch(err => {
            console.error(err);
            showToast("Error de conexión con el servidor", "error");
            // Restaurar botón (Aquí es donde fallaba antes)
            if(btn) { 
                btn.innerHTML = originalText; 
                btn.disabled = false; 
            }
        });
    }

    function showToast(msg, type) {
        const box = document.getElementById('toast-box');
        const t = document.createElement('div');
        t.className = `toast t-${type}`;
        t.innerHTML = (type === 'success') ? `<i class="fa-solid fa-check-circle"></i> ${msg}` : `<i class="fa-solid fa-circle-exclamation"></i> ${msg}`;
        box.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }
</script>