<?php

namespace Src\Models;

require_once __DIR__ . '/../../config/bd.php';

use mysqli;
use DateTime;
use Exception;

class Evento
{
    private ?int $id;
    private string $titulo;
    private ?string $descripcion;
    private string $fecha;
    private ?string $imagen;
    private string $hora;
    private ?float $cover;
    private ?string $imagen_portada;
    private string $estado;
    private ?int $created_by;
    private string $created_at;
    private ?string $updated_at;

    private mysqli $conn;

    public function __construct(array $data = [])
    {
        global $conn;
        $this->conn = $conn;

        $this->id = $data['id'] ?? null;
        $this->titulo = isset($data['titulo']) ? trim($data['titulo']) : '';
        $this->descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;

        $this->fecha = $data['fecha'] ?? date('Y-m-d');
        $this->imagen = $data['imagen'] ?? null;
        $this->hora = $data['hora'] ?? '00:00:00';
        $this->cover = isset($data['cover']) ? (float)$data['cover'] : null;
        $this->imagen_portada = $data['imagen_portada'] ?? null;
        $this->estado = $data['estado'] ?? 'programado';
        $this->created_by = $data['created_by'] ?? null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /* ==============================
       VALIDACIONES INTERNAS
       ============================== */
    private function validar(): void
    {
        if (empty($this->titulo)) {
            throw new Exception("El título no puede estar vacío.");
        }

        // Validar estado
        $estados_validos = ['programado', 'finalizado', 'cancelado'];
        if (!in_array($this->estado, $estados_validos)) {
            throw new Exception("Estado inválido: {$this->estado}");
        }

        // Validar fecha
        $d = DateTime::createFromFormat('Y-m-d', $this->fecha);
        if (!$d || $d->format('Y-m-d') !== $this->fecha) {
            throw new Exception("Fecha inválida: {$this->fecha}");
        }

        // Validar hora
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $this->hora)) {
            throw new Exception("Hora inválida: {$this->hora}");
        }

        // Validar created_by si no es null
        if ($this->created_by !== null) {
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $this->created_by);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                throw new Exception("El usuario con id {$this->created_by} no existe.");
            }
        }

        // Validar cover
        if ($this->cover !== null && $this->cover < 0) {
            throw new Exception("El cover no puede ser negativo.");
        }
    }

    /* ==============================
       CRUD CON VALIDACIONES
       ============================== */

    public function countAll($conn)
    {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM eventos WHERE estado='programado'");
        $row = $stmt->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function create(): bool
    {
        $this->validar();

        $sql = "INSERT INTO eventos 
                (titulo, descripcion, fecha, imagen, hora, cover, imagen_portada, estado, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssdssis",
            $this->titulo,
            $this->descripcion,
            $this->fecha,
            $this->imagen,
            $this->hora,
            $this->cover,
            $this->imagen_portada,
            $this->estado,
            $this->created_by,
            $this->created_at
        );
        $ok = $stmt->execute();
        if ($ok) $this->id = $stmt->insert_id;
        return $ok;
    }

    public function update(): bool
    {
        if ($this->id === null) {
            throw new Exception("No se puede actualizar un evento sin ID.");
        }

        $this->validar();
        $this->updated_at = date('Y-m-d H:i:s');

        $sql = "UPDATE eventos SET 
                titulo=?, descripcion=?, fecha=?, imagen=?, hora=?, cover=?, imagen_portada=?, estado=?, updated_at=? 
                WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sssssdsssi",
            $this->titulo,
            $this->descripcion,
            $this->fecha,
            $this->imagen,
            $this->hora,
            $this->cover,
            $this->imagen_portada,
            $this->estado,
            $this->updated_at,
            $this->id
        );
        return $stmt->execute();
    }

    public static function delete(int $id): bool
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function show(int $id): ?Evento
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return new Evento($row);
        }
        return null;
    }

    public static function index(): array
    {
        global $conn;
        $res = $conn->query("SELECT * FROM eventos ORDER BY fecha DESC");
        $eventos = [];
        while ($row = $res->fetch_assoc()) {
            $eventos[] = new Evento($row);
        }
        return $eventos;
    }


    // metodos especificos 
    // Obtener próximos eventos
    public function obtenerProximosEventos($conn): array
    {
        $stmt = $conn->prepare("
        SELECT e.*, 
               COALESCE(e.imagen_portada, 
                        (SELECT f.ruta FROM fotos f WHERE f.evento_id = e.id LIMIT 1)
               ) AS imagen_portada
        FROM eventos e
        WHERE e.fecha >= CURDATE() AND e.estado = 'programado'
        ORDER BY e.fecha ASC
    ");
        $stmt->execute();
        $result = $stmt->get_result();
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
        $stmt->close();
        return $eventos;
    }

    // Obtener eventos pasados
    public function obtenerEventosPasados($conn, int $limit = 8): array
    {
        $stmt = $conn->prepare("
        SELECT e.*, 
               COALESCE(e.imagen_portada, 
                        (SELECT f.ruta FROM fotos f WHERE f.evento_id = e.id LIMIT 1)
               ) AS imagen_portada,
               (SELECT COUNT(*) FROM fotos f WHERE f.evento_id = e.id) AS cantidad_fotos
        FROM eventos e
        WHERE e.fecha < CURDATE() AND e.estado = 'finalizado'
        ORDER BY e.fecha DESC
        LIMIT ?
    ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $eventos[] = $row;
        }
        $stmt->close();
        return $eventos;
    }
}
