<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;
    
    public function __construct($db) {
        $this->user = new Usuarios($db);
    }
    
    public function login($username, $password) {
        if ($user = $this->user->authenticate($username, $password)) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol'] = $user['rol'];
            return true;
        }
        return false;
    }
    
    public function createAdmin() {
        return $this->user->createAdmin('admin', 'admin123', 'Administrador', 'admin@boliche.com');
    }
    
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }
    
    public function logout() {
        session_start();
        session_destroy();
    }
}