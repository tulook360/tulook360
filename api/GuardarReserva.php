<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once '../config/database.php';
require_once '../models/PublicoModelo.php';

// Recibimos el JSON del móvil
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validamos datos mínimos
if (empty($data['cli_id']) || empty($data['serv_id']) || empty($data['especialista_id']) || empty($data['fecha']) || empty($data['hora'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos para la reserva']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // Armamos el array tal cual lo espera tu modelo
    $params = [
        'neg_id'          => $data['neg_id'],
        'suc_id'          => $data['suc_id'],
        'cli_id'          => $data['cli_id'], // ID del usuario logueado en la App
        'serv_id'         => $data['serv_id'],
        'especialista_id' => $data['especialista_id'],
        'fecha_completa'  => $data['fecha'] . ' ' . $data['hora'] . ':00', // Unimos fecha y hora
        'precio'          => $data['precio'],
        'notas'           => isset($data['notas']) ? $data['notas'] : '',
        'qr_token'        => isset($data['qr_token']) ? $data['qr_token'] : uniqid() // Generamos token si no viene
    ];

    // Guardamos usando la transacción de tu modelo
    $res = $modelo->registrarCitaCompleta($params);
    
    echo json_encode($res);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>