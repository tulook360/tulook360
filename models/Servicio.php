<?php
// models/Servicio.php

class ServicioModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function listar($negocioId, $estado = 'A', $busqueda = '') {
        $sql = "SELECT s.*, t.tser_nombre, 
                       IFNULL(GROUP_CONCAT(i.img_url SEPARATOR ','), '') as galeria_urls
                FROM tbl_servicio s
                LEFT JOIN tbl_tipo_servicio t ON s.tser_id = t.tser_id
                LEFT JOIN tbl_img_recurso ir ON s.serv_id = ir.img_ref_id AND ir.img_tipo = 'SERVICIO'
                LEFT JOIN tbl_imagen i ON ir.img_id = i.img_id
                WHERE s.neg_id = :negId AND s.serv_estado = :estado";
        
        $params = [':negId' => $negocioId, ':estado' => $estado];

        if (!empty($busqueda)) {
            $sql .= " AND s.serv_nombre LIKE :busqueda";
            $params[':busqueda'] = "%$busqueda%";
        }

        $sql .= " GROUP BY s.serv_id ORDER BY t.tser_nombre ASC, s.serv_nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosFormulario($negocioId) {
        $stmtCat = $this->pdo->prepare("SELECT * FROM tbl_tipo_servicio WHERE neg_id = ? AND tser_estado = 'A' ORDER BY tser_nombre");
        $stmtCat->execute([$negocioId]);
        
        $stmtSuc = $this->pdo->prepare("SELECT suc_id, suc_nombre FROM tbl_sucursal WHERE neg_id = ? AND suc_estado = 'A' ORDER BY suc_id ASC");
        $stmtSuc->execute([$negocioId]);

        $stmtIns = $this->pdo->prepare("SELECT pro_id, pro_nombre, pro_codigo, pro_unidad, pro_contenido, pro_unidad_consumo 
                                      FROM tbl_producto 
                                      WHERE neg_id = ? AND pro_insumo = 1 AND pro_estado = 'A' 
                                      ORDER BY pro_nombre ASC");
        $stmtIns->execute([$negocioId]);

        return [
            'categorias' => $stmtCat->fetchAll(PDO::FETCH_ASSOC),
            'sucursales' => $stmtSuc->fetchAll(PDO::FETCH_ASSOC),
            'insumos'    => $stmtIns->fetchAll(PDO::FETCH_ASSOC) 
        ];
    }

    public function guardar($datos, $asignaciones, $insumos, $galeria = []) {
        try {
            $this->pdo->beginTransaction();

            $sqlServ = "INSERT INTO tbl_servicio (tser_id, neg_id, serv_nombre, serv_descripcion, serv_precio, serv_duracion, serv_espera, serv_resumen, serv_estado) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'A')";
            $stmtServ = $this->pdo->prepare($sqlServ);
            $stmtServ->execute([
                $datos['tser_id'], $datos['neg_id'], $datos['nombre'], $datos['descripcion'], 
                $datos['precio'], $datos['duracion'], $datos['espera'], $datos['resumen']
            ]);
            $servicioId = $this->pdo->lastInsertId();

            if (!empty($galeria)) {
                $sqlImg = "INSERT INTO tbl_imagen (neg_id, img_url, img_principal, img_orden) VALUES (?, ?, 0, 0)";
                $stmtImg = $this->pdo->prepare($sqlImg);
                $sqlRec = "INSERT INTO tbl_img_recurso (img_id, img_tipo, img_ref_id) VALUES (?, 'SERVICIO', ?)";
                $stmtRec = $this->pdo->prepare($sqlRec);

                foreach ($galeria as $url) {
                    $stmtImg->execute([$datos['neg_id'], $url]);
                    $imgId = $this->pdo->lastInsertId();
                    $stmtRec->execute([$imgId, $servicioId]);
                }
            }

            if (!empty($asignaciones)) {
                $sqlAsig = "INSERT INTO tbl_servicio_sucursal (serv_id, suc_id, ss_precio_personalizado, ss_estado) VALUES (?, ?, ?, 'A')";
                $stmtAsig = $this->pdo->prepare($sqlAsig);
                foreach ($asignaciones as $sucId => $precioEspecial) {
                    $p = ($precioEspecial === '' || $precioEspecial == 0) ? null : $precioEspecial;
                    $stmtAsig->execute([$servicioId, $sucId, $p]);
                }
            }

            if (!empty($insumos)) {
                $sqlIns = "INSERT INTO tbl_servicio_insumo (serv_id, pro_id, si_cantidad) VALUES (?, ?, ?)";
                $stmtIns = $this->pdo->prepare($sqlIns);
                foreach ($insumos as $proId => $cantidad) {
                    if ($cantidad > 0) $stmtIns->execute([$servicioId, $proId, $cantidad]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerCompleto($id, $negocioId) {
        $sql = "SELECT * FROM tbl_servicio WHERE serv_id = ? AND neg_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $negocioId]);
        $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$servicio) return null;

        $sqlGal = "SELECT i.img_id, i.img_url FROM tbl_imagen i
                   INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id
                   WHERE ir.img_tipo = 'SERVICIO' AND ir.img_ref_id = ? ORDER BY i.img_id ASC";
        $stmtGal = $this->pdo->prepare($sqlGal);
        $stmtGal->execute([$id]);
        $servicio['galeria'] = $stmtGal->fetchAll(PDO::FETCH_ASSOC);

        $sqlAsig = "SELECT suc_id, ss_precio_personalizado FROM tbl_servicio_sucursal WHERE serv_id = ?";
        $stmtAsig = $this->pdo->prepare($sqlAsig);
        $stmtAsig->execute([$id]);
        $asignaciones = [];
        while ($row = $stmtAsig->fetch(PDO::FETCH_ASSOC)) {
            $asignaciones[$row['suc_id']] = $row['ss_precio_personalizado'];
        }
        $servicio['asignaciones'] = $asignaciones;

        $sqlReceta = "SELECT pro_id, si_cantidad FROM tbl_servicio_insumo WHERE serv_id = ?";
        $stmtReceta = $this->pdo->prepare($sqlReceta);
        $stmtReceta->execute([$id]);
        $receta = [];
        while ($row = $stmtReceta->fetch(PDO::FETCH_ASSOC)) {
            $receta[$row['pro_id']] = $row['si_cantidad'];
        }
        $servicio['receta'] = $receta;

        return $servicio;
    }

    public function actualizar($id, $datos, $asignaciones, $insumos, $nuevasFotos = []) {
        try {
            $this->pdo->beginTransaction();

            $sqlUpd = "UPDATE tbl_servicio SET tser_id=?, serv_nombre=?, serv_descripcion=?, serv_precio=?, serv_duracion=?, serv_espera=?, serv_resumen=?
                       WHERE serv_id=? AND neg_id=?";
            
            $this->pdo->prepare($sqlUpd)->execute([
                $datos['tser_id'], $datos['nombre'], $datos['descripcion'], $datos['precio'], 
                $datos['duracion'], $datos['espera'], $datos['resumen'], $id, $datos['neg_id']
            ]);

            if (!empty($nuevasFotos)) {
                $sqlImg = "INSERT INTO tbl_imagen (neg_id, img_url, img_principal, img_orden) VALUES (?, ?, 0, 0)";
                $stmtImg = $this->pdo->prepare($sqlImg);
                $sqlRec = "INSERT INTO tbl_img_recurso (img_id, img_tipo, img_ref_id) VALUES (?, 'SERVICIO', ?)";
                $stmtRec = $this->pdo->prepare($sqlRec);
                foreach ($nuevasFotos as $url) {
                    $stmtImg->execute([$datos['neg_id'], $url]);
                    $imgId = $this->pdo->lastInsertId();
                    $stmtRec->execute([$imgId, $id]);
                }
            }

            $this->pdo->prepare("DELETE FROM tbl_servicio_sucursal WHERE serv_id = ?")->execute([$id]);
            if (!empty($asignaciones)) {
                $sqlIns = "INSERT INTO tbl_servicio_sucursal (serv_id, suc_id, ss_precio_personalizado, ss_estado) VALUES (?, ?, ?, 'A')";
                $stmtIns = $this->pdo->prepare($sqlIns);
                foreach ($asignaciones as $sucId => $precioEspecial) {
                    $p = ($precioEspecial === '' || $precioEspecial == 0) ? null : $precioEspecial;
                    $stmtIns->execute([$id, $sucId, $p]);
                }
            }

            $this->pdo->prepare("DELETE FROM tbl_servicio_insumo WHERE serv_id = ?")->execute([$id]);
            if (!empty($insumos)) {
                $sqlInsRec = "INSERT INTO tbl_servicio_insumo (serv_id, pro_id, si_cantidad) VALUES (?, ?, ?)";
                $stmtInsRec = $this->pdo->prepare($sqlInsRec);
                foreach ($insumos as $proId => $cantidad) {
                    if ($cantidad > 0) $stmtInsRec->execute([$id, $proId, $cantidad]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function eliminarLogico($id, $negocioId) {
        $sql = "UPDATE tbl_servicio SET serv_estado = 'I' WHERE serv_id = ? AND neg_id = ?";
        $this->pdo->prepare($sql)->execute([$id, $negocioId]);
    }

    public function reactivar($id, $negocioId) {
        $sql = "UPDATE tbl_servicio SET serv_estado = 'A' WHERE serv_id = ? AND neg_id = ?";
        $this->pdo->prepare($sql)->execute([$id, $negocioId]);
    }

    // =========================================================================
    // LA JOYA DE LA CORONA: ELIMINAR FOTO CON '?' (IMPOSIBLE DE FALLAR)
    // =========================================================================
    public function eliminarFoto($imgId, $negocioId) {
        $stmt = $this->pdo->prepare("SELECT img_id FROM tbl_imagen WHERE img_id = ? AND neg_id = ?");
        $stmt->execute([$imgId, $negocioId]);
        
        if (!$stmt->fetch()) return false;

        $this->pdo->prepare("DELETE FROM tbl_img_recurso WHERE img_id = ?")->execute([$imgId]);
        return $this->pdo->prepare("DELETE FROM tbl_imagen WHERE img_id = ?")->execute([$imgId]);
    }
}