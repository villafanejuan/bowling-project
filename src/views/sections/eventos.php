<?php
// Crear directorio de uploads si no existe
$target_dir = "../../uploads/eventos/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

function generateSafeFileName($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $cleanName = preg_replace('/[^a-z0-9]+/', '-', strtolower(pathinfo($originalName, PATHINFO_FILENAME)));
    return $cleanName . '-' . uniqid() . '.' . $ext;
}

// Procesar acciones
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            if (isset($_POST['titulo'], $_POST['descripcion'], $_POST['fecha'])) {
                $imagen = null;
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . "." . $ext;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_dir . $filename)) {
                        $imagen = $filename;
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO eventos (titulo, descripcion, fecha, imagen, estado) VALUES (?, ?, ?, ?, 'programado')");
                $stmt->bind_param("ssss", $_POST['titulo'], $_POST['descripcion'], $_POST['fecha'], $imagen);
                $stmt->execute();
            }
            break;
            
        case 'update':
            if (isset($_POST['evento_id'], $_POST['titulo'], $_POST['descripcion'], $_POST['fecha'], $_POST['estado'])) {
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . "." . $ext;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_dir . $filename)) {
                        // Eliminar imagen anterior
                        $stmt = $conn->prepare("SELECT imagen FROM eventos WHERE id = ?");
                        $stmt->bind_param("i", $_POST['evento_id']);
                        $stmt->execute();
                        $old_image = $stmt->get_result()->fetch_assoc()['imagen'];
                        if ($old_image && file_exists($target_dir . $old_image)) {
                            unlink($target_dir . $old_image);
                        }
                        
                        // Actualizar con nueva imagen
                        $stmt = $conn->prepare("UPDATE eventos SET titulo = ?, descripcion = ?, fecha = ?, imagen = ?, estado = ? WHERE id = ?");
                        $stmt->bind_param("sssssi", $_POST['titulo'], $_POST['descripcion'], $_POST['fecha'], $filename, $_POST['estado'], $_POST['evento_id']);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE eventos SET titulo = ?, descripcion = ?, fecha = ?, estado = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $_POST['titulo'], $_POST['descripcion'], $_POST['fecha'], $_POST['estado'], $_POST['evento_id']);
                }
                $stmt->execute();
            }
            break;
            
        case 'delete':
            if (isset($_POST['evento_id'])) {
                // Eliminar imagen
                $stmt = $conn->prepare("SELECT imagen FROM eventos WHERE id = ?");
                $stmt->bind_param("i", $_POST['evento_id']);
                $stmt->execute();
                $imagen = $stmt->get_result()->fetch_assoc()['imagen'];
                if ($imagen && file_exists($target_dir . $imagen)) {
                    unlink($target_dir . $imagen);
                }
                
                // Eliminar evento
                $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
                $stmt->bind_param("i", $_POST['evento_id']);
                $stmt->execute();
            }
            break;
    }
    
    header("Location: ?section=eventos");
    exit();
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
                <h4 class="mb-1">Gestión de Eventos</h4>
                <p class="text-muted mb-0">Administra los eventos del boliche</p>
            </div>
            <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#newEventoModal">
                <i class="fas fa-plus me-2"></i>Nuevo Evento
            </button>
        </div>
    </div>
</div>

<!-- Filtros y búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Buscar eventos..." id="searchEvents">
            </div>
            <div class="col-md-4">
                <select class="form-select" id="filterStatus">
                    <option value="">Todos los estados</option>
                    <option value="programado">Programado</option>
                    <option value="finalizado">Finalizado</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary w-100" id="clearFilters">
                        <i class="fas fa-times me-2"></i>Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de eventos -->
<div class="row g-4">
    <?php foreach ($eventos as $evento): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 event-card">
            <div class="card-img-wrapper position-relative">
                <img src="../../uploads/ev/<?= $evento['imagen'] ?? 'default.jpg' ?>" 
                     class="card-img-top" style="height: 200px; object-fit: cover;" 
                     alt="<?= htmlspecialchars($evento['titulo']) ?>">
                <div class="card-img-overlay" style="background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 100%);">
                    <span class="badge" style="background: <?= $evento['estado'] === 'programado' ? 'var(--primary-color)' : 'var(--secondary-color)' ?>">
                        <?= ucfirst(htmlspecialchars($evento['estado'])) ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0"><?= htmlspecialchars($evento['titulo']) ?></h5>
                    <span class="badge bg-<?= $evento['estado'] === 'programado' ? 'success' : ($evento['estado'] === 'finalizado' ? 'secondary' : 'danger') ?>">
                        <?= ucfirst($evento['estado']) ?>
                    </span>
                </div>
                <p class="card-text">
                    <?= nl2br(htmlspecialchars($evento['descripcion'])) ?>
                </p>
                <p class="text-muted mb-3">
                    <i class="fas fa-calendar me-2"></i>
                    <?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?>
                </p>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-sm btn-primary me-2" 
                            onclick="editEvento(<?= htmlspecialchars(json_encode($evento)) ?>)">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-danger" 
                            onclick="deleteEvento(<?= $evento['id'] ?>)">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Nuevo Evento -->
<div class="modal fade" id="newEventoModal" tabindex="-1">
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
                        <label>Título:</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción:</label>
                        <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Fecha y Hora:</label>
                        <input type="datetime-local" name="fecha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Imagen:</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-action">Crear Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Evento -->
<div class="modal fade" id="editEventoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="evento_id" id="edit_evento_id">
                    <div class="mb-3">
                        <label>Título:</label>
                        <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción:</label>
                        <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Fecha y Hora:</label>
                        <input type="datetime-local" name="fecha" id="edit_fecha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Estado:</label>
                        <select name="estado" id="edit_estado" class="form-control" required>
                            <option value="programado">Programado</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Nueva Imagen: (dejar en blanco para mantener)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
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

<!-- Form para eliminar evento -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="evento_id" id="delete_evento_id">
</form>

<script>
function editEvento(evento) {
    document.getElementById('edit_evento_id').value = evento.id;
    document.getElementById('edit_titulo').value = evento.titulo;
    document.getElementById('edit_descripcion').value = evento.descripcion;
    document.getElementById('edit_fecha').value = evento.fecha.slice(0, 16);
    document.getElementById('edit_estado').value = evento.estado;
    new bootstrap.Modal(document.getElementById('editEventoModal')).show();
}

function deleteEvento(eventoId) {
    if (confirm('¿Está seguro de que desea eliminar este evento?')) {
        document.getElementById('delete_evento_id').value = eventoId;
        document.getElementById('deleteForm').submit();
    }
}
</script>