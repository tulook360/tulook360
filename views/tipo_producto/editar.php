<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar Tipo</h1>
        <p class="section-subtitle">Modificando: <b><?= htmlspecialchars($tipo['tpro_nombre']) ?></b></p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('tipoProducto', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('tipoProducto', 'actualizar') ?>" method="POST" autocomplete="off">
            
            <input type="hidden" name="id" value="<?= $tipo['tpro_id'] ?>">

            <div class="form-group form-group-last">
                <label class="form-label">Nombre de la Categoría <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nombre" class="form-control" 
                           value="<?= htmlspecialchars($tipo['tpro_nombre']) ?>" required>
                    <i class="fa-solid fa-box input-icon"></i>
                </div>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('tipoProducto', 'actualizar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-rotate"></i> <span>Guardar Cambios</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para editar tipos.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>