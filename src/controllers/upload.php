<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['evento_id']) || !isset($_FILES['fotos'])) {
        die('Error: Datos incompletos');
    }

    $evento_id = $_POST['evento_id'];
    
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        $file = array(
            'name' => $_FILES['fotos']['name'][$key],
            'type' => $_FILES['fotos']['type'][$key],
            'tmp_name' => $tmp_name,
            'error' => $_FILES['fotos']['error'][$key],
            'size' => $_FILES['fotos']['size'][$key]
        );

        $upload_result = uploadImage($file, "eventos/$evento_id");
        
        if (isset($upload_result['success'])) {
            $sql = "INSERT INTO fotos (evento_id, ruta_foto, thumbnail) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iss", $evento_id, $upload_result['file_path'], $upload_result['thumb_path']);
            mysqli_stmt_execute($stmt);
        }
    }

    header("Location: evento.php?id=$evento_id");
    exit();
}
?>