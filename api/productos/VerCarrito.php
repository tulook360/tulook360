<?php
// api/productos/VerCarrito.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$cli_id = $_GET['id_usuario'] ?? null;

if (!$cli_id) {
    echo json_encode(['success' => false, 'error' => 'Usuario no identificado']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    $items = $modelo->obtenerCarrito($cli_id);
    
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>