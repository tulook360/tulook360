<div class="modal-overlay" id="modalConfirm">
    <div class="modal-box">
        
        <div class="modal-icon" id="modalIconBox">
            <i class="fa-solid fa-triangle-exclamation" id="modalIconI"></i>
        </div>
        
        <h3 class="modal-title" id="modalTitle">¿Estás seguro?</h3>
        <p class="modal-text" id="modalText">...</p>
        
        <div class="modal-actions">
            <button class="btn-modal btn-cancel" id="btnModalCancelar">Cancelar</button>
            <a href="#" class="btn-modal btn-confirm" id="btnModalConfirmar">Confirmar</a>
        </div>
    </div>
</div>

<script src="<?= asset('recursos/js/modal.js') ?>"></script>

<?php 
    // Verificamos si hay mensaje pendiente
    $flash = get_flash(); 
    if ($flash): 
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            mostrarNotificacion(
                "<?= addslashes($flash['titulo']) ?>", 
                "<?= addslashes($flash['mensaje']) ?>", 
                "<?= $flash['tipo'] ?>"
            );
        });
    </script>
<?php endif; ?>


<script>
// Lógica del Menú Móvil
document.addEventListener('DOMContentLoaded', () => {
    const mobileToggle = document.getElementById('mobileToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const sidebar      = document.getElementById('sidebar');
    const overlay      = document.getElementById('menuOverlay');

    function toggleMenu() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    if(mobileToggle) mobileToggle.addEventListener('click', toggleMenu);
    if(closeSidebar) closeSidebar.addEventListener('click', toggleMenu);
    if(overlay)      overlay.addEventListener('click', toggleMenu);
});

// NUEVO: Lógica de Carpetas (Acordeón)
function toggleFolder(element) {
    // El elemento padre es .nav-folder
    const folder = element.parentElement;
    folder.classList.toggle('open');
}
</script>

</body>
</html>