<?php
require_once __DIR__ . '/config/config.php';

$evento_id = $_GET['evento_id'] ?? null;

if (!$evento_id) {
    header('Location: index.php#galeria');
    exit();
}

// Obtener información del evento
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->bind_param("i", $evento_id);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();

if (!$evento) {
    header('Location: index.php#galeria');
    exit();
}

// Obtener todas las fotos del evento
$stmt = $conn->prepare("SELECT * FROM fotos WHERE evento_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $evento_id);
$stmt->execute();
$fotos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($evento['titulo']) ?> - Galería BOLICHE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    <style>
        :root {
            --primary-color: #8a2be2;
            --secondary-color: #ff69b4;
        }

        body {
            background: #111;
            color: white;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
        }

        .album-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),
                        url('uploads/ev/<?= $evento['imagen'] ?? 'default.jpg' ?>') center/cover;
            padding: 100px 0 50px;
            margin-bottom: 30px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .photo-card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 1;
            cursor: pointer;
            transition: all 0.3s;
        }

        .photo-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s;
        }

        .photo-card:hover img {
            transform: scale(1.05);
        }

        .photo-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.5));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .photo-card:hover::after {
            opacity: 1;
        }

        .btn-back {
            background: rgba(255,255,255,0.1);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">BOLICHE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#eventos">Eventos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#galeria">Galería</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contacto">Contacto</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Album Header -->
    <header class="album-header">
        <div class="container">
            <a href="index.php#galeria" class="btn btn-back mb-4">
                <i class="fas fa-arrow-left me-2"></i>Volver a la galería
            </a>
            <h1 class="display-4 mb-2"><?= htmlspecialchars($evento['titulo']) ?></h1>
            <p class="lead mb-0">
                <i class="fas fa-calendar-alt me-2"></i>
                <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                <span class="ms-3">
                    <i class="fas fa-camera me-2"></i>
                    <?= count($fotos) ?> fotos
                </span>
            </p>
        </div>
    </header>

    <!-- Photo Grid -->
    <div class="container">
        <div class="photo-grid">
            <?php foreach ($fotos as $foto): ?>
            <a href="uploads/nuevo_evento/<?= htmlspecialchars($foto['ruta']) ?>" 
               class="photo-card" 
               data-lightbox="album"
               data-title="<?= htmlspecialchars($foto['descripcion'] ?? '') ?>">
                <img src="uploads/nuevo_evento/<?= htmlspecialchars($foto['ruta']) ?>" 
                     alt="<?= htmlspecialchars($foto['descripcion'] ?? $evento['titulo']) ?>">
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': 'Foto %1 de %2'
        });
    </script>
</body>
</html>