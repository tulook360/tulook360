<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar: <?= htmlspecialchars($rol['rol_nombre']) ?></h1>
        <p class="section-subtitle">Marca las acciones permitidas.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('permiso', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-xmark"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<form action="<?= ruta_accion('permiso', 'guardar') ?>" method="POST" id="formPermisos">
    
    <input type="hidden" name="rol_id" value="<?= $rol['rol_id'] ?>">

    <div class="permisos-grid-container">
        
        <style>
            .permisos-grid-container { column-count: 1; column-gap: 1.5rem; }
            @media(min-width: 900px){ .permisos-grid-container { column-count: 2; } }
            
            .card-permiso { break-inside: avoid; margin-bottom: 1.5rem; }
            
            /* FILA PADRE */
            .permiso-row-padre {
                background-color: #fffbfd; border: 1px solid #fbebf4;
                margin-top: 10px; border-radius: 6px; padding: 8px 10px;
                display: flex; align-items: center;
            }
            .permiso-row-padre:hover { background-color: #fff0f6; }
            .label-padre { font-weight: 700; color: var(--color-primario); flex: 1; font-size: 0.95rem; }

            /* CONTENEDOR HIJOS */
            .hijos-container {
                margin-left: 15px; padding-left: 10px;
                border-left: 2px solid #f0f0f0;
                margin-bottom: 5px;
            }

            /* FILA HIJO */
            .permiso-row-hijo {
                display: flex; align-items: center; padding: 6px 10px;
                border-radius: 0 6px 6px 0; transition: 0.2s;
                position: relative;
            }
            .permiso-row-hijo:hover { background: #f8f9fa; }
            
            /* Línea conectora hijo */
            .permiso-row-hijo::before {
                content: ''; position: absolute; left: -12px; top: 50%;
                width: 10px; height: 2px; background: #f0f0f0;
            }
            
            .label-hijo { font-size: 0.85rem; color: #636e72; flex: 1; }

            .check-input {
                width: 16px; height: 16px; accent-color: var(--color-primario);
                margin-right: 10px; cursor: pointer;
            }

            /* Badges */
            .permiso-badge {
                font-size: 0.6rem; padding: 2px 5px; border-radius: 4px; font-weight: 700; margin-left: 5px;
            }
            .badge-sis { background: #ffecec; color: #d63031; }
            .badge-amb { background: #f1f2f6; color: #636e72; }
        </style>

        <?php foreach ($accionesAgrupadas as $nombreCarpeta => $listaPadres): ?>
            
            <div class="card-item card-permiso">
                
                <div class="card-top" style="border-bottom: 1px solid #f1f2f6; padding-bottom: 0.8rem; margin-bottom: 0.5rem;">
                    <div class="card-title" style="margin:0; color: var(--color-primario); font-size: 1rem;">
                        <i class="fa-regular fa-folder-open"></i> <?= htmlspecialchars($nombreCarpeta) ?>
                    </div>
                    <i class="fa-solid fa-check-double" 
                       style="color:#b2bec3; cursor:pointer; font-size:0.9rem;" 
                       onclick="marcarGrupo(this)" 
                       title="Marcar toda la carpeta"></i>
                </div>

                <div class="card-body" style="margin:0;">
                    
                    <?php foreach ($listaPadres as $padre): ?>
                        
                        <?php 
                            $checkedP = in_array($padre['acc_id'], $permisosActuales) ? 'checked' : ''; 
                            // ID único para control JS
                            $padreDomId = 'padre_' . $padre['acc_id'];
                        ?>
                        <label class="permiso-row-padre">
                            <input type="checkbox" name="acciones[]" 
                                   value="<?= $padre['acc_id'] ?>" 
                                   class="check-input" 
                                   id="<?= $padreDomId ?>"
                                   <?= $checkedP ?> 
                                   onchange="toggleHijos('<?= $padreDomId ?>')">
                            
                            <span class="label-padre"><?= htmlspecialchars($padre['acc_nombre']) ?></span>
                            <?= badgeZona($padre['acc_zona']) ?>
                        </label>

                        <?php if (!empty($padre['mis_hijos'])): ?>
                            <div class="hijos-container" id="container_<?= $padreDomId ?>">
                                <?php foreach ($padre['mis_hijos'] as $hijo): ?>
                                    <?php $checkedH = in_array($hijo['acc_id'], $permisosActuales) ? 'checked' : ''; ?>
                                    
                                    <label class="permiso-row-hijo">
                                        <input type="checkbox" name="acciones[]" 
                                               value="<?= $hijo['acc_id'] ?>" 
                                               class="check-input child-of-<?= $padreDomId ?>" 
                                               <?= $checkedH ?>>
                                        
                                        <span class="label-hijo"><?= htmlspecialchars($hijo['acc_nombre']) ?></span>
                                        <?= badgeZona($hijo['acc_zona']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>

                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <div class="form-footer" style="background: white; padding: 1.5rem; border-radius: 16px; margin-top: 1rem; position: sticky; bottom: 20px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); z-index: 100; display: flex; justify-content: flex-end;">
        <button type="button" class="btn-create btn-save" onclick="confirmarEdicion()">
            <i class="fa-solid fa-save"></i> <span>Guardar Permisos</span>
        </button>
    </div>

</form>

<?php
function badgeZona($zona) {
    if ($zona === 'SIS') return '<span class="permiso-badge badge-sis">SIS</span>';
    if ($zona === 'AMB') return '<span class="permiso-badge badge-amb">GLOB</span>';
    return '';
}
?>

<script>
    function confirmarEdicion() {
        preguntar(
            "javascript:document.getElementById('formPermisos').submit()", 
            "¿Guardar Permisos?", 
            "Estás a punto de actualizar el acceso para el rol <b><?= htmlspecialchars($rol['rol_nombre']) ?></b>.", 
            "Sí, Guardar", 
            "success" 
        );
    }

    function marcarGrupo(icon) {
        const card = icon.closest('.card-permiso');
        const checkboxes = card.querySelectorAll('input[type="checkbox"]');
        const estado = !checkboxes[0].checked;
        checkboxes.forEach(cb => { cb.checked = estado; });
    }

    function toggleHijos(padreId) {
        const padreCheck = document.getElementById(padreId);
        const hijosChecks = document.querySelectorAll('.child-of-' + padreId);
        
        hijosChecks.forEach(h => {
            h.checked = padreCheck.checked;
        });
    }
</script>