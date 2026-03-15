<?php
// api/productos/CancelarPedidoApp.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

// Recibir datos por POST (JSON)
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$ord_id = $datos['ord_id'] ?? null;
$cli_id = $datos['cli_id'] ?? null;

if (!$ord_id || !$cli_id) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos para la cancelación']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // Usamos tu función existente que ya maneja transacciones y re-stocking
    $resultado = $modelo->cancelarOrdenUsuario($ord_id, $cli_id);
    
    echo json_encode($resultado);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>