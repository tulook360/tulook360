<?php
// models/Menu.php

class MenuModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ====================================================================
    // LISTAR CON FILTRO: Por defecto trae 'A' (Activos)
    // ====================================================================
    public function listar($estado = 'A') {
        $sql = "SELECT * FROM tbl_menu WHERE menu_estado = :estado ORDER BY menu_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // GUARDAR
    // ====================================================================
    public function guardar(string $nombre, string $descripcion) {
        $sql = "INSERT INTO tbl_menu (menu_nombre, menu_descripcion, menu_estado) 
                VALUES (:nombre, :desc, 'A')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':nombre' => $nombre, ':desc'   => $descripcion]);
    }

    // ====================================================================
    // OBTENER POR ID
    // ====================================================================
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM tbl_menu WHERE menu_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // ACTUALIZAR
    // ====================================================================
    public function actualizar($id, $nombre, $descripcion, $estado) {
        $sql = "UPDATE tbl_menu 
                SET menu_nombre = :nombre, 
                    menu_descripcion = :desc, 
                    menu_estado = :estado 
                WHERE menu_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $nombre, ':desc' => $descripcion, ':estado' => $estado, ':id' => $id
        ]);
    }

    // ====================================================================
    // ELIMINAR LOGICO (Estado 'I')
    // ====================================================================
    public function eliminarLogico($id) {
        $sql = "UPDATE tbl_menu SET menu_estado = 'I' WHERE menu_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ====================================================================
    // REACTIVAR (Estado 'A')
    // ====================================================================
    public function reactivar($id) {
        $sql = "UPDATE tbl_menu SET menu_estado = 'A' WHERE menu_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}