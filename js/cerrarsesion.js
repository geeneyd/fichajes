$(document).ready(function() {
    // Comprobar si el usuario está logueado
    if (!sessionStorage.getItem('empleado')) {
        // Si no está logueado, redirigir al login
        window.location.href = '../index.html';
        return;
    }
    
    $('.nav-link:contains("Cerrar Sesión")').click(function(event) {
        event.preventDefault(); 
        sessionStorage.removeItem('empleado');
        sessionStorage.removeItem('fichaje');
        window.location.href = '../index.html';
    });
});
