<?php
// Configuración de cabeceras
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// 1. INCLUSIONES
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
} else {
    echo json_encode([]);
    exit;
}

if (file_exists('../models/PublicoModelo.php')) {
    require_once '../models/PublicoModelo.php';
} else {
    echo json_encode([]);
    exit;
}

// 2. RECIBIR EL TÉRMINO DE BÚSQUEDA
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Validación: Si escriben menos de 2 letras, devolvemos lista vacía
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // 3. USAR TU FUNCIÓN EXISTENTE
    // Obtenemos los datos crudos de la base de datos
    $datosCrudos = $modelo->buscarVivo($q);

    // 4. TRANSFORMACIÓN DE DATOS (AQUÍ ARREGLAMOS EL ERROR)
    $resultadosListos = [];

    foreach ($datosCrudos as $item) {
        
        // A) BLINDAJE DE IMÁGENES
        $imgUrl = !empty($item['imagen']) ? $item['imagen'] : 'recursos/img/sin_foto.png';
        
        // Si tu función buscarVivo no devuelve 'neg_logo', ponemos uno por defecto para que no falle
        $logoUrl = !empty($item['neg_logo']) ? $item['neg_logo'] : 'recursos/img/sin_foto.png';

        // B) CREACIÓN DE GALERÍA (Para que el carrusel del móvil funcione)
        // Aunque sea una sola foto, la metemos en una lista []
        $galeria = [$imgUrl];

        // C) CONVERSIÓN DE TIPOS (ESTO SOLUCIONA EL CRASH DEL MÓVIL)
        // Forzamos que precio y meta sean STRING (Texto), no números.
        $precioStr = isset($item['precio']) ? (string)$item['precio'] : "0.00";
        $metaStr   = isset($item['meta']) ? (string)$item['meta'] : "";

        // Armamos el objeto final exacto como lo pide el C#
        $resultadosListos[] = [
            'id'           => intval($item['id']),
            'titulo'       => $item['titulo'],
            'precio'       => $precioStr,  // <--- Solución al error de conversión
            'meta'         => $metaStr,    // <--- Solución al error de conversión
            'negocio'      => $item['negocio'],
            'tipo'         => $item['tipo'],
            'imagen'       => $imgUrl,
            'galeria'      => $galeria,    // <--- Nuevo campo necesario
            'logo_negocio' => $logoUrl     // <--- Nuevo campo necesario
        ];
    }

    // 5. DEVOLVER JSON LIMPIO
    echo json_encode($resultadosListos);

} catch (Exception $e) {
    // En caso de error, devolvemos array vacío para no romper la app
    echo json_encode([]);
}
?>