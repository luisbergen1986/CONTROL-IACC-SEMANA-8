<?php
// Conexion a la base de datos usando PDO.
// Ajustar estos valores si cambia la configuracion de MySQL en XAMPP.

$host = 'localhost';
$nombreBD = 'tienda_ecommerce';
$usuario = 'root';
$clave = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$nombreBD};charset=utf8mb4",
        $usuario,
        $clave,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // No se expone el detalle del error al cliente, solo se registra en el log del servidor
    error_log('Error de conexion a BD: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['exito' => false, 'mensaje' => 'No se pudo conectar a la base de datos']));
}
