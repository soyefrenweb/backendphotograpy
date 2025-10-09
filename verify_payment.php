<?php
// /api/verify_payment.php (NUEVO ARCHIVO)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:4200'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_YOUR_ACTUAL_SECRET_KEY'); // ¡Tu clave secreta!

// Datos de Angular
$data = json_decode(file_get_contents("php://input"), true);
$sessionId = $data['sessionId'] ?? null;
$photoId = $data['photoId'] ?? null;

if (!$sessionId || !$photoId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros de sesión.']);
    exit();
}

try {
    // 1. Consulta a Stripe: Obtener la sesión
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    // 2. Verificación CRÍTICA: ¿El pago fue exitoso?
    if ($session->payment_status === 'paid') {
        
        // 3. GENERAR TOKEN DE DESCARGA SEGURO
        // Esto evita que el usuario comparta el enlace de descarga simple.
        $token = bin2hex(random_bytes(16)); // Genera un token aleatorio de 32 caracteres

        // 4. ALMACENAR EL TOKEN: En un entorno real, guardarías este token en una DB
        // junto con el photoId y una fecha de expiración (ej. 1 hora) para que 'download.php' pueda verificarlo.
        
        // ** SIMULACIÓN DE ALMACENAMIENTO DE TOKEN (Reemplazar con DB en producción) **
        $token_storage = json_decode(file_get_contents('tokens.json') ?: '{}', true);
        $token_storage[$token] = ['photoId' => $photoId, 'expires' => time() + 3600]; // Expira en 1 hora
        file_put_contents('tokens.json', json_encode($token_storage));
        // ** FIN SIMULACIÓN **

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'downloadToken' => $token, // Devolvemos el token temporal a Angular
            'message' => 'Pago verificado exitosamente.'
        ]);
        
    } else {
        http_response_code(402); // Payment Required
        echo json_encode(['success' => false, 'message' => 'El pago no fue aprobado. Estado: ' . $session->payment_status]);
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()]);
}
?>