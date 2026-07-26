<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/GestionEnvios.php';

$gestionEnvios = new GestionEnvios($pdo);
$estado = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null;

try {
    $envios = $gestionEnvios->listarEnvios($estado);
    echo json_encode(['exito' => true, 'datos' => $envios]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Error al listar envios: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno al listar los envios']);
}
