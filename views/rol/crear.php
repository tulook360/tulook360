<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nuevo Rol</h1>
        <p class="section-subtitle">Crea un nuevo perfil de trabajo para tu equipo.</p>
    </div>
    
    <div class="header-actions">
        <a href="<?= ruta_accion('rol', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('rol', 'guardar') ?>" method="POST" autocomplete="off">
            
            <div class="form-group form-group-last">
                <label for="nombre" class="form-label">
                    Nombre del Rol <span class="required">*</span>
                </label>
                <input type="text" id="nombre" name="nombre" class="form-control" 
                       placeholder="Ej: Barbero Senior, Recepcionista, Cajero..." required autofocus>
                <small class="form-help">
                    Evita usar nombres genéricos como "Usuario". Sé específico.
                </small>
            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('rol', 'guardar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-save"></i> 
                        <span>Guardar Rol</span>
                    </button>
                <?php else: ?>
                    <div style="background: #ffecec; color: #d63031; padding: 10px 15px; border-radius: 8px; width: 100%; text-align: center; border: 1px solid #fab1a0; font-size: 0.9rem;">
                        <i class="fa-solid fa-lock"></i> No tienes permisos para crear roles.
                    </div>
                <?php endif; ?>
            </div>

        </form>
    </div>

</div>