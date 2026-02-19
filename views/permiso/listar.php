<div class="section-header">
    <div>
        <h1 class="section-title kalam">Permisos por Rol</h1>
        <p class="section-subtitle">Selecciona un rol para configurar su árbol de acceso.</p>
    </div>
    </div>

<div class="grid-cards">

    <?php foreach ($listaRoles as $rol): ?>
        <div class="card-item card-permiso-rol">
            <div class="card-body">
                <div class="rol-icon-wrapper">
                     <i class="fa-solid fa-user-tag icon-role"></i>
                </div>
                <h3 class="rol-title"><?= htmlspecialchars($rol['rol_nombre']) ?></h3>
                <p class="rol-stat">
                    <i class="fa-solid fa-check-double"></i> <b><?= $rol['total_permisos'] ?></b> acciones
                </p>
            </div>

            <div class="card-actions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; border-top: 1px dashed #f1f2f6; padding-top: 1rem;">
                
                <a href="<?= ruta_accion('permiso', 'ver', ['id' => $rol['rol_id']]) ?>" 
                   class="btn-create btn-secondary" style="justify-content: center; width:auto;">
                    <i class="fa-solid fa-eye"></i> Ver
                </a>

                <a href="<?= ruta_accion('permiso', 'gestionar', ['id' => $rol['rol_id']]) ?>" 
                   class="btn-create" style="justify-content: center; width:auto;">
                    <i class="fa-solid fa-pencil"></i> Editar
                </a>

            </div>
        </div>
    <?php endforeach; ?>

</div>