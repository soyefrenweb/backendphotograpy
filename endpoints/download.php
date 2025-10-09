<?php
// ==========================================================
// ARCHIVO: /api/download.php
// FUNCIÓN: Verifica el token y sirve el archivo.
// ==========================================================

// Asegúrate de usar rutas seguras para tus imágenes, fuera del acceso web directo
$STORAGE_ROOT = __DIR__ . '/../private_storage/images/'; 

// Incluimos la configuración de la base de datos
require_once 'db_config.php'; 
require_once 'vendor/autoload.php';

use MongoDB\BSON\UTCDateTime;

// 1. Validar Token y ID
if (!isset($_GET['token']) || !isset($_GET['id'])) {
    http_response_code(400); 
    die("Token o ID de imagen no especificado.");
}

$downloadToken = $_GET['token'];
$photoId = intval($_GET['id']);

try {
    $db = getMongoDB();
    $tokensCollection = $db->selectCollection('download_tokens');
    $photosCollection = $db->selectCollection('photos');
    
    // 2. Buscar y obtener el token de descarga
    $tokenData = $tokensCollection->findOne([
        'token' => $downloadToken,
        'photoId' => $photoId
    ]);

    if (!$tokenData) {
        http_response_code(403); // Forbidden
        die("Acceso denegado: Token inválido o ya utilizado.");
    }
    
    // 3. Verificar expiración
    $expiresAt = $tokenData['expiresAt']->toDateTime(); // Convertir a objeto DateTime
    $currentTime = new \DateTime();

    if ($expiresAt < $currentTime) {
        // Opcional: Borrar token expirado
        $tokensCollection->deleteOne(['_id' => $tokenData['_id']]);
        http_response_code(403);
        die("Acceso denegado: Token expirado.");
    }

    // 4. Buscar la información del archivo (ruta) en la colección 'photos'
    $photoData = $photosCollection->findOne(['id' => $photoId]);

    if (!$photoData || !isset($photoData['filename'])) {
        http_response_code(404);
        die("Información de la imagen no encontrada en la base de datos.");
    }

    $filename = $photoData['filename'];
    $imagePath = $STORAGE_ROOT . $filename; 

    if (!file_exists($imagePath)) {
        http_response_code(404);
        die("El archivo de imagen no existe en el servidor.");
    }
    
    // 5. ELIMINAR EL TOKEN de la base de datos para evitar su reutilización (CRÍTICO)
    $tokensCollection->deleteOne(['_id' => $tokenData['_id']]);
    
    // 6. ENVIAR EL ARCHIVO AL NAVEGADOR (Descarga)
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . filesize($imagePath));
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    readfile($imagePath);

} catch (\Exception $e) {
    // Manejo de errores de base de datos o servidor
    http_response_code(500);
    die("Error interno del servidor: " . $e->getMessage());
}
?>
