<?php
// ==========================================================
// ARCHIVO: /api/get_photos.php
// FUNCIÓN: Obtiene la lista de fotos y precios desde MongoDB.
// ==========================================================

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Incluimos la configuración de la base de datos
require_once 'db_config.php';
require_once 'vendor/autoload.php';

// La URL base de tu servidor PHP
// Angular usará esta URL para cargar las imágenes
$base_url = 'http://localhost:8082/api/images/'; // Ajusta el puerto si es diferente

try {
    // 1. Obtener la conexión a MongoDB
    $db = getMongoDB();
    
    // 2. Seleccionar la colección de fotos
    $photosCollection = $db->selectCollection('photos');
    
    // 3. Obtener todos los documentos de la colección
    // find({}) devuelve un cursor con todos los documentos.
    $cursor = $photosCollection->find([]);
    
    $photos = [];
    
    // 4. Procesar y formatear los resultados
    foreach ($cursor as $doc) {
        // Asegúrate de que el documento tenga los campos necesarios
        $id = $doc['id'] ?? null;
        $filename = $doc['filename'] ?? null;
        $caption = $doc['caption'] ?? 'Sin descripción';
        $price = $doc['price'] ?? 0;

        if ($id !== null && $filename !== null) {
            $photos[] = [
                'id' => (int)$id, // Aseguramos que el ID sea numérico
                'url' => $base_url . $filename, // Construimos la URL pública
                'caption' => $caption,
                'price' => (float)$price // Aseguramos el precio
            ];
        }
    }
    
    // 5. Respuesta exitosa
    http_response_code(200);
    echo json_encode($photos);
    
} catch (\Exception $e) {
    // Manejo de errores de la base de datos
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar las fotos: ' . $e->getMessage()]);
}
?>
