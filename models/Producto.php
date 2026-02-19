<?php
// models/Producto.php

class ProductoModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ====================================================================
    // 1. LISTAR PRODUCTOS (Con Galería para Carrusel)
    // ====================================================================
    public function listar($negocioId, $estado = 'A', $busqueda = '') {
        // Usamos GROUP_CONCAT para unir todas las URLs en una sola cadena separada por comas
        // IFNULL asegura que si no hay fotos, devuelva vacío en lugar de NULL
        $sql = "SELECT p.*, t.tpro_nombre,
                       IFNULL(GROUP_CONCAT(i.img_url SEPARATOR ','), '') as galeria_urls
                FROM tbl_producto p
                LEFT JOIN tbl_tipo_producto t ON p.tpro_id = t.tpro_id
                
                -- JOIN para traer las imágenes vinculadas
                LEFT JOIN tbl_img_recurso ir ON p.pro_id = ir.img_ref_id AND ir.img_tipo = 'PRODUCTO'
                LEFT JOIN tbl_imagen i ON ir.img_id = i.img_id
                
                WHERE p.neg_id = :negId AND p.pro_estado = :estado";
        
        $params = [':negId' => $negocioId, ':estado' => $estado];

        if (!empty($busqueda)) {
            $sql .= " AND (p.pro_nombre LIKE :b1 OR p.pro_codigo LIKE :b2)";
            $params[':b1'] = "%$busqueda%";
            $params[':b2'] = "%$busqueda%";
        }

        // IMPORTANTE: Agrupar por producto para que el GROUP_CONCAT funcione
        $sql .= " GROUP BY p.pro_id ORDER BY p.pro_nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // 2. OBTENER CATEGORÍAS
    // ====================================================================
    public function obtenerCategorias($negocioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_tipo_producto WHERE neg_id = :nid AND tpro_estado = 'A' ORDER BY tpro_nombre");
        $stmt->execute([':nid' => $negocioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // 3. GUARDAR (Producto + Galería + Factor de Conversión)
    // ====================================================================
    public function guardar($datos, $galeria = []) {
        try {
            $this->pdo->beginTransaction();

            // A. Insertar Producto (Bodega Global)
            // [CAMBIO] Se agregaron: pro_contenido, pro_unidad_consumo
            $sqlProd = "INSERT INTO tbl_producto 
            (tpro_id, neg_id, pro_nombre, pro_descripcion, pro_precio, pro_costo_compra, pro_stock, pro_codigo, pro_unidad, pro_contenido, pro_unidad_consumo, pro_insumo, pro_venta, pro_estado) 
            VALUES 
            (:tpro, :neg, :nom, :desc, :precio, :costo, :stock, :cod, :uni, :cont, :ucons, :ins, :ven, 'A')";

            $stmtProd = $this->pdo->prepare($sqlProd);
            $stmtProd->execute([
                ':tpro'   => $datos['tpro_id'],
                ':neg'    => $datos['neg_id'],
                ':nom'    => $datos['nombre'],
                ':desc'   => $datos['descripcion'],
                ':precio' => $datos['precio'],
                ':costo'  => $datos['costo'],
                ':stock'  => $datos['stock'],
                ':cod'    => $datos['codigo'],
                ':uni'    => $datos['unidad'],         // Unidad de Compra (Grande)
                ':cont'   => $datos['contenido'],      // Factor (Ej: 1000)
                ':ucons'  => $datos['unidad_consumo'], // Unidad de Uso (Pequeña)
                ':ins'    => $datos['insumo'],
                ':ven'    => $datos['venta']
            ]);

            $productoId = $this->pdo->lastInsertId();

            // B. Insertar Galería
            if (!empty($galeria)) {
                $sqlImg = "INSERT INTO tbl_imagen (neg_id, img_url, img_principal, img_orden) VALUES (:neg, :url, 0, 0)";
                $stmtImg = $this->pdo->prepare($sqlImg);

                // Vinculación con tipo 'PRODUCTO'
                $sqlRec = "INSERT INTO tbl_img_recurso (img_id, img_tipo, img_ref_id) VALUES (:imgId, 'PRODUCTO', :refId)";
                $stmtRec = $this->pdo->prepare($sqlRec);

                foreach ($galeria as $url) {
                    $stmtImg->execute([':neg' => $datos['neg_id'], ':url' => $url]);
                    $imgId = $this->pdo->lastInsertId();
                    $stmtRec->execute([':imgId' => $imgId, ':refId' => $productoId]);
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ====================================================================
    // 4. OBTENER COMPLETO (Para Editar)
    // ====================================================================
    public function obtenerCompleto($id, $negocioId) {
        // A. Datos Producto
        $sql = "SELECT * FROM tbl_producto WHERE pro_id = :id AND neg_id = :negId LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':negId' => $negocioId]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) return null;

        // B. Obtener Galería
        $sqlGal = "SELECT i.img_id, i.img_url 
                   FROM tbl_imagen i
                   INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id
                   WHERE ir.img_tipo = 'PRODUCTO' AND ir.img_ref_id = :id
                   ORDER BY i.img_id ASC";
        
        $stmtGal = $this->pdo->prepare($sqlGal);
        $stmtGal->execute([':id' => $id]);
        $producto['galeria'] = $stmtGal->fetchAll(PDO::FETCH_ASSOC);

        return $producto;
    }

    // ====================================================================
    // 5. ACTUALIZAR
    // ====================================================================
    public function actualizar($id, $datos, $nuevasFotos = []) {
        try {
            $this->pdo->beginTransaction();

            // A. Update Producto (Incluyendo nuevos campos)
            $sqlUpd = "UPDATE tbl_producto SET 
                        tpro_id = :tpro, 
                        pro_nombre = :nom, 
                        pro_descripcion = :desc, 
                        pro_precio = :precio, 
                        pro_costo_compra = :costo,
                        pro_stock = :stock, 
                        pro_codigo = :cod, 
                        pro_unidad = :uni, 
                        pro_contenido = :cont, 
                        pro_unidad_consumo = :ucons,
                        pro_insumo = :ins, 
                        pro_venta = :ven
                       WHERE pro_id = :id AND neg_id = :negId";
            
            $stmtUpd = $this->pdo->prepare($sqlUpd);
            $stmtUpd->execute([
                ':tpro'   => $datos['tpro_id'],
                ':nom'    => $datos['nombre'],
                ':desc'   => $datos['descripcion'],
                ':precio' => $datos['precio'],
                ':costo'  => $datos['costo'],
                ':stock'  => $datos['stock'],
                ':cod'    => $datos['codigo'],
                ':uni'    => $datos['unidad'],
                ':cont'   => $datos['contenido'],      // [NUEVO]
                ':ucons'  => $datos['unidad_consumo'], // [NUEVO]
                ':ins'    => $datos['insumo'],
                ':ven'    => $datos['venta'],
                ':id'     => $id,
                ':negId'  => $datos['neg_id']
            ]);

            // B. Insertar NUEVAS Imágenes
            if (!empty($nuevasFotos)) {
                $sqlImg = "INSERT INTO tbl_imagen (neg_id, img_url, img_principal, img_orden) VALUES (:neg, :url, 0, 0)";
                $stmtImg = $this->pdo->prepare($sqlImg);

                $sqlRec = "INSERT INTO tbl_img_recurso (img_id, img_tipo, img_ref_id) VALUES (:imgId, 'PRODUCTO', :refId)";
                $stmtRec = $this->pdo->prepare($sqlRec);

                foreach ($nuevasFotos as $url) {
                    $stmtImg->execute([':neg' => $datos['neg_id'], ':url' => $url]);
                    $imgId = $this->pdo->lastInsertId();
                    $stmtRec->execute([':imgId' => $imgId, ':refId' => $id]);
                }
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // 6. Validar Código Único
    public function existeCodigo($codigo, $negocioId, $excluirId = null) {
        $sql = "SELECT pro_id FROM tbl_producto WHERE pro_codigo = :cod AND neg_id = :negId";
        $params = [':cod' => $codigo, ':negId' => $negocioId];
        
        if ($excluirId) {
            $sql .= " AND pro_id != :id";
            $params[':id'] = $excluirId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Eliminar Lógico
    public function eliminarLogico($id, $negocioId) {
        $sql = "UPDATE tbl_producto SET pro_estado = 'I' WHERE pro_id = :id AND neg_id = :negId";
        $this->pdo->prepare($sql)->execute([':id' => $id, ':negId' => $negocioId]);
    }

    // Reactivar
    public function reactivar($id, $negocioId) {
        $sql = "UPDATE tbl_producto SET pro_estado = 'A' WHERE pro_id = :id AND neg_id = :negId";
        $this->pdo->prepare($sql)->execute([':id' => $id, ':negId' => $negocioId]);
    }

    // Eliminar foto de galería
    public function eliminarFoto($imgId, $negocioId) {
        $sqlCheck = "SELECT img_id FROM tbl_imagen WHERE img_id = :id AND neg_id = :negId";
        $stmt = $this->pdo->prepare($sqlCheck);
        $stmt->execute([':id' => $imgId, ':negId' => $negocioId]);
        if (!$stmt->fetch()) return false;

        $sqlDelRel = "DELETE FROM tbl_img_recurso WHERE img_id = :id";
        $this->pdo->prepare($sqlDelRel)->execute([':id' => $imgId]);

        $sqlDelImg = "DELETE FROM tbl_imagen WHERE img_id = :id";
        return $this->pdo->prepare($sqlDelImg)->execute([':id' => $imgId]);
    }
}