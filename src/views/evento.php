<?php
session_start();

if (!isset($_GET['name'])) die("Evento no especificado.");

$event = basename($_GET['name']); // prevenir path traversal
$dir = "./uploads/$event/";

// Verificamos si existe
if (!is_dir($dir)) {
    die("El evento no existe.");
}

// 🗑 Eliminar una foto individual (solo si logueado)
if (isset($_SESSION['loggedin']) && isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $path = $dir . $file;
    if (file_exists($path)) {
        unlink($path);
        header("Location: evento.php?name=" . urlencode($event));
        exit;
    }
}

// 📸 Cargar imágenes del evento
$images = [];
foreach (scandir($dir) as $file) {
    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp'])) {
        $images[$file] = filemtime($dir . $file);
    }
}
arsort($images); // más nuevas primero
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= ucfirst(str_replace('_',' ',$event)) ?> - Boliche Nocturno</title>
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
      <h1 class="text-2xl font-bold"><?= ucfirst(str_replace('_',' ',$event)) ?></h1>
      <div class="flex items-center gap-3">
        <a href="eventos.php" class="text-pink-500 font-bold hover:text-pink-400">⬅ Volver</a>
        <?php if (isset($_SESSION['loggedin'])): ?>
          <a href="./admin/admin.php" class="px-3 py-1 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Panel Admin</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Galería -->
  <main class="max-w-7xl mx-auto px-6 py-10">
    <?php if (empty($images)): ?>
      <p class="text-center text-gray-500">No hay fotos cargadas aún para este evento.</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($images as $img => $time): ?>
          <div class="relative group">
            <img src="<?= $dir . $img ?>" 
                 class="w-full h-56 object-cover rounded-lg shadow-md hover:scale-105 transition cursor-pointer"
                 onclick="openModal('<?= $dir . $img ?>')" 
                 alt="<?= htmlspecialchars($img) ?>">

            <?php if (isset($_SESSION['loggedin'])): ?>
              <a href="?name=<?= urlencode($event) ?>&delete=<?= urlencode($img) ?>"
                 onclick="return confirm('¿Eliminar esta foto?')"
                 class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition">
                🗑
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Modal -->
  <div id="modal" class="fixed inset-0 hidden items-center justify-center bg-black/90 z-50">
    <img id="modalImg" class="max-h-[90vh] max-w-[90vw] shadow-2xl" src="">
    <button onclick="closeModal()" 
            class="absolute top-5 right-5 text-white text-4xl font-bold hover:text-pink-400">×</button>
  </div>

  <!-- Footer -->
  <footer class="py-6 bg-gray-900 text-center text-gray-400 border-t border-gray-800 mt-10">
    <p>© 2025 Boliche Nocturno</p>
  </footer>

  <!-- Scripts -->
  <script>
    function openModal(src) {
      const modal = document.getElementById('modal');
      const modalImg = document.getElementById('modalImg');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      modalImg.src = src;
    }
    function closeModal() {
      const modal = document.getElementById('modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
    document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });
    document.getElementById('modal').addEventListener('click', e => { if(e.target===e.currentTarget) closeModal(); });
  </script>

</body>
</html>
