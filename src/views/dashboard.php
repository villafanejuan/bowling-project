<?php
ob_start();
session_start();
require_once __DIR__ . '/../../config/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Asegurarse de que la conexión esté disponible
if (!isset($conn) || $conn === null) {
    die("Error: No hay conexión a la base de datos");
}

// Obtener información del usuario actual
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, rol, last_login FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../../login.php");
    exit();
}

// Obtener estadísticas
$result = $conn->query("SELECT COUNT(*) as count FROM eventos WHERE estado = 'programado'");
$eventos_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM fotos");
$fotos_count = $result->fetch_assoc()['count'];

if ($user['rol'] === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as count FROM usuarios");
    $usuarios_count = $result->fetch_assoc()['count'];
} else {
    $usuarios_count = 0;
}

// Determinar la página activa
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$section = $_GET['section'] ?? 'home';

// Función para cargar una sección y pasar variables
function loadSection($section) {
    // Hacer las variables globales disponibles dentro de la función
    global $conn, $user, $eventos_count, $fotos_count, $usuarios_count;

    // Validar la sección y permisos
    if ($section === 'usuarios' && $_SESSION['rol'] !== 'admin') {
        $section = 'home';
    }

    // Incluir el archivo de la sección
    $file = __DIR__ . '/sections/' . $section . '.php';
    if (file_exists($file)) {
        include $file;
    } else {
        include 'sections/home.php';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Boliche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Estilos para eventos */
        .event-card {
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .event-card .card-img-wrapper {
            overflow: hidden;
        }

        .event-card img {
            transition: transform 0.3s ease;
        }

        .event-card:hover img {
            transform: scale(1.1);
        }

        .event-card .badge {
            position: absolute;
            bottom: 15px;
            left: 15px;
            padding: 8px 15px;
            font-size: 0.8em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            margin-bottom: 2rem;
        }

        .section-header p {
            opacity: 0.8;
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        :root {
            --primary-color: #8a2be2;
            --secondary-color: #ff69b4;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }
        
        body {
            background-color: #f8f9fa;
        }

        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            color: white;
            padding-top: var(--header-height);
            position: fixed;
            width: var(--sidebar-width);
            z-index: 1000;
            transition: all 0.3s ease-in-out;
            left: 0;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .toggle-sidebar {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1002;
            width: 40px;
            height: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .toggle-sidebar:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar-header {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: var(--header-height);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            z-index: 1001;
            display: flex;
            align-items: center;
            padding: 0 20px;
            transition: all 0.3s;
        }

        .sidebar-collapsed .sidebar-header {
            width: var(--sidebar-collapsed-width);
        }

        .header-title {
            opacity: 1;
            transition: opacity 0.3s;
            white-space: nowrap;
        }

        .sidebar-collapsed .header-title {
            opacity: 0;
            position: absolute;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .nav-link .icon-text {
            opacity: 1;
            transition: all 0.3s;
            white-space: nowrap;
            margin-left: 10px;
        }

        .sidebar.collapsed .nav-link .icon-text {
            display: none;
        }
        
        .sidebar .nav-heading {
            color: rgba(255,255,255,0.6);
            font-size: 0.8em;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 15px 10px;
        }

        .toggle-sidebar {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .content-header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background: white;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .sidebar-collapsed .content-header {
            left: var(--sidebar-collapsed-width);
        }

        .user-info {
            color: #333;
        }
        
        .user-info .text-muted {
            font-size: 0.8rem;
        }
        
        .content-header h5 {
            color: #333;
            font-weight: 500;
        }

        .main-content {
            transition: all 0.3s ease-in-out;
            margin-left: var(--sidebar-width);
            padding: 20px;
            padding-top: calc(var(--header-height) + 20px);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            background: #f8f9fa;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        .nav-link {
            display: flex !important;
            align-items: center;
            padding: 12px 20px !important;
            margin: 5px 15px;
            border-radius: 10px;
            transition: all 0.3s;
            color: rgba(255,255,255,0.8) !important;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link i {
            font-size: 1.2em;
            min-width: 30px;
            text-align: center;
        }

        .sidebar.collapsed .nav-link {
            padding: 12px !important;
            margin: 5px;
            justify-content: center;
        }

        .sidebar.collapsed .nav-link i {
            margin: 0;
            font-size: 1.5em;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .sidebar.collapsed {
                left: -70px;
            }
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
            padding: 0;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
            border-radius: 15px;
            background: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-card {
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            overflow: hidden;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 20px;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .stat-icon::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            transform: translateX(-100%) rotate(45deg);
            transition: transform 0.6s;
        }

        .stat-card:hover .stat-icon::after {
            transform: translateX(100%) rotate(45deg);
        }

        .stat-icon.events {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .stat-icon.photos {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }

        .stat-icon.users {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
        }

        .stat-info {
            flex-grow: 1;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-action {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: opacity 0.3s;
        }
        
        .btn-action:hover {
            opacity: 0.9;
            color: white;
        }

        .user-welcome {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .action-button {
            padding: 15px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            text-decoration: none;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-button:hover {
            transform: translateY(-5px);
            color: var(--secondary-color);
        }

        .action-button i {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <!-- Header fijo -->
    <div class="sidebar-header">
        <button class="toggle-sidebar" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h4 class="mb-0 ms-3 text-white header-title">Boliche</h4>
    </div>

    <!-- Header del contenido principal -->
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center px-4 py-3">
            <div class="d-none d-md-block">
                <h5 class="mb-0">Panel de Control</h5>
            </div>
            <div class="user-info d-flex align-items-center">
                <div class="text-end me-3">
                    <div class="fw-bold"><?= htmlspecialchars($user['username']) ?></div>
                    <small class="text-muted">Último acceso: <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></small>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" 
                     class="rounded-circle" 
                     width="40" 
                     height="40" 
                     alt="Usuario">
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-auto">
                <div class="sidebar">
                    <nav class="nav flex-column">
                        <a class="nav-link <?= $section === 'home' ? 'active' : '' ?>" href="?section=home">
                            <i class="fas fa-home"></i>
                            <span class="icon-text">Dashboard</span>
                        </a>
                        <a class="nav-link <?= $section === 'eventos' ? 'active' : '' ?>" href="?section=eventos">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="icon-text">Eventos</span>
                        </a>
                        <a class="nav-link <?= $section === 'galeria' ? 'active' : '' ?>" href="?section=galeria">
                            <i class="fas fa-images"></i>
                            <span class="icon-text">Fotos</span>
                        </a>
                        <?php if ($user['rol'] === 'admin'): ?>
                        <a class="nav-link <?= $section === 'usuarios' ? 'active' : '' ?>" href="?section=usuarios">
                            <i class="fas fa-users"></i>
                            <span class="icon-text">Usuarios</span>
                        </a>
                        <?php endif; ?>
                        <a class="nav-link" href="../../logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="icon-text">Cerrar Sesión</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Stats Section -->
                <div class="stats-section mb-4">
                    <div class="container-fluid p-0">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title mb-1">Vista General del Sistema</h5>
                                                <p class="text-muted small mb-0">Estadísticas actualizadas</p>
                                            </div>
                                            <button class="btn btn-light btn-sm" onclick="window.location.reload()">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Content -->
                <div class="container-fluid">
                    <?php loadSection($section); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const sidebarHeader = document.querySelector('.sidebar-header');
            
            // Recuperar el estado del sidebar del localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                sidebarHeader.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed');
            }

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                sidebarHeader.classList.toggle('sidebar-collapsed');
                document.body.classList.toggle('sidebar-collapsed');
                
                // Guardar el estado del sidebar en localStorage
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Manejar el colapso automático en dispositivos móviles
            function handleResize() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                } else {
                    if (!sidebarCollapsed) {
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('expanded');
                    }
                }
            }

            // Escuchar el evento resize
            window.addEventListener('resize', handleResize);
            
            // Ejecutar al cargar
            handleResize();
        });
    </script>
</body>
</html>