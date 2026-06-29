// Obtener elementos del DOM
const mobileMenu = document.getElementById('mobileMenu');
const menuBtn = document.querySelector('.navbar-toggler');

// Cerrar menú al hacer click en un enlace (mejor UX)
const mobileMenuLinks = mobileMenu.querySelectorAll('a');
mobileMenuLinks.forEach(link => {
    link.addEventListener('click', function() {
        // Bootstrap cierra automáticamente con su API
        const bsCollapse = new bootstrap.Collapse(mobileMenu, { toggle: false });
        bsCollapse.hide();
    });
});

// Cerrar menú si se hace click fuera
document.addEventListener('click', function(event) {
    const isClickInside = menuBtn.contains(event.target) || mobileMenu.contains(event.target);
    
    if (!isClickInside && mobileMenu.classList.contains('show')) {
        const bsCollapse = new bootstrap.Collapse(mobileMenu, { toggle: false });
        bsCollapse.hide();
    }
});
