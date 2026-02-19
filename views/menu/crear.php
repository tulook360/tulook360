<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nuevo Menú</h1>
        <p class="section-subtitle">Crea una nueva carpeta para organizar acciones.</p>
    </div>
    
    <div class="header-actions">
        <a href="<?= ruta_accion('menu', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('menu', 'guardar') ?>" method="POST" autocomplete="off">
            
            <div class="form-group">
                <label for="nombre" class="form-label">
                    Nombre de la Carpeta <span class="required">*</span>
                </label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       placeholder="Ej: Facturación" required autofocus>
                <small class="form-help">
                    Este será el título que aparecerá en la barra lateral.
                </small>
            </div>

            <div class="form-group form-group-last">
                <label for="descripcion" class="form-label">
                    Descripción (Opcional)
                </label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                          placeholder="Breve detalle de qué contiene esta carpeta..."></textarea>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('menu', 'guardar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-save"></i> 
                        <span>Guardar Carpeta</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para crear menús.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>

</div>