<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foto_id'])) {
    $foto_id = $_POST['foto_id'];
    
    // Obtener la información de la foto antes de eliminarla
    $sql = "SELECT * FROM fotos WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $foto_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $foto = mysqli_fetch_assoc($result);
    
    if ($foto) {
        // Eliminar los archivos físicos
        if (file_exists($foto['ruta_foto'])) {
            unlink($foto['ruta_foto']);
        }
        if (file_exists($foto['thumbnail'])) {
            unlink($foto['thumbnail']);
        }
        
        // Eliminar el registro de la base de datos
        $sql = "DELETE FROM fotos WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $foto_id);
        mysqli_stmt_execute($stmt);
        
        header("Location: evento.php?id=" . $foto['evento_id']);
        exit();
    }
}

header("Location: eventos.php");
exit();
?>