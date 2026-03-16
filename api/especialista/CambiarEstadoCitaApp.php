<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/EspecialistaModelo.php';
date_default_timezone_set('America/Guayaquil');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['id']) || empty($data['accion'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$estado = ($data['accion'] == 'start') ? 'EN_ATENCION' : 'FINALIZADO';

try {
    $db = new Database();
    $modelo = new EspecialistaModelo($db->getConnection());

    // El modelo hace TODO (puntos, inventario, comisiones)
    if($modelo->actualizarEstado($data['id'], $estado)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar el estado.']);
    }

} catch (Exception $e) {
    // Captura el mensaje exacto del modelo (ej: "Stock insuficiente")
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>