<div class="section-header">
    <div>
        <h1 class="section-title kalam">Negocios Registrados</h1>
        <p class="section-subtitle">Supervisión global de clientes y sus administradores.</p>
    </div>
</div>

<div class="tabs-container">
    <a href="<?= ruta_accion('usuario', 'listar_negocios', ['filtro' => 'activos']) ?>" 
       class="tab-link <?= ($filtroActual === 'activos') ? 'active' : '' ?>">
       <i class="fa-solid fa-store"></i> Activos
    </a>
    
    <a href="<?= ruta_accion('usuario', 'listar_negocios', ['filtro' => 'inactivos']) ?>" 
       class="tab-link <?= ($filtroActual === 'inactivos') ? 'active' : '' ?>">
       <i class="fa-solid fa-store-slash"></i> Suspendidos
    </a>
</div>

<div class="filters-bar">
    <form action="index.php" method="GET" class="search-form">
        <input type="hidden" name="token" value="<?= $_GET['token'] ?? '' ?>">
        
        <div class="search-input-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" name="q" class="search-input" 
                   placeholder="Buscar por negocio, cédula o admin..." 
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            
            <?php if(!empty($_GET['q'])): ?>
                <a href="<?= ruta_accion('usuario', 'listar_admin_negocio', ['filtro' => $filtroActual]) ?>" class="clear-search" title="Limpiar">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn-filter">Buscar</button>
    </form>
</div>

<div class="grid-cards">
    <?php if (empty($listaNegocios)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-store-slash empty-icon"></i>
            <p>No se encontraron negocios en <b><?= ucfirst($filtroActual) ?></b>.</p>
        </div>
    <?php else: ?>
        <?php foreach ($listaNegocios as $neg): ?>
            
            <div class="card-item" style="<?= $neg['neg_estado'] == 'I' ? 'opacity:0.85; border:1px dashed #fab1a0;' : '' ?>">
                
                <div class="card-top">
                    <span class="card-id">#<?= str_pad($neg['neg_id'], 3, '0', STR_PAD_LEFT) ?></span>
                    <?php if($neg['neg_estado'] == 'A'): ?>
                        <span class="badge badge-active">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Suspendido</span>
                    <?php endif; ?>
                </div>

                <div class="card-body" style="display:flex; gap:15px; align-items:center;">
                    <?php $logoShow = $neg['neg_logo'] ?: asset('recursos/img/logo.png'); ?>
                    <img src="<?= htmlspecialchars($logoShow) ?>" 
                         style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #f1f2f6;">
                    
                    <div style="min-width:0;">
                        <div class="card-title" style="margin-bottom:0.2rem; font-size:1.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= htmlspecialchars($neg['neg_nombre']) ?>
                        </div>
                        <div class="card-desc" style="font-size:0.85rem;">
                            <div style="margin-bottom:2px;">
                                <i class="fa-solid fa-user-tie" style="color:#b2bec3; width:15px;"></i> 
                                <?= htmlspecialchars($neg['usu_nombres']) ?>
                            </div>
                            <div>
                                <i class="fa-solid fa-id-card" style="color:#b2bec3; width:15px;"></i> 
                                <?= htmlspecialchars($neg['usu_cedula']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-actions">
                    <?php if (tiene_permiso('usuario', 'ver_negocio')): ?>
                        <a href="<?= ruta_accion('usuario', 'ver_negocio', ['idNegocio' => $neg['neg_id']]) ?>" 
                        class="btn-icon btn-edit" title="Ver Detalle Completo">
                        <i class="fa-solid fa-eye"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if($neg['neg_estado'] == 'A'): ?>
                        <?php if (tiene_permiso('usuario', 'desactivar_negocio')): ?>
                            <?php $urlDel = ruta_accion('usuario', 'desactivar_negocio', ['idNegocio' => $neg['neg_id']]); ?>
                            <button class="btn-icon btn-delete" title="Suspender Negocio"
                                    onclick="preguntar('<?= $urlDel ?>', '¿Suspender Negocio?', 'Se bloqueará el acceso al Admin y a todo su personal.', 'Sí, Suspender', 'danger')">
                                <i class="fa-solid fa-power-off"></i>
                            </button>
                        <?php endif; ?>
                    
                    <?php else: ?>
                        <?php if (tiene_permiso('usuario', 'reactivar_negocio')): ?>
                            <?php $urlRec = ruta_accion('usuario', 'reactivar_negocio', ['idNegocio' => $neg['neg_id']]); ?>
                            <button class="btn-icon btn-restore" title="Reactivar Negocio"
                                    onclick="preguntar('<?= $urlRec ?>', '¿Reactivar Negocio?', 'Todo el personal y accesos del negocio serán restaurados.', 'Sí, Reactivar', 'success')">
                                <i class="fa-solid fa-bolt"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>