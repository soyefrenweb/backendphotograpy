<?php
// /endpoints/send_contact.php
// NOTA: Autoload, CORS y manejo de errores se configuran en router.php.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------------------------------------------------------
// 1. Obtener y Validar Datos
// ---------------------------------------------------------
$data = json_decode(file_get_contents('php://input'), true);

log_message("Datos recibidos para Contacto: " . print_r($data, true), 'DEBUG');


// Validación básica de datos
if (!isset($data['name'], $data['email'], $data['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan datos en el formulario.']);
    exit();
}

$name = htmlspecialchars(trim($data['name']));
$email = htmlspecialchars(trim($data['email']));
$message = htmlspecialchars(trim($data['message']));

// ---------------------------------------------------------
// 2. Lógica de PHPMailer (Ahora solo usa la clase, ya cargada por Module.php)
// ---------------------------------------------------------
// Usa las clases cargadas por el autoload de Module.php
$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
        // ... (Tu configuración de PHPMailer) ...
        // ---------------------------------------------------------
        // CONFIGURACIÓN SMTP (¡Personalizar estos valores!)
        // ---------------------------------------------------------
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Servidor SMTP (Ej: smtp.gmail.com)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tu_correo@gmail.com';   // 🔑 TU DIRECCIÓN DE CORREO
        $mail->Password   = 'TU_CONTRASEÑA_O_APP_PASSWORD'; // 🔑 TU CONTRASEÑA O CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL/TLS
        $mail->Port       = 465; // Puerto para SMTPS

        // Destinatarios y Remitente
        $mail->setFrom('tu_correo@gmail.com', 'Sitio Web Contacto'); // 🔑 Tu correo
        $mail->addAddress('tu_destino@ejemplo.com', 'Destinatario'); // 🔑 CORREO DONDE QUIERES RECIBIR EL MENSAJE
        $mail->addReplyTo($email, $name); // Para responder al usuario directamente

        // Contenido del correo
        $mail->isHTML(false); // Correo de texto plano
        $mail->Subject = "Mensaje de contacto de: $name";
        $mail->Body    = "Nombre: $name\nCorreo: $email\nMensaje:\n" . $message;

    $mail->send();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado.']);

} catch (\Exception $e) {
    // Respuesta de Error a Angular
    // Usamos el log_message definido en Module.php
    log_message("Error al enviar el correo: " . $e->getMessage(), 'ERROR'); 
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al enviar. Por favor, intenta de nuevo más tarde.']);
}
?>