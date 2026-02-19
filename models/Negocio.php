<?php
// models/Negocio.php

require_once __DIR__ . '/../nucleo/TimeHelper.php'; // [IMPORTANTE] Importar el Helper

class NegocioModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Obtener tipos (Igual que antes)
    public function obtenerTipos() {
        $stmt = $this->pdo->query("SELECT * FROM tbl_tipo_negocio WHERE tneg_estado = 'A'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [NUEVO] Verificar si el correo ya existe para evitar errores fatales
    public function verificarCorreo($email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_usuario WHERE usu_correo = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0; // Devuelve true si ya existe
    }

    // [ACTUALIZADO] Registro con Transacción y TimeHelper
    public function registrarNuevoNegocio(array $datos) {
        try {
            $this->pdo->beginTransaction();

            // Usamos tu Helper para la hora de Ecuador
            $fechaHoraEc = TimeHelper::now(); 

            // 1. Insertar Negocio
            $sqlNeg = "INSERT INTO tbl_negocio 
                       (tneg_id, neg_nombre, neg_logo, neg_fundacion, neg_estado, neg_fecha_reg) 
                       VALUES (:tipo, :nombre, :logo, :fundacion, 'A', :fechaReg)";
            
            $stmtNeg = $this->pdo->prepare($sqlNeg);
            $stmtNeg->execute([
                ':tipo'      => $datos['tneg_id'],
                ':nombre'    => $datos['negocio_nombre'],
                ':logo'      => $datos['negocio_logo'],
                ':fundacion' => $datos['negocio_fundacion'],
                ':fechaReg'  => $fechaHoraEc
            ]);
            
            $negocioId = $this->pdo->lastInsertId();

            // 2. Insertar Usuario Admin (Password ya viene hasheada del controlador)
            $sqlUsu = "INSERT INTO tbl_usuario 
                       (rol_id, neg_id, usu_cedula, usu_nombres, usu_apellidos, usu_correo, usu_contrasena, usu_estado, usu_fecha_reg) 
                       VALUES (2, :negId, :cedula, :nombres, :apellidos, :correo, :pass, 'A', :fechaReg)";
            
            $stmtUsu = $this->pdo->prepare($sqlUsu);
            $stmtUsu->execute([
                ':negId'     => $negocioId,
                ':cedula'    => $datos['admin_cedula'],
                ':nombres'   => $datos['admin_nombres'],
                ':apellidos' => $datos['admin_apellidos'],
                ':correo'    => $datos['admin_correo'],
                ':pass'      => $datos['admin_pass'], // Ya es el hash
                ':fechaReg'  => $fechaHoraEc
            ]);

            $usuarioId = $this->pdo->lastInsertId();

            // 3. Vincular dueño
            $sqlUpdate = "UPDATE tbl_negocio SET adm_id = :usuId WHERE neg_id = :negId";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([':usuId' => $usuarioId, ':negId' => $negocioId]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ... (dentro de models/Negocio.php) ...

    // ====================================================================
    // 1. LISTAR NEGOCIOS (CON FILTRO DE ESTADO Y BÚSQUEDA)
    // ====================================================================
    public function listarNegociosConAdmin($estado = 'A', $busqueda = '') {
        $sql = "SELECT 
                    n.neg_id, n.neg_nombre, n.neg_logo, n.neg_estado, n.neg_fecha_reg,
                    u.usu_nombres, u.usu_apellidos, u.usu_cedula, u.usu_correo, u.usu_foto
                FROM tbl_negocio n
                INNER JOIN tbl_usuario u ON n.adm_id = u.usu_id
                WHERE n.neg_estado = :estado"; // <--- Filtro de estado agregado

        $params = [':estado' => $estado];

        if (!empty($busqueda)) {
            // Usamos parámetros únicos para evitar conflictos en PDO
            $sql .= " AND (u.usu_cedula LIKE :b1 OR u.usu_nombres LIKE :b2 OR n.neg_nombre LIKE :b3)";
            $termino = "%$busqueda%";
            $params[':b1'] = $termino;
            $params[':b2'] = $termino;
            $params[':b3'] = $termino;
        }

        $sql .= " ORDER BY n.neg_fecha_reg DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // 2. REPORTE 360 DEL NEGOCIO (Para la vista 'Ver')
    // ====================================================================
    public function obtenerReporteCompleto($negocioId) {
        $reporte = [];

        // A) Datos del Negocio y Dueño
        $sqlInfo = "SELECT n.*, u.usu_nombres, u.usu_apellidos, u.usu_correo, u.usu_cedula, u.usu_foto
                    FROM tbl_negocio n
                    INNER JOIN tbl_usuario u ON n.adm_id = u.usu_id
                    WHERE n.neg_id = :id";
        $stmt = $this->pdo->prepare($sqlInfo);
        $stmt->execute([':id' => $negocioId]);
        $reporte['info'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // B) Conteo de Empleados por Rol
        $sqlRoles = "SELECT r.rol_nombre, COUNT(u.usu_id) as total_empleados
                     FROM tbl_rol r
                     LEFT JOIN tbl_usuario u ON r.rol_id = u.rol_id AND u.usu_estado = 'A'
                     WHERE r.neg_id = :id
                     GROUP BY r.rol_id, r.rol_nombre";
        $stmt = $this->pdo->prepare($sqlRoles);
        $stmt->execute([':id' => $negocioId]);
        $reporte['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // C) Matrices / Sucursales
        $sqlSuc = "SELECT * FROM tbl_sucursal WHERE neg_id = :id";
        $stmt = $this->pdo->prepare($sqlSuc);
        $stmt->execute([':id' => $negocioId]);
        $reporte['sucursales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $reporte;
    }

    // ====================================================================
    // 3. DESACTIVACIÓN TOTAL (NUCLEAR)
    // ====================================================================
    public function desactivarNegocioCompleto($negocioId) {
        try {
            $this->pdo->beginTransaction();

            // 1. Apagar el Negocio
            $stmt = $this->pdo->prepare("UPDATE tbl_negocio SET neg_estado = 'I' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // 2. Apagar TODOS los usuarios (Admin + Empleados)
            $stmt = $this->pdo->prepare("UPDATE tbl_usuario SET usu_estado = 'I' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // 3. Apagar Roles
            $stmt = $this->pdo->prepare("UPDATE tbl_rol SET rol_estado = 'I' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // 4. Apagar Sucursales
            $stmt = $this->pdo->prepare("UPDATE tbl_sucursal SET suc_estado = 'I' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // (Aquí podrías apagar productos, servicios, promociones si quisieras ser más drástico)

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ====================================================================
    // 4. REACTIVACIÓN TOTAL (RESURRECCIÓN)
    // ====================================================================
    public function reactivarNegocioCompleto($negocioId) {
        try {
            $this->pdo->beginTransaction();

            // 1. Reactivar el Negocio
            $stmt = $this->pdo->prepare("UPDATE tbl_negocio SET neg_estado = 'A' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // 2. Reactivar TODOS los usuarios (Admin + Empleados)
            $stmt = $this->pdo->prepare("UPDATE tbl_usuario SET usu_estado = 'A' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // 3. Reactivar Roles
            $stmt = $this->pdo->prepare("UPDATE tbl_rol SET rol_estado = 'A' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            // 4. Reactivar Sucursales
            $stmt = $this->pdo->prepare("UPDATE tbl_sucursal SET suc_estado = 'A' WHERE neg_id = :id");
            $stmt->execute([':id' => $negocioId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}