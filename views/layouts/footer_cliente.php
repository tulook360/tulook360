</div> <footer style="text-align:center; padding:30px; margin-top:40px; color:#999; font-size:0.85rem; border-top:1px solid #eee;">
    <p>© <?= date('Y') ?> <strong>TuLook360</strong>. Todos los derechos reservados.</p>
</footer>


<link rel="stylesheet" href="<?= asset('recursos/css/modal_compra.css') ?>">
<div class="modal-overlay-prod" id="modalProducto">
    <div class="modal-prod-container">
        
        <div id="loaderProducto" class="loading-overlay-modal" style="display:none;">
            <i class="fa-solid fa-circle-notch fa-spin fa-2x" style="color:var(--primary); margin-bottom:10px;"></i>
            <p style="color:#aaa; font-weight:600; font-size:0.8rem;">Cargando...</p>
        </div>

        <div class="prod-header-slider">
            <button id="btnPrevProd" class="btn-slide-modal prev" onclick="moveProdSlider(-1)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button id="btnNextProd" class="btn-slide-modal next" onclick="moveProdSlider(1)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div id="sliderImagesBox"></div>
        </div>
        
        <button class="btn-close-prod" onclick="cerrarModalProducto()"><i class="fa-solid fa-xmark"></i></button>

        <div class="prod-body">
            
            <div class="biz-capsule-wrapper">
                <div class="biz-capsule">
                    <img src="" id="logoBizProd" class="biz-logo-circle">
                    <div class="biz-info-col">
                        <span class="biz-label-tiny">Vendido por</span>
                        <span class="biz-name-bold" id="nameBizProd">--</span>
                    </div>
                </div>
            </div>

            <h2 id="titleProd" class="prod-title">--</h2>
            <div class="prod-badge" id="presProd">--</div>
            <p id="descProd" class="prod-desc">--</p>

            <div class="qty-selector">
                <button class="btn-qty" id="btnMenos" onclick="cambiarCant(-1)"><i class="fa-solid fa-minus"></i></button>
                <input type="number" id="inputCantProd" class="input-qty" value="1" readonly>
                <button class="btn-qty" id="btnMas" onclick="cambiarCant(1)"><i class="fa-solid fa-plus"></i></button>
            </div>
            
            <p style="font-size:0.75rem; color:#b2bec3; margin:0; font-weight:600;">
                Stock disponible: <span id="stockReal" style="color:var(--dark);">0</span>
            </p>
        </div>

        <div class="prod-footer">
            <div class="prod-price-col">
                <span class="lbl-total">Total a pagar</span>
                <span class="val-total" id="totalPrice">$0.00</span>
            </div>
            <button class="btn-add-cart" id="btnAddCart" onclick="agregarAlCarrito()">
                Agregar al Carrito <i class="fa-solid fa-cart-shopping"></i>
            </button>
        </div>
    </div>
</div>


<link rel="stylesheet" href="<?= asset('recursos/css/modal_reserva.css') ?>">
<div class="modal-overlay" id="modalReserva">
    <div class="modal-container">
        <div class="modal-header-reserva premium">
            <div class="reserva-visual-box">
                <div id="reservaSlider" class="reserva-slider"></div>
                <button class="slider-btn prev" onclick="moveReservaSlider(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="slider-btn next" onclick="moveReservaSlider(1)"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div class="reserva-info-box">
                <div class="reserva-biz-tag">
                    <img src="" id="reservaBizLogo" class="mini-biz-logo">
                    <span id="reservaBizName">--</span>
                </div>
                <h2 id="reservaTitulo" class="reserva-main-title">Cargando...</h2>
                <p id="reservaDesc" class="reserva-main-desc">--</p>
                <div class="reserva-stats-row">
                    <div class="stat-item"><i class="fa-regular fa-clock"></i> <span id="reservaDuracion">--</span> min</div>
                    <div class="stat-item"><i class="fa-solid fa-hourglass-start"></i> <span id="reservaEspera">--</span> min espera</div>
                    <div class="stat-item price"><span id="reservaPrecio">--</span></div>
                </div>
            </div>
        </div>
        <div class="modal-body-reserva">
            <div class="steps-header">
                <div class="step-line" style="width: 75%;"></div> 
                <div class="step-item active" id="reservaStep1"><div class="step-dot">1</div><span class="step-label">Lugar</span></div>
                <div class="step-item" id="reservaStep2"><div class="step-dot">2</div><span class="step-label">Experto</span></div>
                <div class="step-item" id="reservaStep3"><div class="step-dot">3</div><span class="step-label">Cita</span></div>
                <div class="step-item" id="reservaStep4"><div class="step-dot">4</div><span class="step-label">Resumen</span></div>
            </div>
            <div id="reservaContenido" class="step-content-animate">
                <div class="loading-state"><i class="fa-solid fa-circle-notch fa-spin"></i><p>Conectando...</p></div>
            </div>
        </div>
        <div class="modal-footer-reserva">
            <button class="btn-cancelar-u" id="btnCancelReserva" onclick="cerrarModalReserva()">Cancelar</button>
            <div class="nav-buttons-group">
                <button class="btn-atras-u" id="btnAtrasPaso" onclick="navegarAtras()">Atras</button>
                <button class="btn-siguiente-u" id="btnSiguientePaso" disabled>Siguiente</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="url_detalle_serv" value="<?= ruta_accion('publico', 'ver_detalle_servicio_ajax') ?>">
<input type="hidden" id="url_guardar_cita" value="<?= ruta_accion('publico', 'guardar_reserva_ajax') ?>">
<input type="hidden" id="url_ver_horarios" value="<?= ruta_accion('publico', 'ver_horarios_disponibles_ajax') ?>">
<input type="hidden" id="url_mis_citas"    value="<?= ruta_accion('publico', 'mis_citas') ?>">

<script>
    // Ahora cargamos las rutas desde los inputs, así nunca fallará la sintaxis
    const CONFIG_RESERVA = {
        urlDetalle:  document.getElementById('url_detalle_serv').value,
        urlGuardar:  document.getElementById('url_guardar_cita').value,
        urlHorarios: document.getElementById('url_ver_horarios').value,
        urlMisCitas: document.getElementById('url_mis_citas').value
    };
    
    const CONFIG_PRODUCTO = {
        urlInfo: '<?= ruta_accion("publico", "ver_info_producto_modal_ajax") ?>',
        urlAgregar: '<?= ruta_accion("publico", "agregar_carrito_ajax") ?>'
    };
</script>

<script src="<?= asset('recursos/js/modal_reserva.js') ?>"></script>
<script src="<?= asset('recursos/js/modal_compra.js') ?>"></script>


</body>
</html>