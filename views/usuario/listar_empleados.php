<div class="section-header">
    <div>
        <h1 class="section-title kalam">Mi Equipo</h1>
        <p class="section-subtitle">Gestiona el talento humano de tu negocio.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('usuario', 'crear_empleado')): ?>
            <a href="<?= ruta_accion('usuario', 'crear_empleado') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nuevo</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="toolbar-container">
    
    <div class="tabs-capsule">
        <a href="<?= ruta_accion('usuario', 'listar_empleados', ['filtro' => 'activos']) ?>" 
           class="tab-item <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
           <i class="fa-solid fa-users"></i> Activos
        </a>
        <a href="<?= ruta_accion('usuario', 'listar_empleados', ['filtro' => 'inactivos']) ?>" 
           class="tab-item <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
           <i class="fa-solid fa-trash-arrow-up"></i> Papelera
        </a>
    </div>

    <form action="index.php" method="GET" class="search-form-premium">
        <input type="hidden" name="c" value="usuario">
        <input type="hidden" name="a" value="listar_empleados">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
        
        <div class="search-input-box">
            <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
            
            <input type="text" name="q" class="input-field" 
                   placeholder="Buscar por nombre o cédula..." 
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off">
            
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('usuario', 'listar_empleados', ['filtro' => $filtroActual]) ?>" 
                   class="btn-reset" title="Limpiar">
                    <i class="fa-solid fa-circle-xmark"></i>
                </a>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-submit">BUSCAR</button>
    </form>
</div>

<div class="team-grid">
    <?php if (empty($listaEmpleados)): ?>
        <div class="empty-state">
            <div class="empty-icon-bg"><i class="fa-solid fa-no-user"></i></div>
            <h3>No se encontraron resultados</h3>
            <p>No hay colaboradores con ese criterio.</p>
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('usuario', 'listar_empleados', ['filtro' => $filtroActual]) ?>" class="btn-link">Ver todos</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($listaEmpleados as $emp): ?>
            <div class="emp-card">
                
                <div class="card-header-row">
                    <span class="role-badge"><?= htmlspecialchars($emp['rol_nombre']) ?></span>
                    <span class="status-dot <?= $filtroActual === 'activos' ? 'online' : 'offline' ?>"></span>
                </div>

                <div class="card-profile">
                    <div class="avatar-circle">
                        <?php if($emp['usu_foto']): ?>
                            <img src="<?= htmlspecialchars($emp['usu_foto']) ?>">
                        <?php else: ?>
                            <span><?= strtoupper(substr($emp['usu_nombres'],0,1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 class="emp-name"><?= htmlspecialchars($emp['usu_nombres']) ?> <small><?= htmlspecialchars($emp['usu_apellidos']) ?></small></h4>
                    <span class="emp-id">CI: <?= htmlspecialchars($emp['usu_cedula']) ?></span>
                    <div class="emp-location">
                        <i class="fa-solid fa-store"></i> 
                        <?= !empty($emp['suc_nombre']) ? htmlspecialchars($emp['suc_nombre']) : 'Global / Admin' ?>
                    </div>
                </div>

                <div class="card-actions">
                    <?php if (tiene_permiso('usuario', 'editar_empleado')): ?>
                        <a href="<?= ruta_accion('usuario', 'editar_empleado', ['id' => $emp['usu_id']]) ?>" 
                        class="btn-action edit">
                            <i class="fa-solid fa-pen"></i> <span>Editar</span>
                        </a>
                    <?php endif; ?>

                    <?php if ($filtroActual === 'activos'): ?>
                        <?php if (tiene_permiso('usuario', 'eliminar_empleado')): ?>
                            <?php $urlDel = ruta_accion('usuario', 'eliminar_empleado', ['id' => $emp['usu_id']]); ?>
                            <button type="button" class="btn-action delete" title="Desactivar"
                                    onclick="preguntar('<?= $urlDel ?>', '¿Desactivar?', 'El usuario perderá el acceso.', 'Sí, Desactivar', 'danger')">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (tiene_permiso('usuario', 'reactivar_empleado')): ?>
                            <?php $urlRec = ruta_accion('usuario', 'reactivar_empleado', ['id' => $emp['usu_id']]); ?>
                            <button type="button" class="btn-action restore" title="Restaurar"
                                    onclick="preguntar('<?= $urlRec ?>', '¿Reactivar?', 'El usuario recuperará el acceso.', 'Sí, Restaurar', 'success')">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    /* --- BARRA HERRAMIENTAS --- */
    .toolbar-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }

    /* --- TABS --- */
    .tabs-capsule { background: #f0f2f5; padding: 5px; border-radius: 50px; display: inline-flex; box-shadow: inset 0 2px 5px rgba(0,0,0,0.03); }
    .tab-item { padding: 10px 25px; border-radius: 50px; color: #636e72; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
    .tab-item:hover { color: var(--dark); }
    .tab-item.active { background: white; color: var(--color-primario); box-shadow: 0 4px 15px rgba(0,0,0,0.08); font-weight: 700; transform: scale(1.02); }

    /* --- BUSCADOR PREMIUM --- */
    .search-form-premium { display: flex; gap: 12px; flex: 1; max-width: 550px; justify-content: flex-end; }
    
    /* Caja Input */
    .search-input-box { position: relative; flex: 1; display: flex; align-items: center; background: white; border: 2px solid transparent; border-radius: 16px; padding: 5px 15px 5px 45px; box-shadow: 0 5px 20px rgba(0,0,0,0.04); transition: all 0.3s ease; }
    .search-input-box:focus-within { border-color: var(--color-primario); box-shadow: 0 8px 25px rgba(255, 51, 102, 0.15); transform: translateY(-2px); }
    
    .search-icon { color: #b2bec3; font-size: 1.1rem; margin-right: 10px; }
    
    .input-field { border: none; outline: none; width: 100%; font-size: 0.95rem; color: #2d3436; background: transparent; font-weight: 500; padding: 10px 0; }
    .input-field::placeholder { color: #ccc; font-weight: 400; }
    
    /* Botón X */
    .btn-reset { color: #fab1a0; font-size: 1.2rem; cursor: pointer; transition: 0.2s; display: flex; align-items: center; padding: 5px; }
    .btn-reset:hover { color: #d63031; transform: scale(1.1); }

    /* Botón BUSCAR */
    .btn-submit { background: var(--dark); color: black; border: none; padding: 0 25px; border-radius: 16px; font-weight: 700; cursor: pointer; font-size: 0.85rem; letter-spacing: 1px; transition: all 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .btn-submit:hover { background: #e84393; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }

    /* Móvil */
    @media (max-width: 768px) {
        .toolbar-container { flex-direction: column; align-items: stretch; gap: 20px; }
        .search-form-premium { max-width: 100%; }
        .tabs-capsule { justify-content: center; width: 100%; }
        .tab-item { flex: 1; justify-content: center; }
    }

    /* --- GRID --- */
    .team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
    .emp-card { background: white; border: 1px solid #f0f0f0; border-radius: 20px; overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; }
    .emp-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.08); border-color: transparent; }

    .card-header-row { display: flex; justify-content: space-between; padding: 15px 20px; }
    .role-badge { background: #f8f9fa; color: #636e72; font-size: 0.7rem; font-weight: 800; padding: 5px 10px; border-radius: 6px; text-transform: uppercase; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; }
    .status-dot.online { background: #00b894; box-shadow: 0 0 0 3px rgba(0, 184, 148, 0.15); }
    .status-dot.offline { background: #b2bec3; }

    .card-profile { padding: 0 20px 25px; text-align: center; flex: 1; display: flex; flex-direction: column; align-items: center; }
    .avatar-circle { width: 75px; height: 75px; border-radius: 50%; background: #f1f2f6; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; color: var(--color-primario); overflow: hidden; border: 4px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    .avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
    
    .emp-name { margin: 0 0 5px; font-size: 1.1rem; color: #2d3436; font-weight: 800; }
    .emp-name small { display: block; font-weight: 500; font-size: 0.9rem; color: #636e72; }
    .emp-id { font-size: 0.8rem; color: #b2bec3; margin-bottom: 15px; font-family: monospace; }
    .emp-location { font-size: 0.85rem; color: #2d3436; background: #fff5f8; padding: 6px 15px; border-radius: 20px; font-weight: 600; }
    .emp-location i { color: var(--color-primario); margin-right: 5px; }

    /* --- DISEÑO BOTONES (IGUAL A TU FOTO) --- */
    .card-actions { 
        padding: 15px 20px; 
        border-top: 1px dashed #f0f0f0; 
        display: flex; 
        gap: 12px; 
        background: #fff;
        align-items: center;
    }

    .btn-action { 
        border-radius: 12px; 
        border: none; 
        cursor: pointer; 
        transition: 0.2s; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 700; 
        text-decoration: none; 
    }
    
    /* Botón EDITAR (Grande, Blanco con borde) */
    .edit { 
        flex: 1; /* Ocupa todo el espacio posible */
        padding: 10px 15px; 
        background: white; 
        border: 2px solid #f1f2f6; 
        color: #2d3436; 
        font-size: 0.9rem;
        gap: 8px;
    }
    .edit:hover { 
        border-color: var(--dark); 
        background: var(--dark); 
        color: #e84393; 
    }
    
    /* Botón ELIMINAR/RESTAURAR (Cuadrado pequeño) */
    .delete, .restore { 
        width: 42px; 
        height: 42px; 
        padding: 0; /* Sin texto, solo icono */
        font-size: 1.1rem;
        flex-shrink: 0; /* No se encoge */
    }

    /* Estilo Rojo Suave (Desactivar) */
    .delete { background: #ffecec; color: #d63031; }
    .delete:hover { background: #d63031; color: white; box-shadow: 0 4px 10px rgba(214, 48, 49, 0.3); }
    
    /* Estilo Verde Suave (Restaurar) */
    .restore { background: #e6fffa; color: #00b894; }
    .restore:hover { background: #00b894; color: white; box-shadow: 0 4px 10px rgba(0, 184, 148, 0.3); }

    .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; }
    .empty-icon-bg { width: 80px; height: 80px; background: #f1f2f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; color: #b2bec3; }
    .empty-state h3 { font-weight: 800; color: #2d3436; }
    .empty-state p { color: #636e72; }
    .btn-link { color: var(--color-primario); font-weight: 700; text-decoration: none; margin-top: 10px; display: inline-block; }

    @media (max-width: 600px) {
        .toolbar-container { flex-direction: column; align-items: stretch; }
        .search-form { max-width: 100%; }
        .tabs-capsule { justify-content: center; width: 100%; }
    }
</style>

