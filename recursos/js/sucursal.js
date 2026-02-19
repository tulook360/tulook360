// recursos/js/sucursal.js

document.addEventListener('DOMContentLoaded', function() {
    initImagePreview();
    initMapLeaflet();
});

// 1. PREVISUALIZACIÓN DE IMAGEN (Sin cambios)
function initImagePreview() {
    const input = document.getElementById('inputFoto');
    const wrapper = document.getElementById('photoWrapper');
    const imgPreview = document.getElementById('imgPreview');

    if (!input || !wrapper || !imgPreview) return;

    input.addEventListener('change', function(e) {
        const file = this.files[0];
        if (file) {
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                alert('Formato no válido (Solo JPG/PNG).');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(evt) {
                imgPreview.src = evt.target.result;
                wrapper.classList.add('has-image');
            }
            reader.readAsDataURL(file);
        }
    });
}

// 2. MAPA LEAFLET + GEOLOCALIZACIÓN
function initMapLeaflet() {
    const mapContainer = document.getElementById('mapa');
    if (!mapContainer) return;

    const inputLat = document.getElementById('latitud');
    const inputLon = document.getElementById('longitud');
    const btnGeo = document.getElementById('btnGeo'); // Referencia al nuevo botón
    
    // Coordenadas iniciales
    let lat = inputLat.value ? parseFloat(inputLat.value) : -0.180653;
    let lng = inputLon.value ? parseFloat(inputLon.value) : -78.467834;
    let zoom = inputLat.value ? 16 : 13;

    // Iniciar Mapa
    var map = L.map('mapa').setView([lat, lng], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '© OpenStreetMap'
    }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    // Funciones de actualización
    function updatePosition(newLat, newLng) {
        marker.setLatLng([newLat, newLng]);
        inputLat.value = newLat;
        inputLon.value = newLng;
    }

    // Eventos del Mapa
    marker.on('dragend', function(e) {
        var pos = marker.getLatLng();
        updatePosition(pos.lat, pos.lng);
    });
    map.on('click', function(e) {
        updatePosition(e.latlng.lat, e.latlng.lng);
    });

    // ==========================================
    // NUEVO: Lógica del Botón "Usar mi ubicación"
    // ==========================================
    if (btnGeo && navigator.geolocation) {
        btnGeo.addEventListener('click', function() {
            // Feedback visual de carga
            btnGeo.classList.add('loading');
            const originalText = btnGeo.innerHTML;
            btnGeo.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Obteniendo ubicación...';

            navigator.geolocation.getCurrentPosition(
                // Éxito
                function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    
                    // Mover mapa y marcador
                    map.setView([userLat, userLng], 17); // Zoom cercano
                    updatePosition(userLat, userLng);
                    
                    // Restaurar botón
                    btnGeo.classList.remove('loading');
                    btnGeo.innerHTML = originalText;
                },
                // Error
                function(error) {
                    let msg = "No se pudo obtener la ubicación.";
                    if(error.code === 1) msg = "Debes permitir el acceso a la ubicación en tu navegador.";
                    alert(msg);
                    
                    // Restaurar botón
                    btnGeo.classList.remove('loading');
                    btnGeo.innerHTML = originalText;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    } else if (btnGeo) {
        // Si el navegador es muy viejo y no soporta geo
        btnGeo.style.display = 'none';
    }
    
    setTimeout(() => { map.invalidateSize(); }, 500);
}