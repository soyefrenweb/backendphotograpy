<?php
// /api/verify_payment.php

// Incluir la configuración de la DB
require_once 'db_config.php'; 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:4200'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'vendor/autoload.php';
// Reemplaza con tu clave secreta
\Stripe\Stripe::setApiKey('sk_test_YOUR_ACTUAL_SECRET_KEY'); 

// ... (Código para recibir sessionId y photoId - igual que antes) ...
$data = json_decode(file_get_contents("php://input"), true);
$sessionId = $data['sessionId'] ?? null;
$photoId = $data['photoId'] ?? null;

if (!$sessionId || !$photoId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros de sesión.']);
    exit();
}

try {
    // 1. Consulta a Stripe: Obtener la sesión (IGUAL QUE ANTES)
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    // 2. Verificación CRÍTICA: ¿El pago fue exitoso?
    if ($session->payment_status === 'paid') {
        
        // 3. GENERAR TOKEN DE DESCARGA SEGURO
        $token = bin2hex(random_bytes(16)); 
        
        // 4. ALMACENAR EL TOKEN EN MONGODB 
        $db = getMongoDB();
        $tokensCollection = $db->selectCollection('download_tokens');

        $result = $tokensCollection->insertOne([
            'token' => $token,
            'photoId' => (int)$photoId,
            'sessionId' => $sessionId,
            'expiresAt' => new \MongoDB\BSON\UTCDateTime((time() + 3600) * 1000) // Expira en 1 hora, MongoDB usa milisegundos
        ]);

        if ($result->getInsertedCount() !== 1) {
             throw new \Exception("Fallo al guardar el token en la base de datos.");
        }
        // FIN ALMACENAMIENTO EN MONGODB

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'downloadToken' => $token, // Devolvemos el token temporal a Angular
            'message' => 'Pago verificado y token generado exitosamente.'
        ]);
        
    } else {
        http_response_code(402); // Payment Required
        echo json_encode(['success' => false, 'message' => 'El pago no fue aprobado.']);
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de Stripe: ' . $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
