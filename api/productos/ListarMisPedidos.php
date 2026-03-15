<?php
// api/productos/ListarMisPedidos.php
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
    
    // Usamos la función del modelo que ya tienes (obtenerHistorialPedidos)
    $todos = $modelo->obtenerHistorialPedidos($cli_id);
    
    $activos = [];
    $historial = [];

    foreach ($todos as $p) {
        // 1. Verificamos si ya calificó (usando la función de tu modelo)
        $p['ya_calificado'] = $modelo->ordenYaCalificada($p['ord_id']) ? 1 : 0;

        // 2. Clasificación lógica según el estado
        // Estados "En Curso": PENDIENTE, ACEPTADO, EN_CAMINO, LISTO_RETIRO, RETIRO, PAGADO
        if (in_array($p['ord_estado'], ['PENDIENTE', 'ACEPTADO', 'EN_CAMINO', 'LISTO_RETIRO', 'RETIRO', 'PAGADO'])) {
            $activos[] = $p;
        } else {
            // Estados Finales: ENTREGADO, COMPLETADO, CANCELADO
            $historial[] = $p;
        }
    }

    echo json_encode([
        'success' => true,
        'activos' => $activos,
        'historial' => $historial,
        'conteo' => [
            'en_curso' => count($activos),
            'finalizados' => count($historial)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>