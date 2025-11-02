<?php
session_start();
require_once 'D:/Archivos de programas/XAMPPg/htdocs/Proyect-Boliche/includes/FileHelper.php';

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'boliche_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Obtener el evento
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT e.*, 
                              COALESCE(e.imagen_portada,
                                      (SELECT f.ruta FROM fotos f WHERE f.evento_id = e.id LIMIT 1)
                              ) as imagen_portada
                              FROM eventos e 
                              WHERE e.id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Obtener las fotos del evento
$stmt = $conn->prepare("SELECT * FROM fotos WHERE evento_id = ? ORDER BY orden ASC, created_at DESC");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$fotos = $stmt->get_result();

$fecha = new DateTime($evento['fecha']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($evento['titulo']) ?> - Boliche Nocturno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #e5e7eb; }
        h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="text-gray-900">
    <!-- Header -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-white/95 border-b border-gray-200">
        <div class="max-w-6xl mx-auto flex items-center justify-between px-6 py-4">
            <a href="index.php" class="font-bold text-lg text-gray-900">Boliche Nocturno</a>
            <nav class="flex space-x-4 text-sm text-gray-500">
                <a href="index.php#proximos-eventos" class="hover:text-gray-900">Eventos</a>
                <a href="index.php#eventos-pasados" class="hover:text-gray-900">Galería</a>
                <a href="index.php#reservas" class="hover:text-gray-900">Reservas</a>
                <a href="index.php#contacto" class="hover:text-gray-900">Contacto</a>
            </nav>
        </div>
    </header>

    <!-- Evento Info -->
    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($evento['titulo']) ?></h1>
                <p class="text-gray-500 mb-4">
                    <span class="material-symbols-outlined align-middle">calendar_month</span>
                    <?= $fecha->format('d/m/Y') ?>
                </p>
                <?php if ($evento['descripcion']): ?>
                    <p class="text-gray-700 mb-6"><?= nl2br(htmlspecialchars($evento['descripcion'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Galería de Fotos -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-8">Galería de Fotos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php while ($foto = $fotos->fetch_assoc()): ?>
                    <div class="overflow-hidden bg-white rounded-xl shadow hover:-translate-y-1 hover:shadow-lg transition">
                        <img src="<?= $foto['ruta'] ?>" 
                             alt="Foto del evento <?= htmlspecialchars($evento['titulo']) ?>" 
                             class="w-full h-64 object-cover cursor-pointer"
                             onclick="openLightbox('<?= $foto['ruta'] ?>')" />
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" class="fixed inset-0 bg-black/90 hidden z-50" onclick="closeLightbox()">
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <img id="lightbox-img" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
        </div>
    </div>

    <script>
        function openLightbox(imgSrc) {
            document.getElementById('lightbox').classList.remove('hidden');
            document.getElementById('lightbox-img').src = imgSrc;
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.getElementById('lightbox-img').src = '';
            document.body.style.overflow = '';
        }
    </script>
</body>
</html>