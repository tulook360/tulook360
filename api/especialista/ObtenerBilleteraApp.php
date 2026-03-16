<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/EspecialistaModelo.php';
date_default_timezone_set('America/Guayaquil');

$usu_id = $_GET['usu_id'] ?? null;
$f_ini = $_GET['f_ini'] ?? date('Y-m-01');
$f_fin = $_GET['f_fin'] ?? date('Y-m-d');

if (!$usu_id) {
    echo json_encode(['success' => false, 'error' => 'Falta el ID del especialista']);
    exit;
}

try {
    $db = new Database();
    $modelo = new EspecialistaModelo($db->getConnection());

    $resumen = $modelo->obtenerMetricasComisiones($usu_id, $f_ini, $f_fin);
    $historial = $modelo->obtenerHistorialComisiones($usu_id, $f_ini, $f_fin);

    echo json_encode([
        'success' => true,
        'totales' => [
            'comision' => floatval($resumen['total_comision'] ?? 0),
            'servicios' => intval($resumen['total_servicios'] ?? 0),
            'generado' => floatval($resumen['total_generado'] ?? 0)
        ],
        'historial' => $historial
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al cargar ganancias']);
}
?>