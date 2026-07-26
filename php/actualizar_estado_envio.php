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

$idEnvio = isset($datos['idEnvio']) ? (int) $datos['idEnvio'] : 0;
$nuevoEstado = isset($datos['estado']) ? trim((string) $datos['estado']) : '';

$gestionEnvios = new GestionEnvios($pdo);

try {
    $envio = $gestionEnvios->actualizarEstado($idEnvio, $nuevoEstado);
    echo json_encode(['exito' => true, 'datos' => $envio]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(404);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Error al actualizar envio: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno al actualizar el envio']);
}
