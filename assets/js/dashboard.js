// Obtener elementos del DOM
const menuBtn = document.getElementById('menuBtn');
const mobileMenu = document.getElementById('mobileMenu');
const menuIcon = document.getElementById('menuIcon');

// Event listener para el botón hamburguesa
menuBtn.addEventListener('click', function() {
    // Toggle para mostrar/ocultar el menú
    mobileMenu.classList.toggle('show');
    
    // Cambiar icono del menú (animación visual)
    menuIcon.classList.toggle('rotate-90');
});

// Cerrar menú al hacer click en un enlace (mejor UX)
const mobileMenuLinks = mobileMenu.querySelectorAll('a');
mobileMenuLinks.forEach(link => {
    link.addEventListener('click', function() {
        mobileMenu.classList.remove('show');
        menuIcon.classList.remove('rotate-90');
    });
});

// Cerrar menú si se hace click fuera (opcional)
document.addEventListener('click', function(event) {
    const isClickInside = menuBtn.contains(event.target) || mobileMenu.contains(event.target);
    
    if (!isClickInside && mobileMenu.classList.contains('show')) {
        mobileMenu.classList.remove('show');
        menuIcon.classList.remove('rotate-90');
    }
});
