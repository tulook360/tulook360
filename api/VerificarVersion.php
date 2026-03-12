<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Importamos la conexión a la base de datos (ajusta la ruta si es necesario)
require_once '../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // LÓGICA: Buscamos SIEMPRE la versión con el número más alto (ORDER BY DESC)
    $sql = "SELECT ver_codigo, ver_nombre, ver_obligatorio, ver_novedades, ver_link_descarga 
            FROM tbl_app_version 
            ORDER BY ver_codigo DESC LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $version = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($version) {
        // Formateamos el "1" o "0" a true/false para que C# en MAUI lo entienda perfecto
        $version['ver_obligatorio'] = (bool)$version['ver_obligatorio'];
        
        echo json_encode(['success' => true, 'data' => $version]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No hay versiones registradas.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>