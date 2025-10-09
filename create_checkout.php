<?php
// /api/create_checkout.php
// Integración de Stripe Checkout Session

// Configuración de Errores (Útil para depuración, pero mejor usar un logger profesional en producción)
ini_set('log_errors', 1);
ini_set('error_log', './stripe-errors.log'); 
error_reporting(E_ALL);
ini_set('display_errors', 0); 

// Incluye la librería de Stripe (Asegúrate de haber corrido 'composer install')
require_once 'vendor/autoload.php';

// ----------------------------------------------------
// 1. CONFIGURACIÓN CRÍTICA (¡DEBES REEMPLAZAR ESTO!)
// ----------------------------------------------------
try {
    // Establece la clave secreta de tu API de Stripe. ¡Cámbiala!
    \Stripe\Stripe::setApiKey('sk_test_YOUR_ACTUAL_SECRET_KEY'); 
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de configuración de Stripe."]);
    exit;
}

// ----------------------------------------------------
// 2. ENCABEZADOS CORS SEGUROS
// ----------------------------------------------------
// Define el origen permitido (mejor práctica que usar *)
$allowedOrigin = 'http://localhost:4200'; // Cámbialo por tu dominio en producción
header("Access-Control-Allow-Origin: " . $allowedOrigin);
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Maneja la solicitud preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204); 
    exit();
}

// ----------------------------------------------------
// 3. PROCESAMIENTO DE DATOS DE LA FOTO
// ----------------------------------------------------
$data = json_decode(file_get_contents("php://input"));
$photoId = $data->photoId ?? null;

if (!$photoId) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "ID de foto no proporcionado."]);
    exit;
}

// ** SIMULACIÓN DE DATOS (REEMPLAZAR CON BASE DE DATOS) **
// Un array fijo es inseguro porque un atacante podría adivinar IDs.
$photosData = [
    1 => ['name' => 'Motocross', 'price' => 4000], // 4000 cents = $40.00 MXN
    2 => ['name' => 'Buggi', 'price' => 4000], 
    // Asegúrate de que los precios están en CENTAVOS/PESOS (la unidad más pequeña de tu moneda)
];

if (!isset($photosData[$photoId])) {
    http_response_code(404);
    echo json_encode(["error" => "Foto con ID {$photoId} no encontrada."]);
    exit;
}

$photo = $photosData[$photoId];

try {
    // 4. CREACIÓN DE LA SESIÓN DE STRIPE CHECKOUT
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'mxn',
                'unit_amount' => $photo['price'], 
                'product_data' => [
                    'name' => $photo['name'] . ' (Descarga Digital)',
                ],
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        
        // 5. URLs de Redirección (¡CRÍTICO!)
        // El photoId es esencial para que tu SuccessComponent sepa qué archivo descargar.
        'success_url' => 'http://localhost:4200/success?session_id={CHECKOUT_SESSION_ID}&photoId=' . $photoId, 
        'cancel_url' => 'http://localhost:4200/gallery',
    ]);

    // 6. RESPUESTA EXITOSA
    http_response_code(200);
    // Devuelve la URL de redirección de Stripe al frontend de Angular
    echo json_encode(['url' => $checkout_session->url]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    // 7. Manejo de errores de Stripe
    error_log("Error de Stripe al crear sesión: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor de pago.']);
} catch (Error $e) {
    // 8. Manejo de errores generales de PHP
    error_log("Error PHP general: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error inesperado.']);
}
?>