<?php

header("Content-Type: application/json");

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/cliente.php";
require_once __DIR__ . "/controllers/ClienteController.php";

function responderError($codigo, $mensaje){
    http_response_code($codigo);
    echo json_encode(["error" => $mensaje]);
}

function leerDatosCliente(){
    $campos = ["nombre", "apellido", "cedula", "correo", "telefono"];
    $datos = [];
    foreach ($campos as $campo) {
        $valor = $_POST[$campo] ?? "";
        $datos[$campo] = is_string($valor) ? trim($valor) : "";
    }
    return $datos;
}

function validarCliente($datos){
    if (in_array("", $datos, true)) {
        return "Todos los campos son requeridos";
    }
    if (mb_strlen($datos["nombre"]) > 100 || mb_strlen($datos["apellido"]) > 100) {
        return "El nombre y el apellido deben tener máximo 100 caracteres";
    }
    if (!preg_match('/^[0-9]{10}$/', $datos["cedula"])) {
        return "La cédula debe tener 10 números";
    }
    if (!filter_var($datos["correo"], FILTER_VALIDATE_EMAIL) || mb_strlen($datos["correo"]) > 150) {
        return "El correo no es válido";
    }
    if (!preg_match('/^[0-9]{7,10}$/', $datos["telefono"])) {
        return "El teléfono debe tener entre 7 y 10 números";
    }
    return "";
}

function leerId($origen){
    $id = $origen["id"] ?? "";
    return filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
}

$accion = $_GET["accion"] ?? 'todos';

$accionesSoloPost = ["guardar", "actualizar", "eliminar", "creartabla"];
if (in_array($accion, $accionesSoloPost) && $_SERVER["REQUEST_METHOD"] != 'POST') {
    responderError(405, "El metodo no es permitido");
    exit;
}

try {
$db = new Database();
$conexion = $db->conectar();

$controlador = new ClienteController($conexion);

switch ($accion){
    case "todos":
        $controlador->todos();
        break;
    case "uno":
        $id = leerId($_GET);
        if ($id !== false) {
            $controlador->uno($id);
        } else {
            responderError(400, "El id no es válido");
        }
        break;
    case "guardar":
        $datos = leerDatosCliente();
        $error = validarCliente($datos);
        if ($error == "") {
            $controlador->guardar($datos);
        } else {
            responderError(400, $error);
        }
        break;
    case "actualizar":
        $id = leerId($_POST);
        $datos = leerDatosCliente();
        $error = $id === false ? "El id no es válido" : validarCliente($datos);
        if ($error == "") {
            $controlador->actualizar($id, $datos);
        } else {
            responderError(400, $error);
        }
        break;
    case "eliminar":
        $id = leerId($_POST);
        if ($id !== false) {
            $controlador->eliminar($id);
        } else {
            responderError(400, "El id no es válido");
        }
        break;
    case "creartabla":
        $controlador->creartabla();
        break;

    default:
        responderError(404, "Accion no encontrada");
}


} catch (mysqli_sql_exception $error) {
    error_log($error->getMessage());
    $mensaje = $error->getCode() == 1146
        ? "La tabla clientes no existe. Pulsa Crear tabla para comenzar."
        : "No se pudo completar la operación en la base de datos.";
    responderError(500, $mensaje);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    responderError(500, "No se pudo conectar con la base de datos.");
}
