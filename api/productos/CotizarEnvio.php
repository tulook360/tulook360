<?php
// api/productos/CotizarEnvio.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$cli_id = $data['cli_id'] ?? null;
$lat = $data['lat'] ?? null;
$lon = $data['lon'] ?? null;

if (!$cli_id || $lat === null || $lon === null) {
    echo json_encode(['success' => false, 'error' => 'Datos de ubicación o usuario incompletos']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // 1. Obtenemos lo que tiene en el carrito para saber de qué negocios son los productos
    $items = $modelo->obtenerCarrito($cli_id);
    
    if (empty($items)) {
        echo json_encode(['success' => false, 'error' => 'El carrito está vacío']);
        exit;
    }

    // 2. Usamos tu función matemática Haversine que ya tienes en el Modelo
    $resultado = $modelo->calcularCostoEnvioReal($items, $lat, $lon);
    
    echo json_encode([
        'success' => true, 
        'costo' => $resultado['costo'], 
        'kms' => $resultado['kms']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>