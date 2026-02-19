<?php
// nucleo/Menu.php
require_once __DIR__ . '/../config/database.php';

class Menu {
    public static function obtener(int $rolId): array {
        $db = new Database();
        $pdo = $db->getConnection();

        $sql = "
            SELECT DISTINCT
                a.acc_nombre,
                a.acc_controlador, 
                a.acc_metodo,
                a.acc_icono,
                m.menu_id,
                m.menu_nombre
            FROM tbl_accion a
            LEFT JOIN tbl_menu m ON a.menu_id = m.menu_id
            INNER JOIN tbl_permiso p ON a.acc_id = p.acc_id
            WHERE p.rol_id = :rolId
              AND a.acc_estado = 'A'
              AND (m.menu_estado = 'A' OR m.menu_estado IS NULL)
              AND a.acc_visible = 1  -- SOLO LAS VISIBLES EN EL MENU
            ORDER BY m.menu_id ASC, a.acc_id ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':rolId' => $rolId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $menu = ['sueltas' => [], 'carpetas' => []];

        foreach ($resultados as $row) {
            $url = $row['acc_controlador'] . '/' . $row['acc_metodo'];
            $item = ['titulo' => $row['acc_nombre'], 'url' => $url, 'icono' => $row['acc_icono']];

            if (empty($row['menu_id'])) {
                $menu['sueltas'][] = $item;
            } else {
                $nombreCarpeta = $row['menu_nombre'];
                if (!isset($menu['carpetas'][$nombreCarpeta])) {
                    $menu['carpetas'][$nombreCarpeta] = ['items' => []];
                }
                $menu['carpetas'][$nombreCarpeta]['items'][] = $item;
            }
        }
        return $menu;
    }
}