<?php
// models/TipoServicio.php

class TipoServicioModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ====================================================================
    // 1. LISTAR CATEGORÍAS (Filtrado por Negocio)
    // ====================================================================
    public function listar($negocioId, $estado = 'A') {
        // Seleccionamos solo las categorías que pertenecen al negocio logueado
        $sql = "SELECT * FROM tbl_tipo_servicio 
                WHERE neg_id = :negId AND tser_estado = :estado 
                ORDER BY tser_nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':negId' => $negocioId, ':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // 2. GUARDAR NUEVA CATEGORÍA
    // ====================================================================
    public function guardar($nombre, $negocioId) {
        $sql = "INSERT INTO tbl_tipo_servicio (neg_id, tser_nombre, tser_estado) 
                VALUES (:negId, :nombre, 'A')";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':negId'  => $negocioId,
            ':nombre' => $nombre
        ]);
    }

    // ====================================================================
    // 3. OBTENER POR ID (Validando propiedad)
    // ====================================================================
    public function obtenerPorId($id, $negocioId) {
        // Importante: AND neg_id = :negId evita que editen categorías ajenas
        $sql = "SELECT * FROM tbl_tipo_servicio 
                WHERE tser_id = :id AND neg_id = :negId LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // 4. ACTUALIZAR
    // ====================================================================
    public function actualizar($id, $nombre, $negocioId) {
        $sql = "UPDATE tbl_tipo_servicio 
                SET tser_nombre = :nombre 
                WHERE tser_id = :id AND neg_id = :negId";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $nombre,
            ':id'     => $id,
            ':negId'  => $negocioId
        ]);
    }

    // ====================================================================
    // 5. ELIMINAR (Papelera)
    // ====================================================================
    public function eliminarLogico($id, $negocioId) {
        $sql = "UPDATE tbl_tipo_servicio SET tser_estado = 'I' 
                WHERE tser_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }

    // ====================================================================
    // 6. REACTIVAR
    // ====================================================================
    public function reactivar($id, $negocioId) {
        $sql = "UPDATE tbl_tipo_servicio SET tser_estado = 'A' 
                WHERE tser_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }
}