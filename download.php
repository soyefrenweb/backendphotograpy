<?php
// /api/download.php (VERSIÓN SEGURA)

// Asegúrate de usar rutas seguras para tus imágenes, fuera del acceso web directo
$STORAGE_ROOT = __DIR__ . '/../private_storage/images/'; 

// 1. Validar Token y ID
if (!isset($_GET['token']) || !isset($_GET['id'])) {
    http_response_code(400); 
    die("Token o ID de imagen no especificado.");
}

$downloadToken = $_GET['token'];
$photoId = intval($_GET['id']);

// ** SIMULACIÓN DE VERIFICACIÓN DE TOKEN (Reemplazar con DB en producción) **
$token_storage = json_decode(file_get_contents('tokens.json') ?: '{}', true);

if (!isset($token_storage[$downloadToken])) {
    http_response_code(403); // Forbidden
    die("Acceso denegado: Token inválido.");
}

$tokenData = $token_storage[$downloadToken];

// 2. Verificar expiración y que el token corresponda al ID
if ($tokenData['expires'] < time() || $tokenData['photoId'] != $photoId) {
    http_response_code(403);
    die("Acceso denegado: Token expirado o incorrecto.");
}

// 3. Mapeo del ID a la ruta del archivo (o buscar en DB)
$photosPaths = [
    1 => 'foto1.jpg',
    2 => 'foto2.jpg',
    3 => 'foto3.jpg'
];

if (!isset($photosPaths[$photoId])) {
    http_response_code(404);
    die("Imagen no encontrada.");
}

$filename = $photosPaths[$photoId];
$imagePath = $STORAGE_ROOT . $filename; // Usando la ruta de almacenamiento segura

if (!file_exists($imagePath)) {
    http_response_code(404);
    die("El archivo de imagen no existe en el servidor.");
}

// 4. Invalida el token después de un solo uso (o deja que expire)
// unset($token_storage[$downloadToken]);
// file_put_contents('tokens.json', json_encode($token_storage));

// 5. Forzar la descarga
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($imagePath) . '"');
header('Content-Length: ' . filesize($imagePath));

readfile($imagePath);
exit;
?>