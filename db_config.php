<?php
// ==========================================================
// ARCHIVO: /api/db_config.php
// FUNCIÓN: Manejar la conexión a MongoDB Atlas
// ==========================================================

require_once 'vendor/autoload.php';

use MongoDB\Client;

// 🔑 CLAVE CRÍTICA: REEMPLAZA ESTA CADENA con la que obtuviste de MongoDB Atlas
// Asegúrate de reemplazar <username>, <password> y clustername.
$mongo_uri = "mongodb+srv://fierroeften_db_user:KfuyipeCmIxIa0x4@websitephotogrphy.lxzomib.mongodb.net/";

// Nombre de tu Base de Datos (ej: 'fotografia-db')
$db_name = "fotografia-db"; 

/**
 * Establece la conexión con MongoDB y devuelve la base de datos.
 * @return \MongoDB\Database
 */
function getMongoDB() {
    global $mongo_uri, $db_name;
    
    try {
        // La conexión se hace a nivel de cliente.
        $client = new Client($mongo_uri);

        // Devolvemos la instancia de la base de datos que usaremos
        return $client->selectDatabase($db_name);
        
    } catch (\Exception $e) {
        // En caso de fallo de conexión (ej: error en URI, credenciales, o firewall)
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
        exit();
    }
}
?>
