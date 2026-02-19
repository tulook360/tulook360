<?php
require_once __DIR__ . '/../nucleo/CloudinaryUploader.php';
require_once __DIR__ . '/../nucleo/TimeHelper.php'; // <--- OBLIGATORIO: Importar TimeHelper

class CompraModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function registrarCompra($datos, $detalles, $archivoFoto) {
        try {
            // A. Subir Evidencia (Sin cambios)
            $urlEvidencia = '';
            if (isset($archivoFoto['tmp_name']) && !empty($archivoFoto['tmp_name'])) {
                $urlEvidencia = CloudinaryUploader::subirImagen($archivoFoto['tmp_name'], 'compras_evidencia');
                if (!$urlEvidencia) throw new Exception("Error al subir evidencia.");
            } else {
                if ($datos['tipo_doc'] === 'SIN_SOPORTE') {
                    throw new Exception("Para compras informales, la foto es obligatoria.");
                }
            }

            $this->pdo->beginTransaction();

            // B. Insertar Cabecera CON FECHA LOCAL
            // [CORRECCIÓN] Cambiamos NOW() por :fecha
            $sqlCab = "INSERT INTO tbl_compra 
                       (neg_id, usu_id, com_proveedor, com_tipo_doc, com_numero_doc, com_total, com_evidencia, com_observacion, com_fecha) 
                       VALUES 
                       (:neg, :usu, :prov, :tipo, :num, :total, :evidencia, :obs, :fecha)";
            
            $stmtCab = $this->pdo->prepare($sqlCab);
            $stmtCab->execute([
                ':neg'       => $datos['neg_id'],
                ':usu'       => $datos['usu_id'],
                ':prov'      => $datos['proveedor'],
                ':tipo'      => $datos['tipo_doc'],
                ':num'       => $datos['numero_doc'] ?? null,
                ':total'     => $datos['total'],
                ':evidencia' => $urlEvidencia,
                ':obs'       => $datos['observacion'] ?? '',
                ':fecha'     => TimeHelper::now()
            ]);
            
            $compraId = $this->pdo->lastInsertId();

            // C. Insertar Detalles (Sin cambios en lógica, solo código limpio)
            $sqlDet = "INSERT INTO tbl_compra_detalle (com_id, pro_id, det_cantidad, det_costo_unitario, det_subtotal) 
                       VALUES (:com, :pro, :cant, :costo, :sub)";
            $stmtDet = $this->pdo->prepare($sqlDet);

            $sqlGetProd = "SELECT pro_stock, pro_costo_compra FROM tbl_producto WHERE pro_id = :id LIMIT 1";
            $stmtGetProd = $this->pdo->prepare($sqlGetProd);

            $sqlUpdProd = "UPDATE tbl_producto SET pro_stock = :nuevoStock, pro_costo_compra = :nuevoCosto WHERE pro_id = :id";
            $stmtUpdProd = $this->pdo->prepare($sqlUpdProd);

            foreach ($detalles as $item) {
                $subtotal = $item['cantidad'] * $item['costo'];
                $stmtDet->execute([
                    ':com'   => $compraId,
                    ':pro'   => $item['pro_id'],
                    ':cant'  => $item['cantidad'],
                    ':costo' => $item['costo'],
                    ':sub'   => $subtotal
                ]);

                // Recalcular Costo Promedio
                $stmtGetProd->execute([':id' => $item['pro_id']]);
                $prodActual = $stmtGetProd->fetch(PDO::FETCH_ASSOC);

                if ($prodActual) {
                    $stockActual = (float)$prodActual['pro_stock'];
                    $costoActual = (float)$prodActual['pro_costo_compra'];
                    
                    $cantidadEntrante = (float)$item['cantidad'];
                    $costoEntrante    = (float)$item['costo'];

                    $nuevoStock = $stockActual + $cantidadEntrante;

                    if ($nuevoStock > 0) {
                        $nuevoCosto = (($stockActual * $costoActual) + ($cantidadEntrante * $costoEntrante)) / $nuevoStock;
                    } else {
                        $nuevoCosto = $costoEntrante;
                    }

                    $stmtUpdProd->execute([
                        ':nuevoStock' => $nuevoStock,
                        ':nuevoCosto' => $nuevoCosto,
                        ':id'         => $item['pro_id']
                    ]);
                }
            }

            $this->pdo->commit();
            return $compraId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            if (!empty($urlEvidencia)) {
                CloudinaryUploader::eliminarImagen($urlEvidencia);
            }
            throw $e;
        }
    }

    public function listarHistorial($negocioId) {
        $sql = "SELECT c.*, u.usu_nombres, u.usu_apellidos 
                FROM tbl_compra c
                INNER JOIN tbl_usuario u ON c.usu_id = u.usu_id
                WHERE c.neg_id = :neg
                ORDER BY c.com_fecha DESC"; // Ordena por fecha real local
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg' => $negocioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // En models/CompraModelo.php

    public function obtenerDetalleCompra($compraId) {
        // CORRECCIÓN: Agregué la subconsulta (SELECT img_url ...) para traer la foto
        $sql = "SELECT d.*, p.pro_nombre, p.pro_codigo, p.pro_unidad,
                (SELECT img_url FROM tbl_imagen i 
                 JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                 WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' 
                 ORDER BY i.img_principal DESC LIMIT 1) as pro_foto
                FROM tbl_compra_detalle d
                INNER JOIN tbl_producto p ON d.pro_id = p.pro_id
                WHERE d.com_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $compraId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
