<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar Rol</h1>
        <p class="section-subtitle">Modificando: <b><?= htmlspecialchars($rol['rol_nombre']) ?></b></p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('rol', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('rol', 'actualizar') ?>" method="POST" autocomplete="off">
            
            <input type="hidden" name="id" value="<?= $rol['rol_id'] ?>">

            <div class="form-group form-group-last">
                <label for="nombre" class="form-label">
                    Nombre del Rol <span class="required">*</span>
                </label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($rol['rol_nombre']) ?>" required>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('rol', 'actualizar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-rotate"></i> 
                        <span>Guardar Cambios</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para actualizar roles.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>