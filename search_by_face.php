<?php
// /api/search_by_face.php
// Endpoint para recibir una imagen y simular la búsqueda por reconocimiento facial.

// ----------------------------------------------------
// 1. ENCABEZADOS CORS SEGUROS
// ----------------------------------------------------
// Define el origen permitido (Asegúrate de cambiarlo por tu dominio en producción)
$allowedOrigin = 'http://localhost:4200'; 
header("Access-Control-Allow-Origin: " . $allowedOrigin);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar la solicitud de pre-vuelo OPTIONS (necesaria para CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ----------------------------------------------------
// 2. VALIDACIÓN Y RECEPCIÓN DEL ARCHIVO
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido. Utiliza POST.']);
    exit();
}

if (!isset($_FILES['searchImage']) || $_FILES['searchImage']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'No se subió ningún archivo o hubo un error en la subida.']);
    exit();
}

$file = $_FILES['searchImage'];

// Opcional: Guardar el archivo subido temporalmente para su procesamiento (simulado)
// $uploadDir = 'temp_uploads/';
// if (!is_dir($uploadDir)) {
//     mkdir($uploadDir, 0777, true);
// }
// $tempFilePath = $uploadDir . basename($file['name']);
// move_uploaded_file($file['tmp_name'], $tempFilePath);

// ----------------------------------------------------
// 3. SIMULACIÓN DEL RECONOCIMIENTO FACIAL 
// ⚠️ En una aplicación real, aquí integrarías un SDK o harías una llamada a un servicio
// como AWS Rekognition o Face API de Google/Azure, usando el archivo subido ($file).
// ----------------------------------------------------

// Lógica de Simulación: Asignaremos IDs aleatorios para demostrar el flujo.
$coincidencias = [1, 3]; // SIMULACIÓN: Los rostros encontrados coinciden con las fotos con ID 1 y 3.

// Si quieres simular un fallo en la detección:
// $coincidencias = [];

// ----------------------------------------------------
// 4. RESPUESTA A ANGULAR
// ----------------------------------------------------
if (empty($coincidencias)) {
    http_response_code(200);
    echo json_encode([
        'photoIds' => [],
        'message' => 'Búsqueda completada, no se encontraron coincidencias faciales.'
    ]);
} else {
    http_response_code(200);
    // Devuelve la lista de IDs de fotos que coinciden.
    // Tu componente Angular (gallery.component.ts) usará estos IDs para filtrar originalPhotos.
    echo json_encode([
        'photoIds' => $coincidencias,
        'message' => 'Fotos encontradas exitosamente.'
    ]);
}

// Opcional: Limpiar el archivo subido temporalmente
// if (isset($tempFilePath) && file_exists($tempFilePath)) {
//     unlink($tempFilePath);
// }

?>