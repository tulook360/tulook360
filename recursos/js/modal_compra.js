let prodActual = null;
let cantidadActual = 1;
let currentProdSlide = 0;
let prodImages = [];
let maxStockFisico = 1; 
let limiteEfectivo = 1; 
let promoData = { id: null, modalidad: null, precio: 0, puntos: 0, quedan: 0, esLimitada: false };

// A. ABRIR MODAL
window.abrirModalCompra = function(id, idPromo = null, modalidad = null, precioPromo = 0, puntosPromo = 0, quedanPromo = 0, esLimitada = false) {
    const modal = document.getElementById('modalProducto');
    const loader = document.getElementById('loaderProducto');
    
    modal.classList.add('active');
    loader.style.display = 'flex'; 

    // RESET DE VISIBILIDAD (Por si antes se bloqueó)
    document.querySelector('.prod-header-slider').style.display = 'flex';
    document.querySelector('.prod-footer').style.display = 'flex';
    document.querySelector('.biz-capsule-wrapper').style.display = 'flex';

    // Guardamos los datos de la oferta
    promoData = { 
        id: idPromo, 
        modalidad: modalidad, 
        precio: parseFloat(precioPromo) || 0, 
        puntos: parseInt(puntosPromo) || 0,
        quedan: parseInt(quedanPromo) || 0,
        esLimitada: esLimitada
    };

    document.getElementById('sliderImagesBox').innerHTML = '';

    fetch(`${CONFIG_PRODUCTO.urlInfo}&id=${id}`)
        .then(r => r.json())
        .then(resp => {
            loader.style.display = 'none'; 
            if(resp.success) {
                const puntosUsuario = parseInt(resp.data.puntos_cliente) || 0;

                // VALIDACIÓN DE PUNTOS
                if (idPromo && (modalidad === 'PUNTOS' || modalidad === 'MIXTO')) {
                    if (puntosUsuario < puntosPromo) {
                        return renderizarBloqueoPuntosProd(puntosUsuario, puntosPromo);
                    }
                }
                cargarDatosEnModal(resp.data);
            } else {
                alert(resp.error);
                cerrarModalProducto();
            }
        })
        .catch(err => {
            console.error(err);
            loader.style.display = 'none';
            alert("Error de conexión");
            cerrarModalProducto();
        });
};

// B. CARGAR DATOS (CORRECCIÓN: BOTONES FIJOS, SOLO INSERTAMOS IMÁGENES)
function cargarDatosEnModal(data) {
    prodActual = data;
    maxStockFisico = parseInt(data.pro_stock); // Aquí guardamos el stock real (ej: 11)
    cantidadActual = 1;

    // --- ÚNICA LÓGICA DE LÍMITES Y VISUALIZACIÓN ---
    if (promoData.id && promoData.esLimitada) {
        // Caso Promoción: Mostramos cupos (ej: 10) pero limitamos compra a 2
        document.getElementById('stockReal').innerHTML = `${promoData.quedan} cupos <span style="color:var(--primary); font-size:0.8em;">(Máx. 2 por persona)</span>`;
        
        // El límite para el botón (+) es el menor entre: Stock Físico, Cupos Promo y el máximo de 2
        limiteEfectivo = Math.min(maxStockFisico, promoData.quedan, 2);
    } else {
        // Caso Normal: Mostramos stock de bodega (ej: 11) y ese es el límite
        document.getElementById('stockReal').innerText = maxStockFisico;
        limiteEfectivo = maxStockFisico;
    }

    // Llenar textos básicos
    document.getElementById('logoBizProd').src = data.neg_logo || 'recursos/img/sin_foto.png';
    document.getElementById('nameBizProd').innerText = data.neg_nombre;
    document.getElementById('titleProd').innerText = data.pro_nombre;
    let descClean = data.pro_descripcion ? data.pro_descripcion.replace(/(<([^>]+)>)/gi, "") : "Sin descripción";
    document.getElementById('descProd').innerText = descClean.length > 120 ? descClean.substring(0,120)+'...' : descClean;
    document.getElementById('presProd').innerText = data.txt_presentacion;

    // Gestionar Slider de imágenes
    prodImages = (data.imagenes && data.imagenes.length > 0) ? data.imagenes : ['recursos/img/sin_foto.png'];
    const sliderBox = document.getElementById('sliderImagesBox');
    sliderBox.innerHTML = '';
    currentProdSlide = 0;
    prodImages.forEach((url, idx) => {
        const img = document.createElement('img');
        img.src = url;
        img.className = (idx === 0) ? 'slide-prod-img active' : 'slide-prod-img';
        sliderBox.appendChild(img);
    });

    document.getElementById('btnPrevProd').style.display = prodImages.length > 1 ? 'flex' : 'none';
    document.getElementById('btnNextProd').style.display = prodImages.length > 1 ? 'flex' : 'none';

    document.getElementById('btnAddCart').disabled = false;
    actualizarCalculos(); 
}

// C. MOVER SLIDER (FUNCIÓN FALTANTE AGREGADA)
window.moveProdSlider = function(dir) {
    const slides = document.querySelectorAll('.slide-prod-img');
    if(slides.length <= 1) return;
    
    slides[currentProdSlide].classList.remove('active');
    currentProdSlide = (currentProdSlide + dir + slides.length) % slides.length;
    slides[currentProdSlide].classList.add('active');
};

// D. CAMBIAR CANTIDAD
window.cambiarCant = function(delta) {
    let nueva = cantidadActual + delta;
    if(nueva >= 1 && nueva <= limiteEfectivo) {
        cantidadActual = nueva;
        actualizarCalculos();
    }
};

function actualizarCalculos() {
    document.getElementById('inputCantProd').value = cantidadActual;
    const valTotal = document.getElementById('totalPrice');
    
    // Lógica de Precios: Si hay promo usamos ese precio, si no, el normal
    let precioUnitario = promoData.id ? promoData.precio : parseFloat(prodActual.pro_precio);
    let totalDinero = precioUnitario * cantidadActual;
    let totalPuntos = promoData.id ? (promoData.puntos * cantidadActual) : 0;

    if (promoData.id) {
        if (promoData.modalidad === 'PUNTOS') {
            valTotal.innerHTML = `<span class="pts-badge-premium large"><i class="fa-solid fa-coins"></i> ${totalPuntos} PTS</span>`;
        } else if (promoData.modalidad === 'MIXTO') {
            valTotal.innerHTML = `
                <div style="font-size: 1.4rem; font-weight: 900; color: var(--primary);">$${totalDinero.toFixed(2)}</div>
                <span class="pts-badge-premium"><i class="fa-solid fa-plus"></i> ${totalPuntos} PTS</span>`;
        } else {
            valTotal.innerText = '$' + totalDinero.toFixed(2);
        }
    } else {
        valTotal.innerText = '$' + totalDinero.toFixed(2);
    }

    document.getElementById('btnMenos').disabled = (cantidadActual <= 1);
    document.getElementById('btnMas').disabled = (cantidadActual >= limiteEfectivo);
}

// E. AGREGAR AL CARRITO (CON ANIMACIÓN FLOTANTE)
window.agregarAlCarrito = function() {
    const btn = document.getElementById('btnAddCart');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Guardando...';

    const payload = {
        pro_id: prodActual.pro_id,
        cantidad: cantidadActual,
        prom_id: promoData.id
    };

    fetch(CONFIG_PRODUCTO.urlAgregar, {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(resp => {
        if(resp.success) {
            // 1. CERRAR EL MODAL PRIMERO
            cerrarModalProducto();

            // 2. EJECUTAR ANIMACIÓN
            animarVueloAlCarrito();

        } else {
            if(resp.code === 'NO_LOGIN') {
                if(confirm("Debes iniciar sesión para comprar. ¿Ir al login?")) {
                    window.location.href = 'index.php?c=auth&a=login';
                }
            } else {
                alert("Error: " + resp.error);
            }
        }
    })
    .catch(err => { console.error(err); alert("Error de red"); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
};

// --- FUNCIÓN DE ANIMACIÓN ---
function animarVueloAlCarrito() {
    // A. Crear la bola flotante
    const ball = document.createElement('div');
    ball.className = 'flying-ball';
    document.body.appendChild(ball);

    // B. Coordenadas de Inicio (Centro de la pantalla donde estaba el modal)
    const startX = window.innerWidth / 2;
    const startY = window.innerHeight / 2;

    // C. Coordenadas de Destino (El icono del carrito en el header)
    // NOTA: Asegúrate de que el botón del carrito en el header tenga ID="btnCartHeader"
    // Si no lo tiene, busca por clase. Asumiremos que el botón es el de la clase .btn-profile con el icono fa-cart-shopping
    const cartBtn = document.querySelector('.btn-profile i.fa-cart-shopping').parentElement;
    const rectCart = cartBtn.getBoundingClientRect();
    const endX = rectCart.left + (rectCart.width / 2);
    const endY = rectCart.top + (rectCart.height / 2);

    // D. Posicionar inicio
    ball.style.left = startX + 'px';
    ball.style.top = startY + 'px';

    // E. Forzar reflow para que el navegador note el cambio
    void ball.offsetWidth; 

    // F. Mover al destino
    const deltaX = endX - startX;
    const deltaY = endY - startY;
    
    ball.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0.2)`;
    ball.style.opacity = '0.5';

    // G. Al terminar (0.8s), borrar bola y actualizar contador
    setTimeout(() => {
        ball.remove();
        // LLAMAMOS A LA FUNCIÓN DEL HEADER PARA REFRESCAR EL NÚMERO
        if (typeof cargarCarrito === 'function') {
            cargarCarrito(false); 
        }
    }, 800);
}

window.cerrarModalProducto = function() {
    document.getElementById('modalProducto').classList.remove('active');
};


function renderizarBloqueoPuntosProd(tienes, necesitas) {
    document.querySelector('.prod-header-slider').style.display = 'none';
    document.querySelector('.prod-footer').style.display = 'none';
    document.querySelector('.biz-capsule-wrapper').style.display = 'none';

    document.querySelector('.prod-body').innerHTML = `
        <div class="no-points-wrapper step-content-animate" style="min-height: 350px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
            <div class="no-points-icon-box">
                <i class="fa-solid fa-coins" style="color: #00b894; font-size: 2.5rem;"></i>
                <i class="fa-solid fa-lock" style="font-size: 1rem;"></i>
            </div>
            <h2 class="no-points-title" style="font-family:'Kalam';">¡Casi lo tienes!</h2>
            <p class="no-points-text" style="font-size:0.9rem; color:#888;">Te faltan <b>${necesitas - tienes} puntos</b> para esta oferta.</p>
            
            <div class="points-comparison-card" style="display:flex; gap:20px; background:#f8f9fa; padding:15px; border-radius:15px; margin:20px 0;">
                <div><small style="display:block; color:#aaa;">Tu Saldo</small><b>${tienes} pts</b></div>
                <div style="border-left:1px solid #ddd; padding-left:20px;"><small style="display:block; color:#aaa;">Necesitas</small><b style="color:var(--primary);">${necesitas} pts</b></div>
            </div>

            <button class="btn-add-cart" onclick="cerrarModalProducto()" style="max-width: 200px; background: var(--dark);">ENTENDIDO, VOLVER</button>
        </div>
    `;
}