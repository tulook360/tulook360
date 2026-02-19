<div class="section-header">
    <div>
        <h1 class="section-title kalam">Detalle: <?= htmlspecialchars($rol['rol_nombre']) ?></h1>
        <p class="section-subtitle">Vista de lectura. Estos son los accesos actuales.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('permiso', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Volver</span>
        </a>
        <a href="<?= ruta_accion('permiso', 'gestionar', ['id' => $rol['rol_id']]) ?>" class="btn-create">
            <i class="fa-solid fa-pencil"></i> <span>Editar Permisos</span>
        </a>
    </div>
</div>

<div style="column-count: 1; column-gap: 1.5rem;">
    <style>
        @media(min-width: 900px){ div[style*="column-count"] { column-count: 2 !important; } }
        
        .card-permiso { break-inside: avoid; margin-bottom: 1.5rem; }

        /* FILA PADRE (Lectura) */
        .read-row-padre {
            background-color: #fffbfd; border: 1px solid #fbebf4;
            margin-top: 10px; border-radius: 6px; padding: 8px 10px;
            display: flex; align-items: center;
        }
        
        /* ICONO ESTADO (Check o Candado) */
        .status-icon {
            width: 24px; font-size: 1.1rem; margin-right: 10px; text-align: center;
        }
        
        .label-padre { font-weight: 700; color: var(--color-primario); flex: 1; font-size: 0.95rem; }
        
        /* CONTENEDOR HIJOS */
        .read-hijos-container {
            margin-left: 15px; padding-left: 10px;
            border-left: 2px solid #f0f0f0;
            margin-bottom: 5px;
        }

        /* FILA HIJO (Lectura) */
        .read-row-hijo {
            display: flex; align-items: center; padding: 6px 10px;
            border-radius: 0 6px 6px 0; position: relative;
            border-bottom: 1px dashed #f8f9fa;
        }
        .read-row-hijo:last-child { border-bottom: none; }

        .read-row-hijo::before {
            content: ''; position: absolute; left: -12px; top: 50%;
            width: 10px; height: 2px; background: #f0f0f0;
        }
        
        .label-hijo { font-size: 0.85rem; color: #636e72; flex: 1; }

        /* Badges */
        .permiso-badge {
            font-size: 0.6rem; padding: 2px 5px; border-radius: 4px; font-weight: 700; margin-left: 5px;
        }
        .badge-sis { background: #ffecec; color: #d63031; }
        .badge-amb { background: #f1f2f6; color: #636e72; }
        
        /* Opacidad para items NO permitidos */
        .no-acceso { opacity: 0.5; }
    </style>

    <?php foreach ($accionesAgrupadas as $nombreCarpeta => $listaPadres): ?>
        
        <div class="card-item card-permiso">
            <div class="card-top" style="border-bottom: 1px solid #f1f2f6; padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <div class="card-title" style="margin:0; color: var(--color-primario);">
                    <i class="fa-regular fa-folder-open"></i> <?= htmlspecialchars($nombreCarpeta) ?>
                </div>
            </div>
            
            <div class="card-body" style="margin:0;">
                
                <?php foreach ($listaPadres as $padre): ?>
                    <?php 
                        $tieneP = in_array($padre['acc_id'], $permisosActuales); 
                        $claseOpacidadP = $tieneP ? '' : 'no-acceso';
                    ?>
                    
                    <div class="read-row-padre <?= $claseOpacidadP ?>">
                        <div class="status-icon">
                            <?php if($tieneP): ?>
                                <i class="fa-solid fa-circle-check" style="color: #00b894;"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-lock" style="color: #dfe6e9;"></i>
                            <?php endif; ?>
                        </div>
                        <span class="label-padre"><?= htmlspecialchars($padre['acc_nombre']) ?></span>
                        <?= badgeZona($padre['acc_zona']) ?>
                    </div>

                    <?php if (!empty($padre['mis_hijos'])): ?>
                        <div class="read-hijos-container">
                            <?php foreach ($padre['mis_hijos'] as $hijo): ?>
                                <?php 
                                    $tieneH = in_array($hijo['acc_id'], $permisosActuales);
                                    $claseOpacidadH = $tieneH ? '' : 'no-acceso';
                                ?>
                                <div class="read-row-hijo <?= $claseOpacidadH ?>">
                                    <div class="status-icon" style="transform: scale(0.8);">
                                        <?php if($tieneH): ?>
                                            <i class="fa-solid fa-check" style="color: #00b894;"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-lock" style="color: #dfe6e9;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span class="label-hijo"><?= htmlspecialchars($hijo['acc_nombre']) ?></span>
                                    <?= badgeZona($hijo['acc_zona']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
function badgeZona($zona) {
    if ($zona === 'SIS') return '<span class="permiso-badge badge-sis">SIS</span>';
    if ($zona === 'AMB') return '<span class="permiso-badge badge-amb">GLOBAL</span>';
    return '';
}
?>