<?php
// models/Accion.php

class AccionModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // 1. LISTAR (Con nombre del Padre y Carpeta)
    // models/Accion.php

    // 1. LISTAR AGRUPADO (Padres con sus Hijos)
    public function listar($estado = 'A') {
        // Traemos todo plano primero
        $sql = "SELECT 
                    a.*, 
                    CONCAT(a.acc_controlador, '/', a.acc_metodo) as ruta_visual,
                    m.menu_nombre as carpeta,
                    p.acc_nombre as nombre_padre
                FROM tbl_accion a
                LEFT JOIN tbl_menu m ON a.menu_id = m.menu_id
                LEFT JOIN tbl_accion p ON a.acc_padre_id = p.acc_id
                WHERE a.acc_estado = :estado
                ORDER BY 
                    COALESCE(p.acc_id, a.acc_id) ASC, -- Agrupa por ID del Padre
                    a.acc_padre_id ASC,               -- El padre va primero (NULL)
                    a.acc_nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':estado' => $estado]);
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- PROCESAMIENTO PHP: AGRUPAR PADRE E HIJOS ---
        $agrupados = [];
        
        foreach ($raw as $row) {
            // Si es una acción PADRE (o suelta sin hijos)
            if (empty($row['acc_padre_id'])) {
                $id = $row['acc_id'];
                if (!isset($agrupados[$id])) {
                    $agrupados[$id] = $row;
                    $agrupados[$id]['hijos'] = []; // Preparamos array de hijos
                }
            } else {
                // Si es una HIJA
                $padreId = $row['acc_padre_id'];
                // Si el padre ya existe en el array, la metemos ahí
                if (isset($agrupados[$padreId])) {
                    $agrupados[$padreId]['hijos'][] = $row;
                } else {
                    // Caso raro: Hija huérfana (o padre filtrado/inactivo), la mostramos suelta
                    $agrupados['h_' . $row['acc_id']] = $row;
                    $agrupados['h_' . $row['acc_id']]['hijos'] = [];
                }
            }
        }

        return $agrupados;
    }

    // 2. OBTENER PADRES (Para el select de 'Hija de quién')
    public function obtenerPadres() {
        // Solo acciones que NO tienen padre (son principales) y están activas
        $sql = "SELECT acc_id, acc_nombre, acc_controlador 
                FROM tbl_accion 
                WHERE acc_padre_id IS NULL AND acc_estado = 'A'
                ORDER BY acc_nombre ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. GUARDAR (Nuevos campos separados)
    public function guardar($datos) {
        $sql = "INSERT INTO tbl_accion 
                (acc_nombre, acc_controlador, acc_metodo, acc_icono, menu_id, acc_padre_id, acc_zona, acc_visible, acc_estado) 
                VALUES (:nom, :con, :met, :ico, :men, :pad, :zon, :vis, 'A')";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom' => $datos['nombre'],
            ':con' => $datos['controlador'],
            ':met' => $datos['metodo'],
            ':ico' => $datos['icono'],
            ':men' => $datos['menu_id'],
            ':pad' => $datos['padre_id'],
            ':zon' => $datos['zona'],
            ':vis' => $datos['visible']
        ]);
    }

    // 4. ACTUALIZAR
    public function actualizar($id, $datos) {
        $sql = "UPDATE tbl_accion SET 
                    acc_nombre = :nom,
                    acc_controlador = :con,
                    acc_metodo = :met,
                    acc_icono = :ico,
                    menu_id = :men,
                    acc_padre_id = :pad,
                    acc_zona = :zon,
                    acc_visible = :vis
                WHERE acc_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom' => $datos['nombre'],
            ':con' => $datos['controlador'],
            ':met' => $datos['metodo'],
            ':ico' => $datos['icono'],
            ':men' => $datos['menu_id'],
            ':pad' => $datos['padre_id'],
            ':zon' => $datos['zona'],
            ':vis' => $datos['visible'],
            ':id'  => $id
        ]);
    }

    // 5. OBTENER POR ID
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM tbl_accion WHERE acc_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function eliminarLogico($id) {
        $sql = "UPDATE tbl_accion SET acc_estado = 'I' WHERE acc_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function reactivar($id) {
        $sql = "UPDATE tbl_accion SET acc_estado = 'A' WHERE acc_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}