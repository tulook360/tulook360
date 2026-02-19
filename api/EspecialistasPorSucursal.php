<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once '../config/database.php';
require_once '../models/PublicoModelo.php';

$serv_id = isset($_GET['servicio']) ? intval($_GET['servicio']) : 0;
$suc_id  = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : 0;

if ($serv_id <= 0 || $suc_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos (servicio o sucursal)']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // CONSULTA FILTRADA: 
    // 1. Que el usuario esté activo.
    // 2. Que pertenezca a la sucursal seleccionada (u.suc_id).
    // 3. Que tenga la habilidad para este servicio (tbl_empleado_habilidad).
    // 4. Que tenga horario asignado (opcional, pero recomendado para no mostrar gente sin turno).

    $sql = "SELECT DISTINCT u.usu_id, u.usu_nombres, u.usu_apellidos, u.usu_foto, u.usu_calificacion
            FROM tbl_usuario u
            INNER JOIN tbl_empleado_habilidad h ON u.usu_id = h.usu_id
            INNER JOIN tbl_servicio s ON h.tser_id = s.tser_id
            WHERE s.serv_id = :serv_id 
            AND u.suc_id = :suc_id
            AND u.usu_estado = 'A'
            AND EXISTS (SELECT 1 FROM tbl_empleado_horario eh WHERE eh.usu_id = u.usu_id)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':serv_id' => $serv_id, ':suc_id' => $suc_id]);
    $especialistas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay nadie, devolvemos array vacío pero success true (para que la App diga "No hay nadie disponible")
    if (empty($especialistas)) {
        echo json_encode(['success' => true, 'data' => []]);
    } else {
        // Limpiar fotos
        foreach ($especialistas as &$esp) {
            if (empty($esp['usu_foto'])) $esp['usu_foto'] = 'recursos/img/usuario_default.png';
        }
        echo json_encode(['success' => true, 'data' => $especialistas]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>