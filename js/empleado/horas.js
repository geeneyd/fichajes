$(document).ready(function () {
    // Comprobar si el usuario está logueado
    if (!sessionStorage.getItem('empleado')) {
        // Si no está logueado, redirigir al login
        window.location.href = '../index.html';
        return;
    }

    // Función para obtener el mes y el año actuales
    function obtenerMesAnoActual() {
        const fechaActual = new Date();
        const nombreMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const mes = nombreMeses[fechaActual.getMonth()];
        const ano = fechaActual.getFullYear();
        return `${mes} ${ano}`;
    }

    // Mostrar mes y año actual en el HTML
    $('#mes-actual').text(obtenerMesAnoActual());

    let empleado = JSON.parse(sessionStorage.getItem('empleado'));

    // Verificamos que el empleado exista
    if (empleado && empleado.id) {
        // Hacer la llamada al API para obtener los fichajes del empleado
        $.ajax({
            url: 'http://localhost/apifichajes/api.php',  // Ajustamos la URL base
            type: 'GET',
            data: {
                endpoint: 'fichajes',         // Parámetro del endpoint
                empleado_id: empleado.id      // ID del empleado obtenido de sessionStorage
            },
            success: function(response) {
                let tbody = $('table tbody');
                tbody.empty();
                let totalHoras = 0;  // Variable para acumular el total de horas trabajadas
        
                // Recorrer los resultados y agregarlos a la tabla
                response.forEach(function(fichaje) {
                    // Convertir las horas de entrada y salida a números decimales
                    let horasTrabajadas = parseFloat(fichaje.total_horas);

                    // Sumar las horas trabajadas
                    totalHoras += horasTrabajadas;

                    let fila = `
                        <tr>
                            <td>${fichaje.fecha}</td>
                            <td>${fichaje.hora_entrada || '-'}</td>
                            <td>${fichaje.hora_salida || '-'}</td>
                            <td>${fichaje.total_horas}</td>
                        </tr>
                    `;
                    tbody.append(fila);
                });

                // Mostrar el total de horas trabajadas en el HTML
                $('#total-horas').text(totalHoras.toFixed(2) + ' horas');
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener los fichajes:', error);
                alert('Hubo un problema al cargar los fichajes. Intenta de nuevo más tarde.');
            }
        });
    } else {
        alert('No se encontró el empleado en la sesión. Por favor, inicia sesión de nuevo.');
    }
});