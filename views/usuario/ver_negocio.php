<div class="section-header">
    <div>
        <h1 class="section-title kalam">
            <?= htmlspecialchars($reporte['info']['neg_nombre']) ?>
        </h1>
        <p class="section-subtitle">
            Fundado: <?= date('d/m/Y', strtotime($reporte['info']['neg_fundacion'])) ?> | 
            Admin: <?= htmlspecialchars($reporte['info']['usu_nombres']) ?>
        </p>
    </div>
    <div class="header-actions">
        <a href="<?= ruta_accion('usuario', 'listar_negocios') ?>" class="btn-create btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <span>Volver</span>
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:2rem;">
    
    <div style="background:white; padding:1.5rem; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border-left: 5px solid var(--color-primario);">
        <span style="color:#888; font-size:0.9rem; font-weight:600;">ROLES CREADOS</span>
        <h3 style="font-size:2rem; margin:0.5rem 0; color:var(--color-texto);">
            <?= count($reporte['roles']) ?>
        </h3>
    </div>

    <div style="background:white; padding:1.5rem; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border-left: 5px solid #00b894;">
        <span style="color:#888; font-size:0.9rem; font-weight:600;">SUCURSALES</span>
        <h3 style="font-size:2rem; margin:0.5rem 0; color:var(--color-texto);">
            <?= count($reporte['sucursales']) ?>
        </h3>
    </div>

    <div style="background:white; padding:1.5rem; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,0.03); border-left: 5px solid #0984e3;">
        <span style="color:#888; font-size:0.9rem; font-weight:600;">TOTAL EMPLEADOS</span>
        <h3 style="font-size:2rem; margin:0.5rem 0; color:var(--color-texto);">
            <?php 
                $totalEmp = 0;
                foreach($reporte['roles'] as $r) $totalEmp += $r['total_empleados'];
                echo $totalEmp;
            ?>
        </h3>
    </div>

</div>

<h3 style="margin-bottom:1rem; color:var(--color-primario);">Desglose de Personal</h3>
<div class="card-item" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9fafb; border-bottom:1px solid #eee;">
            <tr>
                <th style="padding:1rem; text-align:left; color:#888; font-size:0.85rem;">ROL</th>
                <th style="padding:1rem; text-align:left; color:#888; font-size:0.85rem;">EMPLEADOS ACTIVOS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reporte['roles'] as $rol): ?>
                <tr style="border-bottom:1px solid #f1f2f6;">
                    <td style="padding:1rem; font-weight:600;">
                        <?= htmlspecialchars($rol['rol_nombre']) ?>
                    </td>
                    <td style="padding:1rem;">
                        <span class="badge badge-active" style="background:#eef0ff; color:#6c5ce7;">
                            <?= $rol['total_empleados'] ?> personas
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if(empty($reporte['roles'])): ?>
                <tr><td colspan="2" style="padding:1.5rem; text-align:center; color:#b2bec3;">Sin roles creados</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if(!empty($reporte['sucursales'])): ?>
    <h3 style="margin:2rem 0 1rem; color:var(--color-primario);">Sucursales</h3>
    <div class="grid-cards">
        <?php foreach($reporte['sucursales'] as $suc): ?>
            <div class="card-item" style="padding:1rem;">
                <div style="font-weight:700;"><?= htmlspecialchars($suc['suc_nombre']) ?></div>
                <div style="font-size:0.9rem; color:#666;"><?= htmlspecialchars($suc['suc_direccion']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>