$(document).ready(function() {
    // Comprobar si el usuario está logueado
    if (!sessionStorage.getItem('empleado')) {
        // Si no está logueado, redirigir al login
        window.location.href = '../index.html';
        return;
    }

    // Cargar datos del empleado desde el sessionStorage
    let empleado = JSON.parse(sessionStorage.getItem('empleado'));

    // Si el empleado está en el sessionStorage, cargar la información en el formulario y la vista del perfil
    if (empleado) {
        // Llenar los campos del perfil con la información del empleado
        $('#nombre').val(empleado.nombre);
        $('#apellido').val(empleado.apellido);
        $('#correo').val(empleado.correo);
        $('#telefono').val(empleado.telefono);
        $('#cedula').val(empleado.cedula);

        // Actualizar los campos en la vista del perfil
        $('p:contains("Nombre:")').html(`<b>Nombre:</b> ${empleado.nombre}`);
        $('p:contains("Apellido:")').html(`<b>Apellido:</b> ${empleado.apellido}`);
        $('p:contains("Correo:")').html(`<b>Correo:</b> ${empleado.correo}`);
        $('p:contains("Teléfono:")').html(`<b>Teléfono:</b> ${empleado.telefono}`);
        $('p:contains("Cédula:")').html(`<b>Cédula:</b> ${empleado.cedula}`);
    } else {
        alert('No se encontró el empleado en la sesión. Por favor, inicia sesión nuevamente.');
    }

    // Al hacer clic en "Guardar Cambios"
    $('.modal-footer .btn-primary').click(function() {
        let datosActualizados = {
            id: empleado.id, // Mantener el ID
            nombre: $('#nombre').val(),
            apellido: $('#apellido').val(),
            correo: $('#correo').val(),
            telefono: $('#telefono').val(),
            cedula: $('#cedula').val(),
            contrasena: empleado.contrasena 
        };

        $.ajax({
            url: 'http://localhost/apifichajes/api.php?endpoint=empleados&id=' + empleado.id,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(datosActualizados),
            success: function(response) {
                alert('Perfil actualizado con éxito.');

                // Actualizar los datos del empleado en sessionStorage
                sessionStorage.setItem('empleado', JSON.stringify(datosActualizados));

                // Actualizar la vista del perfil con los datos nuevos
                $('p:contains("Nombre:")').html(`<b>Nombre:</b> ${datosActualizados.nombre}`);
                $('p:contains("Apellido:")').html(`<b>Apellido:</b> ${datosActualizados.apellido}`);
                $('p:contains("Correo:")').html(`<b>Correo:</b> ${datosActualizados.correo}`);
                $('p:contains("Teléfono:")').html(`<b>Teléfono:</b> ${datosActualizados.telefono}`);
                $('p:contains("Cédula:")').html(`<b>Cédula:</b> ${datosActualizados.cedula}`);

                // Cerrar el modal de edición
                $('#editProfileModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar el perfil:', error);
                alert('Hubo un problema al actualizar el perfil. Intenta de nuevo más tarde.');
            }
        });
    });
});
