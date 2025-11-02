<?php
namespace Src\Models;

require_once __DIR__ . '/../../config/bd.php';

use mysqli;
use Exception;

class Foto
{
    private ?int $id;
    private ?int $evento_id;
    private string $ruta_foto;
    private ?string $thumbnail;
    private int $orden;
    private ?string $created_at;
    private ?int $uploaded_by;
    private string $ruta;
    private ?string $descripcion;

    private mysqli $conn;

    public function __construct(array $data = [])
    {
        global $conn;
        $this->conn = $conn;

        $this->id          = $data['id'] ?? null;
        $this->evento_id   = $data['evento_id'] ?? null;
        $this->ruta_foto   = $data['ruta_foto'] ?? '';
        $this->thumbnail   = $data['thumbnail'] ?? null;
        $this->orden       = $data['orden'] ?? 0;
        $this->created_at  = $data['created_at'] ?? null;
        $this->uploaded_by = $data['uploaded_by'] ?? null;
        $this->ruta        = $data['ruta'] ?? '';
        $this->descripcion = $data['descripcion'] ?? null;
    }

    // ===== GETTERS =====
    public function getId(): ?int { return $this->id; }
    public function getEventoId(): ?int { return $this->evento_id; }
    public function getRutaFoto(): string { return $this->ruta_foto; }
    public function getThumbnail(): ?string { return $this->thumbnail; }
    public function getOrden(): int { return $this->orden; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUploadedBy(): ?int { return $this->uploaded_by; }
    public function getRuta(): string { return $this->ruta; }
    public function getDescripcion(): ?string { return $this->descripcion; }

    // ===== SETTERS =====
    public function setEventoId(?int $evento_id) { $this->evento_id = $evento_id; }
    public function setRutaFoto(string $ruta_foto) { $this->ruta_foto = $ruta_foto; }
    public function setThumbnail(?string $thumbnail) { $this->thumbnail = $thumbnail; }
    public function setOrden(int $orden) { $this->orden = $orden; }
    public function setUploadedBy(?int $uploaded_by) { $this->uploaded_by = $uploaded_by; }
    public function setRuta(string $ruta) { $this->ruta = $ruta; }
    public function setDescripcion(?string $descripcion) { $this->descripcion = $descripcion; }

    // ===== VALIDACIONES =====
    private function validar(): void
    {
        if (empty($this->ruta_foto)) {
            throw new Exception("La ruta de la foto no puede estar vacía.");
        }

        // Validar evento_id
        if ($this->evento_id !== null) {
            $stmt = $this->conn->prepare("SELECT id FROM eventos WHERE id = ?");
            $stmt->bind_param("i", $this->evento_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                throw new Exception("El evento con ID {$this->evento_id} no existe.");
            }
        } else {
            throw new Exception("El evento_id es obligatorio.");
        }

        // Validar uploaded_by
        if ($this->uploaded_by !== null) {
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $this->uploaded_by);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                throw new Exception("El usuario con ID {$this->uploaded_by} no existe.");
            }
        }

        // Validar orden
        if ($this->orden < 0) {
            throw new Exception("El orden no puede ser negativo.");
        }

        // Validar longitud de strings
        if (strlen($this->ruta_foto) > 255 || strlen($this->ruta) > 255) {
            throw new Exception("La ruta de la foto o ruta es demasiado larga.");
        }
    }

    // ===== CRUD =====

    public function crear(): bool
    {
        $this->validar();

        $query = "INSERT INTO fotos (evento_id, ruta_foto, thumbnail, orden, uploaded_by, ruta, descripcion) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "issiiss",
            $this->evento_id,
            $this->ruta_foto,
            $this->thumbnail,
            $this->orden,
            $this->uploaded_by,
            $this->ruta,
            $this->descripcion
        );
        $ok = $stmt->execute();
        if ($ok) $this->id = $stmt->insert_id;
        return $ok;
    }

    public function actualizar(): bool
    {
        if ($this->id === null) {
            throw new Exception("No se puede actualizar una foto sin ID.");
        }

        $this->validar();

        $query = "UPDATE fotos SET 
                    evento_id = ?, 
                    ruta_foto = ?, 
                    thumbnail = ?, 
                    orden = ?, 
                    uploaded_by = ?, 
                    ruta = ?, 
                    descripcion = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "issiissi",
            $this->evento_id,
            $this->ruta_foto,
            $this->thumbnail,
            $this->orden,
            $this->uploaded_by,
            $this->ruta,
            $this->descripcion,
            $this->id
        );
        return $stmt->execute();
    }

    public function eliminar(): bool
    {
        if ($this->id === null) {
            throw new Exception("No se puede eliminar una foto sin ID.");
        }

        $query = "DELETE FROM fotos WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    public function obtenerPorId(int $id): ?self
    {
        $stmt = $this->conn->prepare("SELECT * FROM fotos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return new self($row);
        }
        return null;
    }

    public function obtenerTodos(): array
    {
        $res = $this->conn->query("SELECT * FROM fotos ORDER BY orden ASC");
        $fotos = [];
        while ($row = $res->fetch_assoc()) {
            $fotos[] = new self($row);
        }
        return $fotos;
    }

    public function obtenerPorEvento(int $evento_id): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM fotos WHERE evento_id = ? ORDER BY orden ASC");
        $stmt->bind_param("i", $evento_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $fotos = [];
        while ($row = $res->fetch_assoc()) {
            $fotos[] = new self($row);
        }
        return $fotos;
    }

      public function countAll($conn) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM fotos");
        $row = $stmt->fetch_assoc();
        return $row['total'] ?? 0;
    }
}
