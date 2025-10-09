<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// La URL base de tu servidor PHP
$base_url = 'http://localhost:8082/api/images/'; // Ajusta el puerto si es diferente

// Lista de fotos con URLs completas
$photos = [
    ['id' => 1, 'url' => $base_url . 'foto1.jpg', 'caption' => 'Motocross'],
    ['id' => 2, 'url' => $base_url . 'foto2.jpg', 'caption' => 'Off Road'],
    ['id' => 3, 'url' => $base_url . 'foto3.jpg', 'caption' => 'Motocross'],
    ['id' => 4, 'url' => $base_url . 'suzuki_2022.jpeg', 'caption' => 'Deportiva'],
];

echo json_encode($photos);
?>