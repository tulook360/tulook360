<?php
// api/productos/VerInfo.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$pro_id = $_GET['id'] ?? null;
$cli_id = $_GET['id_usuario'] ?? null; // Recibimos el usuario por si acaso

if (!$pro_id) {
    echo json_encode(['success' => false, 'error' => 'ID de producto requerido']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // Tu misma función del modelo web
    $data = $modelo->obtenerInfoProductoModal($pro_id, $cli_id);
    
    if ($data) {
        // Aseguramos que la presentación se envíe bonita
        $presentacion = floatval($data['pro_contenido']) . ' ' . $data['pro_unidad_consumo'];
        if ($data['pro_unidad'] !== 'Unidad') {
            $presentacion = $data['pro_unidad'] . ' de ' . $presentacion;
        }
        $data['txt_presentacion'] = $presentacion;

        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>