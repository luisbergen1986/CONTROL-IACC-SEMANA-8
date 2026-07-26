<?php

/**
 * Clase para gestionar el modulo de envios del proyecto de tienda online.
 * Permite crear envios asociados a un pedido, listarlos con filtro opcional
 * por estado, obtenerlos por id y actualizar su estado de seguimiento.
 */
class GestionEnvios
{
    private PDO $pdo;

    private const ESTADOS_VALIDOS = ['pendiente', 'en_transito', 'entregado', 'devuelto'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuevo envio asociado a un pedido existente.
     * Genera automaticamente un numero de seguimiento a partir del
     * transportista indicado.
     */
    public function crearEnvio(int $idPedido, string $transportista, string $direccionDestino): array
    {
        if ($idPedido <= 0) {
            throw new InvalidArgumentException('El id del pedido no es valido');
        }
        if (trim($transportista) === '' || trim($direccionDestino) === '') {
            throw new InvalidArgumentException('Transportista y direccion de destino son obligatorios');
        }

        $numeroSeguimiento = $this->generarNumeroSeguimiento($transportista);

        $sql = "INSERT INTO envios (id_pedido, transportista, numero_seguimiento, direccion_destino, estado, fecha_creacion)
                VALUES (:idPedido, :transportista, :numeroSeguimiento, :direccionDestino, 'pendiente', NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idPedido' => $idPedido,
            ':transportista' => $transportista,
            ':numeroSeguimiento' => $numeroSeguimiento,
            ':direccionDestino' => $direccionDestino,
        ]);

        return $this->obtenerEnvioPorId((int) $this->pdo->lastInsertId());
    }

    /**
     * Lista los envios registrados, con filtro opcional por estado.
     */
    public function listarEnvios(?string $estado = null): array
    {
        if ($estado !== null && !in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('Estado de filtro no valido');
        }

        $sql = "SELECT id_envio, id_pedido, transportista, numero_seguimiento, direccion_destino, estado, fecha_creacion, fecha_actualizacion
                FROM envios";
        $params = [];

        if ($estado !== null) {
            $sql .= " WHERE estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY fecha_creacion DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Obtiene un envio por su id. Lanza una excepcion si no existe.
     */
    public function obtenerEnvioPorId(int $idEnvio): array
    {
        $sql = "SELECT id_envio, id_pedido, transportista, numero_seguimiento, direccion_destino, estado, fecha_creacion, fecha_actualizacion
                FROM envios WHERE id_envio = :idEnvio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idEnvio' => $idEnvio]);
        $envio = $stmt->fetch();

        if ($envio === false) {
            throw new RuntimeException('Envio no encontrado');
        }

        return $envio;
    }

    /**
     * Actualiza el estado de un envio, validando que el estado indicado
     * pertenezca al conjunto de estados permitidos.
     */
    public function actualizarEstado(int $idEnvio, string $nuevoEstado): array
    {
        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado indicado no es valido');
        }

        // Se verifica que el envio exista antes de actualizar
        $this->obtenerEnvioPorId($idEnvio);

        $sql = "UPDATE envios SET estado = :estado, fecha_actualizacion = NOW() WHERE id_envio = :idEnvio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':estado' => $nuevoEstado,
            ':idEnvio' => $idEnvio,
        ]);

        return $this->obtenerEnvioPorId($idEnvio);
    }

    /**
     * Genera un numero de seguimiento a partir de las iniciales del
     * transportista, la fecha actual y un identificador aleatorio.
     * Uso interno de la clase.
     */
    private function generarNumeroSeguimiento(string $transportista): string
    {
        $prefijo = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $transportista), 0, 3));
        if ($prefijo === '') {
            $prefijo = 'ENV';
        }

        return $prefijo . '-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }
}
