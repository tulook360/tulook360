<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());
    
    $resultado = $modelo->guardarCalificacion($data);
    echo json_encode(['success' => $resultado]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}