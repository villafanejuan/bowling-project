<?php
namespace Src\Models;

require_once __DIR__ . '/../../config/bd.php';
use mysqli;

class EventoCategoria
{
    private $id;
    private $evento_id;
    private $categoria_id;
    private $created_at;
    private $updated_at;

    private $conn;

    public function __construct($data = [])
    {
        global $conn;
        $this->conn = $conn;

        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->evento_id = $data['evento_id'] ?? null;
            $this->categoria_id = $data['categoria_id'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }

    // ======= GETTERS =======
    public function getId() { return $this->id; }
    public function getEventoId() { return $this->evento_id; }
    public function getCategoriaId() { return $this->categoria_id; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // ======= SETTERS =======
    public function setId($id) { $this->id = $id; }
    public function setEventoId($evento_id) { $this->evento_id = $evento_id; }
    public function setCategoriaId($categoria_id) { $this->categoria_id = $categoria_id; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    // ======= MÉTODOS CRUD =======

    // Crear nueva relación evento-categoría
    public function crear()
    {
        $query = "INSERT INTO evento_categoria (evento_id, categoria_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $this->evento_id, $this->categoria_id);

        return $stmt->execute();
    }

    // Obtener todas las relaciones
    public function obtenerTodos()
    {
        $query = "SELECT * FROM evento_categoria";
        $result = $this->conn->query($query);

        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = new self($row);
        }

        return $datos;
    }

    // Obtener por ID
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM evento_categoria WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $resultado = $stmt->get_result()->fetch_assoc();

        return $resultado ? new self($resultado) : null;
    }

    // Actualizar relación
    public function actualizar()
    {
        $query = "UPDATE evento_categoria SET evento_id = ?, categoria_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iii', $this->evento_id, $this->categoria_id, $this->id);

        return $stmt->execute();
    }

    // Eliminar relación
    public function eliminar()
    {
        $query = "DELETE FROM evento_categoria WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);

        return $stmt->execute();
    }
}
