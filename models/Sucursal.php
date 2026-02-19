<?php
// models/Sucursal.php

class SucursalModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // --- MÉTODOS EXISTENTES (Listar, Guardar, ObtenerPorId, etc...) SE MANTIENEN IGUAL ---
    // (Pego aquí solo los cambios para no hacer spam, pero asume que el resto sigue ahí)

    public function listar($negocioId, $estado = 'A', $busqueda = '') {
        $sql = "SELECT * FROM tbl_sucursal WHERE neg_id = :negId AND suc_estado = :estado";
        $params = [':negId' => $negocioId, ':estado' => $estado];
        if (!empty($busqueda)) {
            $sql .= " AND suc_nombre LIKE :busqueda";
            $params[':busqueda'] = "%$busqueda%";
        }
        $sql .= " ORDER BY suc_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // [MODIFICADO] GUARDAR ahora retorna el ID insertado para poder guardar los horarios después
    public function guardar($datos) {
        $sql = "INSERT INTO tbl_sucursal 
                (neg_id, suc_nombre, suc_direccion, suc_latitud, suc_longitud, suc_telefono, suc_correo, suc_referencia, suc_foto, suc_estado) 
                VALUES 
                (:negId, :nom, :dir, :lat, :lon, :tel, :mail, :ref, :foto, 'A')";
        
        $stmt = $this->pdo->prepare($sql);
        $exito = $stmt->execute([
            ':negId' => $datos['neg_id'],
            ':nom'   => $datos['nombre'],
            ':dir'   => $datos['direccion'],
            ':lat'   => $datos['latitud'],
            ':lon'   => $datos['longitud'],
            ':tel'   => $datos['telefono'],
            ':mail'  => $datos['correo'],
            ':ref'   => $datos['referencia'],
            ':foto'  => $datos['foto']
        ]);

        // Si se guardó bien, devolvemos el ID de la nueva sucursal
        if ($exito) {
            return $this->pdo->lastInsertId(); 
        }
        return false;
    }

    public function obtenerPorId($id, $negocioId) {
        $sql = "SELECT * FROM tbl_sucursal WHERE suc_id = :id AND neg_id = :negId LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE tbl_sucursal SET 
                    suc_nombre = :nom, suc_direccion = :dir, suc_latitud = :lat, suc_longitud = :lon,
                    suc_telefono = :tel, suc_correo = :mail, suc_referencia = :ref, suc_foto = :foto 
                WHERE suc_id = :id AND neg_id = :negId";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'   => $datos['nombre'],
            ':dir'   => $datos['direccion'],
            ':lat'   => $datos['latitud'],
            ':lon'   => $datos['longitud'],
            ':tel'   => $datos['telefono'],
            ':mail'  => $datos['correo'],
            ':ref'   => $datos['referencia'],
            ':foto'  => $datos['foto'],
            ':id'    => $id,
            ':negId' => $datos['neg_id']
        ]);
    }

    public function eliminarLogico($id, $negocioId) {
        $sql = "UPDATE tbl_sucursal SET suc_estado = 'I' WHERE suc_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }

    public function reactivar($id, $negocioId) {
        $sql = "UPDATE tbl_sucursal SET suc_estado = 'A' WHERE suc_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id, ':negId' => $negocioId]);
    }

    // ==========================================================
    // [NUEVO] MÉTODOS PARA HORARIOS (tbl_sucursal_horario)
    // ==========================================================

    // A. Guardar o Actualizar masivamente los horarios
    public function guardarHorarios($sucursalId, $horarios) {
        // 1. Primero borramos los horarios anteriores de esta sucursal (Estrategia Limpieza Total)
        //    Esto evita tener que hacer UPDATE uno por uno o chequear si existe.
        $sqlDel = "DELETE FROM tbl_sucursal_horario WHERE suc_id = :sucId";
        $stmtDel = $this->pdo->prepare($sqlDel);
        $stmtDel->execute([':sucId' => $sucursalId]);

        // 2. Insertamos los nuevos
        $sqlIns = "INSERT INTO tbl_sucursal_horario (suc_id, sh_dia, sh_apertura, sh_cierre, sh_es_descanso) 
                   VALUES (:sucId, :dia, :apertura, :cierre, :descanso)";
        $stmtIns = $this->pdo->prepare($sqlIns);

        foreach ($horarios as $h) {
            $stmtIns->execute([
                ':sucId'    => $sucursalId,
                ':dia'      => $h['dia'],       // "Lunes", "Martes"...
                ':apertura' => $h['apertura'],  // "09:00"
                ':cierre'   => $h['cierre'],    // "18:00"
                ':descanso' => $h['es_descanso'] // 1 o 0
            ]);
        }
    }

    // B. Obtener horarios para la vista de Edición
    public function obtenerHorarios($sucursalId) {
        $sql = "SELECT * FROM tbl_sucursal_horario WHERE suc_id = :sucId ORDER BY sh_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sucId' => $sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Obtener días laborables de TODAS las sucursales de un negocio
    public function obtenerDiasLaborablesPorNegocio($negocioId) {
        // Traemos solo los días que NO son descanso (sh_es_descanso = 0 o NULL)
        $sql = "SELECT h.suc_id, h.sh_dia 
                FROM tbl_sucursal_horario h
                INNER JOIN tbl_sucursal s ON h.suc_id = s.suc_id
                WHERE s.neg_id = :neg 
                AND (h.sh_es_descanso = 0 OR h.sh_es_descanso IS NULL)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg' => $negocioId]);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // MAPEO PARA TRADUCIR LOS NÚMEROS A NOMBRES (Para el Frontend)
        $nombres = [1=>'Lunes', 2=>'Martes', 3=>'Miércoles', 4=>'Jueves', 5=>'Viernes', 6=>'Sábado', 7=>'Domingo'];

        // EL BLOQUE PHP QUE PROCESA LOS DATOS ES EL QUE DEBES CAMBIAR:
        $horarios = [];
        foreach ($filas as $f) {
            // AQUI ESTA EL ERROR: NO TRADUZCAS A NOMBRE. MANDA EL NÚMERO DIRECTO.
            // Antes tenías algo como: $dia = $nombres[$f['sh_dia']]...
            
            // CORRECCIÓN: Enviar el valor crudo (1, 2, 3...)
            // Aseguramos que sea entero para que el JS lo compare bien
            $horarios[$f['suc_id']][] = intval($f['sh_dia']); 
        }
        return $horarios;

    }
}