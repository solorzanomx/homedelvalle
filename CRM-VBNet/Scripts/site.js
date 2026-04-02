/* =============================================
   CRM VB.NET - JavaScript Principal
   ============================================= */

// Toggle sidebar en móvil
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');

    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

// Cerrar sidebar al hacer resize a desktop
window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
    }
});

// Confirmar eliminación
function confirmDelete(message) {
    return confirm(message || '¿Está seguro de realizar esta acción?');
}
