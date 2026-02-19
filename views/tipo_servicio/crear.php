<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nueva Categoría</h1>
        <p class="section-subtitle">Agrupa tus servicios (Ej: Cortes, Manicure).</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('tipoServicio', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('tipoServicio', 'guardar') ?>" method="POST" autocomplete="off">
            
            <div class="form-group form-group-last">
                <label class="form-label">Nombre de la Categoría <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Cabello, Barba, Spa..." required autofocus>
                    <i class="fa-solid fa-tag input-icon"></i>
                </div>
                <small class="form-help">Visible para tus clientes al reservar.</small>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('tipoServicio', 'guardar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-save"></i> <span>Guardar Categoría</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para crear categorías.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>