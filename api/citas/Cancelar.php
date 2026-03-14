<?php
header('Content-Type: application/json');
// Ojo con los dos '../' porque ahora estamos una carpeta más adentro
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['cita_id']) || !isset($data['cli_id'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos (cita_id o cli_id)']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    // Llamamos a la misma función que usa tu Web
    $resultado = $modelo->cancelarCitaUsuario($data['cita_id'], $data['cli_id']);
    echo json_encode($resultado);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}