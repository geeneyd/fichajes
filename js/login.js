$(document).ready(function() {
    $('#loginForm').submit(function(event) {
        event.preventDefault(); // Prevenir el envío del formulario

        var email = $('#email').val();
        var password = $('#password').val();

        // Validar que no estén vacíos
        if (!email || !password) {
            mostrarMensaje('Por favor ingrese todos los campos.', 'danger');
            return;
        }

        // Realizar la solicitud AJAX a la API de login
        $.ajax({
            url: 'http://localhost/apifichajes/api.php?endpoint=login',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                correo: email,
                contrasena: password
            }),
            success: function(response) {
                if (response.message === 'Login exitoso') {
                    // Mostrar mensaje de éxito
                    mostrarMensaje('Inicio de sesión exitoso. Redirigiendo...', 'success');

                    // Guardar toda la información del empleado en sessionStorage
                    sessionStorage.setItem('empleado', JSON.stringify(response.empleado));

                    // Redirigir al usuario (cambiar a la URL que necesites)
                    setTimeout(function() {
                        window.location.href = 'empleado/index.html';
                    }, 2000);
                } else {
                    // Mostrar mensaje de error
                    mostrarMensaje('Credenciales inválidas.', 'danger');
                }
            },
            error: function(xhr) {
                // Manejar errores del servidor o de la API
                if (xhr.status === 400) {
                    mostrarMensaje('Por favor ingrese todos los campos.', 'danger');
                } else {
                    mostrarMensaje('Credenciales inválidas. Inténtalo de nuevo.', 'danger');
                }
            }
        });
    });

    function mostrarMensaje(mensaje, tipo) {
        $('#message').removeClass('d-none').addClass('alert-' + tipo).text(mensaje);
    }
});
