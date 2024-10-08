$(document).ready(function () {
    // Comprobar si el usuario está logueado
    if (!sessionStorage.getItem('empleado')) {
        // Si no está logueado, redirigir al login
        window.location.href = '../index.html';
        return;
    }
    
    // Si existe el fichaje
    if (sessionStorage.getItem('fichaje')) {
        if (sessionStorage.getItem('fichaje')==="entrada") {
            $("#btnEntrada").prop("disabled", true);
            $("#btnSalida").prop("disabled", false);
        } else {
            $("#btnEntrada").prop("disabled", false);
            $("#btnSalida").prop("disabled", false);
        }
    }

    var empleado = JSON.parse(sessionStorage.getItem('empleado'));

    // Fichar entrada
    $("#btnEntrada").click(function () {
        $.ajax({
            url: 'http://localhost/apifichajes/api.php?endpoint=fichajes',
            type: 'POST',
            contentType: 'application/json',
            
            data: JSON.stringify({
                empleado_id: empleado.id, // ID del empleado desde sessionStorage
                tipo: 'entrada' // Fichar entrada
            }),
            success: function (response) {
                alert("Entrada registrada correctamente.");
                // Deshabilitar botón de entrada y habilitar botón de salida
                $("#btnEntrada").prop("disabled", true);
                $("#btnSalida").prop("disabled", false);
                // Guardar fichaje
                sessionStorage.setItem("fichaje", "entrada");
            },
            error: function (xhr, status, error) {
                alert("Error al registrar la entrada.");
            }
        });
    });

    // Fichar salida
    $("#btnSalida").click(function () {
        $.ajax({
            url: 'http://localhost/apifichajes/api.php?endpoint=fichajes',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                empleado_id: empleado.id, // ID del empleado desde sessionStorage
                tipo: 'salida' // Fichar salida
            }),
            success: function (response) {
                alert("Salida registrada correctamente.");
                // Deshabilitar botón de salida y habilitar botón de entrada
                $("#btnEntrada").prop("disabled", false);
                $("#btnSalida").prop("disabled", true);
                // Guardar fichaje
                sessionStorage.setItem("fichaje", "salida");
            },
            error: function (xhr, status, error) {
                alert("Error al registrar la salida.");
            }
        });
    });
});
