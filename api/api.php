<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
// Manejo de solicitud OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); 
    exit();
}

require 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : null;

switch ($method) {
    case 'GET':
        if ($endpoint == 'empleados') {
            listarEmpleados($pdo);
        } elseif ($endpoint == 'fichajes' && isset($_GET['fecha'])) {
            consultarFichajesPorFecha($pdo, $_GET['fecha']);
        } elseif ($endpoint == 'fichajes' && isset($_GET['empleado_id'])) {
            consultarFichajesPorEmpleado($pdo, $_GET['empleado_id']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
        }
        break;

    case 'POST':
        if ($endpoint == 'empleados') {
            crearEmpleado($pdo);
        } elseif ($endpoint == 'fichajes') {
            registrarFichaje($pdo);
        } elseif ($endpoint == 'login') {
            login($pdo);
        }
        break;

    case 'PUT':
        if ($endpoint == 'empleados' && isset($_GET['id'])) {
            editarEmpleado($pdo, $_GET['id']);
        }
        break;

    case 'DELETE':
        if ($endpoint == 'empleados' && isset($_GET['id'])) {
            eliminarEmpleado($pdo, $_GET['id']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no soportado']);
        break;
}

function listarEmpleados($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nombre, apellido, cedula, correo, telefono FROM empleados");
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($empleados);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en el servidor']);
    }
}

function crearEmpleado($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['nombre'], $data['apellido'], $data['cedula'], $data['correo'], $data['contrasena'], $data['telefono'])) {
        http_response_code(400); 
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        return;
    }

    try {
        $hashed_password = password_hash($data['contrasena'], PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO empleados (nombre, apellido, cedula, correo, contrasena, telefono) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['nombre'], $data['apellido'], $data['cedula'], $data['correo'], $hashed_password, $data['telefono']]);

        http_response_code(201); 
        echo json_encode(['message' => 'Empleado creado correctamente']);
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error al crear empleado']);
    }
}

function editarEmpleado($pdo, $id) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['nombre'], $data['apellido'], $data['cedula'], $data['correo'], $data['telefono'])) {
        http_response_code(400); 
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        return;
    }

    try {
        // Verificar si el empleado existe
        $stmt = $pdo->prepare("SELECT id FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        $empleado = $stmt->fetch();

        if (!$empleado) {
            http_response_code(404); 
            echo json_encode(['error' => 'Empleado no encontrado']);
            return;
        }

        // Actualizar los datos del empleado
        $stmt = $pdo->prepare("UPDATE empleados SET nombre = ?, apellido = ?, cedula = ?, correo = ?, telefono = ? WHERE id = ?");
        $stmt->execute([$data['nombre'], $data['apellido'], $data['cedula'], $data['correo'], $data['telefono'], $id]);

        // Si se proporciona una nueva contraseña, la actualizamos.
        if (isset($data['contrasena']) && !empty($data['contrasena'])) {
            $hashed_password = password_hash($data['contrasena'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE empleados SET contrasena = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $id]);
        }

        echo json_encode(['message' => 'Empleado actualizado correctamente']);
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error al actualizar empleado']);
    }
}

function eliminarEmpleado($pdo, $id) {
    try {
        // Verificar si el empleado existe
        $stmt = $pdo->prepare("SELECT id FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        $empleado = $stmt->fetch();

        if (!$empleado) {
            http_response_code(404); 
            echo json_encode(['error' => 'Empleado no encontrado']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM empleados WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['message' => 'Empleado eliminado correctamente']);
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error al eliminar empleado']);
    }
}

function registrarFichaje($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['empleado_id'], $data['tipo']) || !in_array($data['tipo'], ['entrada', 'salida'])) {
        http_response_code(400); 
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        return;
    }

    try {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt = $pdo->prepare("INSERT INTO fichajes (empleado_id, fecha, hora, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['empleado_id'], $fecha, $hora, $data['tipo']]);

        http_response_code(201); 
        echo json_encode(['message' => 'Fichaje registrado correctamente']);
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error al registrar fichaje']);
    }
}

function consultarFichajesPorFecha($pdo, $fecha) {
    try {
        $stmt = $pdo->prepare("
            SELECT e.nombre, e.apellido, f.fecha, 
                MIN(CASE WHEN f.tipo = 'entrada' THEN f.hora END) AS hora_entrada,
                MAX(CASE WHEN f.tipo = 'salida' THEN f.hora END) AS hora_salida
            FROM fichajes f
            INNER JOIN empleados e ON f.empleado_id = e.id
            WHERE f.fecha = ?
            GROUP BY e.nombre, e.apellido, f.fecha
        ");
        $stmt->execute([$fecha]);
        $fichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($fichajes);
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error al consultar fichajes']);
    }
}

function consultarFichajesPorEmpleado($pdo, $empleado_id) {
    try {
        // Preparamos la consulta para obtener los fichajes del empleado
        $stmt = $pdo->prepare("
            SELECT f.fecha, 
                MIN(CASE WHEN f.tipo = 'entrada' THEN f.hora END) AS hora_entrada,
                MAX(CASE WHEN f.tipo = 'salida' THEN f.hora END) AS hora_salida
            FROM fichajes f
            WHERE f.empleado_id = ?
            GROUP BY f.fecha
            ORDER BY f.fecha
        ");

        // Ejecutamos la consulta con el ID del empleado
        $stmt->execute([$empleado_id]);
        $fichajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preparar el resultado con el total de horas trabajadas
        $resultados = [];

        foreach ($fichajes as $fichaje) {
            $horaEntrada = $fichaje['hora_entrada'];
            $horaSalida = $fichaje['hora_salida'];
            
            // Calcular las horas trabajadas solo si hay entrada y salida
            if ($horaEntrada && $horaSalida) {
                $horaEntradaDateTime = new DateTime($horaEntrada);
                $horaSalidaDateTime = new DateTime($horaSalida);
                $intervalo = $horaEntradaDateTime->diff($horaSalidaDateTime);
                $totalHoras = $intervalo->format('%H:%I:%S');
            } else {
                $totalHoras = '00:00:00'; // Si no hay entrada o salida, no se trabajó
            }

            // Agregar los resultados a la lista
            $resultados[] = [
                'fecha' => $fichaje['fecha'],
                'hora_entrada' => $horaEntrada,
                'hora_salida' => $horaSalida,
                'total_horas' => $totalHoras
            ];
        }

        // Devolver el resultado en formato JSON
        echo json_encode($resultados);
    } catch (Exception $e) {
        // Enviar un código de error en caso de excepción
        http_response_code(500); 
        echo json_encode(['error' => 'Error al consultar fichajes']);
    }
}

function login($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['correo'], $data['contrasena'])) {
        http_response_code(400); 
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM empleados WHERE correo = ?");
        $stmt->execute([$data['correo']]);
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($empleado && password_verify($data['contrasena'], $empleado['contrasena'])) {
            unset($empleado['contrasena']); // No devuelvo la contraseña
            echo json_encode(['message' => 'Login exitoso', 'empleado' => $empleado]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
        }
    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error al iniciar sesión']);
    }
}
?>
