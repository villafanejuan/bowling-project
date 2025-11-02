-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-11-2025 a las 21:10:12
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `boliche_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Fiesta Regular', 'Eventos regulares de fin de semana'),
(2, 'Evento Especial', 'Fiestas temáticas y eventos especiales'),
(3, 'Aniversario', 'Celebraciones de aniversario del boliche'),
(4, 'DJ Invitado', 'Eventos con DJs invitados especiales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `hora` time NOT NULL,
  `cover` decimal(10,2) DEFAULT NULL,
  `imagen_portada` varchar(255) DEFAULT NULL,
  `estado` enum('programado','finalizado','cancelado') DEFAULT 'programado',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `descripcion`, `fecha`, `imagen`, `hora`, `cover`, `imagen_portada`, `estado`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'FDD', '🪩 ¡Llega la FIESTA DE DISFRACES más esperada del año!\r\nEste [📅 sábado 9 de noviembre] el boliche [Nombre del lugar] se transforma en un universo de locura, ritmo y fantasía.\r\nVenite con tu mejor disfraz — original, divertido o terrorífico — y mostrá tu estilo en la pista.\r\n\r\n👻 Premios para los mejores disfraces\r\n🎧 DJs en vivo toda la noche\r\n🍸 Tragos temáticos\r\n💥 Sorpresas y performance especiales\r\n\r\n📍 [Dirección / ciudad]\r\n🕒 Desde las [hora de inicio] hasta que salga el sol 🌅\r\n🎟️ Entradas anticipadas por [link / boletería / redes del boliche]\r\n\r\n👉 No vengas de civil. Vení a romperla disfrazado.\r\n#FiestaDeDisfraces #BolicheNight #HalloweenParty #NochesQueQuedan\r\nanda?', '2025-11-16', '690670c46f8c3.jpg', '00:00:00', NULL, NULL, 'programado', NULL, '2025-11-01 20:42:44', '2025-11-02 19:56:35'),
(4, 'sdsd', 'sdadasdas', '2025-11-02', NULL, '00:00:00', NULL, NULL, 'programado', NULL, '2025-11-02 20:01:13', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evento_categoria`
--

CREATE TABLE `evento_categoria` (
  `evento_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos`
--

CREATE TABLE `fotos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) DEFAULT NULL,
  `ruta` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fotos`
--

INSERT INTO `fotos` (`id`, `evento_id`, `ruta_foto`, `thumbnail`, `orden`, `created_at`, `uploaded_by`, `ruta`, `descripcion`) VALUES
(21, 1, '', NULL, 0, '2025-11-02 20:05:08', NULL, '/Proyect-Boliche/uploads/galeria/2025/11/fdd/amtes-6907b974a23e2.webp', 'xxxxxxx'),
(22, 1, '', NULL, 0, '2025-11-02 20:05:08', NULL, '/Proyect-Boliche/uploads/galeria/2025/11/fdd/milei-6907b974e8be8.webp', 'xxxxxxx'),
(23, 1, '', NULL, 0, '2025-11-02 20:05:09', NULL, '/Proyect-Boliche/uploads/galeria/2025/11/fdd/programaia-6907b97501828.webp', 'xxxxxxx'),
(24, 1, '', NULL, 0, '2025-11-02 20:05:09', NULL, '/Proyect-Boliche/uploads/galeria/2025/11/fdd/images-6907b9750b9c6.png', 'xxxxxxx');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','editor') NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `rol`, `nombre`, `email`, `created_at`, `last_login`) VALUES
(4, 'admin', '$2y$10$CqSwLJgJ6WwR16hl3hitEe.yh98si8PRED6mrRoJe.5SqhEbMYcv6', 'admin', 'Administrador', 'admin@boliche.com', '2025-11-01 19:31:46', '2025-11-02 19:56:06');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `evento_categoria`
--
ALTER TABLE `evento_categoria`
  ADD PRIMARY KEY (`evento_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `fotos`
--
ALTER TABLE `fotos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_unique` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `fotos`
--
ALTER TABLE `fotos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `evento_categoria`
--
ALTER TABLE `evento_categoria`
  ADD CONSTRAINT `evento_categoria_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evento_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fotos`
--
ALTER TABLE `fotos`
  ADD CONSTRAINT `fotos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fotos_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
