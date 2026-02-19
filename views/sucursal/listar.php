<link rel="stylesheet" href="<?= asset('recursos/css/sucursal.css') ?>">

<div class="section-header">
    <div>
        <h1 class="section-title kalam">Mis Sucursales</h1>
        <p class="section-subtitle">Gestiona tus locales y ubicaciones.</p>
    </div>
    <div class="header-actions">
        <?php if (tiene_permiso('sucursal', 'crear')): ?>
            <a href="<?= ruta_accion('sucursal', 'crear') ?>" class="btn-create">
                <i class="fa-solid fa-plus"></i> <span>Nueva Sucursal</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('sucursal', 'listar', ['filtro' => 'activos']) ?>" class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-store"></i> Activas
    </a>
    <a href="<?= ruta_accion('sucursal', 'listar', ['filtro' => 'inactivos']) ?>" class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-trash"></i> Papelera
    </a>
</div>

<div class="filters-bar">
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
        
        <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" class="search-input" 
                   placeholder="Buscar por nombre, dirección..." 
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('sucursal', 'listar', ['filtro' => $filtroActual]) ?>" class="clear-search" title="Borrar búsqueda">
                    <i class="fa-solid fa-circle-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <button type="submit" class="btn-filter">
            Buscar
        </button>
    </form>
</div>

<div class="grid-cards">
    <?php if (empty($listaSucursales)): ?>
        <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px;">
            <i class="fa-solid fa-store-slash" style="font-size: 3.5rem; color: #eee; margin-bottom: 20px; display:block;"></i>
            <h3 style="color: #636e72; font-size:1.1rem;">No encontramos sucursales</h3>
            <p style="color: #b2bec3; font-size:0.9rem;">Intenta cambiar el filtro o crea una nueva.</p>
        </div>
    <?php else: ?>
        <?php foreach ($listaSucursales as $suc): ?>
            
            <div class="sucursal-card">
                
                <div class="card-header-img">
                    <?php if ($suc['suc_foto']): ?>
                        <img src="<?= htmlspecialchars($suc['suc_foto']) ?>" alt="Fachada">
                    <?php else: ?>
                        <div class="no-img-box">
                            <i class="fa-regular fa-image" style="font-size: 2rem;"></i>
                        </div>
                    <?php endif; ?>

                    <?php if($suc['suc_estado'] == 'A'): ?>
                        <span class="status-badge status-active">Activa</span>
                    <?php else: ?>
                        <span class="status-badge status-inactive">Inactiva</span>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($suc['suc_nombre']) ?></div>
                    
                    <div class="info-row">
                        <i class="fa-solid fa-location-dot"></i>
                        <span style="line-height: 1.4;"><?= htmlspecialchars($suc['suc_direccion']) ?></span>
                    </div>

                    <?php if($suc['suc_telefono']): ?>
                    <div class="info-row">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span><?= htmlspecialchars($suc['suc_telefono']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    </div>

                <div class="card-footer">
                    <?php if(!empty($suc['suc_latitud']) && !empty($suc['suc_longitud'])): ?>
                        <a href="https://www.google.com/maps?q=<?= $suc['suc_latitud'] ?>,<?= $suc['suc_longitud'] ?>" 
                           target="_blank" 
                           class="btn-map-link" 
                           title="Ver ubicación en Google Maps">
                            <i class="fa-solid fa-map-location-dot"></i> Ver Mapa
                        </a>
                    <?php else: ?>
                        <span class="btn-map-link" style="opacity:0.5; cursor:not-allowed;">
                            <i class="fa-solid fa-map-pin"></i> Sin Ubicación
                        </span>
                    <?php endif; ?>

                    <div style="display:flex; gap: 10px;">
                        <?php if ($filtroActual === 'activos'): ?>
                            
                            <?php if (tiene_permiso('sucursal', 'editar')): ?>
                                <a href="<?= ruta_accion('sucursal', 'editar', ['id' => $suc['suc_id']]) ?>" 
                                   class="action-btn btn-edit" title="Editar datos y horarios">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (tiene_permiso('sucursal', 'eliminar')): ?>
                                <button class="action-btn btn-delete" 
                                        onclick="preguntar('<?= ruta_accion('sucursal', 'eliminar', ['id' => $suc['suc_id']]) ?>', 
                                        '¿Desactivar Sucursal?', 'No podrás recibir citas aquí temporalmente.', 'Sí, Desactivar', 'danger')">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            <?php endif; ?>

                        <?php else: ?>
                            <?php if (tiene_permiso('sucursal', 'reactivar')): ?>
                                <button class="action-btn btn-restore" 
                                        onclick="preguntar('<?= ruta_accion('sucursal', 'reactivar', ['id' => $suc['suc_id']]) ?>', 
                                        '¿Reactivar?', 'La sucursal volverá a estar operativa.', 'Sí, Reactivar', 'success')">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>