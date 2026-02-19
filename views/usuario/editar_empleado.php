<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar Colaborador</h1>
        <p class="section-subtitle">Actualizando perfil de: <b><?= htmlspecialchars($empleado['usu_nombres']) ?></b></p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('usuario', 'listar_empleados') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-xmark"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="main-wrapper">
    <div class="stepper-container">
        <div class="stepper-track-bg"></div>
        <div class="stepper-track-fill" id="progressBar"></div>
        
        <div class="step-item active" id="stepIndicator1">
            <div class="step-circle"><i class="fa-solid fa-user-tag"></i></div>
            <span class="step-label">Rol</span>
        </div>
        <div class="step-item" id="stepIndicator2">
            <div class="step-circle"><i class="fa-solid fa-id-card"></i></div>
            <span class="step-label">Datos</span>
        </div>
        <div class="step-item" id="stepIndicator3">
            <div class="step-circle"><i class="fa-solid fa-store"></i></div>
            <span class="step-label">Sede</span>
        </div>
        <div class="step-item" id="stepIndicator4">
            <div class="step-circle"><i class="fa-solid fa-briefcase"></i></div>
            <span class="step-label">Perfil</span>
        </div>
        <div class="step-item" id="stepIndicator5">
            <div class="step-circle"><i class="fa-solid fa-calendar-days"></i></div>
            <span class="step-label">Agenda</span>
        </div>
    </div>

    <form action="<?= ruta_accion('usuario', 'actualizar_empleado') ?>" method="POST" autocomplete="off" id="formWizard" class="form-content-box">
        
        <input type="hidden" name="id" value="<?= $empleado['usu_id'] ?>">
        <input type="hidden" name="rol_id" id="inputRolId" value="<?= $empleado['rol_id'] ?>">
        <input type="hidden" name="suc_id" id="inputSucursalId" value="<?= $empleado['suc_id'] ?>">

        <div class="step-panel active-panel" id="step1">
            <div class="panel-header"><h3>Cargo o Rol</h3><p>Modifica los permisos.</p></div>
            <div class="role-grid">
                <?php foreach($listaRoles as $rol): ?>
                    <?php 
                        $icono = 'fa-user'; 
                        $nombreLower = strtolower($rol['rol_nombre']);
                        if(strpos($nombreLower, 'especialista')!==false) $icono = 'fa-scissors';
                        elseif(strpos($nombreLower, 'recepcionista')!==false) $icono = 'fa-headset';
                        elseif(strpos($nombreLower, 'admin')!==false) $icono = 'fa-user-tie';
                        
                        $sel = ($rol['rol_id'] == $empleado['rol_id']) ? 'selected' : '';
                    ?>
                    <?php if($rol['rol_id'] != 1): ?>
                        <div class="role-card <?= $sel ?>" onclick="seleccionarRol(this, <?= $rol['rol_id'] ?>, '<?= $nombreLower ?>')">
                            <div class="role-icon-bg"><i class="fa-solid <?= $icono ?>"></i></div>
                            <div class="role-info"><span class="role-name"><?= htmlspecialchars($rol['rol_nombre']) ?></span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header"><h3>Información Personal</h3><p>Edita los datos básicos.</p></div>
            <div class="form-grid-2">
                <div class="form-group"><label class="form-label">Nombres <span class="req">*</span></label><div class="input-wrapper"><input type="text" name="nombres" id="txtNombres" value="<?= htmlspecialchars($empleado['usu_nombres']) ?>" onkeyup="validarPaso2()"><i class="fa-solid fa-user input-icon"></i></div></div>
                <div class="form-group"><label class="form-label">Apellidos <span class="req">*</span></label><div class="input-wrapper"><input type="text" name="apellidos" id="txtApellidos" value="<?= htmlspecialchars($empleado['usu_apellidos']) ?>" onkeyup="validarPaso2()"><i class="fa-regular fa-user input-icon"></i></div></div>
                <div class="form-group"><label class="form-label">Cédula <span class="req">*</span></label><div class="input-wrapper"><input type="text" name="cedula" id="txtCedula" maxlength="10" value="<?= htmlspecialchars($empleado['usu_cedula']) ?>" onkeyup="validarCedulaEnVivo(this)"><i class="fa-solid fa-id-card input-icon"></i></div><small id="msgCedula" class="validation-msg"></small></div>
                <div class="form-group"><label class="form-label">Correo <span class="req">*</span></label><div class="input-wrapper" id="wrapperCorreo"><input type="email" name="correo" id="txtCorreo" value="<?= htmlspecialchars($empleado['usu_correo']) ?>" onblur="verificarCorreoEnVivo()" onkeyup="validarPaso2()"><i class="fa-solid fa-envelope input-icon"></i></div><small id="msgCorreo" class="validation-msg"></small></div>
                <div class="form-group full-width"><label class="form-label">Contraseña (Opcional)</label><div class="input-wrapper"><input type="password" name="password" id="txtPass" placeholder="Dejar vacío para mantener la actual" onkeyup="validarPaso2()"><i class="fa-solid fa-lock input-icon"></i></div></div>
            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header"><h3>Sede</h3><p>Lugar de trabajo.</p></div>
            <div class="category-grid">
                <?php foreach($listaSucursales as $suc): ?>
                    <?php $sel = ($suc['suc_id'] == $empleado['suc_id']) ? 'selected' : ''; ?>
                    <div class="cat-card suc-card <?= $sel ?>" onclick="seleccionarSucursal(this, <?= $suc['suc_id'] ?>)">
                        <div class="cat-icon-bg"><i class="fa-solid fa-store"></i></div>
                        <span class="cat-name"><?= htmlspecialchars($suc['suc_nombre']) ?></span>
                        <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="msgNoSucursal" class="info-box" style="display:none; margin-top:20px;"><i class="fa-solid fa-globe"></i> Acceso global.</div>
        </div>

        <div class="step-panel" id="step4">
            <div class="panel-header"><h3>Acuerdo Profesional</h3><p>Sueldo y comisiones.</p></div>
            <div class="price-hero"><label>Sueldo Base</label><div class="price-input-wrapper"><span class="currency-symbol">$</span><input type="number" step="0.01" name="sueldo" id="txtSueldo" value="<?= $empleado['usu_sueldo_base'] ?>"></div></div>
            
            <div class="row-inputs especialista-only" style="display:none;">
                <div class="input-group-modern"><label class="form-label">Comisión (%)</label><div class="input-wrapper"><input type="number" step="0.01" name="comision" value="<?= $empleado['usu_comision_porcentaje'] ?>"><i class="fa-solid fa-percent input-icon"></i></div></div>
                <div class="input-group-modern"><label class="form-label">Nivel</label><div class="input-wrapper"><select name="nivel"><option value="Junior" <?= $empleado['usu_nivel'] == 'Junior' ? 'selected':'' ?>>Junior</option><option value="Senior" <?= $empleado['usu_nivel'] == 'Senior' ? 'selected':'' ?>>Senior</option><option value="Master" <?= $empleado['usu_nivel'] == 'Master' ? 'selected':'' ?>>Master</option></select><i class="fa-solid fa-medal input-icon"></i></div></div>
            </div>

            <div class="especialista-only" style="display:none; margin-top:30px;">
                <div class="divider-text"><span>Habilidades</span></div>
                <div class="skills-grid-large">
                    <?php foreach($listaCategorias as $cat): ?>
                        <?php $chk = in_array($cat['tser_id'], $misHabilidades) ? 'checked' : ''; ?>
                        <label class="skill-pill-large">
                            <input type="checkbox" name="habilidades[]" value="<?= $cat['tser_id'] ?>" <?= $chk ?>>
                            <div class="pill-content"><i class="<?= $cat['tneg_icono'] ?? 'fa-solid fa-star' ?>"></i><span><?= htmlspecialchars($cat['tser_nombre']) ?></span></div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="bloqueNoEspecialista" class="info-box" style="margin-top:30px; display:none;"><i class="fa-solid fa-info-circle"></i> Solo sueldo base.</div>
        </div>

        <div class="step-panel" id="step5">
            <div class="panel-header"><h3>Días Laborables</h3><p>Marca días de trabajo.</p></div>
            <div class="days-grid" id="gridDiasLaborables"></div> <div id="msgSinHorario" class="info-box" style="display:none; margin-top:20px;">Sin horarios.</div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-nav prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled><i class="fa-solid fa-arrow-left"></i> Atrás</button>
            <div class="steps-dots"><span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
            <button type="button" class="btn-nav next" id="btnNext" onclick="cambiarPaso(1)">Siguiente <i class="fa-solid fa-arrow-right"></i></button>
            <button type="submit" class="btn-nav finish" id="btnFinish" style="display:none;"><i class="fa-solid fa-check"></i> Guardar Cambios</button>
        </div>
    </form>
</div>

<link rel="stylesheet" href="<?= asset('recursos/css/wizard_empleado.css') ?>">

<script>
    // CONFIGURACIÓN PARA MODO EDICIÓN
    const isEditMode = true; 
    const originalEmail = "<?= $empleado['usu_correo'] ?>"; 

    // OJO: misDiasGuardados viene de la BD. 
    // Si ya migraste la BD a números, esto traerá [1, 5...]. Si no, traerá ["Lunes"...].
    // El JS actualizado maneja ambos casos, pero lo ideal es que sean números.
    const misDiasGuardados = <?= json_encode($misDias ?? []) ?>; 

    // horariosSucursales ahora trae { '1': [1, 2, 3...], ... } gracias al cambio en SucursalModelo
    const horariosSucursales = <?= json_encode($horariosJson ?? []) ?>;

    // Mapa para mostrar nombres en el frontend
    const mapaDias = {
        1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 
        5: 'Viernes', 6: 'Sábado', 7: 'Domingo'
    };

    // Orden lógico numérico
    const ordenDias = [1, 2, 3, 4, 5, 6, 7];
</script>

<script src="<?= asset('recursos/js/wizard_empleado.js') ?>"></script>