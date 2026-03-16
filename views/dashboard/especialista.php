<?php
$urlGanancias = ruta_accion("especialista", "mis_ganancias_ajax", [], false);
$fechaHoy = date('Y-m-d');
$fechaInicioMes = date('Y-m-01');
$nombreCorto = explode(' ', $_SESSION['usuario_nombre'])[0];
?>

<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* DISEÑO LÍQUIDO PREMIUM */
    :root {
        --c-bg: #f8fafc; --c-card: #ffffff; --c-text-main: #0f172a; --c-text-muted: #64748b;
        --c-border: #e2e8f0; --c-primary: #ff3366; --c-green: #10b981; --c-purple: #8b5cf6; --c-blue: #3b82f6;
    }
    .wallet-wrapper { padding: 2%; font-family: 'Poppins', sans-serif; width: 100%; box-sizing: border-box; }
    
    .master-header { display: flex; flex-direction: column; gap: 15px; margin-bottom: 3%; }
    .titulo-principal { font-family: 'Kalam', cursive; font-size: 2.2rem; font-weight: 700; color: var(--c-text-main); margin: 0 0 5px 0; line-height: 1.1; }
    .titulo-principal span { color: var(--c-green); }
    .subtitulo { font-size: 0.95rem; color: var(--c-text-muted); margin: 0; font-weight: 500; }

    /* FILTRO */
    .master-filter-box { background: var(--c-card); padding: 3%; border-radius: 20px; border: 1px solid var(--c-green); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.1); display: flex; flex-direction: column; gap: 10px; width: 100%; box-sizing: border-box; }
    .fechas-wrapper { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .date-input-group { display: flex; flex-direction: column; width: 45%; flex-grow: 1; }
    .date-label { font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px; text-align: center; }
    .smart-input { width: 100%; border: none; background: transparent; font-size: 0.8rem; font-weight: 600; color: var(--c-text-main); text-align: center; outline: none; }
    .separator { color: #cbd5e1; font-weight: bold; margin: 0 5px; }
    .btn-master { background: var(--c-green); color: white; border: none; border-radius: 50px; padding: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; width: 100%; transition: 0.2s; }
    .btn-master:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); }

    /* REJILLAS Y TARJETAS */
    .kpi-grid { display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 20px; }
    .kpi-card { background: var(--c-card); border-radius: 20px; padding: 20px; border: 1px solid var(--c-border); display: flex; flex-direction: column; box-sizing: border-box; }
    
    .kpi-title-box { display: flex; align-items: center; margin-bottom: 15px; }
    .kpi-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 12px; flex-shrink: 0;}
    .kpi-name { font-size: 0.85rem; font-weight: 700; color: var(--c-text-muted); text-transform: uppercase; margin: 0; }
    .kpi-valor { font-size: 2.5rem; font-weight: 800; color: var(--c-text-main); margin: 0; line-height: 1; }
    .kpi-sub { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-top: 5px; }

    /* HISTORIAL LISTA */
    .history-list { display: flex; flex-direction: column; gap: 10px; margin-top: 15px; }
    .history-item { background: #f8fafc; padding: 15px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #f1f5f9; transition: 0.2s; }
    .history-item:hover { border-color: var(--c-border); background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
    .hi-left { display: flex; flex-direction: column; }
    .hi-serv { font-weight: 700; color: var(--c-text-main); font-size: 0.95rem; }
    .hi-date { font-size: 0.75rem; color: var(--c-text-muted); margin-top: 2px; }
    .hi-right { text-align: right; }
    .hi-comision { font-weight: 800; color: var(--c-green); font-size: 1.1rem; }
    .hi-detalle { font-size: 0.7rem; color: #94a3b8; font-weight: 600; }
    .empty-box { text-align: center; padding: 40px 20px; color: #cbd5e1; }

    @media (min-width: 768px) {
        .wallet-wrapper { padding: 30px; }
        .kpi-grid { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    }
    @media (min-width: 1024px) {
        .master-header { flex-direction: row; justify-content: space-between; align-items: center; }
        .master-filter-box { flex-direction: row; width: max-content; padding: 6px 12px; border-radius: 50px; align-items: center; }
        .master-filter-box .date-input-group { flex-direction: column; align-items: center; gap: 2px; }
        .master-filter-box .date-label { margin: 0; font-size: 0.55rem; }
        .btn-master { width: max-content; padding: 10px 25px; }
    }
</style>

<div class="wallet-wrapper">
    <div class="master-header">
        <div>
            <h2 class="titulo-principal">Mi Billetera <span>Virtual</span></h2>
            <p class="subtitulo">Hola <?= $nombreCorto ?>, aquí está el resumen de tus comisiones.</p>
        </div>

        <div class="master-filter-box">
            <div class="fechas-wrapper" style="border: none; padding: 0; background: transparent;">
                <div class="date-input-group">
                    <span class="date-label">Desde</span>
                    <input type="date" id="f_ini" class="smart-input" value="<?= $fechaInicioMes ?>">
                </div>
                <span class="separator">-</span> 
                <div class="date-input-group">
                    <span class="date-label">Hasta</span>
                    <input type="date" id="f_fin" class="smart-input" value="<?= $fechaHoy ?>">
                </div>
            </div>
            <button class="btn-master" onclick="cargarBilletera()">
                <i class="fa-solid fa-search"></i> Filtrar
            </button>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="border-top: 4px solid var(--c-green);">
            <div class="kpi-title-box">
                <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--c-green);"><i class="fa-solid fa-wallet"></i></div>
                <h6 class="kpi-name">Mis Ganancias</h6>
            </div>
            <div class="kpi-valor" id="valComision"><i class="fa-solid fa-circle-notch fa-spin fs-4"></i></div>
            <div class="kpi-sub">Comisión neta generada</div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid var(--c-blue);">
            <div class="kpi-title-box">
                <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.15); color: var(--c-blue);"><i class="fa-solid fa-hand-scissors"></i></div>
                <h6 class="kpi-name">Servicios Realizados</h6>
            </div>
            <div class="kpi-valor" id="valServicios">...</div>
            <div class="kpi-sub">Atenciones finalizadas con éxito</div>
        </div>

        <div class="kpi-card" style="border-top: 4px solid var(--c-purple);">
            <div class="kpi-title-box">
                <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.15); color: var(--c-purple);"><i class="fa-solid fa-store"></i></div>
                <h6 class="kpi-name">Producción Total</h6>
            </div>
            <div class="kpi-valor" id="valGenerado">...</div>
            <div class="kpi-sub">Dinero aportado a la caja del negocio</div>
        </div>
    </div>

    <div class="kpi-card" style="margin-top: 10px;">
        <h6 class="kpi-name" style="margin-bottom: 15px;"><i class="fa-solid fa-list-check" style="margin-right:8px;"></i> Historial Detallado</h6>
        <div class="history-list" id="contenedorHistorial">
            </div>
    </div>
</div>

<script>
    function formatearDinero(numero) {
        return '$ ' + parseFloat(numero).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatFecha(fechaString) {
        const opciones = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(fechaString).toLocaleDateString('es-ES', opciones);
    }

    function cargarBilletera() {
        const f_ini = document.getElementById('f_ini').value;
        const f_fin = document.getElementById('f_fin').value;
        const contenedor = document.getElementById('contenedorHistorial');

        document.getElementById('valComision').innerHTML = '<i class="fa-solid fa-spinner fa-spin text-muted fs-4"></i>';
        document.getElementById('valServicios').innerHTML = '...';
        document.getElementById('valGenerado').innerHTML = '...';
        contenedor.innerHTML = '<div class="empty-box"><i class="fa-solid fa-circle-notch fa-spin fa-2x"></i></div>';

        fetch(`<?= $urlGanancias ?>&f_ini=${f_ini}&f_fin=${f_fin}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Actualizar Tarjetas
                document.getElementById('valComision').innerHTML = formatearDinero(data.totales.comision);
                document.getElementById('valServicios').innerHTML = data.totales.servicios;
                document.getElementById('valGenerado').innerHTML = formatearDinero(data.totales.generado);

                // Actualizar Lista Historial
                if(data.historial.length === 0) {
                    contenedor.innerHTML = '<div class="empty-box"><i class="fa-solid fa-file-invoice-dollar fa-3x" style="opacity:0.3; margin-bottom:10px;"></i><p>No tienes comisiones en este rango de fechas.</p></div>';
                } else {
                    let html = '';
                    data.historial.forEach(item => {
                        html += `
                        <div class="history-item">
                            <div class="hi-left">
                                <span class="hi-serv">${item.serv_nombre}</span>
                                <span class="hi-date">${formatFecha(item.det_ini)}</span>
                            </div>
                            <div class="hi-right">
                                <div class="hi-comision">+ ${formatearDinero(item.det_comision_monto)}</div>
                                <div class="hi-detalle">Ticket: $${item.det_precio} (${item.det_comision_porc}%)</div>
                            </div>
                        </div>`;
                    });
                    contenedor.innerHTML = html;
                }
            } else {
                document.getElementById('valComision').innerHTML = '<span style="font-size:1rem; color:red;">Error</span>';
                contenedor.innerHTML = `<div class="empty-box text-danger">${data.message}</div>`;
            }
        })
        .catch(err => {
            document.getElementById('valComision').innerHTML = '<span style="font-size:1rem; color:red;">Error</span>';
            contenedor.innerHTML = '<div class="empty-box text-danger">Error de conexión.</div>';
        });
    }

    document.addEventListener('DOMContentLoaded', cargarBilletera);
</script>