<?php

// ------------------------------------------------------------------
// 1. CONFIGURACIÓN GLOBAL DE ERRORES Y LOGGING
// ------------------------------------------------------------------
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/myphp-errors.log'); // Ruta para guardar logs
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ocultar errores al usuario final por seguridad

/**
 * @param string $message Mensaje a registrar.
 * @param string $level Nivel de severidad (INFO, WARNING, ERROR, CRITICAL).
 */
function log_message(string $message, string $level = 'INFO'): void {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[{$timestamp}] [{$level}] " . $message . "\n", 3, __DIR__ . '/myphp-errors.log');
}

// ------------------------------------------------------------------
// 2. CONFIGURACIÓN CRÍTICA DE CORS (Cross-Origin Resource Sharing)
// ------------------------------------------------------------------
$allowedOrigin = 'http://localhost:4200'; // Angular frontend
header("Access-Control-Allow-Origin: " . $allowedOrigin);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar la solicitud de pre-vuelo OPTIONS (CRÍTICO para peticiones POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ------------------------------------------------------------------
// 3. AUTOLOAD DE DEPENDENCIAS (Composer, Stripe, PHPMailer)
// ------------------------------------------------------------------
require_once __DIR__ . '/vendor/autoload.php';

// ------------------------------------------------------------------
// 4. CONFIGURACIÓN DE SERVICIOS EXTERNOS (Stripe)
// ------------------------------------------------------------------
try {
    // ⚠️ REEMPLAZA CON TU CLAVE SECRETA REAL
    \Stripe\Stripe::setApiKey('sk_test_YOUR_ACTUAL_SECRET_KEY'); 
    log_message('Stripe API Key configurada.', 'DEBUG');
} catch (\Stripe\Exception\ApiErrorException $e) {
    log_message("Error de configuración de Stripe: " . $e->getMessage(), 'CRITICAL');
    http_response_code(500);
    echo json_encode(['error' => 'Error de configuración de servicios.']);
    exit;
} catch (\Exception $e) {
    log_message("Error de autoload: " . $e->getMessage(), 'CRITICAL');
    http_response_code(500);
    echo json_encode(['error' => 'Error de dependencias.']);
    exit;
}

// ------------------------------------------------------------------
// 5. ENRUTAMIENTO (ROUTER)
// ------------------------------------------------------------------

// Mapeo de paths a archivos reales en la carpeta /endpoints
$endpoints_map = [
    'get_photos'      => 'get_photos.php',
    'search_by_face'  => 'search_by_face.php',
    'create_checkout' => 'create_checkout.php',
    'verify_payment'  => 'verify_payment.php',
    'send_contact'    => 'send_contact.php',
    // Puedes añadir más rutas aquí
];

// Obtener la ruta solicitada (ej: 'send_contact' de ?path=send_contact)
$endpoint = $_GET['path'] ?? null;

if (isset($endpoints_map[$endpoint])) {
    $file_path = __DIR__ . '/endpoints/' . $endpoints_map[$endpoint];
    
    // Verificar si el archivo existe antes de incluirlo
    if (file_exists($file_path)) {
        log_message("Ruta solicitada: {$endpoint}. Cargando archivo: {$file_path}", 'INFO');
        // El corazón del router: incluye y ejecuta el código del endpoint
        require_once $file_path; 
    } else {
        // Error 500 si el archivo está mapeado pero no existe
        log_message("Error 500: Archivo de endpoint no encontrado en la ruta: {$file_path}", 'CRITICAL');
        http_response_code(500);
        echo json_encode(['error' => 'Error de configuración del servidor.']);
    }

} else {
    // Error 404 si la ruta no está definida en el mapeo
    log_message("Error 404: Endpoint no encontrado para path: " . ($endpoint ?? 'Nulo'), 'WARNING');
    http_response_code(404);
    echo json_encode(['error' => 'Recurso no encontrado.']);
}

?>
