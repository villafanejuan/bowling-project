<?php
class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT id, username, password, rol FROM usuarios WHERE username = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
    
    public function createAdmin($username, $password, $nombre, $email) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Primero verificar si el usuario existe
        $check_sql = "SELECT id FROM usuarios WHERE username = ?";
        $check_stmt = mysqli_prepare($this->conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Actualizar el usuario existente
            $sql = "UPDATE usuarios SET password = ?, nombre = ?, email = ?, rol = 'admin' WHERE username = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $hashed_password, $nombre, $email, $username);
        } else {
            // Crear nuevo usuario
            $sql = "INSERT INTO usuarios (username, password, rol, nombre, email) VALUES (?, ?, 'admin', ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed_password, $nombre, $email);
        }
        
        return mysqli_stmt_execute($stmt);
    }
}