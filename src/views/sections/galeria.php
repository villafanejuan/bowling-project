<?php
require_once 'D:/Archivos de programas/XAMPPg/htdocs/Proyect-Boliche/includes/FileHelper.php';

$message = '';
$messageType = '';

// Procesar acciones
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'upload') {
        if (isset($_FILES['fotos']) && isset($_POST['evento_id'])) {
            $stmt = $conn->prepare("SELECT titulo, fecha FROM eventos WHERE id = ?");
            $stmt->bind_param("i", $_POST['evento_id']);
            $stmt->execute();
            $evento = $stmt->get_result()->fetch_assoc();
            
            if ($evento) {
                $target_dir = FileHelper::getGalleryPath($evento);
                $event_id = $_POST['evento_id'];
                $descripcion = $_POST['descripcion'] ?? '';
                $uploaded = 0;
                
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['fotos']['error'][$key] == 0) {
                        $filename = FileHelper::generateSafeFileName($_FILES['fotos']['name'][$key]);
                        
                        if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                            $ruta = FileHelper::getRelativePath($target_dir . $filename);
                            $stmt = $conn->prepare("INSERT INTO fotos (evento_id, ruta, descripcion) VALUES (?, ?, ?)");
                            $stmt->bind_param("iss", $event_id, $ruta, $descripcion);
                            if ($stmt->execute()) {
                                $uploaded++;
                            }
                        }
                    }
                }
                $message = $uploaded > 0 ? "Se subieron $uploaded fotos exitosamente." : "No se pudo subir ninguna foto.";
                $messageType = $uploaded > 0 ? 'success' : 'danger';
            }
        }
    } 
    elseif ($action === 'delete') {
        if (isset($_POST['foto_id'])) {
            $stmt = $conn->prepare("SELECT f.*, e.titulo, e.fecha FROM fotos f 
                                  JOIN eventos e ON f.evento_id = e.id 
                                  WHERE f.id = ?");
            $stmt->bind_param("i", $_POST['foto_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result) {
                $target_dir = FileHelper::getGalleryPath($result);
                $fullPath = $target_dir . basename($result['ruta']);
                
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                
                $stmt = $conn->prepare("DELETE FROM fotos WHERE id = ?");
                $stmt->bind_param("i", $_POST['foto_id']);
                if ($stmt->execute()) {
                    $message = "Foto eliminada exitosamente.";
                    $messageType = 'success';
                } else {
                    $message = "Error al eliminar la foto.";
                    $messageType = 'danger';
                }
            }
        }
    }
    elseif ($action === 'update') {
        if (isset($_POST['foto_id'], $_POST['descripcion'])) {
            $stmt = $conn->prepare("UPDATE fotos SET descripcion = ? WHERE id = ?");
            $stmt->bind_param("si", $_POST['descripcion'], $_POST['foto_id']);
            if ($stmt->execute()) {
                $message = "Descripción actualizada exitosamente.";
                $messageType = 'success';
            } else {
                $message = "Error al actualizar la descripción.";
                $messageType = 'danger';
            }
        }
    }
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

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Galería de Fotos</h4>
    <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fas fa-upload me-2"></i>Subir Fotos
    </button>
</div>

<!-- Mensajes de estado -->
<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?> alert-dismissible fade show mb-4" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Filtro por evento -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-center">
            <input type="hidden" name="section" value="galeria">
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
        <div class="card">
            <img src="<?= $foto['ruta'] ?>" 
                 class="card-img-top" 
                 style="height: 200px; object-fit: cover; cursor: pointer;"
                 alt="<?= htmlspecialchars($foto['descripcion']) ?>"
                 onclick="showFoto('<?= $foto['ruta'] ?>', '<?= htmlspecialchars($foto['descripcion']) ?>')">
            <div class="card-body">
                <p class="card-text">
                    <?= htmlspecialchars($foto['descripcion'] ?: 'Sin descripción') ?>
                </p>
                <small class="text-muted">
                    Evento: <?= htmlspecialchars($foto['evento_titulo']) ?>
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
                <img id="modalFoto" class="img-fluid" style="max-height: 80vh;" alt="Foto">
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

<script>
function showFoto(ruta, descripcion) {
    document.getElementById('modalFoto').src = ruta;
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