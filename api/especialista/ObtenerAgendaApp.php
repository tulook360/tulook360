<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/EspecialistaModelo.php'; // <--- MAGIA AQUÍ
date_default_timezone_set('America/Guayaquil');

$usu_id = $_GET['usu_id'] ?? null;

if (!$usu_id) {
    echo json_encode(['success' => false, 'error' => 'Falta el ID del especialista']);
    exit;
}

try {
    $db = new Database();
    $modelo = new EspecialistaModelo($db->getConnection());

    // 1. Citas HOY
    $citasHoy = $modelo->obtenerAgendaDelDia($usu_id);
    foreach ($citasHoy as &$c) {
        $c['receta'] = $modelo->obtenerInsumosPorServicio($c['serv_id'], $c['suc_id']);
        
        // Verificamos stock aquí para que MAUI no tenga que sufrir haciéndolo
        $allInStock = true;
        if (!empty($c['receta'])) {
            foreach ($c['receta'] as $r) {
                if ($r['ps_stock'] <= 0) { $allInStock = false; break; }
            }
        }
        $c['all_in_stock'] = $allInStock;
    }
    unset($c);

    // 2. Citas FUTURAS
    $citasFuturas = $modelo->obtenerCitasFuturas($usu_id);
    foreach ($citasFuturas as &$f) {
        $f['receta'] = $modelo->obtenerInsumosPorServicio($f['serv_id'], 0);
    }
    unset($f);

    echo json_encode([
        'success' => true,
        'hoy' => $citasHoy,
        'futuras' => $citasFuturas
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>