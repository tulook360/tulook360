<div class="section-header">
    <div>
        <h1 class="section-title kalam">Gestión de Menús</h1>
        <p class="section-subtitle">Organiza las carpetas del sistema.</p>
    </div>
    
    <div class="header-actions">
        <?php if (tiene_permiso('menu', 'crear')): ?>
            <a href="<?= ruta_accion('menu', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('menu', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-layer-group"></i> Activos
    </a>
    
    <a href="<?= ruta_accion('menu', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="grid-cards">
    
    <?php if (empty($listaMenus)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-folder-open empty-icon"></i>
            <p>No hay menús en la sección <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>

        <?php foreach ($listaMenus as $menu): ?>
            <div class="card-item">
                
                <div class="card-top">
                    <span class="card-id">#<?= str_pad($menu['menu_id'], 3, '0', STR_PAD_LEFT) ?></span>
                    
                    <?php if($menu['menu_estado'] == 'A'): ?>
                        <span class="badge badge-active">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactivo</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="card-title">
                        <i class="fa-solid fa-folder" style="color: <?= $menu['menu_estado'] == 'A' ? 'var(--color-secundario)' : '#b2bec3' ?>;"></i>
                        <?= htmlspecialchars($menu['menu_nombre']) ?>
                    </div>
                    <div class="card-desc">
                        <?= htmlspecialchars($menu['menu_descripcion'] ?: 'Sin descripción') ?>
                    </div>
                </div>

                <div class="card-actions">
                    
                    <?php if ($filtroActual === 'activos'): ?>
                        <?php if (tiene_permiso('menu', 'editar')): ?>
                            <a href="<?= ruta_accion('menu', 'editar', ['id' => $menu['menu_id']]) ?>" 
                               class="btn-icon btn-edit" title="Editar">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (tiene_permiso('menu', 'eliminar')): ?>
                            <?php $urlEliminar = ruta_accion('menu', 'eliminar', ['id' => $menu['menu_id']]); ?>
                            <button class="btn-icon btn-delete" title="Desactivar" 
                                    onclick="preguntar(
                                        '<?= $urlEliminar ?>', 
                                        '¿Desactivar Menú?', 
                                        'Estás a punto de desactivar la carpeta <b><?= htmlspecialchars($menu['menu_nombre']) ?></b>.', 
                                        'Sí, Desactivar',
                                        'danger'
                                    )">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>

                    <?php else: ?>
                        <?php if (tiene_permiso('menu', 'reactivar')): ?>
                            <?php $urlRestaurar = ruta_accion('menu', 'reactivar', ['id' => $menu['menu_id']]); ?>
                            <button class="btn-icon btn-restore" title="Restaurar"
                                    onclick="preguntar(
                                        '<?= $urlRestaurar ?>', 
                                        '¿Restaurar Menú?', 
                                        'El menú <b><?= htmlspecialchars($menu['menu_nombre']) ?></b> volverá a estar activo.', 
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