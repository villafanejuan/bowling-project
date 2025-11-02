<?php
define('ROOT_PATH', __DIR__ . '/');

session_start();

use Src\Models\Evento;
use Src\Models\Foto;

require_once ROOT_PATH . 'config/bd.php';
require_once ROOT_PATH . 'includes/FileHelper.php';
require_once ROOT_PATH . 'src/models/Eventos.php';
require_once ROOT_PATH . 'src/models/Fotos.php';

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

$evento = new Evento();
$proximosEventos = $evento->obtenerProximosEventos($conn);
$eventosPasados = $evento->obtenerEventosPasados($conn);

// Datos de ejemplo para las nuevas secciones
$djs = [
    ['nombre' => 'DJ Luna', 'estilo' => 'Tech House', 'foto' => 'https://via.placeholder.com/150/ff0080/FFFFFF?text=DJ+LUNA'],
    ['nombre' => 'DJ Sol', 'estilo' => 'Reggaeton & Hits', 'foto' => 'https://via.placeholder.com/150/7928ca/FFFFFF?text=DJ+SOL'],
    ['nombre' => 'DJ Estrella', 'estilo' => 'Electrónica', 'foto' => 'https://via.placeholder.com/150/00bcd4/FFFFFF?text=DJ+ESTRELLA'],
];

$cocteles_estrella = [
    ['nombre' => 'Ritmo Fire', 'descripcion' => 'Gin premium, frutos rojos y toque de jengibre.', 'icono' => 'local_bar'],
    ['nombre' => 'Electric Sky', 'descripcion' => 'Vodka, Blue Curaçao y lima. Nuestro clásico.', 'icono' => 'liquor'],
    ['nombre' => 'VIP Martini', 'descripcion' => 'La versión más elegante y fuerte de un clásico.', 'icono' => 'wine_bar'],
];

$testimonios = [
    ['nombre' => 'Martina S.', 'cita' => '¡El mejor ambiente de la ciudad! La música es increíble.', 'estrellas' => 5],
    ['nombre' => 'Lucas G.', 'cita' => 'Reservar fue súper fácil por WhatsApp. Tragos de 10.', 'estrellas' => 5],
    ['nombre' => 'Flor V.', 'cita' => 'Siempre hay gente nueva y la energía es única.', 'estrellas' => 4],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ritmo | Bar & Boliche - La Noche de la Ciudad</title>
<meta name="description" content="Descubre los próximos eventos, DJs invitados y la mejor coctelería premium de Ritmo Bar & Boliche. ¡Reserva tu mesa VIP ahora!">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Inter:wght@400;600&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
body { font-family: 'Inter', sans-serif; background-color: #0d0d0d; color: #eee; scroll-behavior: smooth; }
h1,h2,h3 { font-family: 'Montserrat', sans-serif; }
.hero-bg { 
    background: linear-gradient(90deg, #ff0080, #7928ca); /* Tonos más oscuros y profundos */
    background-size: 200% 200%; 
    animation: gradient 8s ease infinite; 
    position: relative;
    overflow: hidden;
}
.hero-bg::before { /* Overlay para mejorar la legibilidad del texto */
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3); /* Oscurece un poco el fondo */
    z-index: 1;
}
.hero-content { position: relative; z-index: 2; }

@keyframes gradient { 0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%} }
.card-hover:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 10px 30px rgba(255,0,128,0.4); 
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
}
.glow { 
    text-shadow: 0 0 10px rgba(255,0,128,0.8), 0 0 20px rgba(121,40,202,0.6); 
    /* Ajuste para un efecto de neón más sutil */
}
</style>
</head>
<body class="text-gray-100">

<header class="sticky top-0 z-50 backdrop-blur-sm bg-black/90 border-b border-pink-900/50">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
    <a href="index.php" class="font-black text-3xl glow text-white">RITMO</a>
    <nav class="flex space-x-6 text-sm text-gray-300">
      <a href="#proximos-eventos" class="hover:text-pink-400 font-semibold transition">Eventos</a>
      <a href="#djs" class="hover:text-pink-400 font-semibold transition">DJs</a>
      <a href="#bar" class="hover:text-pink-400 font-semibold transition">Bar</a> 
      <a href="./src/views/galeria.php" class="hover:text-pink-400 font-semibold transition">Galería<a>
      <a href="#reservas" class="px-3 py-1 bg-pink-500 rounded-full hover:bg-pink-600 font-bold transition">RESERVAR</a> <?php if (!empty($_SESSION['loggedin'])): ?>
        <a href="./admin/admin.php" class="hover:text-white hidden sm:inline">Admin</a>
      <?php else: ?>
        <a href="login.php" class="hover:text-white hidden sm:inline">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<section class="hero-bg h-screen flex flex-col justify-center items-center text-center px-6">
  <div class="hero-content">
    <h1 class="text-6xl md:text-8xl font-black mb-4 uppercase glow leading-tight">
        <span class="text-white">LA NOCHE</span><br>DE TU CIUDAD
    </h1>
    <p class="text-2xl md:text-3xl font-light mb-8 text-gray-200">
        Música, Coctelería Premium y una Atmósfera Inigualable.
    </p>
    <a href="#reservas" class="px-10 py-5 bg-pink-500 hover:bg-pink-600 rounded-full font-extrabold text-xl transition transform hover:scale-105 shadow-xl">
        RESERVAR MESA VIP AHORA
    </a>
  </div>
</section>

<section id="proximos-eventos" class="max-w-7xl mx-auto px-6 py-20">
  <h2 class="text-center text-4xl font-bold mb-16 glow text-white">🔥 Próximos Eventos Destacados</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10">
    <?php 
    // Limitar a 4 eventos destacados para no sobrecargar el Home
    $eventos_destacados = array_slice($proximosEventos, 0, 4); 
    foreach ($eventos_destacados as $evento): 
        $fecha = new DateTime($evento['fecha']);
        $imagenPortada = $evento['imagen_portada'] ?? "https://via.placeholder.com/600x400?text=RITMO+EVENTO";
        $diff = $fecha->diff(new DateTime());
    ?>
    <div class="bg-gray-900/80 border border-gray-800 rounded-xl overflow-hidden card-hover shadow-2xl">
      <img src="<?= e($imagenPortada) ?>" alt="<?= e($evento['titulo']) ?>" class="w-full h-64 object-cover">
      <div class="p-6">
        <h3 class="font-extrabold text-2xl mb-2 text-pink-400"><?= e($evento['titulo']) ?></h3>
        <p class="text-gray-400 mb-3 flex items-center gap-2"><span class="material-symbols-outlined text-base">calendar_month</span> <?= $fecha->format('d/m/Y H:i') ?></p>
        <p class="text-purple-400 font-bold mb-4">¡En solo <?= $diff->days ?> días!</p>
        <a href="eventos.php?id=<?= e($evento['id']) ?>" class="block text-center px-4 py-2 bg-pink-500 rounded-full hover:bg-pink-600 transition font-bold uppercase">Ver Lineup</a>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (count($proximosEventos) > 4): ?>
    <div class="flex items-center justify-center col-span-full mt-8">
        <a href="eventos.php" class="text-lg text-pink-400 hover:text-pink-300 font-semibold flex items-center gap-2 transition">
            Ver Calendario Completo <span class="material-symbols-outlined">arrow_forward</span>
        </a>
    </div>
    <?php endif; ?>
  </div>
</section>

---

<section id="djs" class="bg-gray-900 py-20">
  <div class="max-w-7xl mx-auto px-6">
    <h2 class="text-center text-4xl font-bold mb-16 glow text-white">🎧 Nuestros Artistas Invitados</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-10 text-center">
      <?php foreach ($djs as $dj): ?>
        <div class="p-6 rounded-2xl bg-black/50 border border-gray-800 card-hover flex flex-col items-center">
          <img src="<?= e($dj['foto']) ?>" alt="<?= e($dj['nombre']) ?>" class="w-40 h-40 rounded-full mx-auto mb-4 object-cover border-4 border-pink-500 shadow-xl">
          <h3 class="font-extrabold text-2xl text-pink-400 mb-1"><?= e($dj['nombre']) ?></h3>
          <p class="text-gray-400 font-semibold mb-3"><?= e($dj['estilo']) ?></p>
          <a href="#" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1 transition">
            Ver Bio <span class="material-symbols-outlined text-base">arrow_right_alt</span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

---

<section id="bar" class="max-w-7xl mx-auto px-6 py-20">
    <h2 class="text-center text-4xl font-bold mb-16 glow text-white">🍹 Coctelería de Autor & Bar Premium</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <?php foreach ($cocteles_estrella as $coctel): ?>
        <div class="p-6 bg-gradient-to-br from-gray-900 to-gray-800 rounded-xl card-hover shadow-2xl flex items-start gap-4">
            <span class="material-symbols-outlined text-5xl text-pink-500 mt-1"><?= e($coctel['icono']) ?></span>
            <div>
                <h3 class="text-xl font-bold text-white mb-2"><?= e($coctel['nombre']) ?></h3>
                <p class="text-gray-400"><?= e($coctel['descripcion']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-12">
        <a href="#reservas" class="text-lg font-bold text-pink-400 hover:text-pink-300 transition border-b border-pink-400/50 pb-1">
            Ver Carta Completa al Reservar
        </a>
    </div>
</section>

---

<section id="galeria" class="max-w-7xl mx-auto px-6 py-20">
  <h2 class="text-center text-4xl font-bold mb-16 glow text-white">📸 Galería de Eventos Pasados</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
    <?php foreach (array_slice($eventosPasados, 0, 8) as $evento): // Limitar a 8 fotos destacadas
        $fecha = new DateTime($evento['fecha']);
        $imagenPortada = $evento['imagen_portada'] ?? "https://via.placeholder.com/600x400?text=FOTO";
    ?>
    <div class="group relative overflow-hidden bg-gray-900 rounded-xl shadow-lg hover:scale-[1.02] transition duration-300">
      <img src="<?= e($imagenPortada) ?>" alt="<?= e($evento['titulo']) ?>" class="w-full h-48 object-cover transition duration-500 group-hover:scale-110 group-hover:opacity-70">
      <div class="absolute inset-0 bg-black/60 flex flex-col justify-end p-4 opacity-0 group-hover:opacity-100 transition duration-300">
        <p class="font-semibold text-white text-lg"><?= e($evento['titulo']) ?></p>
        <p class="text-gray-400 text-sm"><?= $fecha->format('d/m/Y') ?></p>
        <a href="eventos.php?id=<?= e($evento['id']) ?>" class="text-pink-500 hover:text-pink-400 mt-2 flex items-center gap-1">
          Ver Fotos <span class="material-symbols-outlined text-base">photo_library</span>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-12">
    <a href="galeria.php" class="text-lg text-pink-400 hover:text-pink-300 font-semibold flex items-center justify-center gap-2 transition">
        Ver Más Recuerdos <span class="material-symbols-outlined">collections</span>
    </a>
  </div>
</section>

---

<section class="bg-gradient-to-r from-purple-900 to-black py-20">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-center text-4xl font-bold mb-16 glow text-white">🗣️ Lo que Dicen de Ritmo</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($testimonios as $testimonio): ?>
            <blockquote class="p-6 bg-gray-900/90 rounded-xl shadow-2xl border-l-4 border-pink-500">
                <div class="text-xl mb-4 italic text-gray-200">"<?= e($testimonio['cita']) ?>"</div>
                <footer class="font-bold text-pink-400">- <?= e($testimonio['nombre']) ?></footer>
                <div class="text-yellow-400 text-lg mt-2">
                    <?php for ($i = 0; $i < $testimonio['estrellas']; $i++): ?><span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">star</span><?php endfor; ?>
                </div>
            </blockquote>
            <?php endforeach; ?>
        </div>
    </div>
</section>

---

<section id="reservas" class="py-20 text-center max-w-4xl mx-auto px-6">
  <h2 class="text-4xl font-extrabold mb-6 glow">🍾 ¿Querés una Experiencia VIP?</h2>
  <p class="text-2xl text-gray-300 mb-8 font-light">
    Asegurá tu lugar, reservá tu mesa o sector exclusivo y evitá filas.
  </p>
  <a href="https://wa.me/5491112345678?text=Hola!%20Quiero%20reservar%20una%20mesa%20para%20Ritmo" target="_blank" rel="noopener noreferrer">
    <button class="px-12 py-5 bg-pink-500 hover:bg-pink-600 rounded-full font-extrabold text-xl transition transform hover:scale-105 shadow-2xl">
      <span class="material-symbols-outlined align-middle mr-2">chat</span> RESERVAR POR WHATSAPP
    </button>
  </a>
  <p class="text-sm text-gray-500 mt-4">Respuesta inmediata. Horario de atención: 18:00 a 02:00 hs.</p>
</section>

---

<section class="bg-gray-900/90 py-16 text-center border-t border-gray-800">
  <div class="max-w-4xl mx-auto px-6">
    <h2 class="text-3xl font-bold mb-4 text-white glow">Sé el Primero en Enterarte</h2>
    <p class="text-gray-400 mb-8 text-lg">Ofertas exclusivas, preventas y line-ups de última hora. ¡Suscribite a la comunidad Ritmo!</p>
    <form class="flex flex-col sm:flex-row justify-center gap-4">
      <input type="email" placeholder="Tu correo electrónico (ej: nombre@mail.com)" class="px-6 py-4 rounded-full flex-1 bg-gray-800 text-white border-2 border-gray-700 focus:outline-none focus:border-pink-500 transition placeholder:text-gray-500" required>
      <button type="submit" class="px-8 py-4 bg-purple-600 hover:bg-purple-700 rounded-full font-bold text-white transition shadow-lg">Suscribirme</button>
    </form>
  </div>
</section>

---

<footer id="contacto" class="bg-black text-gray-400 py-12 text-center border-t border-pink-900/50">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
      <div class="text-left mb-4 md:mb-0">
        <p class="font-black text-3xl glow text-white mb-2">RITMO</p>
        <p class="text-sm">Tu destino de música, tragos y diversión.</p>
      </div>
      <div class="text-center md:text-right">
        <p class="text-white font-semibold mb-2 flex items-center justify-center md:justify-end gap-2">
            <span class="material-symbols-outlined text-lg text-pink-500">location_on</span> Av. Siempre Viva 123, Buenos Aires
        </p>
        <p class="mb-2">
            <span class="material-symbols-outlined text-lg text-pink-500 align-middle mr-1">call</span> 
            <a href="tel:+541112345678" class="text-pink-500 hover:text-pink-400 font-bold">+54 11 1234 5678</a>
        </p>
      </div>
    </div>

    <div class="border-t border-gray-800 pt-6">
        <p class="mb-4">Síguenos y únete a la comunidad:</p>
        <div class="flex justify-center gap-8 mt-2 text-3xl">
          <a href="#" class="hover:text-pink-500 transition" aria-label="Instagram de Ritmo">📸</a>
          <a href="#" class="hover:text-pink-500 transition" aria-label="TikTok de Ritmo">🎵</a>
          <a href="#" class="hover:text-pink-500 transition" aria-label="Facebook de Ritmo">💬</a>
        </div>
        <p class="mt-8 text-gray-600 text-sm">© 2025 RITMO Boliche. Todos los derechos reservados.</p>
    </div>
  </div>
</footer>

</body>
</html>

<?php
if ($conn) $conn->close();
?>