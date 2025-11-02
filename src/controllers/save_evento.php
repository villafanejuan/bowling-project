<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $cover = $_POST['cover'];
    
    // Subir imagen de portada
    $imagen_portada = '';
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['imagen_portada'], 'portadas');
        if (isset($upload_result['success'])) {
            $imagen_portada = $upload_result['file_path'];
        }
    }

    $sql = "INSERT INTO eventos (titulo, descripcion, fecha, hora, cover, imagen_portada) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssds", $titulo, $descripcion, $fecha, $hora, $cover, $imagen_portada);
    
    if (mysqli_stmt_execute($stmt)) {
        $evento_id = mysqli_insert_id($conn);
        header("Location: evento.php?id=$evento_id");
        exit();
    } else {
        die("Error al guardar el evento: " . mysqli_error($conn));
    }
}
?>