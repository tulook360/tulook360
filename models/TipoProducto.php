<?php
// models/TipoProducto.php

class TipoProductoModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar($negocioId, $estado = 'A') {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_tipo_producto WHERE neg_id = :nid AND tpro_estado = :est ORDER BY tpro_nombre");
        $stmt->execute([':nid' => $negocioId, ':est' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($nombre, $negocioId) {
        $stmt = $this->pdo->prepare("INSERT INTO tbl_tipo_producto (neg_id, tpro_nombre, tpro_estado) VALUES (:nid, :nom, 'A')");
        return $stmt->execute([':nid' => $negocioId, ':nom' => $nombre]);
    }

    // ====================================================================
    // 3. OBTENER POR ID (Para editar, validando propiedad)
    // ====================================================================
    public function obtenerPorId($id, $negocioId) {
        $sql = "SELECT * FROM tbl_tipo_producto 
                WHERE tpro_id = :id AND neg_id = :negId LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // 4. ACTUALIZAR
    // ====================================================================
    public function actualizar($id, $nombre, $negocioId) {
        $sql = "UPDATE tbl_tipo_producto 
                SET tpro_nombre = :nombre 
                WHERE tpro_id = :id AND neg_id = :negId";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $nombre,
            ':id'     => $id,
            ':negId'  => $negocioId
        ]);
    }

    // ====================================================================
    // 5. ELIMINAR LÓGICO (Papelera)
    // ====================================================================
    public function eliminarLogico($id, $negocioId) {
        $sql = "UPDATE tbl_tipo_producto SET tpro_estado = 'I' 
                WHERE tpro_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }

    // ====================================================================
    // 6. REACTIVAR
    // ====================================================================
    public function reactivar($id, $negocioId) {
        $sql = "UPDATE tbl_tipo_producto SET tpro_estado = 'A' 
                WHERE tpro_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }
}