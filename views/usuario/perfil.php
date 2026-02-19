<link rel="stylesheet" href="<?= asset('recursos/css/perfil.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Mi Perfil</h1>
        <p class="section-subtitle">Información personal.</p>
    </div>
</div>

<div class="profile-card">
    <div class="profile-header-bg"></div>

    <div class="profile-identity">
        <div class="profile-avatar-wrapper">
            <div class="profile-avatar-box">
                <?php if (!empty($usuario['usu_foto'])): ?>
                    <img src="<?= htmlspecialchars($usuario['usu_foto']) ?>" alt="Foto" id="imgFotoActual">
                <?php else: ?>
                    <div class="profile-avatar-initials" id="divInicialesActual">
                        <?= strtoupper(substr($usuario['usu_nombres'], 0, 1) . substr($usuario['usu_apellidos'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (tiene_permiso('usuario', 'actualizarFoto')): ?>
                <form id="formFotoPerfil" enctype="multipart/form-data">
                    <input type="file" id="inputFotoPerfil" name="fotoPerfil" accept="image/png, image/jpeg, image/jpg">
                    <label for="inputFotoPerfil" class="btn-edit-avatar" title="Cambiar foto">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </form>
            <?php endif; ?>
        </div>

        <div class="profile-names">
            <div class="profile-fullname">
                <?= htmlspecialchars($usuario['usu_nombres'] . ' ' . $usuario['usu_apellidos']) ?>
            </div>
            <span class="profile-role-badge">
                <?= htmlspecialchars($usuario['rol_nombre']) ?>
            </span>
        </div>
    </div>

    <div class="profile-info-grid">
        <?php $permisoEditarDato = tiene_permiso('usuario', 'guardarDato'); ?>

        <div class="info-group">
            <span class="info-label">Nombres</span>
            <div class="info-value-wrapper">
                <div class="info-value"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($usuario['usu_nombres']) ?></div>
                <?php if($permisoEditarDato): ?>
                    <button class="btn-mini-edit" onclick="abrirModalEditar('usu_nombres', 'Nombres', '<?= htmlspecialchars($usuario['usu_nombres']) ?>')"><i class="fa-solid fa-pencil"></i></button>
                <?php endif; ?>
            </div>
        </div>
        <div class="info-group">
            <span class="info-label">Apellidos</span>
            <div class="info-value-wrapper">
                <div class="info-value"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($usuario['usu_apellidos']) ?></div>
                <?php if($permisoEditarDato): ?>
                    <button class="btn-mini-edit" onclick="abrirModalEditar('usu_apellidos', 'Apellidos', '<?= htmlspecialchars($usuario['usu_apellidos']) ?>')"><i class="fa-solid fa-pencil"></i></button>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-group">
            <span class="info-label">Cédula / Identificación</span>
            <div class="info-value-wrapper">
                <div class="info-value" style="background: #f1f2f6; color: #636e72;">
                    <i class="fa-solid fa-id-card"></i> 
                    <?= htmlspecialchars($usuario['usu_cedula'] ?? 'No registrada') ?>
                </div>
            </div>
        </div>
        <div class="info-group">
            <span class="info-label">Correo Electrónico</span>
            <div class="info-value-wrapper">
                <div class="info-value"><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($usuario['usu_correo']) ?></div>
                <?php if($permisoEditarDato): ?>
                    <button class="btn-mini-edit" onclick="abrirModalEditar('usu_correo', 'Correo', '<?= htmlspecialchars($usuario['usu_correo']) ?>', 'email')"><i class="fa-solid fa-pencil"></i></button>
                <?php endif; ?>
            </div>
        </div>
        <div class="info-group">
            <span class="info-label">Teléfono</span>
            <div class="info-value-wrapper">
                <div class="info-value"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($usuario['usu_telefono'] ?? 'No registrado') ?></div>
                <?php if($permisoEditarDato): ?>
                    <button class="btn-mini-edit" onclick="abrirModalEditar('usu_telefono', 'Teléfono', '<?= htmlspecialchars($usuario['usu_telefono'] ?? '') ?>', 'tel')"><i class="fa-solid fa-pencil"></i></button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="info-group">
            <span class="info-label">Seguridad</span>
            <div class="info-value-wrapper">
                <div class="info-value" style="color: #636e72;">
                    <i class="fa-solid fa-shield-halved"></i> ************
                </div>
                <?php if (tiene_permiso('usuario', 'cambiarContrasena')): ?>
                    <button class="btn-mini-edit" style="width:auto; padding:0 15px; border-radius:15px;" onclick="abrirModalPassword()">
                        Cambiar Contraseña
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-group">
            <span class="info-label">Miembro Desde</span>
            <div class="info-value"><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($usuario['usu_fecha_reg'])) ?></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalEditarDato">
    <div class="modal-box">
        <h3 class="modal-title" id="editModalTitle">Editar Dato</h3>
        <form id="formEditarDato" onsubmit="guardarDato(event)">
            <input type="hidden" id="editCampo" name="campo">
            <div class="input-group" style="text-align: left; margin-bottom: 1.5rem;">
                <input type="text" id="editValor" name="valor" class="form-control" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="cerrarModalPropio('modalEditarDato')">Cancelar</button>
                <button type="submit" class="btn-modal btn-confirm" style="background: var(--color-primario);">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalPassword">
    <div class="modal-box">
        <h3 class="modal-title">Cambiar Contraseña</h3>
        <p class="modal-text">Ingresa la nueva contraseña.</p>
        
        <form id="formPassword" onsubmit="guardarContrasena(event)">
            <div style="text-align: left; margin-bottom: 1rem;">
                <label class="form-label" style="font-size:0.85rem;">Nueva Contraseña</label>
                <div class="input-wrapper">
                    <input type="text" id="passNew" class="form-control" placeholder="Mín. 8 caracteres, Mayus, Num, Simbolo" required>
                </div>
                <small id="errorPassNew" class="error-msg-modal"></small>
            </div>
            
            <div style="text-align: left; margin-bottom: 1.5rem;">
                <label class="form-label" style="font-size:0.85rem;">Confirmar Contraseña</label>
                <div class="input-wrapper">
                    <input type="text" id="passConfirm" class="form-control" placeholder="Repite la contraseña" required>
                </div>
                <small id="errorPassConfirm" class="error-msg-modal"></small>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="cerrarModalPropio('modalPassword')">Cancelar</button>
                <button type="submit" class="btn-modal btn-confirm" style="background: var(--color-primario);">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
    const urlActualizarFoto = '<?= ruta_accion("usuario", "actualizarFoto", [], false) ?>';
    const urlGuardarDato    = '<?= ruta_accion("usuario", "guardarDato", [], false) ?>';
    const urlCambiarPass    = '<?= ruta_accion("usuario", "cambiarContrasena", [], false) ?>';
</script>

<script src="<?= asset('recursos/js/perfil.js') ?>"></script>