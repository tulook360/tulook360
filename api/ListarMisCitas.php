<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Guayaquil');

// 1. REUTILIZAMOS tus archivos de configuración y el modelo que ya funciona en WEB
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PublicoModelo.php';

$db = new Database();
$pdo = $db->getConnection();

// Instanciamos el modelo que ya tienes programado
$modelo = new PublicoModelo($pdo);

// 2. RECIBIMOS el ID del usuario desde la App
$idUsuario = $_GET['id_usuario'] ?? null;

if (!$idUsuario) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario requerido']);
    exit;
}

try {
    // 3. REUTILIZAMOS la lógica de limpieza que ya tienes en el modelo
    $modelo->actualizarCitasPerdidas($idUsuario);

    // 4. REUTILIZAMOS la función maestra del modelo para obtener las citas
    $citas = $modelo->obtenerMisCitas($idUsuario);

    // Devolvemos el resultado exactamente como lo sacó tu modelo
    echo json_encode([
        'success' => true,
        'data' => $citas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}