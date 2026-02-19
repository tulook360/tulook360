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

    // 1. LLAMADA A LA SUPER FUNCIÓN DEL MODELO
    $data = $modelo->obtenerPerfilNegocioFull($id);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Negocio no encontrado']);
        exit;
    }

    // 2. LIMPIEZA DE TIPOS DE DATOS (Solo precios, SIN tocar URLs)
    
    // C) Servicios (Precios a String)
    if (!empty($data['servicios'])) {
        foreach ($data['servicios'] as &$serv) {
            $serv['serv_precio'] = (string)$serv['serv_precio'];
        }
    }

    // D) Productos (Precios a String)
    if (!empty($data['productos'])) {
        foreach ($data['productos'] as &$prod) {
            $prod['pro_precio'] = (string)$prod['pro_precio'];
        }
    }

    // 3. RESPUESTA DIRECTA
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>