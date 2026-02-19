<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    require_once '../models/PublicoModelo.php';
} else {
    echo json_encode([]);
    exit;
}

$offset  = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit   = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
$semilla = isset($_GET['semilla']) ? $_GET['semilla'] : date('YmdH'); 

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    $datosRaw = $modelo->obtenerServiciosDashboard($offset, $limit, $semilla);

    $resultados = [];

    foreach ($datosRaw as $item) {
        
        // A) GESTIÓN DE IMÁGENES (AHORA TRAEMOS TODAS)
        $imgPortada = 'recursos/img/sin_foto.png';
        $galeriaCompleta = []; // Array nuevo

        if (!empty($item['imagenes']) && is_array($item['imagenes']) && count($item['imagenes']) > 0) {
            // 1. La primera es la portada
            $imgPortada = $item['imagenes'][0];
            
            // 2. Guardamos TODAS en la galería
            $galeriaCompleta = $item['imagenes'];
        } else {
            // Si no hay fotos, la galería tiene al menos la de "sin_foto"
            $galeriaCompleta[] = $imgPortada;
        }

        // B) LOGO
        $logoUrl = !empty($item['neg_logo']) ? $item['neg_logo'] : 'recursos/img/sin_foto.png';

        // C) OBJETO FINAL
        $resultados[] = [
            'id'           => $item['serv_id'],
            'titulo'       => $item['serv_nombre'],
            'precio'       => (string)$item['serv_precio'],
            'meta'         => (string)$item['serv_duracion'],
            'negocio'      => $item['neg_nombre'],
            'tipo'         => 'servicio',
            
            'imagen'       => $imgPortada,      // MANTENEMOS ESTO (FOTO PRINCIPAL)
            'galeria'      => $galeriaCompleta, // NUEVO: ARRAY CON TODAS LAS FOTOS
            
            'logo_negocio' => $logoUrl 
        ];
    }

    echo json_encode($resultados);

} catch (Exception $e) {
    echo json_encode([]);
}
?>