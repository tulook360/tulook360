<?php
// ARCHIVO: models/PromocionModelo.php
class PromocionModelo {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function listarPorNegocio($neg_id, $filtro = 'activos') {
        $estado = ($filtro === 'inactivos') ? 'I' : 'A';

        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM tbl_promocion_historial h WHERE h.prom_id = p.prom_id) as total_usos,
                
                -- Traer los puntos de la tabla de canje
                fc.puntos_necesarios,
                
                -- Obtener Precio Original
                COALESCE(s.serv_precio, prod.pro_precio, 0) as precio_real,
                
                -- Obtener Foto
                (
                    SELECT i.img_url 
                    FROM tbl_imagen i
                    JOIN tbl_img_recurso ir ON i.img_id = ir.img_id
                    WHERE (ir.img_tipo = 'SERVICIO' AND ir.img_ref_id = s.serv_id)
                    OR (ir.img_tipo = 'PRODUCTO' AND ir.img_ref_id = prod.pro_id)
                    ORDER BY i.img_orden ASC LIMIT 1
                ) as foto_item

                FROM tbl_promocion p 
                LEFT JOIN tbl_promocion_serv ps ON p.prom_id = ps.prom_id
                LEFT JOIN tbl_servicio s ON ps.serv_id = s.serv_id
                LEFT JOIN tbl_promocion_prod pp ON p.prom_id = pp.prom_id
                LEFT JOIN tbl_producto prod ON pp.pro_id = prod.pro_id
                
                -- UNIÓN CLAVE: Traer datos de fidelidad/puntos
                LEFT JOIN tbl_fidelidad_canje fc ON p.prom_id = fc.prom_id

                WHERE p.neg_id = :neg AND p.prom_estado = :estado
                ORDER BY p.prom_id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':neg' => $neg_id, ':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function guardarCompleto($data) {
        try {
            $this->db->beginTransaction();

            // 1. Insertar en tbl_promocion (La base)
            // modalidad: 'PRECIO', 'MIXTO', 'PUNTOS'
            $sql = "INSERT INTO tbl_promocion (
                        neg_id, prom_nombre, prom_tipo, prom_modalidad, 
                        prom_desc, prom_precio_oferta, prom_ini, 
                        prom_fin, prom_limite_usos, prom_estado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['neg_id'],
                $data['nombre'],
                $data['tipo_item'], // 'SERVICIO' o 'PRODUCTO'
                $data['modalidad'],
                $data['descripcion'],
                $data['precio_oferta'] ?? 0,
                $data['fecha_ini'],
                $data['fecha_fin'],
                $data['limite'] ?? 0
            ]);

            $prom_id = $this->db->lastInsertId();

            // 2. Vincular con el Ítem (Pivot)
            if ($data['tipo_item'] === 'SERVICIO') {
                $stmtPivot = $this->db->prepare("INSERT INTO tbl_promocion_serv (prom_id, serv_id) VALUES (?, ?)");
            } else {
                $stmtPivot = $this->db->prepare("INSERT INTO tbl_promocion_prod (prom_id, pro_id) VALUES (?, ?)");
            }
            $stmtPivot->execute([$prom_id, $data['item_id']]);

            // 3. Lógica de Fidelidad (Solo si es MIXTO o PUNTOS)
            if ($data['modalidad'] !== 'PRECIO') {
                $sqlCanje = "INSERT INTO tbl_fidelidad_canje (
                                neg_id, prom_id, puntos_necesarios, 
                                fidcanje_copago, fidcanje_estado
                            ) VALUES (?, ?, ?, ?, 'A')";
                $stmtC = $this->db->prepare($sqlCanje);
                // Si es PUNTOS, el copago es 0. Si es MIXTO, el copago es el precio_oferta.
                $copago = ($data['modalidad'] === 'PUNTOS') ? 0 : $data['precio_oferta'];
                
                $stmtC->execute([
                    $data['neg_id'],
                    $prom_id,
                    $data['puntos_req'],
                    $copago
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'id' => $prom_id];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // --- NUEVO MÉTODO PARA CARGAR CATÁLOGO ---
    public function obtenerItemsDisponibles($neg_id, $tipo) {
        if ($tipo === 'SERVICIO') {
            // Corregido: Hacemos una subconsulta para traer la primera foto de tbl_imagen + tbl_img_recurso
            $sql = "SELECT s.serv_id as id, s.serv_nombre as nombre, s.serv_precio as precio,
                    (
                        SELECT i.img_url 
                        FROM tbl_imagen i
                        INNER JOIN tbl_img_recurso r ON i.img_id = r.img_id
                        WHERE r.img_tipo = 'SERVICIO' AND r.img_ref_id = s.serv_id
                        ORDER BY i.img_orden ASC LIMIT 1
                    ) as foto
                    FROM tbl_servicio s 
                    WHERE s.neg_id = :neg AND s.serv_estado = 'A'";
        } else {
            // Corregido: Lo mismo para productos
            $sql = "SELECT p.pro_id as id, p.pro_nombre as nombre, p.pro_precio as precio,
                    (
                        SELECT i.img_url 
                        FROM tbl_imagen i
                        INNER JOIN tbl_img_recurso r ON i.img_id = r.img_id
                        WHERE r.img_tipo = 'PRODUCTO' AND r.img_ref_id = p.pro_id
                        ORDER BY i.img_orden ASC LIMIT 1
                    ) as foto
                    FROM tbl_producto p
                    WHERE p.neg_id = :neg AND p.pro_estado = 'A' AND p.pro_venta = 1";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':neg' => $neg_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) { // Aquí el nombre no importa tanto, pero que sea consistente
        $sql = "SELECT p.*, 
                fc.puntos_necesarios,
                COALESCE(ps.serv_id, pp.pro_id) as item_id,
                COALESCE(s.serv_precio, prod.pro_precio, 0) as precio_real
                FROM tbl_promocion p
                LEFT JOIN tbl_promocion_serv ps ON p.prom_id = ps.prom_id
                LEFT JOIN tbl_servicio s ON ps.serv_id = s.serv_id
                LEFT JOIN tbl_promocion_prod pp ON p.prom_id = pp.prom_id
                LEFT JOIN tbl_producto prod ON pp.pro_id = prod.pro_id
                LEFT JOIN tbl_fidelidad_canje fc ON p.prom_id = fc.prom_id
                WHERE p.prom_id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarCompleto($data) {
        try {
            $this->db->beginTransaction();

            // 1. Actualizar tbl_promocion
            $sql = "UPDATE tbl_promocion SET 
                        prom_nombre = ?, 
                        prom_modalidad = ?, 
                        prom_desc = ?, 
                        prom_precio_oferta = ?, 
                        prom_ini = ?, 
                        prom_fin = ?, 
                        prom_limite_usos = ?
                    WHERE prom_id = ? AND neg_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['modalidad'],
                $data['descripcion'],
                $data['precio_oferta'] ?? 0,
                $data['fecha_ini'],
                $data['fecha_fin'],
                $data['limite'] ?? 0,
                $data['prom_id'],
                $data['neg_id']
            ]);

            // 2. Manejo de Puntos (Limpiar y Reinsertar es más seguro)
            $this->db->prepare("DELETE FROM tbl_fidelidad_canje WHERE prom_id = ?")->execute([$data['prom_id']]);

            if ($data['modalidad'] !== 'PRECIO') {
                $sqlCanje = "INSERT INTO tbl_fidelidad_canje (neg_id, prom_id, puntos_necesarios, fidcanje_copago, fidcanje_estado) 
                             VALUES (?, ?, ?, ?, 'A')";
                $copago = ($data['modalidad'] === 'PUNTOS') ? 0 : $data['precio_oferta'];
                $this->db->prepare($sqlCanje)->execute([
                    $data['neg_id'],
                    $data['prom_id'],
                    $data['puntos_req'],
                    $copago
                ]);
            }

            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Borrado lógico (Desactivar)
    public function desactivar($id, $neg_id) {
        $sql = "UPDATE tbl_promocion SET prom_estado = 'I' WHERE prom_id = ? AND neg_id = ?";
        return $this->db->prepare($sql)->execute([$id, $neg_id]);
    }

    public function reanudar($id, $neg_id, $f_ini, $f_fin) {
        try {
            $this->db->beginTransaction();

            // 1. Resetear el estado y poner el contador de la tabla principal en 0
            $sql = "UPDATE tbl_promocion SET 
                        prom_estado = 'A', 
                        prom_usos_actuales = 0, 
                        prom_ini = ?, 
                        prom_fin = ? 
                    WHERE prom_id = ? AND neg_id = ?";
            $this->db->prepare($sql)->execute([$f_ini, $f_fin, $id, $neg_id]);

            // 2. BORRAR EL HISTORIAL: Esto es lo que hace que el COUNT(*) de la tarjeta vuelva a 0
            $sqlDel = "DELETE FROM tbl_promocion_historial WHERE prom_id = ?";
            $this->db->prepare($sqlDel)->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }
}