<link rel="stylesheet" href="<?= asset('recursos/css/accion.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Acciones del Sistema</h1>
        <p class="section-subtitle">Gestiona los enlaces y botones disponibles.</p>
    </div>
    
    <div class="header-actions">
        <?php if (tiene_permiso('accion', 'crear')): ?>
            <a href="<?= ruta_accion('accion', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nueva Acción</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('accion', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       Activas
    </a>
    <a href="<?= ruta_accion('accion', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       Papelera
    </a>
</div>

<div class="grid-cards">
    
    <?php if (empty($listaAcciones)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-link-slash empty-icon"></i>
            <p>No hay acciones en la sección <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>

        <?php foreach ($listaAcciones as $padre): ?>
            
            <?php 
                // PRE-CALCULAR URLs (Para JS y PHP)
                $padre['url_editar']   = ruta_accion('accion', 'editar', ['id' => $padre['acc_id']]);
                $padre['url_eliminar'] = ruta_accion('accion', 'eliminar', ['id' => $padre['acc_id']]);
                $padre['url_reactivar'] = ruta_accion('accion', 'reactivar', ['id' => $padre['acc_id']]); // <--- ESTA FALTABA
                
                // Hijos
                if (!empty($padre['hijos'])) {
                    foreach ($padre['hijos'] as &$hijo) { 
                        $hijo['url_editar'] = ruta_accion('accion', 'editar', ['id' => $hijo['acc_id']]);
                    }
                }
            ?>

            <div class="card-item card-parent" id="card-<?= $padre['acc_id'] ?>" 
                 onclick="abrirDetalle(this, <?= htmlspecialchars(json_encode($padre)) ?>)">
                
                <div class="parent-header">
                    <div class="parent-icon">
                        <i class="fa-solid <?= htmlspecialchars($padre['acc_icono'] ?? 'fa-cube') ?>"></i>
                    </div>
                    
                    <div style="flex:1; min-width: 0;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <h3 style="margin:0; font-size:1rem; color:var(--color-texto); font-weight:700;">
                                <?= htmlspecialchars($padre['acc_nombre']) ?>
                            </h3>
                            
                            <?php 
                                $zCls = match($padre['acc_zona']) { 'SIS'=>'bg-sis', 'NEG'=>'bg-neg', default=>'bg-amb' };
                                $zTxt = match($padre['acc_zona']) { 'SIS'=>'SISTEMA', 'NEG'=>'NEGOCIO', default=>'GLOBAL' };
                            ?>
                            <span class="badge-mini <?= $zCls ?>"><?= $zTxt ?></span>
                        </div>

                        <div style="font-size:0.8rem; color:#b2bec3; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($padre['ruta_visual']) ?>
                        </div>
                    </div>
                    
                    <i class="fa-solid fa-chevron-down mobile-arrow"></i>
                </div>

                <div class="mobile-details">
                    <div style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
                        
                        <?php if ($filtroActual === 'activos'): ?>
                             <?php if (tiene_permiso('accion', 'editar')): ?>
                                <a href="<?= $padre['url_editar'] ?>" class="btn-icon btn-edit"><i class="fa-solid fa-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (tiene_permiso('accion', 'eliminar')): ?>
                                <button class="btn-icon btn-delete" onclick="event.stopPropagation(); preguntar('<?= $padre['url_eliminar'] ?>', '¿Desactivar?', 'Se ocultará todo.', 'Sí', 'danger')"><i class="fa-solid fa-trash-can"></i></button>
                            <?php endif; ?>

                        <?php else: ?>
                            <?php if (tiene_permiso('accion', 'reactivar')): ?>
                                <button class="btn-icon btn-restore" title="Restaurar"
                                        onclick="event.stopPropagation(); preguntar('<?= $padre['url_reactivar'] ?>', '¿Restaurar?', 'Volverá a estar activa.', 'Sí, Restaurar', 'success')">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>


<div id="sidePanel" class="details-panel">
    <i class="fa-solid fa-xmark panel-close" onclick="cerrarPanel()"></i>
    <div id="panelContent"></div>
</div>


<script>
    let panelAbierto = false;
    const filtroActual = '<?= $filtroActual ?>'; // Pasamos el filtro a JS

    function abrirDetalle(card, datos) {
        const esMovil = window.innerWidth <= 900;

        if (esMovil) {
            if (card.classList.contains('open')) {
                card.classList.remove('open');
            } else {
                document.querySelectorAll('.card-parent.open').forEach(c => c.classList.remove('open'));
                card.classList.add('open');
            }
        } else {
            document.querySelectorAll('.card-parent').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            llenarPanel(datos);
            document.getElementById('sidePanel').classList.add('active');
            panelAbierto = true;
        }
    }

    function cerrarPanel() {
        document.getElementById('sidePanel').classList.remove('active');
        document.querySelectorAll('.card-parent').forEach(c => c.classList.remove('selected'));
        panelAbierto = false;
    }

    function llenarPanel(d) {
        const container = document.getElementById('panelContent');
        
        let hijosHtml = '';
        if (d.hijos && d.hijos.length > 0) {
            d.hijos.forEach(h => {
                hijosHtml += `
                    <div class="panel-child-row">
                        <div>
                            <div style="font-weight:600; font-size:0.9rem;">${h.acc_nombre}</div>
                            <div style="font-size:0.75rem; color:#b2bec3;">func: ${h.acc_metodo}</div>
                        </div>
                        <div>
                            <?php if (tiene_permiso('accion', 'editar')): ?>
                                <a href="${h.url_editar}" class="btn-mini"><i class="fa-solid fa-pencil"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                `;
            });
        } else {
            hijosHtml = '<p style="text-align:center; color:#ccc;">No tiene funciones internas.</p>';
        }

        // Generar Botones Principales (PC)
        let botonesHtml = '';

        if (filtroActual === 'activos') {
            // ESTADO ACTIVO
            <?php if (tiene_permiso('accion', 'editar')): ?>
                botonesHtml += `<a href="${d.url_editar}" class="btn-create" style="width:auto; padding:0.6rem 1.2rem;"><i class="fa-solid fa-pencil"></i> Editar Principal</a>`;
            <?php endif; ?>
            
            <?php if (tiene_permiso('accion', 'eliminar')): ?>
                botonesHtml += ` <button class="btn-icon btn-delete" onclick="preguntar('${d.url_eliminar}', '¿Desactivar?', 'Se ocultará todo.', 'Sí', 'danger')"><i class="fa-solid fa-trash-can"></i></button>`;
            <?php endif; ?>
        
        } else {
            // ESTADO PAPELERA (AQUÍ ESTABA EL PROBLEMA)
            <?php if (tiene_permiso('accion', 'reactivar')): ?>
                botonesHtml += `
                    <button class="btn-icon btn-restore" title="Restaurar"
                            onclick="preguntar('${d.url_reactivar}', '¿Restaurar?', 'Volverá a estar activa.', 'Sí, Restaurar', 'success')">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>`;
            <?php endif; ?>
        }

        container.innerHTML = `
            <div class="panel-header">
                <i class="fa-solid ${d.acc_icono} panel-icon-big"></i>
                <div class="panel-title">${d.acc_nombre}</div>
                <div style="margin-top:5px;"><span class="panel-badge">${d.acc_zona}</span></div>
                <p style="color:#666; margin-top:1rem; font-family:monospace; background:#f9f9f9; padding:5px; border-radius:5px;">
                    ${d.ruta_visual}
                </p>
                
                <div style="margin-top:1.5rem; display:flex; gap:10px; justify-content:center;">
                    ${botonesHtml}
                </div>
            </div>

            <h4 style="color:var(--color-primario); margin-bottom:1rem; border-bottom:2px solid #f0f0f0; padding-bottom:0.5rem;">
                Funciones Internas
            </h4>
            <div class="panel-children">
                ${hijosHtml}
            </div>
        `;
    }
</script>