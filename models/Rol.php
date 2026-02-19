<?php
// models/Rol.php

class RolModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ====================================================================
    // LISTAR ROLES (AISLAMIENTO TOTAL)
    // ====================================================================
    public function listar($negocioId, $estado = 'A') {
        // Base de la consulta
        $sql = "SELECT * FROM tbl_rol WHERE rol_estado = :estado";

        if ($negocioId === null) {
            // CASO 1: SUPER ADMIN
            // Solo ve los roles GLOBALES (neg_id IS NULL)
            // No ve los roles personalizados de ningún negocio.
            $sql .= " AND neg_id IS NULL";
            $sql .= " ORDER BY rol_nombre ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':estado' => $estado]);

        } else {
            // CASO 2: ADMIN DE NEGOCIO
            // Solo ve SUS roles (neg_id = X)
            // Ya NO ve los globales.
            $sql .= " AND neg_id = :negId";
            $sql .= " ORDER BY rol_nombre ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':estado' => $estado, ':negId' => $negocioId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // GUARDAR (Funciona para ambos casos)
    // ====================================================================
    public function guardar($nombre, $negocioId) {
        // Si negocioId es NULL, se guarda como NULL (Global)
        // Si tiene valor, se guarda con el ID (Negocio)
        $sql = "INSERT INTO tbl_rol (rol_nombre, neg_id, rol_estado) 
                VALUES (:nombre, :negId, 'A')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':nombre' => $nombre, ':negId' => $negocioId]);
    }

    // ====================================================================
    // OBTENER POR ID (Estricto)
    // ====================================================================
    public function obtenerPorId($id, $negocioId) {
        if ($negocioId === null) {
            // Super Admin busca global
            $sql = "SELECT * FROM tbl_rol WHERE rol_id = :id AND neg_id IS NULL LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } else {
            // Negocio busca suyo
            $sql = "SELECT * FROM tbl_rol WHERE rol_id = :id AND neg_id = :negId LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // ACTUALIZAR
    // ====================================================================
    public function actualizar($id, $nombre, $negocioId) {
        if ($negocioId === null) {
            $sql = "UPDATE tbl_rol SET rol_nombre = :nombre WHERE rol_id = :id AND neg_id IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nombre' => $nombre, ':id' => $id]);
        } else {
            $sql = "UPDATE tbl_rol SET rol_nombre = :nombre WHERE rol_id = :id AND neg_id = :negId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nombre' => $nombre, ':id' => $id, ':negId' => $negocioId]);
        }
        return true;
    }

    // ====================================================================
    // ELIMINAR LÓGICO
    // ====================================================================
    public function eliminarLogico($id, $negocioId) {
        if ($negocioId === null) {
            $sql = "UPDATE tbl_rol SET rol_estado = 'I' WHERE rol_id = :id AND neg_id IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } else {
            $sql = "UPDATE tbl_rol SET rol_estado = 'I' WHERE rol_id = :id AND neg_id = :negId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        }
        return true;
    }

    // ====================================================================
    // REACTIVAR
    // ====================================================================
    public function reactivar($id, $negocioId) {
        if ($negocioId === null) {
            $sql = "UPDATE tbl_rol SET rol_estado = 'A' WHERE rol_id = :id AND neg_id IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
        } else {
            $sql = "UPDATE tbl_rol SET rol_estado = 'A' WHERE rol_id = :id AND neg_id = :negId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        }
        return true;
    }

    // models/Rol.php

    // Listar roles permitidos para asignar a empleados
    public function listarRolesEmpleados() {
        // Traemos roles donde neg_id sea NULL (Globales)
        // Y EXCLUIMOS: 1 (Super Admin), 2 (Admin Negocio), 4 (Cliente)
        // Solo queremos: Admin Sucursal, Recepcionista, Especialista
        $sql = "SELECT * FROM tbl_rol 
                WHERE neg_id IS NULL 
                AND rol_estado = 'A' 
                AND rol_id NOT IN (1, 2, 4) 
                ORDER BY rol_nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}