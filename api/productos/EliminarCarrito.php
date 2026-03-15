<?php
// api/productos/EliminarCarrito.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

// Recibimos los datos enviados desde MAUI (método POST)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$car_id = $data['car_id'] ?? null;
$cli_id = $data['cli_id'] ?? null;

if (!$car_id || !$cli_id) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos para eliminar']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // Ejecutamos tu función existente
    $exito = $modelo->eliminarItemCarrito($car_id, $cli_id);
    
    echo json_encode(['success' => $exito]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>