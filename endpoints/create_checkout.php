<?php
// /endpoints/create_checkout.php
// Integración de Stripe Checkout Session

// NOTA: Stripe, CORS y manejo de errores generales se configuran en router.php.

// ----------------------------------------------------
// 1. PROCESAMIENTO DE DATOS DE LA FOTO
// ----------------------------------------------------
$data = json_decode(file_get_contents("php://input"));
$photoId = $data->photoId ?? null;

if (!$photoId) {
    http_response_code(400); // Bad Request
    log_message("Error 400: ID de foto no proporcionado.", 'WARNING');
    echo json_encode(["error" => "ID de foto no proporcionado."]);
    exit;
}

// ** SIMULACIÓN DE DATOS (REEMPLAZAR CON BASE DE DATOS) **
$photosData = [
    1 => ['name' => 'Motocross', 'price' => 4000], 
    2 => ['name' => 'Buggi', 'price' => 4000], 
];

if (!isset($photosData[$photoId])) {
    http_response_code(404);
    log_message("Error 404: Foto con ID {$photoId} no encontrada para checkout.", 'WARNING');
    echo json_encode(["error" => "Foto con ID {$photoId} no encontrada."]);
    exit;
}

$photo = $photosData[$photoId];

try {
    // 2. CREACIÓN DE LA SESIÓN DE STRIPE CHECKOUT
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
        
        // 3. URLs de Redirección (¡CRÍTICO!)
        'success_url' => 'http://localhost:4200/success?session_id={CHECKOUT_SESSION_ID}&photoId=' . $photoId, 
        'cancel_url' => 'http://localhost:4200/gallery',
    ]);

    // 4. RESPUESTA EXITOSA
    http_response_code(200);
    log_message("Checkout de Stripe creado exitosamente para photoId: {$photoId}. Session ID: {$checkout_session->id}", 'INFO');
    echo json_encode(['url' => $checkout_session->url]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    // 5. Manejo de errores de Stripe
    log_message("Error de Stripe al crear sesión para photoId {$photoId}: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor de pago.']);
} catch (Error $e) {
    // 6. Manejo de errores generales de PHP
    log_message("Error PHP general en checkout: " . $e->getMessage(), 'CRITICAL');
    http_response_code(500);
    echo json_encode(['error' => 'Ocurrió un error inesperado.']);
}
?>