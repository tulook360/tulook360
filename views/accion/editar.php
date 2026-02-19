<link rel="stylesheet" href="<?= asset('recursos/css/accion.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Editar Acción</h1>
        <p class="section-subtitle">Modificando: <b><?= htmlspecialchars($accion['acc_nombre']) ?></b></p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('accion', 'listar') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Cancelar</span>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="card-item card-form">
        
        <form action="<?= ruta_accion('accion', 'actualizar') ?>" method="POST" autocomplete="off">
            <input type="hidden" name="id" value="<?= $accion['acc_id'] ?>">

            <div class="form-grid">

                <?php 
                    $esHija = !empty($accion['acc_padre_id']); 
                    $esPadre = !$esHija;
                    // Preparamos el tipo inicial para el JS
                    $tipoJs = $esHija ? 'hija' : 'padre';
                ?>
                <div class="form-group full-width">
                     <label class="form-label">Tipo de Acción</label>
                     <div style="display:flex; gap:20px; margin-bottom:1rem; flex-wrap: wrap;">
                        <label class="radio-card <?= $esPadre ? 'active' : '' ?>" id="lblPadre" onclick="cambiarTipo('padre')">
                            <input type="radio" name="tipo_accion" value="padre" <?= $esPadre ? 'checked' : '' ?> hidden>
                            <i class="fa-solid fa-window-maximize"></i>
                            <div><strong>Vista Principal (Padre)</strong><span>Pantalla nueva</span></div>
                        </label>
                        <label class="radio-card <?= $esHija ? 'active' : '' ?>" id="lblHija" onclick="cambiarTipo('hija')">
                            <input type="radio" name="tipo_accion" value="hija" <?= $esHija ? 'checked' : '' ?> hidden>
                            <i class="fa-solid fa-gears"></i>
                            <div><strong>Función Interna (Hija)</strong><span>Acción dependiente</span></div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nombre Acción <span class="required">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($accion['acc_nombre']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Icono</label>
                    <input type="text" name="icono" class="form-control" value="<?= htmlspecialchars($accion['acc_icono']) ?>">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Nivel de Acceso</label>
                    <input type="hidden" name="zona" id="inputZona" value="<?= $accion['acc_zona'] ?>">
                    <div class="menu-selection-grid">
                        <div class="menu-option-card <?= $accion['acc_zona']=='NEG'?'active':'' ?>" onclick="selectZona(this, 'NEG')">
                            <div class="menu-opt-icon bg-neg"><i class="fa-solid fa-store"></i></div>
                            <div class="menu-opt-info"><span class="title">Negocio</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                        <div class="menu-option-card <?= $accion['acc_zona']=='SIS'?'active':'' ?>" onclick="selectZona(this, 'SIS')">
                            <div class="menu-opt-icon bg-sis"><i class="fa-solid fa-shield-halved"></i></div>
                            <div class="menu-opt-info"><span class="title">Sistema</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                        <div class="menu-option-card <?= $accion['acc_zona']=='AMB'?'active':'' ?>" onclick="selectZona(this, 'AMB')">
                            <div class="menu-opt-icon bg-amb"><i class="fa-solid fa-globe"></i></div>
                            <div class="menu-opt-info"><span class="title">Global</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                    </div>
                </div>

                <div id="bloquePadre" class="form-group full-width" style="display:none;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem;">
                        <div>
                            <label class="form-label">Controlador <span class="required">*</span></label>
                            <input type="text" name="controlador" id="inputControlador" class="form-control code-url-input" value="<?= htmlspecialchars($accion['acc_controlador']) ?>">
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
                                    <option value="<?= $p['acc_id'] ?>" <?= ($accion['acc_padre_id'] == $p['acc_id']) ? 'selected' : '' ?>>
                                        <?= $p['acc_nombre'] ?> (<?= $p['acc_controlador'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Método / Función <span class="required">*</span></label>
                    <input type="text" name="metodo" class="form-control code-url-input" value="<?= htmlspecialchars($accion['acc_metodo']) ?>" required>
                </div>

                <div id="bloqueVisibilidad" class="form-group full-width" style="display:none;">
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <label class="form-label" style="margin:0;">¿Mostrar también en el Menú?</label>
                            <small style="color:#b2bec3;">Actívalo si esta acción interna debe tener un link propio.</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="visible_check" id="checkVisible" <?= ($accion['acc_visible'] == 1) ? 'checked' : '' ?> onchange="toggleMenuSelection()">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="visible" id="inputVisible" value="<?= $accion['acc_visible'] ?>">

                <div class="form-group form-group-last full-width" id="bloqueMenu" style="display:none;">
                    <label class="form-label">Ubicación en el Menú <span class="required">*</span></label>
                    <input type="hidden" name="menu_id" id="inputMenuId" value="<?= $accion['menu_id'] ?>">

                    <div class="menu-selection-grid">
                        <div class="menu-option-card <?= empty($accion['menu_id']) ? 'active' : '' ?>" onclick="selectMenu(this, '')">
                            <div class="menu-opt-icon suelta"><i class="fa-solid fa-layer-group"></i></div>
                            <div class="menu-opt-info"><span class="title">Suelta</span></div>
                            <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                        </div>
                        <?php foreach ($listaCarpetas as $carpeta): ?>
                            <?php $isActive = ($accion['menu_id'] == $carpeta['menu_id']); ?>
                            <div class="menu-option-card <?= $isActive ? 'active' : '' ?>" onclick="selectMenu(this, '<?= $carpeta['menu_id'] ?>')">
                                <div class="menu-opt-icon carpeta"><i class="fa-regular fa-folder-open"></i></div>
                                <div class="menu-opt-info"><span class="title"><?= htmlspecialchars($carpeta['menu_nombre']) ?></span></div>
                                <div class="check-mark"><i class="fa-solid fa-check"></i></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <div class="form-footer">
                <?php if (tiene_permiso('accion', 'actualizar')): ?>
                    <button type="submit" class="btn-create btn-save">
                        <i class="fa-solid fa-rotate"></i> <span>Guardar Cambios</span>
                    </button>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<script>
    // Inicialización: Cargar el estado correcto al abrir la página
    document.addEventListener("DOMContentLoaded", function() {
        cambiarTipo('<?= $tipoJs ?>');
        // Si es hija, verificar si debe mostrar el menú
        if ('<?= $tipoJs ?>' === 'hija') {
            toggleMenuSelection();
        }
    });

    function cambiarTipo(tipo) {
        const lblPadre = document.getElementById('lblPadre');
        const lblHija = document.getElementById('lblHija');
        
        const bloquePadre = document.getElementById('bloquePadre');
        const bloqueHija = document.getElementById('bloqueHija');
        const bloqueVisibilidad = document.getElementById('bloqueVisibilidad');
        const bloqueMenu = document.getElementById('bloqueMenu');
        const inputVisible = document.getElementById('inputVisible');

        lblPadre.classList.toggle('active', tipo === 'padre');
        lblHija.classList.toggle('active', tipo === 'hija');
        
        if(tipo === 'padre') {
            bloquePadre.style.display = 'block';
            bloqueHija.style.display = 'none';
            bloqueVisibilidad.style.display = 'none';
            bloqueMenu.style.display = 'block'; // Padres SIEMPRE muestran menú
            
            inputVisible.value = '1';
            document.getElementById('inputControlador').setAttribute('required', 'true');
        } else {
            bloquePadre.style.display = 'none';
            bloqueHija.style.display = 'block';
            bloqueVisibilidad.style.display = 'block';
            
            // La visibilidad del menú depende del switch
            toggleMenuSelection();
            
            document.getElementById('inputControlador').removeAttribute('required');
        }
    }

    function toggleMenuSelection() {
        const isChecked = document.getElementById('checkVisible').checked;
        const bloqueMenu = document.getElementById('bloqueMenu');
        const inputVisible = document.getElementById('inputVisible');
        
        // Actualizamos el hidden input que lee PHP
        inputVisible.value = isChecked ? '1' : '0';

        // Solo actuamos si estamos en modo hija (en padre siempre se muestra)
        const esHija = document.getElementById('lblHija').classList.contains('active');
        if (esHija) {
            bloqueMenu.style.display = isChecked ? 'block' : 'none';
        }
    }

    function selectMenu(element, id) {
        document.getElementById('inputMenuId').value = id;
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
</script>