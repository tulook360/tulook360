<div class="section-header">
    <div>
        <h1 class="section-title kalam">Roles de Personal</h1>
        <p class="section-subtitle">Gestiona los perfiles de acceso.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('rol', 'crear')): ?>
            <a href="<?= ruta_accion('rol', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo Rol</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('rol', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-user-group"></i> Activos
    </a>
    <a href="<?= ruta_accion('rol', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="grid-cards">
    
    <?php if (empty($listaRoles)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-user-tag empty-icon"></i>
            <p>No hay roles en la sección <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>

        <?php foreach ($listaRoles as $rol): ?>
            <div class="card-item">
                <div class="card-top">
                    <span class="card-id">#<?= str_pad($rol['rol_id'], 3, '0', STR_PAD_LEFT) ?></span>
                    
                    <?php if($rol['rol_estado'] == 'A'): ?>
                        <span class="badge badge-active">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactivo</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="card-title">
                        <i class="fa-solid fa-user-tag" style="color: var(--color-primario);"></i>
                        <?= htmlspecialchars($rol['rol_nombre']) ?>
                    </div>
                    <div class="card-desc">
                        Acceso personalizado.
                    </div>
                </div>

                <div class="card-actions">
                    <?php if ($filtroActual === 'activos'): ?>
                        <?php if (tiene_permiso('rol', 'editar')): ?>
                            <a href="<?= ruta_accion('rol', 'editar', ['id' => $rol['rol_id']]) ?>" 
                               class="btn-icon btn-edit" title="Editar">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (tiene_permiso('rol', 'eliminar')): ?>
                            <?php $urlEliminar = ruta_accion('rol', 'eliminar', ['id' => $rol['rol_id']]); ?>
                            <button class="btn-icon btn-delete" title="Desactivar" 
                                    onclick="preguntar(
                                        '<?= $urlEliminar ?>', 
                                        '¿Desactivar Rol?', 
                                        'El rol <b><?= htmlspecialchars($rol['rol_nombre']) ?></b> dejará de estar disponible.', 
                                        'Sí, Desactivar',
                                        'danger'
                                    )">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if (tiene_permiso('rol', 'reactivar')): ?>
                            <?php $urlRestaurar = ruta_accion('rol', 'reactivar', ['id' => $rol['rol_id']]); ?>
                            <button class="btn-icon btn-restore" title="Restaurar"
                                    onclick="preguntar(
                                        '<?= $urlRestaurar ?>', 
                                        '¿Restaurar?', 
                                        'El rol volverá a estar activo.', 
                                        'Sí, Restaurar',
                                        'success'
                                    )">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>