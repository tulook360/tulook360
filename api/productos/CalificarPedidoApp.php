<?php
// api/productos/CalificarPedidoApp.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

// 1. RECUPERAR DATOS DEL POST (JSON)
$json_datos = file_get_contents('php://input');
$entrada = json_decode($json_datos, true);

// 2. VALIDACIÓN BÁSICA
$id_orden   = $entrada['ord_id'] ?? null;
$id_cliente = $entrada['cli_id'] ?? null; // Recibido desde la App

if (!$id_orden || !$id_cliente) {
    echo json_encode(['success' => false, 'error' => 'Datos de identificación incompletos']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // 3. ESTRUCTURAR PAQUETE PARA EL MODELO
    // El modelo 'guardarResenaMasiva' ya maneja la transacción interna
    $paquete_resena = [
        'cli_id'     => $id_cliente,
        'ord_id'     => $id_orden,
        'comentario' => $entrada['comentario'] ?? '',
        'negocios'   => $entrada['negocios'] ?? [], // Array de {id, suc_id, rating}
        'productos'  => $entrada['productos'] ?? []  // Array de {pro_id, neg_id, suc_id, rating}
    ];

    // 4. EJECUTAR LÓGICA DEL MODELO
    $resultado = $modelo->guardarResenaMasiva($paquete_resena);

    if ($resultado['success']) {
        echo json_encode(['success' => true, 'mensaje' => 'Calificación guardada correctamente']);
    } else {
        echo json_encode($resultado);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>