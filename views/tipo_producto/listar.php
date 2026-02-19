<div class="section-header">
    <div>
        <h1 class="section-title kalam">Tipos de Producto</h1>
        <p class="section-subtitle">Categorías para tu inventario (Ej: Shampoos, Ceras, Bebidas).</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('tipoProducto', 'crear')): ?>
            <a href="<?= ruta_accion('tipoProducto', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo Tipo</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('tipoProducto', 'listar', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-boxes-stacked"></i> Activos
    </a>
    <a href="<?= ruta_accion('tipoProducto', 'listar', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="grid-cards">
    
    <?php if (empty($listaTipos)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-box-open empty-icon"></i>
            <p>No hay tipos de producto en <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>

        <?php foreach ($listaTipos as $tipo): ?>
            <div class="card-item">
                <div class="card-top">
                    <span class="card-id">#<?= str_pad($tipo['tpro_id'], 2, '0', STR_PAD_LEFT) ?></span>
                    
                    <?php if($tipo['tpro_estado'] == 'A'): ?>
                        <span class="badge badge-active">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactivo</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="card-title">
                        <i class="fa-solid fa-tag" style="color: var(--color-primario);"></i>
                        <?= htmlspecialchars($tipo['tpro_nombre']) ?>
                    </div>
                    <div class="card-desc">
                        Categoría de inventario.
                    </div>
                </div>

                <div class="card-actions">
                    <?php if ($filtroActual === 'activos'): ?>
                        
                        <?php if (tiene_permiso('tipoProducto', 'editar')): ?>
                            <a href="<?= ruta_accion('tipoProducto', 'editar', ['id' => $tipo['tpro_id']]) ?>" 
                               class="btn-icon btn-edit" title="Editar">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (tiene_permiso('tipoProducto', 'eliminar')): ?>
                            <button class="btn-icon btn-delete" title="Desactivar" 
                                    onclick="preguntar('<?= ruta_accion('tipoProducto', 'eliminar', ['id' => $tipo['tpro_id']]) ?>', '¿Desactivar?', 'Esta categoría ya no aparecerá al crear productos.', 'Sí, Desactivar', 'danger')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        <?php endif; ?>

                    <?php else: ?>
                        
                        <?php if (tiene_permiso('tipoProducto', 'reactivar')): ?>
                            <button class="btn-icon btn-restore" title="Restaurar"
                                    onclick="preguntar('<?= ruta_accion('tipoProducto', 'reactivar', ['id' => $tipo['tpro_id']]) ?>', '¿Restaurar?', 'La categoría volverá a estar activa.', 'Sí, Restaurar', 'success')">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>