<?php
// api/productos/ListarTop.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // Recibir parámetros de paginación
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
    
    // Semilla para mantener el orden al cargar más (Misma lógica que servicios)
    if (session_status() === PHP_SESSION_NONE) session_start();
    $semilla = $_SESSION['semilla_servicios'] ?? 'backup_seed';

    // Usamos TU función exacta del modelo para traer productos
    $productos = $modelo->obtenerProductosDashboard($offset, $limit, $semilla);

    // Mapeamos los datos para que C# los lea exactamente igual que los Servicios
    // Así puedes reciclar la clase ResultadoBusqueda en MAUI
    $datosFormateados = array_map(function($p) {
        return [
            'id' => $p['pro_id'],
            'titulo' => $p['pro_nombre'],
            'precio' => $p['pro_precio'],
            'meta' => "Stock", // En productos usamos meta para indicar stock
            'negocio' => $p['neg_nombre'],
            'tipo' => 'producto', // Para saber que es un producto y no un servicio
            'imagen' => !empty($p['imagenes']) ? $p['imagenes'][0] : 'recursos/img/sin_foto.png',
            'galeria' => !empty($p['imagenes']) ? $p['imagenes'] : ['recursos/img/sin_foto.png'],
            'neg_logo' => !empty($p['neg_logo']) ? $p['neg_logo'] : 'recursos/img/sin_foto.png',
            'puntos_ganados' => intval($p['puntos_ganados']),
            'rating_promedio' => floatval($p['rating_promedio']),
            'votos_total' => intval($p['votos_total']),
            'presentacion' => floatval($p['pro_contenido']) . ' ' . $p['pro_unidad_consumo']
        ];
    }, $productos);

    echo json_encode([
        'success' => true, 
        'data' => $datosFormateados
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>