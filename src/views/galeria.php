<?php
session_start();
require_once '../../config/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Procesar acciones
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'upload':
            if (isset($_FILES['fotos']) && isset($_POST['evento_id'])) {
                $target_dir = "../../uploads/nuevo_evento/";
                $event_id = $_POST['evento_id'];
                $descripcion = $_POST['descripcion'] ?? '';
                
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['fotos']['error'][$key] == 0) {
                        $ext = pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION);
                        $filename = uniqid() . "." . $ext;
                        
                        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                            $stmt = $conn->prepare("INSERT INTO fotos (evento_id, ruta, descripcion) VALUES (?, ?, ?)");
                            $stmt->bind_param("iss", $event_id, $filename, $descripcion);
                            $stmt->execute();
                        }
                    }
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['foto_id'])) {
                // Obtener ruta de la foto
                $stmt = $conn->prepare("SELECT ruta FROM fotos WHERE id = ?");
                $stmt->bind_param("i", $_POST['foto_id']);
                $stmt->execute();
                $ruta = $stmt->get_result()->fetch_assoc()['ruta'];
                
                // Eliminar archivo
                if ($ruta && file_exists("../../uploads/nuevo_evento/" . $ruta)) {
                    unlink("../../uploads/nuevo_evento/" . $ruta);
                }
                
                // Eliminar registro
                $stmt = $conn->prepare("DELETE FROM fotos WHERE id = ?");
                $stmt->bind_param("i", $_POST['foto_id']);
                $stmt->execute();
            }
            break;
            
        case 'update':
            if (isset($_POST['foto_id'], $_POST['descripcion'])) {
                $stmt = $conn->prepare("UPDATE fotos SET descripcion = ? WHERE id = ?");
                $stmt->bind_param("si", $_POST['descripcion'], $_POST['foto_id']);
                $stmt->execute();
            }
            break;
    }
    
    header("Location: galeria.php" . (isset($_GET['evento_id']) ? "?evento_id=" . $_GET['evento_id'] : ""));
    exit();
}

// Obtener eventos para el selector
$eventos = $conn->query("SELECT id, titulo, fecha FROM eventos ORDER BY fecha DESC")->fetch_all(MYSQLI_ASSOC);

// Obtener fotos según el evento seleccionado
$evento_id = $_GET['evento_id'] ?? null;
if ($evento_id) {
    $stmt = $conn->prepare("SELECT f.*, e.titulo as evento_titulo FROM fotos f 
                           JOIN eventos e ON f.evento_id = e.id 
                           WHERE f.evento_id = ? 
                           ORDER BY f.id DESC");
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $fotos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $result = $conn->query("SELECT f.*, e.titulo as evento_titulo FROM fotos f 
                           JOIN eventos e ON f.evento_id = e.id 
                           ORDER BY f.id DESC");
    $fotos = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Fotos - Boliche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos del sidebar y comunes */
        :root {
            --primary-color: #8a2be2;
            --secondary-color: #ff69b4;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            color: white;
            padding-top: 20px;
        }
        
        .sidebar .nav-link {
            color: white;
            opacity: 0.8;
            transition: opacity 0.3s;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
        }
        
        .sidebar .nav-link:hover {
            opacity: 1;
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            opacity: 1;
        }
        
        .main-content {
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .foto-card {
            transition: transform 0.3s;
        }

        .foto-card:hover {
            transform: translateY(-5px);
        }

        .foto-imagen {
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
            cursor: pointer;
        }

        .btn-action {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }
        
        .btn-action:hover {
            opacity: 0.9;
            color: white;
        }

        .modal-foto {
            max-height: 80vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h4>Boliche Admin</h4>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="admin.php">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="eventos.php">
                        <i class="fas fa-calendar me-2"></i> Eventos
                    </a>
                    <a class="nav-link active" href="galeria.php">
                        <i class="fas fa-images me-2"></i> Fotos
                    </a>
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users me-2"></i> Usuarios
                    </a>
                    <?php endif; ?>
                    <a class="nav-link" href="../../logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="mb-0">Galería de Fotos</h4>
                            </div>
                            <div class="col text-end">
                                <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="fas fa-upload me-2"></i>Subir Fotos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="container-fluid">
                    <!-- Filtro por evento -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label">Filtrar por Evento:</label>
                                    <select name="evento_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">Todos los eventos</option>
                                        <?php foreach ($eventos as $evento): ?>
                                        <option value="<?= $evento['id'] ?>" 
                                                <?= ($evento_id == $evento['id'] ? 'selected' : '') ?>>
                                            <?= htmlspecialchars($evento['titulo']) ?> - 
                                            <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Galería de fotos -->
                    <div class="row">
                        <?php foreach ($fotos as $foto): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card foto-card">
                                <img src="../../uploads/nuevo_evento/<?= $foto['ruta'] ?>" 
                                     class="foto-imagen" 
                                     alt="<?= htmlspecialchars($foto['descripcion']) ?>"
                                     onclick="showFoto('<?= $foto['ruta'] ?>', '<?= htmlspecialchars($foto['descripcion']) ?>')">
                                <div class="card-body">
                                    <p class="card-text">
                                        <?= htmlspecialchars($foto['descripcion'] ?: 'Sin descripción') ?>
                                    </p>
                                    <small class="text-muted">
                                        Evento: <?= htmlspecialchars($foto['evento_titulo']) ?><br>
                                        ID: <?= $foto['id'] ?>
                                    </small>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="editFoto(<?= $foto['id'] ?>, '<?= htmlspecialchars($foto['descripcion']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteFoto(<?= $foto['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Subir Fotos -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Fotos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload">
                        <div class="mb-3">
                            <label>Evento:</label>
                            <select name="evento_id" class="form-control" required>
                                <?php foreach ($eventos as $evento): ?>
                                <option value="<?= $evento['id'] ?>">
                                    <?= htmlspecialchars($evento['titulo']) ?> - 
                                    <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Fotos:</label>
                            <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label>Descripción (opcional):</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-action">Subir Fotos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Foto -->
    <div class="modal fade" id="viewFotoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ver Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalFoto" class="img-fluid modal-foto" alt="Foto">
                    <p id="modalDescripcion" class="mt-3"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Foto -->
    <div class="modal fade" id="editFotoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Descripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="foto_id" id="edit_foto_id">
                        <div class="mb-3">
                            <label>Descripción:</label>
                            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-action">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form para eliminar foto -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="foto_id" id="delete_foto_id">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showFoto(ruta, descripcion) {
            document.getElementById('modalFoto').src = '../../uploads/nuevo_evento/' + ruta;
            document.getElementById('modalDescripcion').textContent = descripcion || 'Sin descripción';
            new bootstrap.Modal(document.getElementById('viewFotoModal')).show();
        }

        function editFoto(fotoId, descripcion) {
            document.getElementById('edit_foto_id').value = fotoId;
            document.getElementById('edit_descripcion').value = descripcion;
            new bootstrap.Modal(document.getElementById('editFotoModal')).show();
        }

        function deleteFoto(fotoId) {
            if (confirm('¿Está seguro de que desea eliminar esta foto?')) {
                document.getElementById('delete_foto_id').value = fotoId;
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
</body>
</html>