// recursos/js/carrusel.js

// Almacenar los intervalos de tiempo para cada carrusel
const carruselIntervals = {};

document.addEventListener('DOMContentLoaded', () => {
    initAutoPlay();
});

function initAutoPlay() {
    // Buscar todos los carruseles en la página
    const carruseles = document.querySelectorAll('.carousel');
    
    carruseles.forEach(carrusel => {
        // El ID del carrusel es 'carousel-123'
        const idCompleto = carrusel.id; 
        const idServicio = idCompleto.split('-')[1]; // Sacamos el número ID

        // Iniciar movimiento automático (cada 3.5 segundos)
        iniciarTimer(idServicio);

        // Pausar al pasar el mouse
        carrusel.addEventListener('mouseenter', () => {
            detenerTimer(idServicio);
        });

        // Reanudar al quitar el mouse
        carrusel.addEventListener('mouseleave', () => {
            iniciarTimer(idServicio);
        });
    });
}

function iniciarTimer(id) {
    // Evitar dobles timers
    if (carruselIntervals[id]) clearInterval(carruselIntervals[id]);
    
    carruselIntervals[id] = setInterval(() => {
        moveSlide(id, 1); // Mover 1 a la derecha
    }, 3500); // 3.5 segundos
}

function detenerTimer(id) {
    if (carruselIntervals[id]) {
        clearInterval(carruselIntervals[id]);
        delete carruselIntervals[id];
    }
}

// Función Manual (Flechas)
function moveSlide(servicioId, direction) {
    const carousel = document.getElementById('carousel-' + servicioId);
    if (!carousel) return;

    const items = carousel.querySelectorAll('.carousel-item');
    let activeIndex = -1;

    // Buscar activo actual
    items.forEach((item, index) => {
        if (item.classList.contains('active')) {
            activeIndex = index;
        }
    });

    if (activeIndex === -1) return; // Seguridad

    // Calcular siguiente índice (Cíclico)
    let newIndex = activeIndex + direction;
    if (newIndex < 0) newIndex = items.length - 1;
    if (newIndex >= items.length) newIndex = 0;

    // Aplicar cambio (La transición CSS de opacidad hará el resto)
    items[activeIndex].classList.remove('active');
    items[newIndex].classList.add('active');
}