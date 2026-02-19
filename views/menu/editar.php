<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar Menú</h1>
        <p class="section-subtitle">Modifica los datos de la carpeta "<?= $menu['menu_nombre'] ?>"</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('menu', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('menu', 'actualizar') ?>" method="POST" autocomplete="off">
            
            <input type="hidden" name="id" value="<?= $menu['menu_id'] ?>">

            <div class="form-group">
                <label for="nombre" class="form-label">
                    Nombre de la Carpeta <span class="required">*</span>
                </label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($menu['menu_nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($menu['menu_descripcion']) ?></textarea>
            </div>

            <div class="form-group form-group-last">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-control">
                    <option value="A" <?= $menu['menu_estado'] == 'A' ? 'selected' : '' ?>>Activo</option>
                    <option value="I" <?= $menu['menu_estado'] == 'I' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('menu', 'actualizar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-save"></i> 
                        <span>Guardar Cambios</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para guardar los cambios.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>