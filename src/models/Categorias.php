<?php
namespace Src\Models;

require_once __DIR__ . '/../../config/bd.php';

class Categoria {
    private $conn;

    private ?int $id;
    private string $nombre;
    private ?string $descripcion;

    public function __construct(array $data = []) {
        global $conn;
        $this->conn = $conn;

        $this->id          = $data['id'] ?? null;
        $this->nombre      = $data['nombre'] ?? '';
        $this->descripcion = $data['descripcion'] ?? null;
    }

    // --- GETTERS ---
    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }

    // --- SETTERS ---
    public function setId(int $id): void { $this->id = $id; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(?string $descripcion): void { $this->descripcion = $descripcion; }

    // --- MÉTODOS CRUD ---

    // Crear una nueva categoría
    public function create(): bool {
        $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $this->nombre, $this->descripcion);
        return $stmt->execute();
    }

    // Actualizar una categoría existente
    public function update(): bool {
        if (!$this->id) return false;
        $sql = "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $this->nombre, $this->descripcion, $this->id);
        return $stmt->execute();
    }

    // Eliminar una categoría
    public function delete(): bool {
        if (!$this->id) return false;
        $sql = "DELETE FROM categorias WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }

    // Mostrar una categoría por ID
    public function show(int $id): ?array {
        $sql = "SELECT * FROM categorias WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    // Listar todas las categorías
    public function index(): array {
        $sql = "SELECT * FROM categorias ORDER BY id DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Guardar (decide entre CREATE o UPDATE automáticamente)
    public function save(): bool {
        return $this->id ? $this->update() : $this->create();
    }
}
