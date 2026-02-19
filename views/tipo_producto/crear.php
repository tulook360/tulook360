<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nuevo Tipo de Producto</h1>
        <p class="section-subtitle">Crea una categoría para organizar tu stock.</p>
    </div>
    
    <div class="header-actions">
        <a href="<?= ruta_accion('tipoProducto', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('tipoProducto', 'guardar') ?>" method="POST" autocomplete="off">
            
            <div class="form-group form-group-last">
                <label class="form-label">Nombre de la Categoría <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Shampoos, Ceras, Bebidas..." required autofocus>
                    <i class="fa-solid fa-box input-icon"></i>
                </div>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('tipoProducto', 'guardar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-save"></i> <span>Guardar Categoría</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para crear tipos.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>