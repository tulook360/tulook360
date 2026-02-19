<?php
// models/Permiso.php

class PermisoModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ... (listarRolesConConteo y obtenerPermisosDelRol quedan IGUAL) ...
    
    public function listarRolesConConteo($negocioId) {
        $sql = "SELECT r.rol_id, r.rol_nombre, r.rol_estado, COUNT(p.per_id) as total_permisos
                FROM tbl_rol r
                LEFT JOIN tbl_permiso p ON r.rol_id = p.rol_id
                WHERE r.rol_estado = 'A'";

        if ($negocioId === null) {
            $sql .= " AND r.neg_id IS NULL";
            $sql .= " GROUP BY r.rol_id, r.rol_nombre, r.rol_estado ORDER BY r.rol_id ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } else {
            $sql .= " AND r.neg_id = :negId";
            $sql .= " GROUP BY r.rol_id, r.rol_nombre, r.rol_estado ORDER BY r.rol_id ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':negId' => $negocioId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPermisosDelRol($rolId) {
        $sql = "SELECT acc_id FROM tbl_permiso WHERE rol_id = :rolId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':rolId' => $rolId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ====================================================================
    // 3. OBTENER ACCIONES AGRUPADAS (OPTIMIZADO JERARQUÍA)
    // ====================================================================
    public function obtenerAccionesAgrupadas($esSuperAdmin = false) {
        // 1. Traer TODAS las acciones planas
        $sql = "SELECT 
                    a.acc_id, a.acc_nombre, a.acc_zona, a.acc_padre_id, a.menu_id,
                    COALESCE(m.menu_nombre, 'Acciones Sueltas') as nombre_carpeta,
                    m.menu_id as id_carpeta_orden
                FROM tbl_accion a
                LEFT JOIN tbl_menu m ON a.menu_id = m.menu_id
                WHERE a.acc_estado = 'A'";

        if (!$esSuperAdmin) {
            $sql .= " AND a.acc_zona IN ('NEG', 'AMB')";
        }
        
        // Ordenamos para procesar
        $sql .= " ORDER BY m.menu_id ASC, a.acc_id ASC";

        $stmt = $this->pdo->query($sql);
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Procesamiento en PHP (Armar el árbol)
        $padres = [];
        $hijos  = [];

        // Paso A: Separar Padres de Hijos
        foreach ($raw as $row) {
            if (empty($row['acc_padre_id'])) {
                // Es un Padre (Vista Principal)
                // Preparamos un array 'hijos' vacío dentro de él
                $row['mis_hijos'] = [];
                $padres[$row['acc_id']] = $row;
            } else {
                // Es un Hijo (Botón interno)
                $hijos[] = $row;
            }
        }

        // Paso B: Meter cada Hijo dentro de su Padre
        foreach ($hijos as $hijo) {
            $padreId = $hijo['acc_padre_id'];
            if (isset($padres[$padreId])) {
                $padres[$padreId]['mis_hijos'][] = $hijo;
            } else {
                // Caso extremo: Hijo huérfano (su padre no existe o está inactivo)
                // Lo convertimos en padre temporal para que no se pierda
                $hijo['nombre_carpeta'] = 'Huérfanos / Sin Padre';
                $hijo['mis_hijos'] = [];
                $padres[$hijo['acc_id']] = $hijo;
            }
        }

        // Paso C: Agrupar los Padres (ya rellenos) en Carpetas
        $resultadoFinal = [];
        foreach ($padres as $p) {
            $carpeta = $p['nombre_carpeta'];
            if (!isset($resultadoFinal[$carpeta])) {
                $resultadoFinal[$carpeta] = [];
            }
            $resultadoFinal[$carpeta][] = $p;
        }

        return $resultadoFinal;
    }

    // ... (guardarPermisos queda IGUAL) ...
    public function guardarPermisos($rolId, $accionesIds) {
        try {
            $this->pdo->beginTransaction();
            $sqlDel = "DELETE FROM tbl_permiso WHERE rol_id = :rolId";
            $stmtDel = $this->pdo->prepare($sqlDel);
            $stmtDel->execute([':rolId' => $rolId]);

            if (!empty($accionesIds) && is_array($accionesIds)) {
                $sqlIns = "INSERT INTO tbl_permiso (rol_id, acc_id) VALUES (:rolId, :accId)";
                $stmtIns = $this->pdo->prepare($sqlIns);
                foreach ($accionesIds as $accId) {
                    $stmtIns->execute([':rolId' => $rolId, ':accId' => $accId]);
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}