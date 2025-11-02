<?php

class FileHelper {
    private static function createDirectory($path) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    public static function getEventImagePath() {
        return self::createDirectory(__DIR__ . '/../uploads/eventos/');
    }

    public static function getGalleryPath($evento) {
        $fecha = new DateTime($evento['fecha']);
        $año = $fecha->format('Y');
        $mes = $fecha->format('m');
        $evento_slug = self::slugify($evento['titulo']);
        
        return self::createDirectory('D:/Archivos de programas/XAMPPg/htdocs/Proyect-Boliche/uploads/galeria/' . "{$año}/{$mes}/{$evento_slug}/");
    }

    public static function generateSafeFileName($originalName) {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = self::slugify(pathinfo($originalName, PATHINFO_FILENAME));
        return $name . '-' . uniqid() . '.' . $ext;
    }

    private static function slugify($text) {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Trim
        $text = trim($text, '-');
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // Lowercase
        $text = strtolower($text);
        return $text;
    }

    public static function getRelativePath($fullPath) {
        $basePath = 'D:/Archivos de programas/XAMPPg/htdocs/Proyect-Boliche/';
        $relativePath = str_replace($basePath, '', $fullPath);
        return '/Proyect-Boliche/' . str_replace('\\', '/', $relativePath);
    }
}