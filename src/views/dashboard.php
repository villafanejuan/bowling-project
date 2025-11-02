<?php
// =====================================
// DASHBOARD.PHP - Admin Panel
// Modelo + Tailwind + Buenas prácticas
// =====================================

define('ROOT_PATH', __DIR__ . '/../../');
session_start();

require_once ROOT_PATH . 'config/bd.php';
require_once ROOT_PATH . 'src/models/Usuario.php';
require_once ROOT_PATH . 'src/models/Evento.php';
require_once ROOT_PATH . 'src/models/Foto.php';

use Src\Models\Usuario;
use Src\Models\Evento;
use Src\Models\Foto;

// Instancias de modelos
$usuarioModel = new Usuario();
$eventoModel  = new Evento();
$fotoModel    = new Foto();

// Usuario activo (simulado, sin login)
$user = $usuarioModel->getById(1); // ejemplo, reemplazar por sesión real

// Estadísticas
$eventos_count  = $eventoModel->countAll($conn);
$fotos_count    = $fotoModel->countAll($conn);
$usuarios_count = $user['rol'] === 'admin' ? $usuarioModel->countAll($conn) : 0;

// Sección activa
$section = $_GET['section'] ?? 'home';

// Función para cargar secciones dinámicas
function loadSection($section) {
    $file = __DIR__ . '/sections/' . $section . '.php';
    if (file_exists($file)) {
        include $file;
    } else {
        include __DIR__ . '/sections/home.php';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Boliche</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
h1,h2,h3 { font-family: 'Montserrat', sans-serif; }
</style>
</head>
<body class="flex min-h-screen">

<!-- Sidebar -->
<aside class="w-64 bg-gradient-to-b from-purple-700 via-pink-500 to-pink-400 text-white p-6 hidden md:flex flex-col">
    <h2 class="text-2xl font-bold mb-6">Boliche Admin</h2>
    <nav class="flex flex-col gap-2">
        <a href="?section=home" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/20 <?= $section==='home'?'bg-white/20':'' ?>">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>
        <a href="?section=eventos" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/20 <?= $section==='eventos'?'bg-white/20':'' ?>">
            <i class="fas fa-calendar-alt"></i><span>Eventos</span>
        </a>
        <a href="?section=galeria" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/20 <?= $section==='galeria'?'bg-white/20':'' ?>">
            <i class="fas fa-images"></i><span>Fotos</span>
        </a>
        <?php if ($user['rol']==='admin'): ?>
        <a href="?section=usuarios" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white/20 <?= $section==='usuarios'?'bg-white/20':'' ?>">
            <i class="fas fa-users"></i><span>Usuarios</span>
        </a>
        <?php endif; ?>
    </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-6 md:ml-64">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Panel de Control</h1>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="font-semibold"><?= htmlspecialchars($user['username']) ?></div>
                <div class="text-sm text-gray-500">Último acceso: <?= date('d/m/Y H:i', strtotime($user['last_login'] ?? 'now')) ?></div>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random" class="w-10 h-10 rounded-full" alt="Usuario">
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
            <div class="w-16 h-16 flex items-center justify-center rounded-lg bg-purple-600 text-white text-2xl"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="text-2xl font-bold"><?= $eventos_count ?></div>
                <div class="text-gray-500 uppercase text-sm">Eventos</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
            <div class="w-16 h-16 flex items-center justify-center rounded-lg bg-blue-500 text-white text-2xl"><i class="fas fa-images"></i></div>
            <div>
                <div class="text-2xl font-bold"><?= $fotos_count ?></div>
                <div class="text-gray-500 uppercase text-sm">Fotos</div>
            </div>
        </div>
        <?php if ($user['rol']==='admin'): ?>
        <div class="bg-white rounded-xl shadow p-6 flex items-center gap-4 hover:shadow-lg transition">
            <div class="w-16 h-16 flex items-center justify-center rounded-lg bg-green-500 text-white text-2xl"><i class="fas fa-users"></i></div>
            <div>
                <div class="text-2xl font-bold"><?= $usuarios_count ?></div>
                <div class="text-gray-500 uppercase text-sm">Usuarios</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Dynamic Section -->
    <div class="bg-white rounded-xl shadow p-6">
        <?php loadSection($section); ?>
    </div>
</main>
</body>
</html>
