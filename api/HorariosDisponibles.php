<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once '../config/database.php';
require_once '../models/PublicoModelo.php';

// Recibimos los 3 datos clave
$serv_id = isset($_GET['servicio']) ? intval($_GET['servicio']) : 0;
$usu_id  = isset($_GET['especialista']) ? intval($_GET['especialista']) : 0;
$fecha   = isset($_GET['fecha']) ? $_GET['fecha'] : ''; // Formato YYYY-MM-DD

if ($serv_id <= 0 || $usu_id <= 0 || empty($fecha)) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos (servicio, especialista o fecha)']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // Usamos tu algoritmo inteligente de tiempos
    $horas = $modelo->obtenerHorariosDisponibles($serv_id, $usu_id, $fecha);

    if ($horas === "DESCANSO") {
        echo json_encode([
            'success' => true, 
            'horas' => [], 
            'descanso' => true,
            'mensaje' => 'El especialista descansa este día.'
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'horas' => $horas, // Array de strings ["10:00", "10:15", ...]
            'descanso' => false 
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>