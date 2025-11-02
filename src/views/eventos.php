<?php
session_start();

// Directorio raíz de los eventos
$dir = "./uploads/";

// Crear si no existe
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Escanear subcarpetas (cada una es un evento)
$events = array_filter(scandir($dir), function($f) use ($dir) {
    return $f !== '.' && $f !== '..' && is_dir($dir . $f);
});

// Ordenar por fecha de modificación (más recientes primero)
usort($events, function($a, $b) use ($dir) {
    return filemtime($dir . $b) - filemtime($dir . $a);
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eventos - Boliche Nocturno</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f9fafb; color: #111; }
    h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <!-- Header -->
  <header class="sticky top-0 bg-white border-b border-gray-200 shadow-sm z-50">
    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Galería de Eventos</h1>
      <nav class="flex items-center gap-4 text-sm">
        <a href="index.php" class="text-gray-600 hover:text-gray-900">🏠 Inicio</a>
        <?php if (isset($_SESSION['loggedin'])): ?>
          <a href="./admin/admin.php" class="text-gray-600 hover:text-gray-900 font-semibold">Subir Fotos</a>
          <a href="./admin/logout.php" class="text-red-500 hover:text-red-400 font-semibold">Cerrar Sesión</a>
        <?php else: ?>
          <a href="./admin/login.php" class="text-pink-500 hover:text-pink-400 font-semibold">Iniciar Sesión</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Contenido -->
  <main class="max-w-7xl mx-auto px-6 py-12 flex-grow">
    <h2 class="text-center text-2xl font-bold mb-8">Eventos Recientes</h2>

    <?php if (empty($events)): ?>
      <p class="text-center text-gray-500">Aún no hay eventos cargados.</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($events as $event): 
          $eventDir = $dir . $event . "/";
          $images = array_filter(scandir($eventDir), function($f) use ($eventDir) {
              return in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp']);
          });
          $firstImage = !empty($images) ? reset($images) : null;
        ?>
          <a href="evento.php?name=<?= urlencode($event) ?>"
             class="block bg-white rounded-xl overflow-hidden shadow-md hover:shadow-lg hover:-translate-y-1 transition transform">
            <?php if ($firstImage): ?>
              <img src="<?= $eventDir . $firstImage ?>" 
                   alt="<?= htmlspecialchars(str_replace('_', ' ', $event)) ?>" 
                   class="w-full h-56 object-cover">
            <?php else: ?>
              <div class="w-full h-56 bg-gray-200 flex items-center justify-center text-gray-500 text-sm">
                Sin fotos
              </div>
            <?php endif; ?>
            <div class="p-4 text-center">
              <h3 class="font-bold text-lg"><?= ucfirst(str_replace('_', ' ', $event)) ?></h3>
              <p class="text-gray-500 text-sm">
                <?= date("d/m/Y H:i", filemtime($eventDir)) ?>
              </p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Footer -->
  <footer class="py-6 bg-gray-900 text-center text-gray-400 border-t border-gray-800 mt-10">
    <p>© 2025 Boliche Nocturno</p>
  </footer>

</body>
</html>
