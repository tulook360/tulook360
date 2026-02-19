<link rel="stylesheet" href="<?= asset('recursos/css/accion.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Registrar Acción</h1>
        <p class="section-subtitle">Define una nueva funcionalidad del sistema.</p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('accion', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('accion', 'guardar') ?>" method="POST" autocomplete="off">
            
            <div class="form-grid">

                <div class="form-group full-width">
                     <label class="form-label">Tipo de Acción</label>
                     <div style="display:flex; gap:20px; margin-bottom:1rem; flex-wrap: wrap;">
                        
                        <label class="radio-card active" id="lblPadre" onclick="cambiarTipo('padre')">
                            <input type="radio" name="tipo_accion" value="padre" checked hidden>
                            <i class="fa-solid fa-window-maximize"></i>
                            <div>
                                <strong>Vista Principal (Padre)</strong>
                                <span>Pantalla nueva (Ej: Listar)</span>
                            </div>
                        </label>
                        
                        <label class="radio-card" id="lblHija" onclick="cambiarTipo('hija')">
                            <input type="radio" name="tipo_accion" value="hija" hidden>
                            <i class="fa-solid fa-gears"></i>
                            <div>
                                <strong>Función Interna (Hija)</strong>
                                <span>Acción dependiente (Ej: Guardar)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Zona de Acceso</label>
                    <input type="hidden" name="zona" id="inputZona" value="NEG">
                    
                    <div class="menu-selection-grid">
                        <div class="menu-option-card active" onclick="selectZona(this, 'NEG')">
                            <div class="menu-opt-icon" style="background:#eef0ff; color:#6c5ce7;"><i class="fa-solid fa-store"></i></div>
                            <div class="menu-opt-info"><span class="title">Negocio (NEG)</span><span class="desc">Para locales</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                        <div class="menu-option-card" onclick="selectZona(this, 'SIS')">
                            <div class="menu-opt-icon" style="background:#ffecec; color:#d63031;"><i class="fa-solid fa-shield-halved"></i></div>
                            <div class="menu-opt-info"><span class="title">Sistema (SIS)</span><span class="desc">Solo Super Admin</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                        <div class="menu-option-card" onclick="selectZona(this, 'AMB')">
                            <div class="menu-opt-icon" style="background:#f1f2f6; color:#636e72;"><i class="fa-solid fa-globe"></i></div>
                            <div class="menu-opt-info"><span class="title">Global (AMB)</span><span class="desc">Para ambos</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre Acción <span class="required">*</span></label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Crear Usuario" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Icono</label>
                    <input type="text" name="icono" class="form-control" value="fa-circle">
                </div>

                <div id="bloquePadre" class="form-group full-width">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
                        <div>
                            <label class="form-label">Controlador <span class="required">*</span></label>
                            <input type="text" name="controlador" id="inputControlador" class="form-control code-url-input" placeholder="usuario">
                        </div>
                        </div>
                </div>

                <div id="bloqueHija" class="form-group full-width" style="display:none;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
                        <div>
                            <label class="form-label">Pertenece a la Vista... <span class="required">*</span></label>
                            <select name="padre_id" class="form-control">
                                <option value="">-- Selecciona la Vista Padre --</option>
                                <?php foreach($listaPadres as $p): ?>
                                    <option value="<?= $p['acc_id'] ?>">
                                        <?= $p['acc_nombre'] ?> (<?= $p['acc_controlador'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-help">El controlador se heredará automáticamente.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Método / Función <span class="required">*</span></label>
                    <input type="text" name="metodo" class="form-control code-url-input" placeholder="guardar_empleado" required>
                </div>

                <div id="bloqueVisibilidad" class="form-group full-width" style="display:none;">
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <label class="form-label" style="margin:0;">¿Mostrar también en el Menú?</label>
                            <small style="color:#b2bec3;">Actívalo si esta acción interna debe tener un link propio.</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="visible_check" id="checkVisible" onchange="toggleMenuSelection()">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="visible" id="inputVisible" value="1">

                <div class="form-group form-group-last full-width" id="bloqueMenu">
                    <label class="form-label">Ubicación en el Menú</label>
                    <input type="hidden" name="menu_id" id="inputMenuId" value="">

                    <div class="menu-selection-grid">
                        <div class="menu-option-card active" onclick="selectMenu(this, '')">
                            <div class="menu-opt-icon suelta"><i class="fa-solid fa-layer-group"></i></div>
                            <div class="menu-opt-info"><span class="title">Suelta</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>

                        <?php foreach ($listaCarpetas as $carpeta): ?>
                            <div class="menu-option-card" onclick="selectMenu(this, '<?= $carpeta['menu_id'] ?>')">
                                <div class="menu-opt-icon carpeta"><i class="fa-regular fa-folder-open"></i></div>
                                <div class="menu-opt-info"><span class="title"><?= htmlspecialchars($carpeta['menu_nombre']) ?></span></div>
                                <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('accion', 'guardar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-save"></i> <span>Guardar Acción</span>
                    </button>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<script>
    function cambiarTipo(tipo) {
        const lblPadre = document.getElementById('lblPadre');
        const lblHija = document.getElementById('lblHija');
        
        const bloquePadre = document.getElementById('bloquePadre');
        const bloqueHija = document.getElementById('bloqueHija');
        const bloqueVisibilidad = document.getElementById('bloqueVisibilidad');
        const bloqueMenu = document.getElementById('bloqueMenu');
        const inputVisible = document.getElementById('inputVisible');
        const checkVisible = document.getElementById('checkVisible');

        lblPadre.classList.toggle('active', tipo === 'padre');
        lblHija.classList.toggle('active', tipo === 'hija');
        
        if(tipo === 'padre') {
            // --- ES PADRE ---
            bloquePadre.style.display = 'block';
            bloqueHija.style.display = 'none';
            
            // Padres siempre visibles en menú
            bloqueVisibilidad.style.display = 'none';
            bloqueMenu.style.display = 'block'; 
            inputVisible.value = '1';
            
            // Validaciones
            document.getElementById('inputControlador').setAttribute('required', 'true');

        } else {
            // --- ES HIJA ---
            bloquePadre.style.display = 'none';
            bloqueHija.style.display = 'block';
            
            // Visibilidad opcional
            bloqueVisibilidad.style.display = 'block';
            checkVisible.checked = false; 
            inputVisible.value = '0';
            
            // Por defecto oculta el menú, se activa con el switch
            bloqueMenu.style.display = 'none'; 

            // Validaciones
            document.getElementById('inputControlador').removeAttribute('required');
        }
    }

    function toggleMenuSelection() {
        const isChecked = document.getElementById('checkVisible').checked;
        const bloqueMenu = document.getElementById('bloqueMenu');
        const inputVisible = document.getElementById('inputVisible');
        
        // Actualizar valor hidden
        inputVisible.value = isChecked ? '1' : '0';

        // Si es hija y activamos switch, mostramos menú
        // (Si fuera padre, toggleMenuSelection no se llama o no afecta porque bloqueMenu siempre visible)
        // Pero por seguridad verificamos el tipo actual si quisieramos ser estrictos
        
        bloqueMenu.style.display = isChecked ? 'block' : 'none';
    }

    function selectMenu(element, id) {
        document.getElementById('inputMenuId').value = id;
        // Buscar solo en este contenedor
        const container = element.parentElement;
        container.querySelectorAll('.menu-option-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }

    function selectZona(element, codigo) {
        document.getElementById('inputZona').value = codigo;
        const container = element.parentElement;
        container.querySelectorAll('.menu-option-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }
    
    // Inicializar
    cambiarTipo('padre');
</script>