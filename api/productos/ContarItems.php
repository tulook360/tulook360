<?php
// api/carrito/VerCarrito.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$cli_id = $_GET['id_usuario'] ?? null;

if (!$cli_id) {
    echo json_encode(['success' => false, 'count' => 0, 'error' => 'Usuario no identificado']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // USAMOS TU FUNCIÓN EXISTENTE EXACTA
    $items = $modelo->obtenerCarrito($cli_id);
    
    // Devolvemos el mismo formato que espera tu web, incluyendo el 'count'
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items) // <- ¡AQUÍ ESTÁ TU NÚMERO RECICLADO!
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0, 'error' => $e->getMessage()]);
}
?>