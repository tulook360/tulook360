<?php 
$urlNomina = ruta_accion("metricas", "reportes_comisiones_ajax", [], false);
$urlStock = ruta_accion("metricas", "reportes_stock_ajax", [], false);
$fechaHoy = date('Y-m-d');
$fechaInicioMes = date('Y-m-01');

$empleados = $empleados ?? [];
$sucursales = $sucursales ?? [];
?>

<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ================= DISEÑO ULTRA-MINIMALISTA ================= */
    :root {
        --text-main: #0f172a; --text-muted: #64748b; --text-light: #94a3b8;
        --bg-body: #f8fafc; --bg-surface: #ffffff;
        --border-color: #e2e8f0;
        --accent: #ff3366; --success: #10b981;
    }
    
    .saas-container { font-family: 'Inter', sans-serif; padding: 2% 5%; max-width: 1200px; margin: 0 auto; width: 100%; box-sizing: border-box; color: var(--text-main); }
    
    /* HEADER */
    .saas-header { margin-bottom: 30px; }
    .saas-title { font-family: 'Kalam', cursive; font-size: 2.5rem; margin: 0; line-height: 1.1; color: var(--text-main); }
    .saas-desc { color: var(--text-muted); margin: 5px 0 0 0; font-size: 0.95rem; }

    /* NAVEGACIÓN LÍNEA INFERIOR */
    .saas-nav { display: flex; gap: 20px; border-bottom: 1px solid var(--border-color); margin-bottom: 25px; overflow-x: auto; }
    .nav-item { background: none; border: none; padding: 12px 5px; font-size: 0.95rem; font-weight: 600; color: var(--text-muted); cursor: pointer; position: relative; white-space: nowrap; transition: 0.2s; }
    .nav-item:hover { color: var(--text-main); }
    .nav-item.active { color: var(--accent); }
    .nav-item.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: var(--accent); border-radius: 2px; }

    .view-content { display: none; animation: fadeIn 0.3s ease; }
    .view-content.active { display: block; }

    /* TARJETAS KPI SUPERIORES */
    .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px; }
    .kpi-card { background: var(--bg-surface); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center; }
    .kpi-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
    .kpi-value { font-size: 1.8rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.5px; }
    .kpi-card.highlight { background: var(--text-main); border-color: var(--text-main); }
    .kpi-card.highlight .kpi-label { color: #94a3b8; }
    .kpi-card.highlight .kpi-value { color: #ffffff; }

    /* BARRA DE HERRAMIENTAS (FILTROS) */
    .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; align-items: center; justify-content: space-between; }
    .toolbar-filters { display: flex; flex-wrap: wrap; gap: 10px; flex: 1; }
    .tool-input { background: #f1f5f9; border: 1px solid transparent; padding: 10px 15px; border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.85rem; font-weight: 500; color: var(--text-main); outline: none; transition: 0.2s; cursor: pointer; }
    .tool-input:focus, .tool-input:hover { border-color: var(--border-color); background: white; }
    .btn-action { background: var(--text-main); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 8px;}
    .btn-action:hover { background: var(--accent); }

    /* TABLA MINIMALISTA */
    .table-wrapper { background: var(--bg-surface); border-radius: 12px; border: 1px solid var(--border-color); overflow-x: auto; }
    .saas-table { width: 100%; border-collapse: collapse; min-width: 600px; }
    .saas-table th { padding: 15px 20px; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color); background: #fbfbfc; }
    .saas-table td { padding: 15px 20px; font-size: 0.9rem; color: var(--text-main); border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .saas-table tr:last-child td { border-bottom: none; }
    .saas-table tr:hover td { background: #f8fafc; }

    /* Estilos de celdas */
    .td-flex { display: flex; align-items: center; gap: 12px; }
    .avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; background: #eee; }
    .info-title { font-weight: 600; margin: 0; font-size: 0.9rem; color: var(--text-main); }
    .info-sub { font-size: 0.75rem; color: var(--text-light); }
    .val-money { font-weight: 600; font-family: monospace; font-size: 1rem; }
    .badge { background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; color: var(--text-muted); }

    .msg-empty { padding: 40px; text-align: center; color: var(--text-light); font-size: 0.9rem; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* ================= MAGIA MÓVIL (LISTA FLUIDA) ================= */
    @media (max-width: 768px) {
        .saas-container { padding: 15px; }
        .toolbar-filters { flex-direction: column; width: 100%; }
        .tool-input { width: 100%; box-sizing: border-box; }
        .btn-action { width: 100%; justify-content: center; padding: 12px;}

        /* Convertimos la tabla en una lista de items */
        .saas-table, .saas-table tbody { display: block; width: 100%; }
        .saas-table thead { display: none; } 
        
        .saas-table tr { 
            display: flex; flex-wrap: wrap; 
            padding: 15px; border-bottom: 1px solid var(--border-color);
        }
        .saas-table tr:last-child { border-bottom: none; }
        
        .saas-table td { padding: 0; border: none; }
        
        /* Celda principal (Nombre/Foto) ocupa todo el ancho arriba */
        .saas-table td:nth-child(1) { width: 100%; margin-bottom: 15px; }
        
        /* Las demás celdas se dividen abajo equitativamente */
        .saas-table td:not(:nth-child(1)) { 
            flex: 1; display: flex; flex-direction: column; align-items: flex-start; gap: 4px;
        }
        
        /* Agregamos etiquetas virtuales en móvil */
        .saas-table td:not(:nth-child(1))::before {
            content: attr(data-label);
            font-size: 0.65rem; color: var(--text-light); font-weight: 600; text-transform: uppercase;
        }
        
        /* El total a la derecha */
        .saas-table td:last-child { align-items: flex-end; }
    }
</style>

<div class="saas-container">
    <div class="saas-header">
        <h1 class="saas-title">Reportes</h1>
        <p class="saas-desc">Control financiero y de inventario de la empresa.</p>
    </div>

    <div class="saas-nav">
        <button class="nav-item active" onclick="switchTab('nomina', this)">Nómina de Personal</button>
        <button class="nav-item" onclick="switchTab('stock', this)">Inventario de Sucursales</button>
    </div>

    <div id="view-nomina" class="view-content active">
        
        <div class="kpi-row">
            <div class="kpi-card">
                <span class="kpi-label">Sueldos Fijos (A)</span>
                <span class="kpi-value" id="kpi-base">$0.00</span>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Comisiones (B)</span>
                <span class="kpi-value" id="kpi-com" style="color: var(--blue);">$0.00</span>
            </div>
            <div class="kpi-card highlight">
                <span class="kpi-label">Nómina Total a Pagar (A+B)</span>
                <span class="kpi-value" id="kpi-total" style="color: var(--success);">$0.00</span>
            </div>
        </div>

        <div class="toolbar">
            <div class="toolbar-filters">
                <input type="date" id="nom_ini" class="tool-input" value="<?= $fechaInicioMes ?>">
                <input type="date" id="nom_fin" class="tool-input" value="<?= $fechaHoy ?>">
                <select id="nom_empleado" class="tool-input" style="flex: 1; min-width: 200px;">
                    <option value="">Toda la Plantilla</option>
                    <?php foreach($empleados as $emp): ?>
                        <option value="<?= $emp['usu_id'] ?>"><?= htmlspecialchars($emp['nombre']) ?> (<?= $emp['rol_nombre'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn-action" onclick="cargarNomina()">Calcular Nómina</button>
        </div>

        <div class="table-wrapper">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Empleado / Cargo</th>
                        <th>Sede</th>
                        <th>(A) Base</th>
                        <th>(B) Comis.</th>
                        <th style="text-align: right;">Total a Pagar</th>
                    </tr>
                </thead>
                <tbody id="tbody-nomina">
                    <tr><td colspan="5" class="msg-empty">Selecciona las fechas y calcula la nómina.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="view-stock" class="view-content">
        
        <div class="kpi-row">
            <div class="kpi-card highlight" style="background: #1e293b; border-color: #1e293b;">
                <span class="kpi-label">Valorización de Inventario</span>
                <span class="kpi-value" id="kpi-capital" style="color: #60a5fa;">$0.00</span>
            </div>
        </div>

        <div class="toolbar">
            <div class="toolbar-filters">
                <select id="stk_sucursal" class="tool-input" style="width: 100%; max-width: 400px;">
                    <option value="">Todas las Sucursales (Consolidado)</option>
                    <?php foreach($sucursales as $s): ?>
                        <option value="<?= $s['suc_id'] ?>"><?= htmlspecialchars($s['suc_nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn-action" onclick="cargarStock()">Analizar Inventario</button>
        </div>

        <div class="table-wrapper">
            <table class="saas-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Ubicación</th>
                        <th>Stock Físico</th>
                        <th>Costo/u</th>
                        <th style="text-align: right;">Capital Inmovilizado</th>
                    </tr>
                </thead>
                <tbody id="tbody-stock">
                    <tr><td colspan="5" class="msg-empty">Haz clic en Analizar para cargar el inventario.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function fmt(num) { return '$' + parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

    function switchTab(id, btn) {
        document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.view-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('view-' + id).classList.add('active');
    }

    // ================= LÓGICA NÓMINA =================
    function cargarNomina() {
        const ini = document.getElementById('nom_ini').value;
        const fin = document.getElementById('nom_fin').value;
        const usu = document.getElementById('nom_empleado').value;
        const tbody = document.getElementById('tbody-nomina');
        
        tbody.innerHTML = '<tr><td colspan="5" class="msg-empty"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</td></tr>';

        fetch(`<?= $urlNomina ?>&f_ini=${ini}&f_fin=${fin}&usu_id=${usu}`)
        .then(r => r.json())
        .then(data => {
            if(!data.success || data.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="msg-empty">No hay datos para esta selección.</td></tr>';
                document.getElementById('kpi-base').innerText = '$0.00';
                document.getElementById('kpi-com').innerText = '$0.00';
                document.getElementById('kpi-total').innerText = '$0.00';
                return;
            }
            
            let html = '';
            let gBase = 0; let gCom = 0; let gPay = 0;

            data.datos.forEach(e => {
                let base = parseFloat(e.sueldo_base) || 0;
                let com = parseFloat(e.comision_neta) || 0;
                let total = base + com; 
                
                gBase += base; gCom += com; gPay += total;

                const foto = e.usu_foto ? e.usu_foto : 'https://ui-avatars.com/api/?name='+encodeURI(e.empleado_nombre)+'&background=f1f5f9&color=64748b';
                let tagMes = data.meses_calculados > 1 ? `<span style="font-size:0.65rem; color:#94a3b8; display:block;">x${data.meses_calculados} meses</span>` : '';

                html += `<tr>
                    <td>
                        <div class="td-flex">
                            <img src="${foto}" class="avatar">
                            <div>
                                <h4 class="info-title">${e.empleado_nombre}</h4>
                                <span class="info-sub">${e.rol_nombre} (${e.total_servicios} serv.)</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Sede"><span class="badge">${e.suc_nombre}</span></td>
                    <td data-label="Sueldo Base"><span class="val-money">${fmt(base)}</span> ${tagMes}</td>
                    <td data-label="Comisiones"><span class="val-money" style="color:var(--blue)">${fmt(com)}</span></td>
                    <td data-label="Total a Pagar" style="text-align: right;"><span class="val-money" style="color:var(--success)">${fmt(total)}</span></td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
            
            // Actualizar KPIs Arriba
            document.getElementById('kpi-base').innerText = fmt(gBase);
            document.getElementById('kpi-com').innerText = fmt(gCom);
            document.getElementById('kpi-total').innerText = fmt(gPay);
        });
    }

    // ================= LÓGICA INVENTARIO =================
    function cargarStock() {
        const suc_id = document.getElementById('stk_sucursal').value;
        const tbody = document.getElementById('tbody-stock');
        
        tbody.innerHTML = '<tr><td colspan="5" class="msg-empty"><i class="fa-solid fa-spinner fa-spin"></i> Consultando...</td></tr>';

        fetch(`<?= $urlStock ?>&suc_id=${suc_id}`)
        .then(r => r.json())
        .then(data => {
            if(!data.success || data.datos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="msg-empty">El inventario está vacío.</td></tr>';
                document.getElementById('kpi-capital').innerText = '$0.00';
                return;
            }
            
            let html = '';
            let gCap = 0;

            data.datos.forEach(p => {
                gCap += parseFloat(p.capital_inmovilizado);
                const foto = p.pro_foto ? p.pro_foto : 'https://ui-avatars.com/api/?name=P&background=f1f5f9&color=64748b';
                let alertColor = parseFloat(p.stock_fisico) <= parseFloat(p.ps_stock_min) ? 'color:var(--accent);' : '';

                html += `<tr>
                    <td>
                        <div class="td-flex">
                            <img src="${foto}" class="avatar" style="border-radius: 8px;">
                            <div>
                                <h4 class="info-title">${p.pro_nombre}</h4>
                                <span class="info-sub">Ref: ${p.pro_codigo}</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Sede"><span class="badge">${p.suc_nombre}</span></td>
                    <td data-label="Stock">
                        <span class="val-money" style="${alertColor}">${p.stock_fisico}</span> 
                        <span style="font-size:0.7rem; color:var(--text-light);">${p.pro_unidad}</span>
                    </td>
                    <td data-label="Costo /u"><span class="val-money" style="color:var(--text-muted)">${fmt(p.costo_unitario)}</span></td>
                    <td data-label="Capital" style="text-align: right;"><span class="val-money">${fmt(p.capital_inmovilizado)}</span></td>
                </tr>`;
            });
            
            tbody.innerHTML = html;
            document.getElementById('kpi-capital').innerText = fmt(gCap);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarNomina();
        cargarStock();
    });
</script>