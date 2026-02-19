<div class="section-header">
    <div>
        <h1 class="section-title kalam">Nuevo Colaborador</h1>
        <p class="section-subtitle">Incorpora talento a tu equipo en pocos pasos.</p>
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

    <form action="<?= ruta_accion('usuario', 'guardar_empleado') ?>" method="POST" autocomplete="off" id="formWizard" class="form-content-box">
        
        <input type="hidden" name="rol_id" id="inputRolId">
        <input type="hidden" name="suc_id" id="inputSucursalId">

        <div class="step-panel active-panel" id="step1">
            <div class="panel-header">
                <h3>¿Qué cargo ocupará?</h3>
                <p>Define los permisos y funciones dentro del sistema.</p>
            </div>
            
            <div class="role-grid">
                <?php foreach($listaRoles as $rol): ?>
                    <?php 
                        // Lógica visual de iconos
                        $icono = 'fa-user';
                        $desc = 'Acceso estándar.';
                        $nombreLower = strtolower($rol['rol_nombre']);
                        
                        if(strpos($nombreLower, 'especialista') !== false || strpos($nombreLower, 'barbero') !== false) { 
                            $icono = 'fa-scissors'; $desc = 'Realiza servicios y gana comisiones.';
                        } elseif(strpos($nombreLower, 'recepcionista') !== false) {
                            $icono = 'fa-headset'; $desc = 'Gestiona citas y caja.';
                        } elseif(strpos($nombreLower, 'admin') !== false) {
                            $icono = 'fa-user-tie'; $desc = 'Gestiona personal e inventario.';
                        }
                    ?>
                    <?php if($rol['rol_id'] != 1): ?>
                        <div class="role-card" onclick="seleccionarRol(this, <?= $rol['rol_id'] ?>, '<?= $nombreLower ?>')">
                            <div class="role-icon-bg"><i class="fa-solid <?= $icono ?>"></i></div>
                            <div class="role-info">
                                <span class="role-name"><?= htmlspecialchars($rol['rol_nombre']) ?></span>
                                <span class="role-desc"><?= $desc ?></span>
                            </div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step-panel" id="step2">
            <div class="panel-header">
                <h3>Información Personal</h3>
                <p>Datos básicos para crear su cuenta.</p>
            </div>

            <div class="form-grid-2">
                
                <div class="form-group">
                    <label class="form-label">Nombres <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="nombres" id="txtNombres" class="form-control" placeholder="Ej: Juan Carlos" onkeyup="validarPaso2()">
                        <i class="fa-solid fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Apellidos <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="apellidos" id="txtApellidos" class="form-control" placeholder="Ej: Pérez López" onkeyup="validarPaso2()">
                        <i class="fa-regular fa-user input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Cédula / DNI <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="cedula" id="txtCedula" class="form-control" maxlength="10" placeholder="10 dígitos" onkeyup="validarCedulaEnVivo(this)">
                        <i class="fa-solid fa-id-card input-icon"></i>
                    </div>
                    <small id="msgCedula" class="validation-msg"></small>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico <span class="req">*</span></label>
                    <div class="input-wrapper" id="wrapperCorreo">
                        <input type="email" name="correo" id="txtCorreo" class="form-control" placeholder="usuario@negocio.com" onblur="verificarCorreoEnVivo()" onkeyup="validarPaso2()">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <i class="fa-solid fa-circle-notch fa-spin input-icon-right" id="loaderCorreo" style="display:none; color:var(--primary);"></i>
                    </div>
                    <small id="msgCorreo" class="validation-msg" style="display:none;"></small>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Contraseña Temporal <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="txtPass" class="form-control" placeholder="Mínimo 8 caracteres" onkeyup="validarPaso2()">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <i class="fa-solid fa-eye-slash toggle-pass" onclick="togglePassword('txtPass', this)"></i>
                    </div>
                </div>

            </div>
        </div>

        <div class="step-panel" id="step3">
            <div class="panel-header">
                <h3>Asignación de Sede</h3>
                <p>¿En qué sucursal trabajará principalmente?</p>
            </div>

            <div class="category-grid">
                <?php foreach($listaSucursales as $suc): ?>
                    <div class="cat-card suc-card" onclick="seleccionarSucursal(this, <?= $suc['suc_id'] ?>)">
                        <div class="cat-icon-bg"><i class="fa-solid fa-store"></i></div>
                        <span class="cat-name"><?= htmlspecialchars($suc['suc_nombre']) ?></span>
                        <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div id="msgNoSucursal" class="info-box" style="display:none; text-align:center; margin-top:20px;">
                <i class="fa-solid fa-globe"></i> Este rol tiene acceso global, no requiere sucursal específica.
            </div>
        </div>

        <div class="step-panel" id="step4">
                <div class="panel-header">
                    <h3>Acuerdo Profesional</h3>
                    <p>Define sueldo, comisiones y habilidades.</p>
                </div>

                <div class="price-hero">
                    <label>Sueldo Base Mensual</label>
                    <div class="price-input-wrapper">
                        <span class="currency-symbol">$</span>
                        <input type="number" step="0.01" name="sueldo" id="txtSueldo" placeholder="0.00" oninput="validarPaso4()">
                    </div>
                </div>

                <div class="row-inputs especialista-only" style="display:none; margin-top:30px;">
                    
                    <div class="input-group-modern">
                        <label>Comisión por Servicio (%)</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.01" name="comision" class="form-control" placeholder="Ej: 40">
                            <i class="fa-solid fa-percent input-icon"></i>
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label>Nivel Técnico</label>
                        <div class="input-wrapper">
                            <select name="nivel" class="form-control">
                                <option value="Junior">Junior (Aprendiz)</option>
                                <option value="Senior">Senior (Experto)</option>
                                <option value="Master">Master (Maestro)</option>
                            </select>
                            <i class="fa-solid fa-medal input-icon"></i>
                        </div>
                    </div>

                </div>

                <div class="especialista-only" style="display:none; margin-top:30px;">
                    <div class="divider-text"><span>Habilidades (Familias de Servicio)</span></div>

                    <div class="skills-grid-large">
                        <?php foreach($listaCategorias as $cat): ?>
                            <label class="skill-pill-large">
                                <input type="checkbox" name="habilidades[]" value="<?= $cat['tser_id'] ?>">
                                <div class="pill-content">
                                    <i class="<?= !empty($cat['tneg_icono']) ? $cat['tneg_icono'] : 'fa-solid fa-star' ?>"></i>
                                    <span><?= htmlspecialchars($cat['tser_nombre']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="bloqueNoEspecialista" class="info-box" style="margin-top:30px;">
                    <i class="fa-solid fa-info-circle"></i> Solo aplica sueldo base para este rol.
                </div>
            </div>

        <div class="step-panel" id="step5">
            <div class="panel-header">
                <h3>Días Laborables</h3>
                <p>Marca los días que <b>TRABAJA</b>. (Basado en la sucursal seleccionada).</p>
            </div>

            <div class="days-grid" id="gridDiasLaborables">
                </div>
            
            <div id="msgSinHorario" class="info-box" style="display:none; margin-top:20px;">
                <i class="fa-solid fa-calendar-xmark"></i> Selecciona una sucursal para ver los días disponibles.
            </div>
        </div>

        <div class="form-footer">
            <button type="button" class="btn-nav prev" id="btnPrev" onclick="cambiarPaso(-1)" disabled>
                <i class="fa-solid fa-arrow-left"></i> Atrás
            </button>
            
            <div class="steps-dots">
                <span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>

            <button type="button" class="btn-nav next" id="btnNext" onclick="cambiarPaso(1)" disabled>
                Siguiente <i class="fa-solid fa-arrow-right"></i>
            </button>
            
            <button type="submit" class="btn-nav finish" id="btnFinish" style="display:none;">
                <i class="fa-solid fa-check"></i> Finalizar Contratación
            </button>
        </div>

    </form>
</div>


<link rel="stylesheet" href="<?= asset('recursos/css/wizard_empleado.css') ?>">

<script>
    // 1. Datos iniciales para el JS externo (MODO CREAR)
    const isEditMode = false;
    
    // horariosSucursales ahora tendrá { '1': [1, 2, 3...], '2': [1, 5, 6...] } (Números de días)
    const horariosSucursales = <?= json_encode($horariosJson ?? []) ?>;
    
    // Usamos un objeto para mapear Número -> Nombre en la interfaz
    const mapaDias = {
        1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 
        5: 'Viernes', 6: 'Sábado', 7: 'Domingo'
    };
    
    // El orden lógico sigue siendo 1 al 7
    const ordenDias = [1, 2, 3, 4, 5, 6, 7]; 
</script>

<script src="<?= asset('recursos/js/wizard_empleado.js') ?>"></script>