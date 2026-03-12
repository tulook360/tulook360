<?php
// Ruta segura
$urlResumen = ruta_accion("metricas", "resumen_ajax", [], false);
$urlCitas = ruta_accion("metricas", "citas_ajax", [], false);
$urlTopServicios = ruta_accion("metricas", "top_servicios_ajax", [], false);
$urlVentasCat = ruta_accion("metricas", "ventas_cat_ajax", [], false);

$fechaHoy = date('Y-m-d');
$fechaInicioMes = date('Y-m-01');
?>

<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    /* 1. CONTENEDOR Y CABECERA MAESTRA (TU CÓDIGO ORIGINAL INTACTO) */
    .dashboard-wrapper {
        padding: 5px; 
        background-color: transparent !important;
        font-family: 'Poppins', sans-serif;
        width: 100%;
        max-width: 100vw;
        overflow-x: hidden;
        box-sizing: border-box;
    }
    .master-header { display: flex; flex-direction: column; gap: 20px; margin-bottom: 35px; }
    .titulo-principal { font-family: 'Kalam', cursive; font-size: 2.2rem; font-weight: 700; color: #1e293b; margin: 0 0 8px 0; line-height: 1.1; }
    .titulo-principal span { color: #ff3366; }
    .titulo-principal i { color: #ff3366; margin-right: 8px; font-size: 1.8rem; }
    .subtitulo { font-size: 0.95rem; color: #64748b; margin: 0; font-weight: 500; }

    /* 2. REJILLAS */
    .kpi-grid-50, .kpi-grid-33 { display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 25px; }

    /* 3. TARJETAS Y ELEMENTOS INTERNOS */
    .kpi-card {
        background: #ffffff; border-radius: 20px; padding: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); border: 1px solid #f1f5f9;
        display: flex; flex-direction: column; width: 100%; box-sizing: border-box; overflow: hidden; 
    }
    .kpi-card-header { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
    .kpi-title-box { display: flex; align-items: center; }
    .kpi-icon { width: 45px; height: 45px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-right: 12px; }
    .kpi-name { font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
    
    /* El número grande que te gusta (Debajo de la cabecera, jamás chocará con el filtro) */
    .kpi-valor-principal { font-size: 3.2rem; font-weight: 800; color: #0f172a; letter-spacing: -1px; margin: 0 0 20px 0; line-height: 1; word-wrap: break-word; }

    /* 4. FILTROS EN MÓVIL (TU DISEÑO ORIGINAL PERFECTO) */
    .master-filter-box { background: #ffffff; padding: 15px; border-radius: 20px; border: 1px solid #ff3366; box-shadow: 0 10px 25px rgba(255, 51, 102, 0.1); display: flex; flex-direction: column; gap: 10px; }
    .smart-filter { background: #f8fafc; border-radius: 16px; padding: 10px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 10px; width: 100%; }
    .fechas-wrapper { display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 6px 12px; }
    .date-input-group { display: flex; flex-direction: column; width: 45%; }
    .date-label { font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px; }
    .smart-input { width: 100%; border: none; background: transparent; font-size: 0.8rem; font-weight: 600; color: #1e293b; padding: 0; font-family: 'Poppins', sans-serif; outline: none; }
    .separator { color: #cbd5e1; font-weight: bold; margin: 0 5px; }
    .smart-btn, .btn-master { background: #10b981; color: white; border: none; border-radius: 12px; padding: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; width: 100%; }
    .btn-master { background: #ff3366; border-radius: 50px; font-weight: 700; width: auto; white-space: nowrap; }

    /* 5. DESGLOSES */
    .desglose-grid, .grid-3-citas { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    .grid-3-citas .desglose-caja:last-child { grid-column: span 2; }
    .desglose-caja { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 14px; padding: 12px 15px; display: flex; flex-direction: column; justify-content: center; }
    .desglose-header { display: flex; align-items: center; margin-bottom: 5px; }
    .desglose-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
    .desglose-valor { font-size: 1.3rem; font-weight: 800; color: #1e293b; word-wrap: break-word; }
    .punto { width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
    .punto-servicios { background-color: #ff3366; }
    .punto-productos { background-color: #3b82f6; }
    .chart-box { position: relative; width: 100%; }
    .empty-chart-container { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; text-align: center; padding: 20px; }
    .empty-chart-container i { font-size: 3rem; margin-bottom: 10px; opacity: 0.3; }

    /* 6. PC (DESKTOP) */
    @media (min-width: 768px) {
        .dashboard-wrapper { padding: 30px 10px; }
        .kpi-card { padding: 30px; border-radius: 24px; }
        .master-filter-box { flex-direction: row; align-items: center; padding: 10px 20px; border-radius: 50px; }
        .grid-3-citas { grid-template-columns: repeat(3, 1fr); }
        .grid-3-citas .desglose-caja:last-child { grid-column: span 1; }
    }
    @media (min-width: 1024px) {
        .master-header { flex-direction: row; justify-content: space-between; align-items: flex-end; }
        
        /* FILAS AL 50% */
        .kpi-grid-50 { grid-template-columns: 1fr 1fr; gap: 15px; }
        
        /* Cabeceras de Tarjeta (Titulo Izq, Filtro Der) */
        .kpi-card-header { flex-direction: row; justify-content: space-between; align-items: center; }

        /* --- FILTROS COMPACTOS EN PC --- */
        .smart-filter { 
            flex-direction: row; align-items: center; padding: 4px; 
            border-radius: 50px; width: max-content; background: #ffffff; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); 
            border: 1px solid #cbd5e1; 
            flex-shrink: 0; /* EVITA QUE SE APLASTE */
        }
        .fechas-wrapper { border: none; padding: 0 8px; background: transparent; }
        .date-input-group { width: auto; flex-direction: row; align-items: center; }
        .smart-input { width: 95px !important; text-align: center; font-size: 0.8rem; }
        .smart-btn { width: 34px; height: 34px; border-radius: 50px; padding: 0; margin-left: 2px; flex-shrink: 0; }
        .smart-btn span, .date-label { display: none !important; }
    }
</style>

<div class="dashboard-wrapper">
    
    <div class="master-header">
        <div class="hero-header" style="margin-bottom: 0;">
            <h2 class="titulo-principal">
                <i class="fa-solid fa-chart-pie"></i>
                Mi Panel <span>Financiero</span>
            </h2>
            <p class="subtitulo">Sincroniza y supervisa todo tu negocio en un solo lugar.</p>
        </div>

        <div class="master-filter-box">
            <div class="fechas-wrapper" style="border: none; background: transparent;">
                <div class="date-input-group">
                    <span class="date-label">Global Desde</span>
                    <input type="date" id="f_ini_master" class="smart-input" value="<?= $fechaInicioMes ?>">
                </div>
                <span class="separator">-</span> 
                <div class="date-input-group">
                    <span class="date-label">Hasta</span>
                    <input type="date" id="f_fin_master" class="smart-input" value="<?= $fechaHoy ?>">
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; width: 100%;">
                <button class="btn-master" onclick="aplicarFiltroGlobal()" style="flex: 1;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Aplicar a Todo
                </button>
                
                <button class="btn-master" onclick="generarPDF()" id="btnGenerarPdf" style="background: #1e293b; flex: 1;">
                    <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                </button>
            </div>
        </div>

        <div id="pdfLoading" style="display: none; align-items: center; gap: 10px; color: #ff3366; font-weight: 600; margin-top: 10px;">
            <i class="fa-solid fa-circle-notch fa-spin"></i> Generando reporte, por favor espera...
        </div>
    </div>

    <div class="kpi-grid-50">
        
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-title-box">
                    <div class="kpi-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <h6 class="kpi-name">Ingresos Brutos</h6>
                </div>
                
                <div class="smart-filter">
                    <div class="fechas-wrapper">
                        <div class="date-input-group">
                            <input type="date" id="f_ini" class="smart-input" value="<?= $fechaInicioMes ?>" onchange="validarFechas()">
                        </div>
                        <span class="separator">-</span> 
                        <div class="date-input-group">
                            <input type="date" id="f_fin" class="smart-input" value="<?= $fechaHoy ?>" onchange="validarFechas()">
                        </div>
                    </div>
                    <button class="smart-btn" onclick="cargarIngresos()"><i class="fa-solid fa-search"></i></button>
                </div>
            </div>

            <div class="kpi-valor-principal" id="valTotal">
                <i class="fa-solid fa-circle-notch fa-spin text-muted fs-3"></i>
            </div>

            <div class="desglose-grid">
                <div class="desglose-caja">
                    <div class="desglose-header"><span class="punto punto-servicios"></span><span class="desglose-label">Servicios</span></div>
                    <div class="desglose-valor" id="valServicios">...</div>
                </div>
                <div class="desglose-caja">
                    <div class="desglose-header"><span class="punto punto-productos"></span><span class="desglose-label">Productos</span></div>
                    <div class="desglose-valor" id="valProductos">...</div>
                </div>
            </div>

            <div class="chart-box" style="height: 140px;"><canvas id="chartIngresos"></canvas></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-title-box">
                    <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;"><i class="fa-solid fa-calendar-check"></i></div>
                    <h6 class="kpi-name">Citas Procesadas</h6>
                </div>
                
                <div class="smart-filter">
                    <div class="fechas-wrapper">
                        <div class="date-input-group">
                            <input type="date" id="f_ini_citas" class="smart-input" value="<?= $fechaInicioMes ?>" onchange="validarFechasCitas()">
                        </div>
                        <span class="separator">-</span> 
                        <div class="date-input-group">
                            <input type="date" id="f_fin_citas" class="smart-input" value="<?= $fechaHoy ?>" onchange="validarFechasCitas()">
                        </div>
                    </div>
                    <button class="smart-btn" style="background: #3b82f6;" onclick="cargarCitas()"><i class="fa-solid fa-search"></i></button>
                </div>
            </div>

            <div class="kpi-valor-principal" id="valTotalCitas">
                <i class="fa-solid fa-circle-notch fa-spin text-muted fs-3"></i>
            </div>

            <div class="grid-3-citas">
                <div class="desglose-caja"><div class="desglose-header"><span class="punto" style="background-color: #10b981;"></span><span class="desglose-label">Finalizadas</span></div><div class="desglose-valor" id="valCitasFin">...</div></div>
                <div class="desglose-caja"><div class="desglose-header"><span class="punto" style="background-color: #ef4444;"></span><span class="desglose-label">Perdidas</span></div><div class="desglose-valor" id="valCitasPer">...</div></div>
                <div class="desglose-caja"><div class="desglose-header"><span class="punto" style="background-color: #f59e0b;"></span><span class="desglose-label">Canceladas</span></div><div class="desglose-valor" id="valCitasCan">...</div></div>
            </div>

            <div class="chart-box" style="height: 140px;"><canvas id="chartCitas"></canvas></div>
        </div>
    </div>

    <div class="kpi-grid-50">

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-title-box">
                    <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <h6 class="kpi-name">Top Servicios</h6>
                </div>
                
                <div class="smart-filter">
                    <div class="fechas-wrapper">
                        <div class="date-input-group">
                            <input type="date" id="f_ini_top" class="smart-input" value="<?= $fechaInicioMes ?>">
                        </div>
                        <span class="separator">-</span> 
                        <div class="date-input-group">
                            <input type="date" id="f_fin_top" class="smart-input" value="<?= $fechaHoy ?>">
                        </div>
                    </div>
                    <button class="smart-btn" style="background: #8b5cf6;" onclick="cargarTopServicios()">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div id="containerTopServicios" style="height: 240px; width: 100%; position: relative;">
                <canvas id="chartTopServicios"></canvas>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-title-box">
                    <div class="kpi-icon" style="background: rgba(255, 159, 64, 0.15); color: #f4b06d;"><i class="fa-solid fa-tags"></i></div>
                    <h6 class="kpi-name">Ventas / Categoría</h6>
                </div>
                
                <div class="smart-filter">
                    <div class="fechas-wrapper">
                        <div class="date-input-group">
                            <input type="date" id="f_ini_cat" class="smart-input" value="<?= $fechaInicioMes ?>" onchange="validarFechasCat()">
                        </div>
                        <span class="separator">-</span>
                        <div class="date-input-group">
                            <input type="date" id="f_fin_cat" class="smart-input" value="<?= $fechaHoy ?>" onchange="validarFechasCat()">
                        </div>
                    </div>
                    <button class="smart-btn" style="background: #f4b06d; color: white;" onclick="cargarVentasCat()"><i class="fa-solid fa-search"></i></button>
                </div>
            </div>
            
            <div id="containerVentasCat" class="chart-box" style="height: 250px; margin-top: 10px;">
                <canvas id="chartVentasCat"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
    let chartInstancia = null;

    function validarFechas() {
        const f_ini = document.getElementById('f_ini').value;
        const f_fin = document.getElementById('f_fin');
        if (f_fin.value < f_ini) { f_fin.value = f_ini; }
    }

    function formatearDinero(numero) {
        return '$ ' + parseFloat(numero).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function cargarIngresos() {
        const f_ini = document.getElementById('f_ini').value;
        const f_fin = document.getElementById('f_fin').value;
        
        document.getElementById('valTotal').innerHTML = '<i class="fa-solid fa-spinner fa-spin text-muted" style="font-size: 1.2rem;"></i>';
        document.getElementById('valServicios').innerHTML = '...';
        document.getElementById('valProductos').innerHTML = '...';

        fetch(`<?= $urlResumen ?>&f_ini=${f_ini}&f_fin=${f_fin}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('valTotal').innerHTML = formatearDinero(data.total);
                document.getElementById('valServicios').innerHTML = formatearDinero(data.servicios);
                document.getElementById('valProductos').innerHTML = formatearDinero(data.productos);
                
                renderizarGrafica(data.grafica); 
            } else {
                document.getElementById('valTotal').innerHTML = `<span style="font-size: 1rem; color: #ff3366;">${data.message}</span>`;
            }
        })
        .catch(err => {
            document.getElementById('valTotal').innerHTML = `<span style="font-size: 1rem; color: #ff3366;">Error</span>`;
        });
    }

    function renderizarGrafica(datosDb) {
        const labels = datosDb.map(d => d.etiqueta);
        const dataServicios = datosDb.map(d => parseFloat(d.total_servicios));
        const dataProductos = datosDb.map(d => parseFloat(d.total_productos));

        const ctx = document.getElementById('chartIngresos').getContext('2d');
        
        if (chartInstancia) chartInstancia.destroy();

        chartInstancia = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Servicios',
                        data: dataServicios,
                        borderColor: '#ff3366',
                        backgroundColor: 'rgba(255, 51, 102, 0.1)',
                        borderWidth: 3, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff', pointBorderColor: '#ff3366'
                    },
                    {
                        label: 'Productos',
                        data: dataProductos,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 6,
                        pointBackgroundColor: '#ffffff', pointBorderColor: '#3b82f6', borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { 
                        backgroundColor: '#1e293b', padding: 12,
                        titleFont: { size: 12, weight: 'normal' },
                        bodyFont: { size: 14, weight: 'bold' },
                        mode: 'index', intersect: false,
                        callbacks: { label: function(context) { return context.dataset.label + ': $ ' + context.raw.toFixed(2); } }
                    }
                },
                scales: {
                    x: { display: true, grid: { display: false }, ticks: { color: '#94a3b8', font: {size: 10}, maxRotation: 0, maxTicksLimit: 6 } },
                    y: { display: false, min: 0 }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });
    }

    let chartInstanciaCitas = null;

    function validarFechasCitas() {
        const f_ini = document.getElementById('f_ini_citas').value;
        const f_fin = document.getElementById('f_fin_citas');
        if (f_fin.value < f_ini) { f_fin.value = f_ini; }
    }

    function cargarCitas() {
        const f_ini = document.getElementById('f_ini_citas').value;
        const f_fin = document.getElementById('f_fin_citas').value;
        
        document.getElementById('valTotalCitas').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-muted" style="font-size: 1.2rem;"></i>';
        document.getElementById('valCitasFin').innerHTML = '...';
        document.getElementById('valCitasPer').innerHTML = '...';
        document.getElementById('valCitasCan').innerHTML = '...';

        fetch(`<?= $urlCitas ?>&f_ini=${f_ini}&f_fin=${f_fin}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('valTotalCitas').innerHTML = data.total;
                document.getElementById('valCitasFin').innerHTML = data.finalizadas;
                document.getElementById('valCitasPer').innerHTML = data.perdidas;
                document.getElementById('valCitasCan').innerHTML = data.canceladas;
                renderizarGraficaCitas(data.grafica); 
            } else {
                document.getElementById('valTotalCitas').innerHTML = `<span style="font-size: 1rem; color: #ff3366;">${data.message}</span>`;
            }
        }).catch(err => console.error(err));
    }

    function renderizarGraficaCitas(datosDb) {
        const labels = datosDb.map(d => d.etiqueta);
        const dataFin = datosDb.map(d => parseInt(d.finalizadas));
        const dataPer = datosDb.map(d => parseInt(d.perdidas));
        const dataCan = datosDb.map(d => parseInt(d.canceladas));

        const ctx = document.getElementById('chartCitas').getContext('2d');
        if (chartInstanciaCitas) chartInstanciaCitas.destroy();

        chartInstanciaCitas = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Finalizadas', data: dataFin, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2 },
                    { label: 'Perdidas', data: dataPer, borderColor: '#ef4444', backgroundColor: 'transparent', borderWidth: 2, tension: 0.4, pointRadius: 2, borderDash: [4, 4] },
                    { label: 'Canceladas', data: dataCan, borderColor: '#f59e0b', backgroundColor: 'transparent', borderWidth: 2, tension: 0.4, pointRadius: 2, borderDash: [2, 2] }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { 
                        backgroundColor: '#1e293b', padding: 12, mode: 'index', intersect: false,
                        callbacks: { label: function(c) { return c.dataset.label + ': ' + c.raw; } }
                    }
                },
                scales: {
                    x: { display: true, grid: { display: false }, ticks: { color: '#94a3b8', font: {size: 10}, maxRotation: 0, maxTicksLimit: 6 } },
                    y: { display: false, min: 0 }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });
    }

    let chartInstanciaTopServ = null;

    function cargarTopServicios() {
        const f_ini = document.getElementById('f_ini_top').value;
        const f_fin = document.getElementById('f_fin_top').value;
        const container = document.getElementById('containerTopServicios');

        container.innerHTML = '<div class="empty-chart-container"><i class="fa-solid fa-circle-notch fa-spin"></i><p>Analizando...</p></div>';

        fetch(`<?= $urlTopServicios ?>&f_ini=${f_ini}&f_fin=${f_fin}`)
        .then(res => res.json())
        .then(data => {
            if(data.success && data.datos && data.datos.length > 0) {
                container.innerHTML = '<canvas id="chartTopServicios"></canvas>';
                renderizarBarrasServicios(data.datos);
            } else {
                container.innerHTML = `
                    <div class="empty-chart-container">
                        <i class="fa-solid fa-chart-bar"></i>
                        <p>No hay ventas en este rango</p>
                    </div>`;
            }
        });
    }

    function renderizarBarrasServicios(datosDb) {
        const labels = datosDb.map(d => d.etiqueta);
        const valores = datosDb.map(d => parseFloat(d.total));
        const ctx = document.getElementById('chartTopServicios').getContext('2d');
        
        if (chartInstanciaTopServ) chartInstanciaTopServ.destroy();

        chartInstanciaTopServ = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ingresos',
                    data: valores,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 8,
                    barThickness: 20
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { display: false } },
                    y: { ticks: { font: { size: 11, weight: '600' }, color: '#1e293b' }, grid: { display: false } }
                }
            }
        });
    }

    // ==========================================
    // LÓGICA PARA TARJETA 4 (VENTAS POR CATEGORÍA)
    // ==========================================
    let chartInstanciaVentasCat = null;

    function validarFechasCat() {
        const f_ini = document.getElementById('f_ini_cat').value;
        const f_fin = document.getElementById('f_fin_cat');
        if (f_fin.value < f_ini) { f_fin.value = f_ini; }
    }

    function cargarVentasCat() {
        const f_ini = document.getElementById('f_ini_cat').value;
        const f_fin = document.getElementById('f_fin_cat').value;
        const container = document.getElementById('containerVentasCat');

        container.innerHTML = '<div class="empty-chart-container"><i class="fa-solid fa-circle-notch fa-spin"></i><p>Analizando...</p></div>';

        fetch(`<?= $urlVentasCat ?>&f_ini=${f_ini}&f_fin=${f_fin}`)
        .then(res => res.json())
        .then(data => {
            if(data.success && data.datos && data.datos.length > 0) {
                container.innerHTML = '<canvas id="chartVentasCat"></canvas>';
                renderizarGraficaVentasCat(data.datos);
            } else {
                container.innerHTML = `
                    <div class="empty-chart-container">
                        <i class="fa-solid fa-chart-pie"></i>
                        <p>No hay datos en este rango</p>
                    </div>`;
            }
        }).catch(err => {
            container.innerHTML = '<div class="empty-chart-container"><p>Error de conexión</p></div>';
        });
    }

    function renderizarGraficaVentasCat(datosDb) {
        const labels = datosDb.map(d => d.etiqueta);
        const valores = datosDb.map(d => parseFloat(d.total));
        
        // Paleta de colores sin bordes para que se vea más fina
        const colores = ['#ff3366', '#3b82f6', '#ff9f40', '#10b981', '#8b5cf6', '#0ea5e9', '#f43f5e', '#14b8a6'];

        const ctx = document.getElementById('chartVentasCat').getContext('2d');
        if (chartInstanciaVentasCat) chartInstanciaVentasCat.destroy();

        chartInstanciaVentasCat = new Chart(ctx, {
            type: 'doughnut', 
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: colores,
                    borderWidth: 0, 
                    hoverOffset: 8 
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Dona delgada
                plugins: { 
                    legend: { 
                        display: true, 
                        position: 'bottom', // LEYENDA ABAJO para no aplastar el circulo
                        labels: { 
                            font: { size: 11, family: "'Poppins', sans-serif" }, 
                            color: '#64748b', 
                            usePointStyle: true, 
                            padding: 20 
                        } 
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 12, family: "'Poppins', sans-serif", weight: 'normal' },
                        bodyFont: { size: 14, weight: 'bold', family: "'Poppins', sans-serif" },
                        callbacks: {
                            label: function(context) {
                                // 1. Calcular el total de todas las categorías
                                let total = context.dataset.data.reduce((acumulador, valorActual) => acumulador + valorActual, 0);
                                
                                // 2. Obtener el valor de la rebanada actual
                                let valor = context.raw;
                                
                                // 3. Calcular el porcentaje
                                let porcentaje = ((valor / total) * 100).toFixed(1) + '%';
                                
                                // 4. Armar el mensaje final: "Nombre: $ 0.00 (XX.X%)"
                                let label = context.label || '';
                                if (label) { label += ': '; }
                                label += '$ ' + valor.toLocaleString('en-US', { minimumFractionDigits: 2 });
                                label += ' (' + porcentaje + ')'; // Agrega el porcentaje al final
                                
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    function aplicarFiltroGlobal() {
        const iniMaster = document.getElementById('f_ini_master').value;
        const finMaster = document.getElementById('f_fin_master').value;

        ['f_ini', 'f_ini_citas', 'f_ini_top', 'f_ini_cat'].forEach(id => document.getElementById(id).value = iniMaster);
        ['f_fin', 'f_fin_citas', 'f_fin_top', 'f_fin_cat'].forEach(id => document.getElementById(id).value = finMaster);

        cargarIngresos();
        cargarCitas();
        cargarTopServicios();
        cargarVentasCat(); // <-- NUEVO
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarIngresos();
        cargarCitas(); 
        cargarTopServicios();
        cargarVentasCat(); // <-- NUEVO
    });


    // ==========================================
    // LÓGICA PARA EXPORTAR A PDF (INVISIBLE Y PERFECTO)
    // ==========================================
    async function generarPDF() {
        const btnPdf = document.getElementById('btnGenerarPdf');
        const loading = document.getElementById('pdfLoading');
        
        // 1. Mostrar estado de carga (sin ocultar nada más)
        btnPdf.disabled = true;
        btnPdf.style.opacity = '0.5';
        loading.style.display = 'flex';

        // Subimos el scroll para evitar bugs visuales en html2canvas
        window.scrollTo(0, 0);

        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4'); // 'p' = Vertical (Portrait)
            
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const margin = 15; 
            let currentY = 30; // Altura inicial donde empezamos a pegar imágenes

            // 2. Colocar el Título del Reporte (Centrado y elegante)
            const f_ini = document.getElementById('f_ini_master').value;
            const f_fin = document.getElementById('f_fin_master').value;
            
            pdf.setFont("helvetica", "bold");
            pdf.setFontSize(18);
            pdf.text("Reporte Financiero TuLook360", pdfWidth / 2, 15, { align: "center" });
            pdf.setFont("helvetica", "normal");
            pdf.setFontSize(12);
            pdf.text(`Período: ${f_ini} hasta ${f_fin}`, pdfWidth / 2, 22, { align: "center" });

            // 3. CAPTURAR Y APILAR TARJETAS SILENCIOSAMENTE
            const cards = document.querySelectorAll('.kpi-card');
            
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                
                // Le tomamos foto a la tarjeta tal cual está (no altera la UI)
                const canvas = await html2canvas(card, {
                    scale: 2, 
                    useCORS: true,
                    backgroundColor: '#ffffff'
                });

                const imgData = canvas.toDataURL('image/png');
                
                // Calculamos el ancho de la tarjeta en el PDF
                let imgWidth = pdfWidth - (margin * 2);
                let imgHeight = (canvas.height * imgWidth) / canvas.width;

                // SEGURO ANTI-HOJAS EN BLANCO (Para Móviles)
                // Si la tarjeta es más alta que la hoja entera, la escalamos hacia abajo
                const maxAllowedHeight = pdfHeight - (margin * 2);
                if (imgHeight > maxAllowedHeight) {
                    imgHeight = maxAllowedHeight;
                    imgWidth = (canvas.width * imgHeight) / canvas.height;
                }

                // Si al sumar esta tarjeta nos pasamos del largo de la hoja, creamos hoja nueva
                if (currentY + imgHeight > pdfHeight - margin) {
                    pdf.addPage();
                    currentY = margin; 
                }

                // Centramos la tarjeta horizontalmente (útil si se encogió)
                const xPos = (pdfWidth - imgWidth) / 2;

                // Pegamos la imagen en el PDF
                pdf.addImage(imgData, 'PNG', xPos, currentY, imgWidth, imgHeight);
                
                // Bajamos la coordenada Y para la siguiente tarjeta
                currentY += imgHeight + 10; 
            }

            // 4. Guardar el archivo
            pdf.save(`Reporte_TuLook360_${f_ini}_al_${f_fin}.pdf`);

        } catch (error) {
            console.error("Error al generar PDF: ", error);
            alert("Ocurrió un error al generar el reporte.");
        } finally {
            // 5. Restaurar botón
            loading.style.display = 'none';
            btnPdf.disabled = false;
            btnPdf.style.opacity = '1';
        }
    }
</script>