<div class="section-header">
    <div>
        <h1 class="section-title kalam">Contrato y Habilidades</h1>
        <p class="section-subtitle">Gestionando perfil de: <b><?= htmlspecialchars($info['usu_nombres'] . ' ' . $info['usu_apellidos']) ?></b></p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('usuario', 'listar_empleados') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Volver</span>
        </a>
    </div>
</div>

<?php $esEspecialista = ($info['rol_nombre'] === 'Especialista'); ?>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('usuario', 'guardar_contrato') ?>" method="POST" autocomplete="off">
            
            <input type="hidden" name="id" value="<?= $info['usu_id'] ?>">

            <div class="form-section-label" style="margin-top:0;">Acuerdo Económico</div>
            <div class="form-grid">
                
                <div class="form-group">
                    <label class="form-label">Sueldo Base Mensual ($)</label>
                    <div class="input-wrapper">
                        <input type="number" step="0.01" name="sueldo" class="form-control" 
                               value="<?= $info['usu_sueldo_base'] ?>" placeholder="0.00">
                        <i class="fa-solid fa-dollar-sign input-icon"></i>
                    </div>
                    <small style="color:#636e72; font-size:0.75rem;">Fijo mensual.</small>
                </div>

                <?php if ($esEspecialista): ?>
                    <div class="form-group">
                        <label class="form-label">Comisión por Servicio (%)</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.01" name="comision" class="form-control" 
                                   value="<?= $info['usu_comision_porcentaje'] ?>" placeholder="Ej: 40">
                            <i class="fa-solid fa-percent input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nivel / Rango</label>
                        <div class="input-wrapper">
                            <select name="nivel" class="form-control">
                                <option value="Junior" <?= $info['usu_nivel'] == 'Junior' ? 'selected' : '' ?>>Junior</option>
                                <option value="Senior" <?= $info['usu_nivel'] == 'Senior' ? 'selected' : '' ?>>Senior</option>
                                <option value="Master" <?= $info['usu_nivel'] == 'Master' ? 'selected' : '' ?>>Master</option>
                            </select>
                            <i class="fa-solid fa-medal input-icon"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-group full-width" style="grid-column: 1/-1;">
                        <div style="background:#f1f2f6; padding:10px; border-radius:8px; font-size:0.85rem; color:#636e72;">
                            <i class="fa-solid fa-info-circle"></i> 
                            El rol <b><?= htmlspecialchars($info['rol_nombre']) ?></b> no aplica para comisiones por servicio ni niveles técnicos.
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <?php if ($esEspecialista): ?>
                <div class="form-section-label">Habilidades y Disponibilidad</div>
                <p style="font-size:0.9rem; color:#636e72; margin-bottom:1.5rem;">
                    Activa los servicios que realiza.
                </p>

                <div class="skills-grid">
                    <?php foreach($listaCategorias as $cat): ?>
                        <?php $activo = in_array($cat['tser_id'], $misHabilidades); ?>
                        <label class="skill-card <?= $activo ? 'active' : '' ?>">
                            <div style="display:flex; align-items:center; justify-content:space-between; width:100%;">
                                <span class="skill-name"><?= htmlspecialchars($cat['tser_nombre']) ?></span>
                                <div class="toggle-switch">
                                    <input type="checkbox" name="habilidades[]" value="<?= $cat['tser_id'] ?>" 
                                           <?= $activo ? 'checked' : '' ?> onchange="toggleSkill(this)">
                                    <span class="slider"></span>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-footer">
                <?php if (tiene_permiso('usuario', 'guardar_contrato')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-handshake"></i> <span>Guardar Acuerdo</span>
                    </button>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<style>
    /* Estilos para la Grid de Habilidades */
    .skills-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 2rem;
    }

    .skill-card {
        background: #fff;
        border: 1px solid #dfe6e9;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .skill-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .skill-card.active { border-color: var(--color-primario); background: #fff0f6; }
    .skill-name { font-weight: 600; color: #2d3436; }

    /* Estilos del Switch Toggle */
    .toggle-switch { position: relative; width: 40px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #b2bec3; transition: .4s; border-radius: 24px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    
    input:checked + .slider { background-color: var(--color-primario); }
    input:checked + .slider:before { transform: translateX(16px); }
</style>

<script>
    // Efecto visual al cambiar el switch
    function toggleSkill(checkbox) {
        const card = checkbox.closest('.skill-card');
        if(checkbox.checked) {
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    }
</script>