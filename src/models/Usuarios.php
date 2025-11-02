<?php

namespace Src\Models;

require_once __DIR__ . '/../../config/bd.php';

use mysqli;
use Exception;

class Usuario
{
    private ?int $id;
    private string $username;
    private string $password;
    private string $rol; // 'admin' o 'editor'
    private ?string $nombre;
    private ?string $email;
    private string $created_at;
    private ?string $last_login;

    private mysqli $conn;

    public function __construct(array $data = [])
    {
        global $conn;
        $this->conn = $conn;

        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->rol = $data['rol'] ?? 'editor';
        $this->nombre = $data['nombre'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->last_login = $data['last_login'] ?? null;
    }

    /* ===================================================
       ================   GETTERS   =======================
       =================================================== */

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    public function getRol(): string
    {
        return $this->rol;
    }
    public function getNombre(): ?string
    {
        return $this->nombre;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
    public function getLastLogin(): ?string
    {
        return $this->last_login;
    }

    /* ===================================================
       ================   SETTERS   =======================
       =================================================== */

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    public function setRol(string $rol): void
    {
        $validos = ['admin', 'editor'];
        if (!in_array($rol, $validos)) {
            throw new Exception("Rol inválido: $rol");
        }
        $this->rol = $rol;
    }
    public function setNombre(?string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
    public function setLastLogin(?string $fecha): void
    {
        $this->last_login = $fecha;
    }

    /* ===================================================
       ================   CRUD MÉTODOS   =================
       =================================================== */

    /** CREATE */
    public function create(): bool
    {
        $sql = "INSERT INTO usuarios (username, password, rol, nombre, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $this->username, $this->password, $this->rol, $this->nombre, $this->email);
        $ok = $stmt->execute();
        if ($ok) $this->id = $stmt->insert_id;
        return $ok;
    }

    /** UPDATE */
    public function update(): bool
    {
        if ($this->id === null) {
            throw new Exception("No se puede actualizar un usuario sin ID.");
        }

        $sql = "UPDATE usuarios SET username=?, password=?, rol=?, nombre=?, email=?, last_login=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssi",
            $this->username,
            $this->password,
            $this->rol,
            $this->nombre,
            $this->email,
            $this->last_login,
            $this->id
        );
        return $stmt->execute();
    }

    /** DELETE */
    public static function delete(int $id): bool
    {
        global $conn;
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /** SHOW (ID) */
    public static function show(int $id): ?Usuario
    {
        global $conn;
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new Usuario($row);
        }
        return null;
    }

    /** INDEX */
    public static function index(): array
    {
        global $conn;
        $sql = "SELECT * FROM usuarios ORDER BY id DESC";
        $result = $conn->query($sql);

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = new Usuario($row);
        }

        return $usuarios;
    }

    /* ===================================================
       ================   AUTH MÉTODOS   =================
       =================================================== */

    /** Autenticación */
    public static function authenticate(string $username, string $password): ?Usuario
    {
        global $conn;

        $sql = "SELECT * FROM usuarios WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Actualizamos last_login
                $update = $conn->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = ?");
                $update->bind_param("i", $user['id']);
                $update->execute();
                return new Usuario($user);
            }
        }
        return null;
    }

    /** Crear o actualizar admin */
    public static function createAdmin(string $username, string $password, ?string $nombre, ?string $email): bool
    {
        global $conn;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Verificar si el usuario existe
        $check = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $sql = "UPDATE usuarios SET password=?, nombre=?, email=?, rol='admin' WHERE username=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $hashed_password, $nombre, $email, $username);
        } else {
            $sql = "INSERT INTO usuarios (username, password, rol, nombre, email) VALUES (?, ?, 'admin', ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $hashed_password, $nombre, $email);
        }

        return $stmt->execute();
    }
    public function getById($id) {
        global $conn; // o pasar $conn como parámetro
        $stmt = $conn->prepare("SELECT id, nombre, rol, last_login FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function countAll($conn) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $row = $stmt->fetch_assoc();
        return $row['total'] ?? 0;
    }
}