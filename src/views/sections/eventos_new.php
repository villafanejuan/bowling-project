<?php
require_once __DIR__ . '/../../includes/FileHelper.php';

$message = '';
$messageType = '';

// Procesar acciones
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            // Validar datos requeridos
            if (!empty($_POST['titulo']) && !empty($_POST['fecha']) && isset($_FILES['cover'])) {
                $titulo = $_POST['titulo'];
                $fecha = $_POST['fecha'];
                $descripcion = $_POST['descripcion'] ?? '';
                $precio = $_POST['precio'] ?? null;

                // Crear evento
                $stmt = $conn->prepare("INSERT INTO eventos (titulo, fecha, descripcion, precio) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssd", $titulo, $fecha, $descripcion, $precio);
                
                if ($stmt->execute()) {
                    $evento_id = $conn->insert_id;
                    
                    // Procesar imagen de portada
                    if ($_FILES['cover']['error'] == 0) {
                        $target_dir = FileHelper::getEventImagePath();
                        
                        $filename = FileHelper::generateSafeFileName($_FILES['cover']['name']);
                        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target_dir . $filename)) {
                            $ruta = FileHelper::getRelativePath($target_dir . $filename);
                            $stmt = $conn->prepare("UPDATE eventos SET imagen = ? WHERE id = ?");
                            $stmt->bind_param("si", $ruta, $evento_id);
                            $stmt->execute();
                            
                            $message = "Evento creado exitosamente.";
                            $messageType = 'success';
                        }
                    }
                }
            }
            break;
            
        case 'update':
            if (isset($_POST['evento_id']) && !empty($_POST['titulo']) && !empty($_POST['fecha'])) {
                $evento_id = $_POST['evento_id'];
                $titulo = $_POST['titulo'];
                $fecha = $_POST['fecha'];
                $descripcion = $_POST['descripcion'] ?? '';
                $precio = $_POST['precio'] ?? null;
                
                // Actualizar información básica
                $stmt = $conn->prepare("UPDATE eventos SET titulo = ?, fecha = ?, descripcion = ?, precio = ? WHERE id = ?");
                $stmt->bind_param("sssdi", $titulo, $fecha, $descripcion, $precio, $evento_id);
                
                if ($stmt->execute()) {
                    // Procesar nueva imagen si se proporcionó
                    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
                        $target_dir = FileHelper::getEventImagePath();
                        
                        $filename = FileHelper::generateSafeFileName($_FILES['cover']['name']);
                        if (move_uploaded_file($_FILES['cover']['tmp_name'], $target_dir . $filename)) {
                            $ruta = FileHelper::getRelativePath($target_dir . $filename);
                            $stmt = $conn->prepare("UPDATE eventos SET imagen = ? WHERE id = ?");
                            $stmt->bind_param("si", $ruta, $evento_id);
                            $stmt->execute();
                        }
                    }
                    
                    $message = "Evento actualizado exitosamente.";
                    $messageType = 'success';
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['evento_id'])) {
                $evento_id = $_POST['evento_id'];
                
                // Obtener información del evento
                $stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
                $stmt->bind_param("i", $evento_id);
                $stmt->execute();
                $evento = $stmt->get_result()->fetch_assoc();
                
                if ($evento) {
                    // Eliminar imagen de portada
                    if ($evento['imagen']) {
                        $target_dir = FileHelper::getEventImagePath();
                        $fullPath = $target_dir . basename($evento['imagen']);
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                    
                    // Eliminar fotos asociadas
                    $stmt = $conn->prepare("SELECT ruta FROM fotos WHERE evento_id = ?");
                    $stmt->bind_param("i", $evento_id);
                    $stmt->execute();
                    $fotos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    
                    foreach ($fotos as $foto) {
                        $fullPath = __DIR__ . '/../../' . $foto['ruta'];
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                    
                    // Eliminar registros
                    $conn->begin_transaction();
                    try {
                        $conn->query("DELETE FROM fotos WHERE evento_id = $evento_id");
                        $conn->query("DELETE FROM eventos WHERE id = $evento_id");
                        $conn->commit();
                        
                        $message = "Evento eliminado exitosamente.";
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $message = "Error al eliminar el evento.";
                        $messageType = 'danger';
                    }
                }
            }
            break;
    }
}

// Obtener lista de eventos
$result = $conn->query("SELECT * FROM eventos ORDER BY fecha DESC");
$eventos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Header -->
<div class="section-header card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Eventos</h4>
                <p class="text-muted mb-0">Gestiona los eventos del boliche</p>
            </div>
            <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-2"></i>Nuevo Evento
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

<!-- Lista de eventos -->
<div class="row g-4">
    <?php foreach ($eventos as $evento): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <?php if (!empty($evento['imagen'])): ?>
            <img src="<?= $evento['imagen'] ?>" 
                 class="card-img-top" 
                 alt="<?= htmlspecialchars($evento['titulo']) ?>"
                 style="height: 200px; object-fit: cover;">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($evento['titulo']) ?></h5>
                <p class="card-text">
                    <small class="text-muted">
                        <i class="far fa-calendar me-2"></i><?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                    </small>
                </p>
                <?php if (!empty($evento['descripcion'])): ?>
                <p class="card-text"><?= htmlspecialchars($evento['descripcion']) ?></p>
                <?php endif; ?>
                <?php if (!empty($evento['precio'])): ?>
                <p class="card-text">
                    <strong>Precio:</strong> $<?= number_format($evento['precio'], 2) ?>
                </p>
                <?php endif; ?>
                <div class="btn-group">
                    <button class="btn btn-sm btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editModal"
                            data-evento-id="<?= $evento['id'] ?>"
                            data-titulo="<?= htmlspecialchars($evento['titulo']) ?>"
                            data-fecha="<?= $evento['fecha'] ?>"
                            data-descripcion="<?= htmlspecialchars($evento['descripcion']) ?>"
                            data-precio="<?= $evento['precio'] ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" 
                            onclick="if(confirm('¿Estás seguro de eliminar este evento?')) document.getElementById('deleteForm<?= $evento['id'] ?>').submit();">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <form id="deleteForm<?= $evento['id'] ?>" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="evento_id" value="<?= $evento['id'] ?>">
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal para crear evento -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Título:</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fecha:</label>
                        <input type="date" name="fecha" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Precio:</label>
                        <input type="number" name="precio" class="form-control" step="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagen de Portada:</label>
                        <input type="file" name="cover" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar evento -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="evento_id" id="editEventoId">
                    
                    <div class="mb-3">
                        <label class="form-label">Título:</label>
                        <input type="text" name="titulo" id="editTitulo" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fecha:</label>
                        <input type="date" name="fecha" id="editFecha" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="editDescripcion" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Precio:</label>
                        <input type="number" name="precio" id="editPrecio" class="form-control" step="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nueva Imagen de Portada:</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Dejar vacío para mantener la imagen actual</small>
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
    var eventoId = button.getAttribute('data-evento-id');
    var titulo = button.getAttribute('data-titulo');
    var fecha = button.getAttribute('data-fecha');
    var descripcion = button.getAttribute('data-descripcion');
    var precio = button.getAttribute('data-precio');
    
    document.getElementById('editEventoId').value = eventoId;
    document.getElementById('editTitulo').value = titulo;
    document.getElementById('editFecha').value = fecha;
    document.getElementById('editDescripcion').value = descripcion;
    document.getElementById('editPrecio').value = precio;
});
</script>