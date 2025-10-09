<?php
    // Archivo: /api/send_contact.php

    // ---------------------------------------------------------
    // Incluir PHPMailer (Asegúrate de haber corrido 'composer require phpmailer/phpmailer')
    // ---------------------------------------------------------
    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;


    // ---------------------------------------------------------
    // DIRECTIVAS DE DEBUGGING: Muestra errores detallados
    // CAMBIAR a 0 en PRODUCCIÓN por seguridad
    // ---------------------------------------------------------
    ini_set('display_errors', 1); // ⚠️ CAMBIAR a 0 en producción
    ini_set('display_startup_errors', 1); // ⚠️ CAMBIAR a 0 en producción
    error_reporting(E_ALL);
    // ---------------------------------------------------------

    // Cabeceras para permitir CORS (Comunicación con Angular)
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); 
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Manejar la solicitud de pre-vuelo OPTIONS (necesaria para CORS)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Obtener los datos JSON de Angular
    $data = json_decode(file_get_contents('php://input'), true);

    // Validación básica de datos
    if (!isset($data['name'], $data['email'], $data['message'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan datos en el formulario.']);
        exit();
    }

    $name = htmlspecialchars(trim($data['name']));
    $email = htmlspecialchars(trim($data['email']));
    $message = htmlspecialchars(trim($data['message']));

    $mail = new PHPMailer(true);

    // Crea un array con los datos recibidos para devolverlos
$debug_response = [
    'success' => true,
    'message' => '¡DEBUG MODE OK! Datos recibidos. El envío de correo real ha sido omitido.',
    'data_received' => [
        'name' => $name,
        'email' => $email,
        'message' => $message
    ]
];

http_response_code(200);
echo json_encode($debug_response);
exit();

    try {
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
        
        // Respuesta de Éxito a Angular
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Mensaje enviado.']);

    } catch (Exception $e) {
        // Respuesta de Error a Angular
        http_response_code(500);
        // Descomenta la siguiente línea para ver el error exacto de PHPMailer durante el debugging:
        // echo json_encode(['success' => false, 'message' => "Error al enviar: {$mail->ErrorInfo}"]);
        echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje. Intente más tarde.']);
    } 
?>