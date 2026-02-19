<div class="section-header">
    <div>
        <h1 class="section-title kalam">Categorías de Servicios</h1>
        <p class="section-subtitle">Organiza tu catálogo (Ej: Cortes, Barba, Tintes).</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('tipoServicio', 'crear')): ?>
            <a href="<?= ruta_accion('tipoServicio', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nueva Categoría</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('tipoServicio', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-tags"></i> Activas
    </a>
    <a href="<?= ruta_accion('tipoServicio', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="grid-cards">
    <?php if (empty($listaCategorias)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-tag empty-icon"></i>
            <p>No hay categorías registradas en <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>
        <?php foreach ($listaCategorias as $cat): ?>
            <div class="card-item">
                
                <div class="card-top">
                    <span class="card-id">#<?= str_pad($cat['tser_id'], 3, '0', STR_PAD_LEFT) ?></span>
                    <?php if($cat['tser_estado'] == 'A'): ?>
                        <span class="badge badge-active">Activa</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactiva</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="card-title">
                        <i class="fa-solid fa-layer-group" style="color: var(--color-primario);"></i>
                        <?= htmlspecialchars($cat['tser_nombre']) ?>
                    </div>
                    <div class="card-desc">
                        Categoría del catálogo.
                    </div>
                </div>

                <div class="card-actions">
                    <?php if ($filtroActual === 'activos'): ?>
                        
                        <?php if (tiene_permiso('tipoServicio', 'editar')): ?>
                            <a href="<?= ruta_accion('tipoServicio', 'editar', ['id' => $cat['tser_id']]) ?>" 
                               class="btn-icon btn-edit" title="Editar">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (tiene_permiso('tipoServicio', 'eliminar')): ?>
                            <button class="btn-icon btn-delete" title="Desactivar"
                                    onclick="preguntar('<?= ruta_accion('tipoServicio', 'eliminar', ['id' => $cat['tser_id']]) ?>', '¿Desactivar?', 'La categoría dejará de estar visible en el catálogo.', 'Sí, Desactivar', 'danger')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>

                    <?php else: ?>
                        
                        <?php if (tiene_permiso('tipoServicio', 'reactivar')): ?>
                            <button class="btn-icon btn-restore" title="Restaurar"
                                    onclick="preguntar('<?= ruta_accion('tipoServicio', 'reactivar', ['id' => $cat['tser_id']]) ?>', '¿Restaurar?', 'La categoría volverá a estar activa.', 'Sí, Restaurar', 'success')">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>