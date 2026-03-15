<?php
// api/productos/ProcesarCompra.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$cli_id = $data['cli_id'] ?? null;

if (!$cli_id) {
    echo json_encode(['success' => false, 'error' => 'Usuario no identificado']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    $items = $modelo->obtenerCarrito($cli_id);

    if (empty($items)) throw new Exception("Carrito vacío");

    $subtotal = 0;
    foreach ($items as $i) { $subtotal += floatval($i['subtotal']); }

    $costoEnvio = ($data['tipo_entrega'] === 'DOMICILIO') 
        ? floatval($modelo->calcularCostoEnvioReal($items, $data['lat'], $data['lon'])['costo']) 
        : 0;

    $datosOrden = [
        'cli_id' => $cli_id,
        'codigo' => 'ORD-' . strtoupper(substr(uniqid(), -5)),
        'token' => "T360-" . bin2hex(random_bytes(4)),
        'subtotal' => $subtotal,
        'envio' => $costoEnvio,
        'total' => $subtotal + $costoEnvio,
        'tipo_entrega' => $data['tipo_entrega'],
        'direccion' => $data['direccion'] ?? '',
        'referencia' => $data['referencia'] ?? '',
        'lat' => $data['lat'] ?? 0,
        'lon' => $data['lon'] ?? 0
    ];

    $res = $modelo->registrarOrden($datosOrden, $items);

    // AQUÍ LA CORRECCIÓN: Nos aseguramos de enviar el ID como string pase lo que pase
    if($res['success']) {
        $res['ord_id'] = (string)$res['ord_id'];
    }

    echo json_encode($res);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}