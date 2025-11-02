<?php
// 1. Manejo de Rutas y Conexión Modularizada
// Define la constante ROOT_PATH para usar rutas relativas basadas en la ubicación de este archivo.
define('ROOT_PATH', __DIR__ . '/'); 

session_start();

// Incluye la conexión a la DB ($conn) desde tu archivo de configuración
require_once ROOT_PATH . 'config/bd.php';
// Incluye otras utilidades. ¡Adiós a la ruta absoluta!
require_once ROOT_PATH . 'includes/FileHelper.php';

// Se eliminó la conexión a la base de datos redundante.

// Función para limpiar recursos y redirigir
function clean_up_and_redirect($conn, $stmt = null) {
    if ($stmt && is_a($stmt, 'mysqli_stmt')) {
        $stmt->close();
    }
    $conn->close();
    header('Location: index.php');
    exit;
}

// 2. Validación y Obtención del Evento ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$evento_id = (int)$_GET['id']; // Se castea a entero para mayor seguridad

// 3. Consulta Principal del Evento (usando consulta preparada)
$stmt = $conn->prepare("SELECT e.*, 
                                  COALESCE(e.imagen_portada,
                                          (SELECT f.ruta FROM fotos f WHERE f.evento_id = e.id LIMIT 1)
                                  ) as imagen_portada
                                  FROM eventos e 
                                  WHERE e.id = ?");

if (!$stmt->bind_param("i", $evento_id) || !$stmt->execute()) {
    clean_up_and_redirect($conn, $stmt);
}

$result = $stmt->get_result();
$evento = $result->fetch_assoc();
$stmt->close(); // Cierre explícito del primer statement

if (!$evento) {
    clean_up_and_redirect($conn);
}

// 4. Obtener las Fotos del Evento (usando consulta preparada)
$stmt = $conn->prepare("SELECT * FROM fotos WHERE evento_id = ? ORDER BY orden ASC, created_at DESC");

if (!$stmt->bind_param("i", $evento_id) || !$stmt->execute()) {
    clean_up_and_redirect($conn, $stmt);
}

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

        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-8">Galería de Fotos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php while ($foto = $fotos->fetch_assoc()): ?>
                    <div class="overflow-hidden bg-white rounded-xl shadow hover:-translate-y-1 hover:shadow-lg transition">
                        <img src="<?= htmlspecialchars($foto['ruta']) ?>" 
                             alt="Foto del evento <?= htmlspecialchars($evento['titulo']) ?>" 
                             class="w-full h-64 object-cover cursor-pointer"
                             onclick="openLightbox('<?= htmlspecialchars($foto['ruta']) ?>')" />
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

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
<?php 
// 5. Cierre de Recursos
$stmt->close(); // Cierre explícito del segundo statement (fotos)
$conn->close(); // Cierre explícito de la conexión a la base de datos
?>