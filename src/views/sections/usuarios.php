<?php
// Verificar si el usuario es admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ?section=home");
    exit();
}

// Procesar acciones
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            if (isset($_POST['username'], $_POST['password'], $_POST['rol'])) {
                $username = trim($_POST['username']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $rol = $_POST['rol'];
                
                $stmt = $conn->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $password, $rol);
                $stmt->execute();
            }
            break;
            
        case 'update':
            if (isset($_POST['user_id'], $_POST['username'], $_POST['rol'])) {
                $stmt = $conn->prepare("UPDATE usuarios SET username = ?, rol = ? WHERE id = ?");
                $stmt->bind_param("ssi", $_POST['username'], $_POST['rol'], $_POST['user_id']);
                $stmt->execute();
                
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $password, $_POST['user_id']);
                    $stmt->execute();
                }
            }
            break;
            
        case 'delete':
            if (isset($_POST['user_id']) && $_POST['user_id'] != $_SESSION['user_id']) {
                $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->bind_param("i", $_POST['user_id']);
                $stmt->execute();
            }
            break;
    }
    
    header("Location: ?section=usuarios");
    exit();
}

// Obtener lista de usuarios
$result = $conn->query("SELECT id, username, rol, last_login FROM usuarios ORDER BY username");
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Gestión de Usuarios</h4>
    <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
    </button>
</div>

<!-- Lista de usuarios -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle me-2 text-<?= $usuario['rol'] === 'admin' ? 'primary' : 'secondary' ?>" style="font-size: 1.5em;"></i>
                                <?= htmlspecialchars($usuario['username']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $usuario['rol'] === 'admin' ? 'primary' : 'secondary' ?>">
                                <?= ucfirst($usuario['rol']) ?>
                            </span>
                        </td>
                        <td><?= $usuario['last_login'] ? date('d/m/Y H:i', strtotime($usuario['last_login'])) : 'Nunca' ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="editUser(<?= htmlspecialchars(json_encode($usuario)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="deleteUser(<?= $usuario['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label>Usuario:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Rol:</label>
                        <select name="rol" class="form-control" required>
                            <option value="editor">Editor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-action">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label>Usuario:</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña: (dejar en blanco para mantener)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Rol:</label>
                        <select name="rol" id="edit_rol" class="form-control" required>
                            <option value="editor">Editor</option>
                            <option value="admin">Administrador</option>
                        </select>
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

<!-- Form para eliminar usuario -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_rol').value = user.rol;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function deleteUser(userId) {
    if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('deleteForm').submit();
    }
}
</script>