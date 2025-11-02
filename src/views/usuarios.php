<?php
session_start();
require_once '../../config/config.php';

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
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
    
    header("Location: usuarios.php");
    exit();
}

// Obtener lista de usuarios
$result = $conn->query("SELECT id, username, rol, last_login FROM usuarios ORDER BY username");
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Boliche</title>
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

        .btn-action {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }
        
        .btn-action:hover {
            opacity: 0.9;
            color: white;
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
                    <a class="nav-link" href="galeria.php">
                        <i class="fas fa-images me-2"></i> Fotos
                    </a>
                    <a class="nav-link active" href="usuarios.php">
                        <i class="fas fa-users me-2"></i> Usuarios
                    </a>
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
                                <h4 class="mb-0">Gestión de Usuarios</h4>
                            </div>
                            <div class="col text-end">
                                <button class="btn btn-action" data-bs-toggle="modal" data-bs-target="#newUserModal">
                                    <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                                            <td><?= htmlspecialchars($usuario['username']) ?></td>
                                            <td><?= htmlspecialchars($usuario['rol']) ?></td>
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
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>