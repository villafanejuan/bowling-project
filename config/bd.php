<?php
// Configuración de la base de datos
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'boliche_db');

// Manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Intentar conectar a la base de datos
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    // Establecer el conjunto de caracteres a utf8
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Funciones de utilidad
function debug($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}
?>