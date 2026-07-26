<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/GestionEnvios.php';

$datos = json_decode(file_get_contents('php://input'), true);

if (!is_array($datos)) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Cuerpo de la solicitud invalido']);
    exit;
}

$idPedido = isset($datos['idPedido']) ? (int) $datos['idPedido'] : 0;
$transportista = isset($datos['transportista']) ? trim((string) $datos['transportista']) : '';
$direccionDestino = isset($datos['direccionDestino']) ? trim((string) $datos['direccionDestino']) : '';

$gestionEnvios = new GestionEnvios($pdo);

try {
    $envio = $gestionEnvios->crearEnvio($idPedido, $transportista, $direccionDestino);
    http_response_code(201);
    echo json_encode(['exito' => true, 'datos' => $envio]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Error al crear envio: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno al crear el envio']);
}
