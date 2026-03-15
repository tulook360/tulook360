<?php
// api/productos/AgregarCarrito.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$cli_id = $data['cli_id'] ?? null;
$pro_id = $data['pro_id'] ?? null;
$cantidad = $data['cantidad'] ?? 1;
// Ignoramos promociones por ahora
$prom_id = 0; 

if (!$cli_id || !$pro_id) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos (Falta Usuario o Producto)']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // Tu misma función del modelo web
    $res = $modelo->agregarAlCarrito($cli_id, $pro_id, $cantidad, $prom_id);
    
    echo json_encode($res);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>