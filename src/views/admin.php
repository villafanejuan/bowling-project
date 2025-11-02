<?php
session_start();
require_once '../../config/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Obtener estadísticas
$result = $conn->query("SELECT COUNT(*) as count FROM eventos WHERE estado = 'programado'");
$eventos_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM fotos");
$fotos_count = $result->fetch_assoc()['count'];

if ($_SESSION['rol'] === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as count FROM usuarios");
    $usuarios_count = $result->fetch_assoc()['count'];
}

// Obtener información del usuario actual
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, rol, last_login FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Boliche</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .welcome-message {
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .menu-item {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .menu-item a {
            display: block;
            padding: 10px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .menu-item a:hover {
            background: #45a049;
        }
        .logout-btn {
            background: #ff4444;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Panel de Administración</h1>
        <a href="../../logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>

    <div class="container">
        <div class="welcome-message">
            <h2>Bienvenido, <?= htmlspecialchars($user['username']) ?></h2>
            <p>Rol: <?= htmlspecialchars($user['rol']) ?></p>
            <p>Último acceso: <?= $user['last_login'] ? date('d/m/Y H:i:s', strtotime($user['last_login'])) : 'Primer acceso' ?></p>
        </div>

        <div class="menu-grid">
            <div class="menu-item">
                <h3>Eventos</h3>
                <p>Gestionar eventos y fiestas</p>
                <a href="../views/eventos.php">Gestionar Eventos</a>
            </div>

            <div class="menu-item">
                <h3>Fotos</h3>
                <p>Administrar fotos de eventos</p>
                <a href="../views/galeria.php">Gestionar Fotos</a>
            </div>

            <?php if ($user['rol'] === 'admin'): ?>
            <div class="menu-item">
                <h3>Usuarios</h3>
                <p>Administrar usuarios del sistema</p>
                <a href="../views/usuarios.php">Gestionar Usuarios</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>