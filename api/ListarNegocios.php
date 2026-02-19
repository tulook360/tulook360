<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// 1. INCLUSIONES
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    require_once '../models/PublicoModelo.php';
} else {
    echo json_encode(['success' => false, 'error' => 'Faltan archivos de configuración']);
    exit;
}

// 2. RECIBIR DATOS
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // 3. LLAMAR A TU FUNCIÓN (Devuelve los datos puros de la BD)
    $negocios = $modelo->obtenerDirectorioNegocios($q);

    // 4. RESPUESTA DIRECTA (Sin modificar URLs)
    echo json_encode($negocios);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>