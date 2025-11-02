<?php
require_once __DIR__ . '/../../includes/FileHelper.php';

$message = '';
$messageType = '';

// Procesar acciones
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'upload':
            if (isset($_FILES['fotos']) && isset($_POST['evento_id'])) {
                // Obtener información del evento
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
            break;
            
        case 'delete':
            if (isset($_POST['foto_id'])) {
                // Obtener información de la foto y el evento
                $stmt = $conn->prepare("SELECT f.ruta, e.* FROM fotos f JOIN eventos e ON f.evento_id = e.id WHERE f.id = ?");
                $stmt->bind_param("i", $_POST['foto_id']);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                if ($result) {
                    $target_dir = FileHelper::getGalleryPath($result);
                    $fullPath = $target_dir . basename($result['ruta']);
                    
                    // Eliminar archivo
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    
                    // Eliminar registro
                    $stmt = $conn->prepare("DELETE FROM fotos WHERE id = ?");
                    $stmt->bind_param("i", $_POST['foto_id']);
                    if ($stmt->execute()) {
                        $message = "Foto eliminada exitosamente.";
                        $messageType = 'success';
                    }
                }
            }
            break;
    }
}

// Obtener eventos para el selector
$eventos = $conn->query("SELECT id, titulo, fecha FROM eventos ORDER BY fecha DESC")->fetch_all(MYSQLI_ASSOC);

// Obtener fotos según el evento seleccionado
$evento_id = $_GET['evento_id'] ?? null;
if ($evento_id) {
    $stmt = $conn->prepare("
        SELECT f.*, e.titulo as evento_titulo, e.fecha 
        FROM fotos f 
        JOIN eventos e ON f.evento_id = e.id 
        WHERE f.evento_id = ? 
        ORDER BY f.id DESC
    ");
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $fotos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $result = $conn->query("
        SELECT f.*, e.titulo as evento_titulo, e.fecha 
        FROM fotos f 
        JOIN eventos e ON f.evento_id = e.id 
        ORDER BY f.id DESC
    ");
    $fotos = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!-- Header -->
<div class="section-header card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Galería de Fotos</h4>
                <p class="text-muted mb-0">Gestiona las fotos de los eventos</p>
            </div>
            <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-2"></i>Subir Fotos
            </button>
        </div>
    </div>
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
        <form method="GET" class="row align-items-end">
            <input type="hidden" name="section" value="galeria">
            <div class="col-md-4">
                <label class="form-label">Filtrar por Evento:</label>
                <select name="evento_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos los eventos</option>
                    <?php foreach ($eventos as $evento): ?>
                        <option value="<?= $evento['id'] ?>" <?= ($evento_id == $evento['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($evento['titulo']) ?> - <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Grid de fotos -->
<div class="row g-4">
    <?php foreach ($fotos as $foto): ?>
    <div class="col-md-4 col-lg-3">
        <div class="card h-100">
            <img src="<?= $foto['ruta'] ?>" 
                 class="card-img-top" 
                 alt="<?= htmlspecialchars($foto['descripcion'] ?? $foto['evento_titulo']) ?>"
                 style="height: 200px; object-fit: cover;">
            <div class="card-body">
                <h6 class="card-title"><?= htmlspecialchars($foto['evento_titulo']) ?></h6>
                <p class="card-text small text-muted">
                    <?= htmlspecialchars($foto['descripcion'] ?? 'Sin descripción') ?>
                </p>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal" 
                            data-foto-id="<?= $foto['id'] ?>"
                            data-descripcion="<?= htmlspecialchars($foto['descripcion'] ?? '') ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" 
                            onclick="if(confirm('¿Estás seguro de eliminar esta foto?')) document.getElementById('deleteForm<?= $foto['id'] ?>').submit();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <form id="deleteForm<?= $foto['id'] ?>" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="foto_id" value="<?= $foto['id'] ?>">
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal para subir fotos -->
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
                        <label class="form-label">Evento:</label>
                        <select name="evento_id" class="form-select" required>
                            <?php foreach ($eventos as $evento): ?>
                                <option value="<?= $evento['id'] ?>" <?= ($evento_id == $evento['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($evento['titulo']) ?> - <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Fotos:</label>
                        <input type="file" name="fotos[]" class="form-control" multiple accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Fotos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar descripción -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Descripción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="foto_id" id="editFotoId">
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="editDescripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var fotoId = button.getAttribute('data-foto-id');
    var descripcion = button.getAttribute('data-descripcion');
    
    document.getElementById('editFotoId').value = fotoId;
    document.getElementById('editDescripcion').value = descripcion;
});
</script>