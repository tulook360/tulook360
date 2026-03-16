    /* recursos/js/modal_reserva.js */

    document.addEventListener('DOMContentLoaded', () => {
        // Lógica del Menú Lateral (Sidebar)
        const btnMenu = document.getElementById('btnMenu');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        const toggleMenu = () => {
            if(sidebar && overlay) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        };

        if(btnMenu) btnMenu.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);
    });

    // --- LÓGICA DEL MODAL DE RESERVA (WIZARD) ---

    const modalReserva = document.getElementById('modalReserva');
    const reservaBody  = document.getElementById('reservaContenido');
    const btnNext      = document.getElementById('btnSiguientePaso');
    const btnCancel    = document.getElementById('btnCancelReserva'); // Corregido selector ID

    // ESTADO DE LA RESERVA (Memoria temporal)
    let reservaState = {
        step: 1,
        data: null,
        selection: { sucursal: null, especialista: null, fecha: null, hora: null, notas: '' }
    };

    let reservaSliderInterval;


    // 1. FUNCIÓN GLOBAL PARA ABRIR EL MODAL (Ahora soporta Promociones)

    window.abrirModalServicio = function(idServicio, idPromo = null, modalidadPromo = null, precioPromo = 0, puntosPromo = 0) {
        if(!modalReserva) return;
        
        modalReserva.classList.add('active');

        // --- ESTO ES LO QUE TE FALTA PARA RESETEAR ---
        document.querySelector('.modal-header-reserva').style.display = 'flex';
        document.querySelector('.modal-footer-reserva').style.display = 'flex';
        document.querySelector('.steps-header').style.display = 'flex';
        // ----------------------------------------------

        document.getElementById('reservaTitulo').innerText = 'Cargando...';
        reservaBody.innerHTML = `<div class="loading-state"><i class="fa-solid fa-circle-notch fa-spin"></i><p>Conectando...</p></div>`;

        // Estado inicial
        reservaState = { 
            step: 1, data: null, 
            promo: { id: idPromo, modalidad: modalidadPromo, precio: parseFloat(precioPromo), puntos: parseInt(puntosPromo) },
            selection: { sucursal: null, especialista: null, fecha: null, hora: null, notas: '' } 
        };

        fetch(CONFIG_RESERVA.urlDetalle + '&id=' + idServicio)
            .then(r => r.json())
            .then(resp => {
                if(resp.success) {
                    reservaState.data = resp.data;
                    const info = resp.data.info;
                    const puntosUsuario = parseInt(resp.data.puntos_cliente) || 0;

                    // VALIDACIÓN DE PUNTOS
                    if (idPromo && (modalidadPromo === 'PUNTOS' || modalidadPromo === 'MIXTO')) {
                        if (puntosUsuario < puntosPromo) {
                            // ERROR AQUÍ: No escribas el HTML aquí, llama a la función de abajo
                            // que ya tiene las instrucciones de ocultar todo el modal.
                            renderizarBloqueoPuntos(puntosUsuario, puntosPromo);
                            return; // Detenemos aquí
                        }
                    }

                    // SI TODO ESTÁ BIEN, CARGAMOS EL MODAL NORMAL
                    document.querySelector('.steps-header').style.display = 'flex';
                    btnNext.style.display = 'block';
                    finalizarCargaExitosa(info, resp.data.imagenes);
                }
            });
    };


    function renderizarBloqueoPuntos(tienes, necesitas) {
        // ESTO ES LO QUE LIMPIA TODO EL MODAL
        document.querySelector('.modal-header-reserva').style.display = 'none';
        document.querySelector('.modal-footer-reserva').style.display = 'none';
        document.querySelector('.steps-header').style.display = 'none';

        reservaBody.innerHTML = `
            <div class="no-points-wrapper step-content-animate" style="height: 100vh; display: flex; flex-direction: column; justify-content: center;">
                <div class="no-points-icon-box">
                    <i class="fa-solid fa-coins"></i>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="no-points-title">¡Casi lo tienes!</h2>
                <p class="no-points-text">Para acceder a esta oferta exclusiva necesitas acumular un poco más de puntos en este negocio.</p>
                
                <div class="points-comparison-card">
                    <div class="p-comp-item">
                        <span class="p-comp-label">Tu Saldo</span>
                        <span class="p-comp-value">${tienes} pts</span>
                    </div>
                    <div class="p-comp-item" style="border-left: 1px solid #eee; padding-left: 20px;">
                        <span class="p-comp-label">Faltan</span>
                        <span class="p-comp-value needed">${necesitas - tienes} pts</span>
                    </div>
                </div>

                <button class="btn-siguiente-u" onclick="cerrarModalReserva()" style="width: 100%; max-width: 250px; background: var(--dark); margin: 0 auto;">
                    ENTENDIDO, VOLVER
                </button>
            </div>
        `;
    }
    

    // 2. NAVEGACIÓN
    if(btnNext) {
        btnNext.onclick = () => {
            if (reservaState.step === 1) {
                reservaState.step = 2; renderizarPaso2();
            } else if (reservaState.step === 2) {
                reservaState.step = 3; renderizarPaso3();
            } else if (reservaState.step === 3) {
                const notaInput = document.getElementById('citaNotas');
                reservaState.selection.notas = notaInput ? notaInput.value : ''; 
                reservaState.step = 4; renderizarPaso4();
            } else if (reservaState.step === 4) {
                ejecutarGuardadoFinal(); 
            }
            actualizarBotonesFooter(); 
        };
    }

    function ejecutarGuardadoFinal() {
        const btnNext = document.getElementById('btnSiguientePaso');
        btnNext.disabled = true;
        btnNext.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Guardando...';

        const qrToken = 'T360-' + Math.random().toString(36).substr(2, 9).toUpperCase();

        const datosReserva = {
            neg_id: reservaState.data.info.neg_id,
            suc_id: reservaState.selection.sucursal,
            serv_id: reservaState.data.info.serv_id,
            especialista_id: reservaState.selection.especialista,
            fecha: reservaState.selection.fecha,
            hora: reservaState.selection.hora,
            notas: reservaState.selection.notas || '', 
            qr_token: qrToken,
            // --- Lógica de precio y promo para el envío ---
            precio: reservaState.promo.id ? reservaState.promo.precio : reservaState.data.info.serv_precio,
            prom_id: reservaState.promo.id || null,

            puntos: reservaState.promo.id ? reservaState.promo.puntos : 0
        };

        // USAMOS LA CONFIGURACIÓN GLOBAL
        fetch(CONFIG_RESERVA.urlGuardar, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosReserva)
        })
        .then(r => r.json())
        .then(resp => {
            if(resp.success) {
                // 1. OCULTAMOS TODO LO QUE SOBRA PARA PANTALLA COMPLETA
                document.querySelector('.modal-header-reserva').style.display = 'none';
                document.querySelector('.modal-footer-reserva').style.display = 'none';
                document.querySelector('.steps-header').style.display = 'none';

                // 2. DIBUJAMOS EL ÉXITO CON ESTILO FULL-HEIGHT
                reservaBody.innerHTML = `
                    <div style="text-align:center; padding:20px; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;" class="step-content-animate">
                        
                        <div style="width: 85px; height: 85px; background: #e1ffe1; color: #00b894; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; font-size: 2.8rem; box-shadow: 0 10px 25px rgba(0,184,148,0.2);">
                            <i class="fa-solid fa-check"></i>
                        </div>

                        <h2 style="font-weight: 900; color: var(--dark); margin: 0 0 10px; font-size: 2rem; letter-spacing: -1px;">¡Reserva Lista!</h2>
                        
                        <p style="color: #888; font-size: 1rem; margin-bottom: 35px; max-width: 300px; line-height: 1.4;">
                            Tu cita en <b style="color:var(--dark)">${reservaState.data.info.neg_nombre}</b> ha sido confirmada con éxito.
                        </p>
                        
                        <div style="background: white; border-radius: 35px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.07); border: 1px solid #f1f2f6; width: 100%; max-width: 320px; margin-bottom: 35px; position: relative; overflow: hidden;">
                            <span style="font-size: 0.65rem; font-weight: 800; color: #ccc; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 15px;">Pase de Acceso</span>
                            
                            <div style="background: #f8f9fc; padding: 15px; border-radius: 25px; display: inline-block; margin-bottom: 20px;">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${qrToken}" alt="QR" style="mix-blend-mode: multiply; width: 160px; height: 160px;">
                            </div>

                            <div style="font-family: 'Space Mono', monospace; font-size: 1.3rem; font-weight: 800; color: var(--primary); letter-spacing: 4px; background: #fff0f3; padding: 10px; border-radius: 15px;">
                                ${qrToken}
                            </div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:12px; width: 100%; max-width: 280px;">
                            <button class="btn-siguiente-u btn-finish-glow" onclick="window.location.href='${CONFIG_RESERVA.urlMisCitas}'" style="width: 100%; font-size: 0.9rem; padding: 16px;">
                                <i class="fa-solid fa-calendar-days" style="margin-right:8px"></i> IR A MIS CITAS
                            </button>
                            
                            <button class="btn-cancelar-u" onclick="location.reload()" style="color: #636e72; font-size: 0.85rem; padding: 10px;">
                                Cerrar y seguir explorando
                            </button>
                        </div>
                    </div>`;
            } else {
                alert("Error al reservar: " + resp.error);
                btnNext.disabled = false;
                btnNext.innerText = "Confirmar Reserva";
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error de conexión al guardar.");
            btnNext.disabled = false;
            btnNext.innerText = "Confirmar Reserva";
        });
    }

    // 3. RENDERIZADO DE PASOS
    function renderizarPaso1() { // SUCURSALES
        actualizarIndicadorPasos(1);
        const sucursales = reservaState.data.sucursales;
        let html = '<h3 style="margin-top:0; font-weight:800; font-size:1.2rem;">1. ¿Dónde te atendemos?</h3>';
        
        if(!sucursales || sucursales.length === 0) {
            reservaBody.innerHTML = '<p style="text-align:center; margin-top:40px; color:#999;">No hay sucursales disponibles para este servicio.</p>'; 
            return;
        }

        html += '<div class="sucursales-grid">';
        sucursales.forEach(suc => {
            const selected = (reservaState.selection.sucursal === suc.suc_id) ? 'selected' : '';
            html += `
                <div class="sucursal-card-premium ${selected}" onclick="selectOption('sucursal', ${suc.suc_id}, this)">
                    <div class="suc-icon"><i class="fa-solid fa-shop"></i></div>
                    <div style="width:100%">
                        <div style="font-weight:800; color:var(--dark); font-size:0.95rem; margin-bottom:4px;">${suc.suc_nombre}</div>
                        <div style="font-size:0.75rem; color:#888; line-height:1.2;">
                            <i class="fa-solid fa-location-dot me-1"></i> ${suc.suc_direccion}
                        </div>
                    </div>
                </div>`;
        });
        html += '</div>';
        reservaBody.innerHTML = html;
        btnNext.innerText = "Siguiente";
        validarBotonSiguiente();
    }

    function renderizarPaso2() { // ESPECIALISTAS
        actualizarIndicadorPasos(2);
        const especialistas = reservaState.data.especialistas.filter(e => e.suc_id == reservaState.selection.sucursal);
        
        let html = '<h3 style="margin-top:0; font-weight:800; font-size:1.3rem; color:var(--dark);">2. Selecciona a tu Experto</h3>';
        html += '<p style="font-size:0.85rem; color:#888; margin-top:-5px;">Contamos con los mejores profesionales para ti.</p>';
        
        if(!especialistas || especialistas.length === 0) {
            html += `
                <div style="text-align:center; padding:40px; color:#999;">
                    <i class="fa-solid fa-user-slash" style="font-size:3rem; opacity:0.2; margin-bottom:15px;"></i>
                    <p>No hay especialistas disponibles en esta sucursal.</p>
                </div>`;
        } else {
            html += '<div class="specialists-grid">';
            especialistas.forEach(esp => {
                const foto = esp.usu_foto || `https://ui-avatars.com/api/?name=${esp.usu_nombres}&background=random&color=fff&size=128`;
                const selected = (reservaState.selection.especialista === esp.usu_id) ? 'selected' : '';
                
                // --- LOGICA DE CALIFICACIÓN REAL ---
                const votos = parseInt(esp.usu_votos || 0);
                const calif = parseFloat(esp.usu_calificacion || 0).toFixed(1);
                
                let htmlRating;

                // REGLA: Si tiene 0 votos (aunque tenga 5.0 por defecto), es NUEVO
                if (votos === 0) {
                    htmlRating = `<span style="background:#f1f2f6; color:#999; padding:2px 8px; border-radius:8px; font-size:0.65rem; font-weight:800; letter-spacing:0.5px;">NUEVO</span>`;
                } else {
                    // Si tiene votos, mostramos Estrella + Promedio + (Cantidad)
                    htmlRating = `<i class="fa-solid fa-star" style="color:#f1c40f;"></i> ${calif} <span style="color:#b2bec3; font-size:0.7rem; font-weight:600; margin-left:3px;">(${votos})</span>`;
                }

                html += `
                    <div class="specialist-card ${selected}" onclick="selectOption('especialista', ${esp.usu_id}, this)">
                        <div class="sp-avatar-wrapper">
                            <img src="${foto}" class="sp-avatar" alt="${esp.usu_nombres}">
                            <div class="sp-online-dot" title="Disponible"></div>
                        </div>
                        <div class="sp-name">${esp.usu_nombres.split(' ')[0]}</div>
                        
                        <div class="sp-rating" style="display:flex; align-items:center; justify-content:center;">
                            ${htmlRating} 
                        </div>
                    </div>`;
            });
            html += '</div>';
        }
        document.getElementById('reservaContenido').innerHTML = html;
        btnNext.innerText = "Siguiente";
        validarBotonSiguiente();
    }

    function renderizarPaso3() { // FECHA Y HORA
        actualizarIndicadorPasos(3);
        let html = '<h3 style="margin-top:0; font-weight:800; font-size:1.3rem; color:var(--dark);">3. Finaliza tu Reserva</h3>';
        
        const diasSemana = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        html += '<span class="reserva-section-title">Selecciona el día</span>';
        html += '<div class="date-scroller-premium">';
        
        // Obtenemos la hora actual del cliente
        const ahora = new Date();
        const horaActual = ahora.getHours();
        
        // Si ya son más de las 20:00 (8 PM), empezamos el calendario desde mañana (i = 1)
        // Ajusta el "20" a la hora máxima que consideres prudente para agendar el mismo día
        let inicioCalendario = (horaActual >= 20) ? 1 : 0;

        for(let i = inicioCalendario; i < 14; i++) {
            const d = new Date(); d.setDate(ahora.getDate() + i);
            const diaNombre = diasSemana[d.getDay()];
            const diaNum = d.getDate();
            
            // --- FIX DE ZONA HORARIA LOCAL ---
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const fechaFull = `${year}-${month}-${day}`; // Siempre será tu fecha real
            
            const selected = (reservaState.selection.fecha === fechaFull) ? 'selected' : '';
            html += `
                <div class="date-card-premium ${selected}" onclick="selectDate('${fechaFull}', this)">
                    <span class="dia-txt">${diaNombre}</span>
                    <span class="num-txt">${diaNum}</span>
                </div>`;
        }
        html += '</div>';

        html += '<span class="reserva-section-title" style="margin-top:10px;">Horarios disponibles</span>';
        html += '<div class="time-grid-premium" id="timeGridContainer">';
        if(reservaState.selection.fecha) {
            html += '<div style="grid-column:1/-1; text-align:center; padding:20px; color:#999;"><i class="fa-solid fa-circle-notch fa-spin"></i> Buscando...</div>';
            setTimeout(() => { consultarHorasAjax(reservaState.selection.fecha); }, 100);
        } else {
            html += '<div style="grid-column:1/-1; text-align:center; color:#bbb; padding:20px; font-size:0.85rem; background:#fdfdfd; border-radius:15px; border:2px dashed #eee;">Selecciona un día para ver turnos</div>';
        }
        html += '</div>';

        html += `<span class="reserva-section-title">Notas adicionales</span><div class="reserva-notas-box"><textarea id="citaNotas" placeholder="Escribe aquí si necesitas algo especial para tu servicio..." rows="2"></textarea></div>`;

        document.getElementById('reservaContenido').innerHTML = html;
        btnNext.innerText = "Confirmar Reserva";
        validarBotonSiguiente();
    }

    function renderizarPaso4() { // PASO FINAL: RESUMEN
        actualizarIndicadorPasos(4);
        
        const suc = reservaState.data.sucursales.find(s => s.suc_id == reservaState.selection.sucursal);
        const esp = reservaState.data.especialistas.find(e => e.usu_id == reservaState.selection.especialista);
        const info = reservaState.data.info;
        const promo = reservaState.promo; // Accedemos a los datos de la promoción

        // --- LÓGICA DE PRECIO DINÁMICO PARA EL RESUMEN ---
        let htmlPrecioLinea = ""; // Lo que sale al lado del nombre del servicio
        let htmlTotalFinal  = ""; // Lo que sale en el bloque de abajo

        if (promo.id) {
            if (promo.modalidad === 'PUNTOS') {
                htmlPrecioLinea = `<span class="pts-badge-premium"><i class="fa-solid fa-coins"></i> ${promo.puntos} PTS</span>`;
                htmlTotalFinal  = `<span class="pts-badge-premium large"><i class="fa-solid fa-coins"></i> ${promo.puntos} PTS</span>`;
            } else if (promo.modalidad === 'MIXTO') {
                htmlPrecioLinea = `$${parseFloat(promo.precio).toFixed(2)} <span class="pts-badge-premium" style="font-size:0.6rem;"><i class="fa-solid fa-plus"></i> ${promo.puntos} PTS</span>`;
                htmlTotalFinal  = `$${parseFloat(promo.precio).toFixed(2)} <span class="pts-badge-premium"><i class="fa-solid fa-plus"></i> ${promo.puntos} PTS</span>`;
            } else {
                htmlPrecioLinea = `$${parseFloat(promo.precio).toFixed(2)}`;
                htmlTotalFinal  = `$${parseFloat(promo.precio).toFixed(2)}`;
            }
        } else {
            // Caso Normal: Sin promoción
            htmlPrecioLinea = `$${parseFloat(info.serv_precio).toFixed(2)}`;
            htmlTotalFinal  = `$${parseFloat(info.serv_precio).toFixed(2)}`;
        }

        let html = `
            <h3 style="margin-top:0; font-weight:800; font-size:1.4rem; color:var(--dark);">4. Revisa tu Reserva</h3>
            <p style="font-size:0.9rem; color:#888; margin-top:-5px;">Todo listo para tu transformación.</p>
            
            <div class="resumen-grid-premium">
                <div class="resumen-card-left">
                    <div class="resumen-item">
                        <div class="resumen-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div><span class="resumen-label">Lugar</span><div class="resumen-value">${suc.suc_nombre}</div></div>
                    </div>
                    <div class="resumen-item">
                        <div class="resumen-icon-box"><i class="fa-solid fa-user-check"></i></div>
                        <div><span class="resumen-label">Especialista</span><div class="resumen-value">${esp.usu_nombres}</div></div>
                    </div>
                    <div class="resumen-item">
                        <div class="resumen-icon-box"><i class="fa-solid fa-calendar-day"></i></div>
                        <div><span class="resumen-label">Fecha</span><div class="resumen-value">${reservaState.selection.fecha}</div></div>
                    </div>
                    <div class="resumen-item">
                        <div class="resumen-icon-box"><i class="fa-solid fa-clock"></i></div>
                        <div><span class="resumen-label">Hora</span><div class="resumen-value">${reservaState.selection.hora}</div></div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:15px;">
                    <div class="resumen-card-right">
                        <span style="position:absolute; top:-12px; left:20px; background:var(--primary); color:white; font-size:0.65rem; padding:4px 12px; border-radius:50px; font-weight:800; letter-spacing:1px; box-shadow: 0 4px 10px rgba(255, 51, 102, 0.2);">RECETA DEL SERVICIO</span>
                        
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                            <h4 style="margin:0; font-weight:800; color:var(--dark); font-size:1.1rem;">${info.serv_nombre}</h4>
                            <span style="font-weight:900; color:var(--primary); font-size:1.2rem;">${htmlPrecioLinea}</span>
                        </div>

                        <div style="margin-top:20px; padding-top:15px; border-top:1px solid rgba(255, 51, 102, 0.1); display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:800; font-size:0.8rem; color:var(--dark);">TOTAL A PAGAR:</span>
                            <span style="font-size:1.6rem; font-weight:900; color:var(--dark);">${htmlTotalFinal}</span>
                        </div>
                    </div>
                    
                    ${reservaState.selection.notas ? `
                        <div style="background: #f8f9fc; border-radius: 20px; padding: 15px 20px; border: 1px solid #eee;">
                            <p style="font-size: 0.85rem; color: #555; margin:0;">${reservaState.selection.notas}</p>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        document.getElementById('reservaContenido').innerHTML = html;
        
        if (btnNext) {
            btnNext.innerText = "Confirmar y Reservar ✨";
            btnNext.disabled = false;
            btnNext.classList.add('btn-finish-glow');
        }
    }

    // 4. FUNCIONES DE APOYO
    window.selectOption = function(type, value, el) {
        reservaState.selection[type] = value;
        el.parentElement.querySelectorAll('.selected').forEach(item => item.classList.remove('selected'));
        el.classList.add('selected');
        validarBotonSiguiente();
    };

    window.selectDate = function(fecha, el) {
        reservaState.selection.fecha = fecha;
        reservaState.selection.hora = null;
        const scroller = el.parentElement;
        scroller.querySelectorAll('.date-card-premium').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        consultarHorasAjax(fecha);
        validarBotonSiguiente();
    };

    function consultarHorasAjax(fecha) {
        const containerHoras = document.getElementById('timeGridContainer');
        if(containerHoras) containerHoras.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:20px; color:#999;"><i class="fa-solid fa-circle-notch fa-spin"></i> Consultando agenda...</div>';
        
        // USAMOS LA CONFIGURACIÓN GLOBAL
        const params = `&servicio=${reservaState.data.info.serv_id}&especialista=${reservaState.selection.especialista}&fecha=${fecha}`;
        
        fetch(CONFIG_RESERVA.urlHorarios + params)
            .then(r => r.json())
            .then(resp => {
                if(resp.success) renderizarHorasDinamicas(resp);
                else if(containerHoras) containerHoras.innerHTML = `<p style="color:red; grid-column:1/-1; text-align:center;">${resp.error}</p>`;
            })
            .catch(err => {
                if(containerHoras) containerHoras.innerHTML = '<p style="color:red; grid-column:1/-1; text-align:center;">Error de conexión</p>';
            });
    }

    function renderizarHorasDinamicas(resp) {
        const container = document.getElementById('timeGridContainer');
        if(!container) return;
        
        if (resp.descanso) {
            container.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:30px 20px; background:#fff5f5; border-radius:15px; border:1px dashed #ff6b81;"><p style="margin:0; font-weight:800; color:#e14d64;">Día de Descanso</p></div>`;
            return;
        }
        const horas = resp.horas;
        if(!horas || horas.length === 0) {
            container.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:25px; background:#f8f9fc; border-radius:15px;"><p style="margin:0; font-weight:700; color:#999;">Sin turnos disponibles 📅</p></div>`;
            return;
        }
        let hHtml = '';
        horas.forEach(h => {
            hHtml += `<div class="time-pill-premium" onclick="selectOption('hora', '${h}', this)">${h}</div>`;
        });
        container.innerHTML = hHtml;
    }

    function validarBotonSiguiente() {
        if(!btnNext) return;
        let isValid = false;
        if (reservaState.step === 1 && reservaState.selection.sucursal) isValid = true;
        if (reservaState.step === 2 && reservaState.selection.especialista) isValid = true;
        if (reservaState.step === 3 && reservaState.selection.fecha && reservaState.selection.hora) isValid = true;
        if (reservaState.step === 4) { isValid = true; btnNext.classList.add('btn-finish-glow'); } 
        else { btnNext.classList.remove('btn-finish-glow'); }
        btnNext.disabled = !isValid;
    }

    window.cerrarModalReserva = function() {
        if(reservaSliderInterval) clearInterval(reservaSliderInterval);
        modalReserva.classList.remove('active');
        setTimeout(() => { reservaBody.innerHTML = ''; }, 400);
    };

    function startReservaAutoSlider() {
        if(reservaSliderInterval) clearInterval(reservaSliderInterval);
        reservaSliderInterval = setInterval(() => moveReservaSlider(1), 4000);
    }

    window.moveReservaSlider = function(dir) {
        const images = document.querySelectorAll('#reservaSlider img');
        if(images.length <= 1) return;
        let activeIndex = 0;
        images.forEach((img, i) => { if(img.classList.contains('active')) activeIndex = i; });
        images[activeIndex].classList.remove('active');
        let newIndex = (activeIndex + dir + images.length) % images.length;
        images[newIndex].classList.add('active');
    };

    window.navegarAtras = function() {
        if (reservaState.step > 1) {
            reservaState.step--;
            if (reservaState.step === 1) renderizarPaso1();
            if (reservaState.step === 2) renderizarPaso2();
            if (reservaState.step === 3) {
                renderizarPaso3();
                if(reservaState.selection.fecha) consultarHorasAjax(reservaState.selection.fecha);
            }
            actualizarBotonesFooter();
        }
    };

    function actualizarBotonesFooter() {
        const btnAtras = document.getElementById('btnAtrasPaso');
        if (btnAtras) btnAtras.style.display = (reservaState.step > 1) ? 'block' : 'none';
        actualizarIndicadorPasos(reservaState.step);
    }

    function actualizarIndicadorPasos(step) {
        const steps = [1,2,3,4].map(i => document.getElementById('reservaStep'+i));
        steps.forEach((s, i) => {
            if(!s) return;
            s.classList.remove('active', 'completed');
            if((i+1) === step) s.classList.add('active');
            if((i+1) < step) s.classList.add('completed');
        });
        const cont = document.getElementById('reservaContenido');
        if(cont) { cont.classList.remove('step-content-animate'); void cont.offsetWidth; cont.classList.add('step-content-animate'); }
    }


    // PEGA ESTO AL FINAL DE TU ARCHIVO modal_reserva.js
    function finalizarCargaExitosa(info, fotos) {
        // 1. Llenar los textos de la cabecera
        document.getElementById('reservaTitulo').innerText = info.serv_nombre;
        document.getElementById('reservaDesc').innerText = info.serv_descripcion || 'Sin descripción disponible.';
        document.getElementById('reservaDuracion').innerText = info.serv_duracion;
        document.getElementById('reservaEspera').innerText = info.serv_espera;
        document.getElementById('reservaBizName').innerText = info.neg_nombre;
        document.getElementById('reservaBizLogo').src = info.neg_logo || 'recursos/img/sin_foto.png';

        // 2. Lógica de precio Premium (Normal vs Promo)
        const precioSpan = document.getElementById('reservaPrecio');
        const p = reservaState.promo;
        
        if (p.id) {
            if (p.modalidad === 'PUNTOS') {
                // Caso: Canje total por puntos
                precioSpan.innerHTML = `<span class="pts-badge-premium large"><i class="fa-solid fa-coins"></i> ${p.puntos} PTS</span>`;
            } else if (p.modalidad === 'MIXTO') {
                // Caso: Dinero + Puntos (Imagen 78b427)
                precioSpan.innerHTML = `
                    <span style="text-decoration:line-through; font-size:0.7rem; color:#aaa; margin-right:5px;">$${parseFloat(info.serv_precio).toFixed(2)}</span>
                    <span style="color:#e84393; font-weight:900;">$${p.precio.toFixed(2)}</span>
                    <span class="pts-badge-premium"><i class="fa-solid fa-plus"></i> <i class="fa-solid fa-coins"></i> ${p.puntos} PTS</span>`;
            } else {
                // Caso: Solo Descuento
                precioSpan.innerHTML = `
                    <span style="text-decoration:line-through; font-size:0.7rem; color:#aaa; margin-right:5px;">$${parseFloat(info.serv_precio).toFixed(2)}</span>
                    <span style="color:#e84393; font-weight:900;">$${p.precio.toFixed(2)}</span>`;
            }
        } else {
            precioSpan.innerText = `$${parseFloat(info.serv_precio).toFixed(2)}`;
        }

        // 3. Reiniciar y llenar el Slider
        const sliderCont = document.getElementById('reservaSlider');
        sliderCont.innerHTML = '';
        const listaFotos = (fotos && fotos.length > 0) ? fotos : ['recursos/img/sin_foto.png'];
        listaFotos.forEach((url, i) => {
            const img = document.createElement('img');
            img.src = url;
            if(i === 0) img.className = 'active';
            sliderCont.appendChild(img);
        });

        // 4. Iniciar el Paso 1 (Sucursales)
        renderizarPaso1();
    }
