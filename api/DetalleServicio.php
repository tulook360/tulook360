<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    require_once '../models/PublicoModelo.php';
} else {
    echo json_encode(['success' => false, 'error' => 'Configuración no encontrada']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // Obtenemos solo Info Básica y Sucursales
    $data = $modelo->obtenerDetalleServicioModal($id);

    if ($data) {
        // Limpiamos URLs de sucursales
        foreach ($data['sucursales'] as &$suc) {
            if (empty($suc['suc_foto'])) $suc['suc_foto'] = 'recursos/img/sin_foto.png';
        }

        // ELIMINAMOS LA LISTA DE ESPECIALISTAS DE AQUÍ PARA NO CONFUNDIR
        unset($data['especialistas']); 

        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Servicio no encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>