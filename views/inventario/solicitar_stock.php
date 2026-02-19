<?php $pageTitle = "Solicitar Stock"; ?>

<link href="https://fonts.googleapis.com/css2?family=Kalam:wght@700&family=Outfit:wght@400;500;700;800&display=swap" rel="stylesheet">

<style>
    /* --- VARIABLES --- */
    :root {
        --color-bg: #f8fafc;
        --color-card: #ffffff;
        --color-text: #0f172a;
        --color-muted: #94a3b8;
        
        --color-accent: #e84393; /* Tu rosa */
        --color-success: #10b981;
        --color-danger: #ef4444;
        --color-warning: #f59e0b;

        --radius: 20px;
        --shadow: 0 10px 30px -10px rgba(0,0,0,0.08);
    }

    body { background-color: var(--color-bg); font-family: 'Outfit', sans-serif; overflow-x: hidden; }
    
    /* --- HEADER --- */
    .shop-header {
        max-width: 1200px; margin: 0 auto; padding: 2rem 2rem 0;
        display: flex; justify-content: space-between; align-items: flex-end;
    }
    .shop-title {
        font-family: 'Kalam', cursive;
        font-size: 2.5rem; color: var(--color-text); margin: 0; line-height: 1;
    }
    .shop-subtitle { color: var(--color-muted); margin: 5px 0 0 0; font-size: 1rem; }

    /* --- BUSCADOR --- */
    .search-dock {
        position: sticky; top: 15px; z-index: 100;
        max-width: 600px; margin: 2rem auto; padding: 0 1rem;
    }
    .search-box {
        background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);
        padding: 12px 20px; border-radius: 50px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 1px solid rgba(255,255,255,0.5);
        display: flex; align-items: center; gap: 15px; transition: 0.3s;
    }
    .search-box:focus-within { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(232, 67, 147, 0.15); border-color: var(--color-accent); }
    
    .search-input {
        border: none; outline: none; width: 100%; background: transparent;
        font-size: 1rem; color: var(--color-text); font-family: 'Outfit', sans-serif;
    }
    .btn-clear { background: none; border: none; color: var(--color-muted); cursor: pointer; transition: 0.2s; }
    .btn-clear:hover { color: var(--color-danger); transform: scale(1.1); }

    /* --- GRID --- */
    .shop-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 25px; max-width: 1200px; margin: 0 auto; padding: 0 2rem 8rem 2rem;
    }

    /* TARJETA */
    .prod-card {
        background: var(--color-card); border-radius: var(--radius);
        box-shadow: var(--shadow); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative; overflow: hidden; display: flex; flex-direction: column;
        border: 1px solid rgba(255,255,255,0.5);
    }
    .prod-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.12); border-color: var(--color-accent); }
    
    /* Estado Agregado (IMPORTANTE: !important para que el JS no falle al ocultar) */
    .prod-card.added { display: none !important; }
    .prod-card.disabled { opacity: 0.6; pointer-events: none; filter: grayscale(1); }

    /* Badge Stock */
    .stock-tag {
        position: absolute; top: 12px; left: 12px; z-index: 2;
        padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.5px;
        backdrop-filter: blur(4px); box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .st-ok { background: rgba(220, 252, 231, 0.9); color: #166534; }
    .st-low { background: rgba(254, 249, 195, 0.9); color: #854d0e; }
    .st-out { background: rgba(254, 226, 226, 0.9); color: #991b1b; }

    /* Imagen */
    .img-zone {
        height: 180px; padding: 20px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        position: relative;
    }
    .prod-img { 
        width: 100%; height: 100%; object-fit: contain; 
        transition: transform 0.3s ease; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.08));
    }
    .prod-card:hover .prod-img { transform: scale(1.05); }

    /* Cuerpo */
    .info-zone { padding: 15px 20px 20px; flex: 1; display: flex; flex-direction: column; }
    
    .p-title { 
        font-size: 1.1rem; font-weight: 700; color: var(--color-text); 
        margin: 0 0 5px; line-height: 1.2;
    }
    .p-meta { font-size: 0.8rem; color: var(--color-muted); margin-bottom: 15px; font-weight: 500; }

    /* Controles */
    .actions-row { margin-top: auto; display: flex; flex-direction: column; gap: 10px; }
    
    .stepper {
        display: flex; align-items: center; justify-content: space-between;
        background: #f1f5f9; border-radius: 12px; padding: 4px;
    }
    .s-btn {
        width: 32px; height: 32px; border: none; border-radius: 8px;
        background: white; color: var(--color-text); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s;
    }
    .s-btn:hover { background: var(--color-accent); color: white; }
    
    .s-input {
        width: 50px; text-align: center; border: none; background: transparent;
        font-weight: 700; color: var(--color-text); font-size: 1rem;
        font-family: 'Kalam', cursive;
    }

    .btn-add {
        width: 100%; border: none; padding: 12px; border-radius: 12px;
        background: var(--color-text); color: white;
        font-weight: 700; font-size: 0.9rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: 0.3s; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
    }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15, 23, 42, 0.3); background: var(--color-accent); }
    .btn-add:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; transform: none; }

    /* --- FAB --- */
    .fab-cart {
        position: fixed; bottom: 30px; right: 30px; z-index: 1000;
        background: var(--color-text); color: white;
        padding: 15px 25px; border-radius: 50px; cursor: pointer;
        display: flex; align-items: center; gap: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3); transition: 0.3s;
    }
    .fab-cart:hover { transform: scale(1.05); background: var(--color-accent); }
    .fab-cart.pulse { animation: pulse 0.4s ease-in-out; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }

    .fab-badge {
        background: var(--color-accent); color: white; padding: 2px 8px;
        border-radius: 10px; font-weight: 800; font-size: 0.8rem;
        border: 2px solid var(--color-text);
    }

    /* --- SIDEBAR CARRITO --- */
    .cart-overlay { 
        position: fixed; top:0; left:0; width:100%; height:100%; 
        background:rgba(0,0,0,0.5); z-index:2000; 
        opacity:0; visibility:hidden; transition:0.3s; backdrop-filter: blur(3px); 
    }
    .cart-overlay.active { opacity:1; visibility:visible; }

    .cart-panel {
        position: fixed; top:0; right:0; width:100%; max-width:400px; height:100vh;
        background: white; z-index:2001; 
        /* CLAVE: Usar translate para asegurar que esté oculto */
        transform: translateX(100%); 
        transition: transform 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
        display: flex; flex-direction: column; box-shadow: -10px 0 40px rgba(0,0,0,0.2);
    }
    .cart-panel.active { transform: translateX(0); }

    .cp-header { padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .cp-title { font-family: 'Kalam', cursive; font-size: 1.8rem; margin: 0; color: var(--color-text); line-height: 1; }
    .cp-close { background: #f1f5f9; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; transition: 0.2s; color: var(--color-text); }
    .cp-close:hover { background: #e2e8f0; transform: rotate(90deg); }

    .cp-body { flex: 1; overflow-y: auto; padding: 20px; }
    
    .cart-item { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #f1f5f9; animation: slideIn 0.3s ease; }
    @keyframes slideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
    
    .ci-img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; background: #f8fafc; border: 1px solid #eee; }
    .ci-info { flex: 1; }
    .ci-name { font-weight: 700; color: var(--color-text); font-size: 0.95rem; margin-bottom: 4px; }
    .ci-unit { font-size: 0.8rem; color: var(--color-muted); }
    
    .ci-ctrl { display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 4px; border-radius: 8px; width: fit-content; margin-top: 5px; }
    .ci-btn { width: 24px; height: 24px; border: none; background: white; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .ci-val { font-weight: 700; font-size: 0.9rem; width: 20px; text-align: center; }

    .ci-del { color: var(--color-danger); cursor: pointer; padding: 8px; transition: 0.2s; }
    .ci-del:hover { transform: scale(1.2); }

    .cp-footer { padding: 25px; border-top: 1px solid #f1f5f9; background: #fcfcfc; }
    .btn-checkout { width: 100%; padding: 15px; border: none; border-radius: 15px; background: var(--color-accent); color: white; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 8px 20px rgba(232, 67, 147, 0.3); transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 10px; }
    .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(232, 67, 147, 0.4); }

    .empty-cart-msg { text-align: center; padding: 4rem 1rem; color: var(--color-muted); }
    .empty-cart-msg i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }

    /* --- MODAL CONFIRMACIÓN (NATIVO LOOK) --- */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 3000; /* Mayor que el sidebar (2001) */
        display: none; align-items: center; justify-content: center;
        backdrop-filter: blur(2px);
    }
    .modal-overlay.active { display: flex; } /* Clase para activarlo */

    .modal-box {
        background: white; width: 90%; max-width: 400px; padding: 30px;
        border-radius: 20px; text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        transform: scale(0.9); opacity: 0; transition: all 0.3s ease;
    }
    .modal-overlay.active .modal-box { transform: scale(1); opacity: 1; }

    .modal-title { font-family: 'Kalam', cursive; font-size: 1.8rem; margin: 0 0 10px; color: var(--color-text); }
    .modal-text { color: var(--color-muted); margin-bottom: 25px; }
    
    .modal-btns { display: flex; gap: 15px; justify-content: center; }
    .btn-m { padding: 12px 25px; border-radius: 50px; border: none; font-weight: 700; cursor: pointer; font-size: 0.9rem; }
    .btn-m-cancel { background: #f1f5f9; color: var(--color-text); }
    .btn-m-confirm { background: var(--color-accent); color: white; box-shadow: 0 5px 15px rgba(232, 67, 147, 0.3); }

    /* TOAST */
    .toast-box { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: #2d3436; color: white; padding: 12px 25px; border-radius: 50px; font-weight: 600; box-shadow: 0 10px 30px rgba(0,0,0,0.3); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 9000; opacity: 0; }
    .toast-box.show { transform: translateX(-50%) translateY(0); opacity: 1; }
</style>

<div class="shop-header">
    <div>
        <h1 class="shop-title">Realizar Pedido</h1>
        <p class="shop-subtitle">Solicita insumos a la Bodega Global.</p>
    </div>
</div>

<div class="search-dock">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass" style="color: var(--color-accent);"></i>
        <input type="text" id="buscadorGlobal" class="search-input" placeholder="¿Qué necesitas reponer hoy?" onkeyup="filtrarProductos()">
        <button class="btn-clear" id="btnClearSearch" onclick="limpiarBusqueda()" style="display:none;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<div class="shop-grid" id="gridProductos">
    <?php foreach ($catalogo as $prod): 
        $stock = (float)$prod['pro_stock'];
        $bloqueado = ($stock <= 0);
        $fotoUrl = $prod['pro_foto'] ?: 'recursos/img/sin_foto.png';
        
        $stClass = 'st-ok'; $stText = 'Disponible';
        if($stock <= 0) { $stClass = 'st-out'; $stText = 'Agotado'; }
        elseif($stock <= 10) { $stClass = 'st-low'; $stText = 'Poco Stock'; }
    ?>
    <div class="prod-card <?= $bloqueado ? 'disabled' : '' ?>" id="card_prod_<?= $prod['pro_id'] ?>" data-name="<?= strtolower($prod['pro_nombre']) ?>">
        
        <div class="stock-tag <?= $stClass ?>"><?= $stText ?></div>
        
        <div class="img-zone">
            <img src="<?= $fotoUrl ?>" class="prod-img" id="img_<?= $prod['pro_id'] ?>">
        </div>

        <div class="info-zone">
            <h3 class="p-title"><?= $prod['pro_nombre'] ?></h3>
            <div class="p-meta"><?= $prod['pro_unidad'] ?></div>

            <?php if(!$bloqueado): ?>
                <div class="actions-row">
                    <div class="stepper">
                        <button class="s-btn" onclick="ajustarCantGrid(<?= $prod['pro_id'] ?>, -1)"><i class="fa-solid fa-minus"></i></button>
                        <input type="text" id="cant_<?= $prod['pro_id'] ?>" value="1" class="s-input" readonly>
                        <button class="s-btn" onclick="ajustarCantGrid(<?= $prod['pro_id'] ?>, 1, <?= $stock ?>)"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <button class="btn-add" onclick="agregarAlCarrito(<?= $prod['pro_id'] ?>, '<?= htmlspecialchars($prod['pro_nombre'], ENT_QUOTES) ?>', <?= $stock ?>, '<?= $fotoUrl ?>', '<?= $prod['pro_unidad'] ?>')">
                        Agregar <i class="fa-solid fa-cart-plus"></i>
                    </button>
                </div>
            <?php else: ?>
                <button class="btn-add" disabled style="margin-top:auto;">No Disponible</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="fab-cart" onclick="toggleCart()" id="fabCart">
    <div style="position:relative;">
        <i class="fa-solid fa-cart-shopping" style="font-size:1.2rem;"></i>
        <span class="fab-badge" style="position:absolute; top:-10px; right:-10px;" id="fabCount">0</span>
    </div>
    <span style="font-weight:700;">Ver Pedido</span>
</div>

<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-panel" id="cartPanel">
    <div class="cp-header">
        <h2 class="cp-title">Tu Pedido</h2>
        <button class="cp-close" onclick="toggleCart()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    
    <div class="cp-body" id="listaCarrito"></div>

    <div class="cp-footer">
        <button class="btn-checkout" onclick="preguntarEnvio()">
            Confirmar Solicitud <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>

<div id="modalConfirm" class="modal-overlay">
    <div class="modal-box">
        <h3 class="modal-title">¿Confirmar Pedido?</h3>
        <p class="modal-text">Se enviará la solicitud a Bodega Central.</p>
        <div class="modal-btns">
            <button class="btn-m btn-m-cancel" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-m btn-m-confirm" onclick="ejecutarEnvio()">Sí, Enviar</button>
        </div>
    </div>
</div>

<div id="toast" class="toast-box">Notificación</div>

<script>
    let carrito = [];

    // --- INTERFAZ ---
    function toggleCart() {
        document.getElementById('cartPanel').classList.toggle('active');
        document.getElementById('cartOverlay').classList.toggle('active');
    }

    function filtrarProductos() {
        const txt = document.getElementById('buscadorGlobal').value.toLowerCase();
        document.getElementById('btnClearSearch').style.display = txt ? 'block' : 'none';
        
        document.querySelectorAll('.prod-card').forEach(card => {
            if(card.classList.contains('added')) { return; }
            const name = card.dataset.name;
            card.style.display = name.includes(txt) ? 'flex' : 'none';
        });
    }

    function limpiarBusqueda() {
        document.getElementById('buscadorGlobal').value = '';
        filtrarProductos();
    }

    function ajustarCantGrid(id, delta, max = 999) {
        const input = document.getElementById('cant_' + id);
        let val = parseInt(input.value) + delta;
        if(val < 1) val = 1;
        if(val > max) { val = max; mostrarToast(`⚠️ Máximo: ${max}`); }
        input.value = val;
    }

    // --- AGREGAR ---
    function agregarAlCarrito(id, nombre, max, foto, unidad) {
        const cant = parseInt(document.getElementById('cant_' + id).value);
        if(cant > max) return;

        animarVuelo(id);
        carrito.push({ id, nombre, cantidad: cant, max, foto, unidad });

        // Ocultar del grid
        setTimeout(() => {
            const card = document.getElementById('card_prod_' + id);
            card.classList.add('added'); 
            filtrarProductos();
        }, 50);

        document.getElementById('cant_' + id).value = 1;
        mostrarToast("✅ Agregado");
        actualizarCarritoUI();
    }

    // --- ELIMINAR DIRECTO ---
    function eliminarDelCarrito(index) {
        const item = carrito[index];
        // Restaurar en Grid
        const card = document.getElementById('card_prod_' + item.id);
        if(card) {
            card.classList.remove('added'); 
            // Validar si el filtro está activo para mostrarlo o no
            const txt = document.getElementById('buscadorGlobal').value.toLowerCase();
            const name = card.dataset.name;
            if(!name.includes(txt)) card.style.display = 'none';
            else card.style.display = 'flex';
        }

        carrito.splice(index, 1);
        actualizarCarritoUI();
        mostrarToast("Producto eliminado");
    }

    function actualizarCarritoUI() {
        const total = carrito.reduce((acc, item) => acc + item.cantidad, 0);
        document.getElementById('fabCount').innerText = total;
        
        const fab = document.getElementById('fabCart');
        fab.classList.remove('pulse'); void fab.offsetWidth; fab.classList.add('pulse');

        const lista = document.getElementById('listaCarrito');
        lista.innerHTML = '';

        if(carrito.length === 0) {
            lista.innerHTML = `<div class="empty-cart-msg"><i class="fa-solid fa-basket-shopping"></i><p>Tu pedido está vacío.</p></div>`;
            return;
        }

        carrito.forEach((item, index) => {
            const html = `
            <div class="cart-item">
                <img src="${item.foto}" class="ci-img">
                <div class="ci-info">
                    <div class="ci-name">${item.nombre}</div>
                    <div class="ci-unit">${item.unidad}</div>
                    <div class="ci-ctrl">
                        <button class="ci-btn" onclick="modificarCarrito(${index}, -1)"><i class="fa-solid fa-minus"></i></button>
                        <span class="ci-val">${item.cantidad}</span>
                        <button class="ci-btn" onclick="modificarCarrito(${index}, 1)"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
                <i class="fa-solid fa-trash-can ci-del" onclick="eliminarDelCarrito(${index})"></i>
            </div>`;
            lista.innerHTML += html;
        });
    }

    function modificarCarrito(index, delta) {
        const item = carrito[index];
        let nuevo = item.cantidad + delta;
        if(nuevo < 1) return;
        if(nuevo > item.max) { mostrarToast(`⚠️ Máximo: ${item.max}`); return; }
        item.cantidad = nuevo;
        actualizarCarritoUI();
    }

    // --- MODAL DE ENVÍO ---
    function preguntarEnvio() {
        if(carrito.length === 0) return mostrarToast("El pedido está vacío");
        // ABRIR EL MODAL
        document.getElementById('modalConfirm').classList.add('active');
    }

    function cerrarModal() {
        document.getElementById('modalConfirm').classList.remove('active');
    }

    function ejecutarEnvio() {
        cerrarModal(); // Cerrar modal primero
        
        const urlParams = new URLSearchParams(window.location.search);
        let url = "index.php?c=inventario&m=procesar_pedido";
        if(urlParams.get('token')) url += "&token=" + urlParams.get('token');

        fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ items: carrito })
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                mostrarToast("🚀 Pedido Enviado");
                setTimeout(() => window.location.href = "index.php?c=inventario&m=mis_pedidos" + (urlParams.get('token') ? "&token="+urlParams.get('token') : ""), 1500);
            } else {
                mostrarToast("❌ " + d.message);
            }
        })
        .catch(() => mostrarToast("❌ Error de conexión"));
    }

    // --- EFECTOS ---
    function mostrarToast(msg) {
        const t = document.getElementById('toast');
        t.innerText = msg; t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    function animarVuelo(id) {
        const img = document.getElementById('img_' + id);
        const fab = document.getElementById('fabCart');
        if(!img || !fab) return;

        const clone = img.cloneNode(true);
        const rect = img.getBoundingClientRect();
        const rectFab = fab.getBoundingClientRect();

        clone.style.position = 'fixed';
        clone.style.width = '80px'; clone.style.height = '80px';
        clone.style.top = rect.top + 'px'; clone.style.left = rect.left + 'px';
        clone.style.borderRadius = '50%'; clone.style.zIndex = 3000;
        clone.style.transition = 'all 0.6s cubic-bezier(0.2, 1, 0.3, 1)';
        clone.style.pointerEvents = 'none';
        
        document.body.appendChild(clone);
        
        setTimeout(() => {
            clone.style.top = (rectFab.top + 10) + 'px';
            clone.style.left = (rectFab.left + 10) + 'px';
            clone.style.transform = 'scale(0.1)';
            clone.style.opacity = 0;
        }, 20);

        setTimeout(() => clone.remove(), 600);
    }
</script>