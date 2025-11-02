<?php 
session_start(); 
require_once 'D:/Archivos de programas/XAMPPg/htdocs/Proyect-Boliche/includes/FileHelper.php';

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'boliche_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Boliche Nocturno</title>
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
        <a href="#proximos-eventos" class="hover:text-gray-900">Eventos</a>
        <a href="#galeria" class="hover:text-gray-900">Galería</a>
        <a href="#reservas" class="hover:text-gray-900">Reservas</a>
        <a href="#contacto" class="hover:text-gray-900">Contacto</a>
        <?php if (isset($_SESSION['loggedin'])): ?>
          <a href="./admin/admin.php" class="hover:text-gray-900">Subir Fotos</a>
          <a href="./admin/logout.php" class="hover:text-red-500 font-semibold">Cerrar Sesión</a>
        <?php else: ?>
          <a href="login.php" class="hover:text-gray-900 font-semibold">Iniciar Sesión</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section class="max-w-6xl mx-auto mt-8 px-6 py-6 flex flex-col gap-3 justify-center items-center text-center">
    <h1 class="text-3xl font-bold text-gray-900">Viví la noche con estilo</h1>
    <p class="text-gray-500">La mejor música, DJs invitados y energía única</p>
  </section>

  <!-- Próximos eventos -->
  <section id="proximos-eventos" class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-center text-2xl font-bold mb-8">Próximos Eventos</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php
        // Obtener eventos futuros
        $stmt = $conn->prepare("SELECT e.*, 
                              COALESCE(e.imagen_portada,
                                      (SELECT f.ruta FROM fotos f WHERE f.evento_id = e.id LIMIT 1)
                              ) as imagen_portada 
                              FROM eventos e 
                              WHERE e.fecha >= CURDATE() AND e.estado = 'programado'
                              ORDER BY e.fecha ASC");
        $stmt->execute();
        $eventos = $stmt->get_result();
        
        while ($evento = $eventos->fetch_assoc()): 
          $fecha = new DateTime($evento['fecha']);
          $imagenPortada = $evento['imagen_portada'] ? $evento['imagen_portada'] : "https://via.placeholder.com/600x400?text=" . urlencode($evento['titulo']);
        ?>
        <div class="overflow-hidden bg-white rounded-xl shadow hover:-translate-y-1 hover:shadow-lg transition">
          <img src="<?= $imagenPortada ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="w-full h-64 object-cover" />
          <div class="flex items-center justify-between px-3 py-2 bg-black/40 text-white text-sm font-semibold">
            <div>
              <p><?= htmlspecialchars($evento['titulo']) ?></p>
              <p class="text-xs text-gray-300"><?= $fecha->format('d/m/Y') ?></p>
            </div>
            <a href="eventos.php?id=<?= $evento['id'] ?>" class="px-2 py-1 bg-white/20 rounded-md hover:bg-white/30">
              <span class="material-symbols-outlined text-sm">info</span>
            </a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Eventos pasados -->
  <section id="eventos-pasados" class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-center text-2xl font-bold mb-8">📸 Eventos Pasados</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php
        // Obtener eventos pasados
        $stmt = $conn->prepare("SELECT e.*, 
                              COALESCE(e.imagen_portada,
                                      (SELECT f.ruta FROM fotos f WHERE f.evento_id = e.id LIMIT 1)
                              ) as imagen_portada,
                              (SELECT COUNT(*) FROM fotos f WHERE f.evento_id = e.id) as cantidad_fotos
                              FROM eventos e 
                              WHERE e.fecha < CURDATE() AND e.estado = 'finalizado'
                              ORDER BY e.fecha DESC 
                              LIMIT 8");
        $stmt->execute();
        $eventos_pasados = $stmt->get_result();
        
        while ($evento = $eventos_pasados->fetch_assoc()): 
          $fecha = new DateTime($evento['fecha']);
          $imagenPortada = $evento['imagen_portada'] ? $evento['imagen_portada'] : "https://via.placeholder.com/600x400?text=" . urlencode($evento['titulo']);
        ?>
        <div class="overflow-hidden bg-white rounded-xl shadow hover:-translate-y-1 hover:shadow-lg transition">
          <img src="<?= $imagenPortada ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" class="w-full h-64 object-cover" />
          <div class="flex items-center justify-between px-3 py-2 bg-black/40 text-white text-sm font-semibold">
            <div>
              <p><?= htmlspecialchars($evento['titulo']) ?></p>
              <p class="text-xs text-gray-300"><?= $fecha->format('d/m/Y') ?></p>
            </div>
            <a href="eventos.php?id=<?= $evento['id'] ?>" class="px-2 py-1 bg-white/20 rounded-md hover:bg-white/30" title="Ver <?= $evento['cantidad_fotos'] ?> fotos">
              <span class="material-symbols-outlined text-sm">photo_library</span>
            </a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Reservas -->
  <section id="reservas" class="max-w-xl mx-auto px-6 py-12 text-center">
    <h2 class="text-2xl font-bold mb-6">Reservá tu mesa</h2>
    <a href="https://wa.me/5491112345678?text=Hola!%20Quiero%20reservar%20una%20mesa%20para%20esta%20noche" target="_blank">
      <button class="px-6 py-3 bg-black text-white font-semibold hover:bg-gray-800 transition">📲 Reservar por WhatsApp</button>
    </a>
  </section>

  <!-- Footer -->
  <footer id="contacto" class="bg-gray-100 py-8 text-center text-gray-500">
    <p>📍 Av. Siempre Viva 123, Buenos Aires</p>
    <p>📱 <a href="tel:+541112345678" class="text-pink-500 hover:underline">+54 11 1234 5678</a></p>
    <p class="mt-2">© 2025 Boliche Nocturno</p>
  </footer>

</body>
</html>
