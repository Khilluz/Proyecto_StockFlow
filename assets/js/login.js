// Obtener elementos del DOM
const loginForm = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const errorMessage = document.getElementById('errorMessage');
const errorText = document.getElementById('errorText');

// Expresión regular para validar email
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Event listener para el formulario
loginForm.addEventListener('submit', function(event) {
    event.preventDefault();
    
    try {
        // Obtener valores del formulario
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const rol = document.querySelector('input[name="rol"]:checked');
        
        // Limpiar mensaje de error previo
        errorMessage.classList.add('d-none');
        errorText.innerHTML = '';
        
        // Validar que ningún campo esté vacío
        if (!email) {
            throw new Error('Por favor, ingresa tu correo electrónico.');
        }
        
        if (!password) {
            throw new Error('Por favor, ingresa tu contraseña.');
        }
        
        if (!rol) {
            throw new Error('Por favor, selecciona un rol.');
        }
        
        // Validar formato de email
        if (!emailRegex.test(email)) {
            throw new Error('Por favor, ingresa un correo electrónico válido.');
        }
        
        // Si todas las validaciones pasan
        console.log('Formulario válido:', {
            email: email,
            password: password,
            rol: rol.value
        });
        
        // Aquí iría la lógica para enviar al servidor
        alert(`✓ Iniciando sesión como ${rol.value}...`);
        
    } catch (error) {
        // Mostrar mensaje de error en el DOM
        errorText.innerHTML = error.message;
        errorMessage.classList.remove('d-none');
        
        // Log en consola para debugging
        console.error('Error de validación:', error.message);
    }
});

// Limpiar mensaje de error cuando el usuario comienza a escribir
emailInput.addEventListener('input', function() {
    if (!errorMessage.classList.contains('d-none')) {
        errorMessage.classList.add('d-none');
    }
});

passwordInput.addEventListener('input', function() {
    if (!errorMessage.classList.contains('d-none')) {
        errorMessage.classList.add('d-none');
    }
});
