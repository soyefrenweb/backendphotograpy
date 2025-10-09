<?php
// /endpoints/search_by_face.php

// ----------------------------------------------------
// 1. VALIDACIÓN Y RECEPCIÓN DEL ARCHIVO
// ----------------------------------------------------

// El router.php ya garantizó que es un método POST.
if (!isset($_FILES['searchImage']) || $_FILES['searchImage']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); // Solicitud incorrecta
    log_message('Error 400: No se subió ningún archivo de búsqueda o hubo un error.', 'WARNING');
    echo json_encode(['error' => 'No se subió ningún archivo o hubo un error en la subida.']);
    exit();
}

$file = $_FILES['searchImage'];

// ----------------------------------------------------
// 2. SIMULACIÓN DEL RECONOCIMIENTO FACIAL 
// ----------------------------------------------------

$coincidencias = [1, 3]; // SIMULACIÓN

// ----------------------------------------------------
// 3. RESPUESTA A ANGULAR
// ----------------------------------------------------
if (empty($coincidencias)) {
    http_response_code(200);
    log_message('Búsqueda facial completada: 0 coincidencias.', 'INFO');
    echo json_encode([
        'photoIds' => [],
        'message' => 'Búsqueda completada, no se encontraron coincidencias faciales.'
    ]);
} else {
    http_response_code(200);
    log_message('Búsqueda facial completada: ' . count($coincidencias) . ' coincidencias encontradas.', 'INFO');
    echo json_encode([
        'photoIds' => $coincidencias,
        'message' => 'Fotos encontradas exitosamente.'
    ]);
}
?>