<?php
class FidelidadModelo {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function obtenerConfiguracion($neg_id) {
        $sql = "SELECT * FROM tbl_fidelidad_config WHERE neg_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$neg_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 1. SOLO ACTUALIZAR ESTADO (Para el Switch Rápido)
    public function actualizarEstado($neg_id, $estado) {
        // Si no existe, crea. Si existe, solo actualiza el estado.
        $sql = "INSERT INTO tbl_fidelidad_config (neg_id, fid_activa, fid_fecha_reg) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE fid_activa = VALUES(fid_activa)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$neg_id, $estado]);
    }

    // 2. SOLO ACTUALIZAR DÍAS (Para el Botón Pequeño)
    public function actualizarDias($neg_id, $dias) {
        // Si no existe, crea. Si existe, solo actualiza los días.
        $sql = "INSERT INTO tbl_fidelidad_config (neg_id, fid_dias_vencimiento, fid_fecha_reg) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE fid_dias_vencimiento = VALUES(fid_dias_vencimiento)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$neg_id, $dias]);
    }


    // --- NUEVAS FUNCIONES PARA ASIGNAR PUNTOS ---

    // 3. TRAER TODO EL CATÁLOGO (CORREGIDO PARA IMÁGENES REALES)
    public function obtenerCatalogoPuntos($neg_id) {
        // Parte A: Productos (SOLO VENDIBLES)
        // Agregamos: AND p.pro_venta = 1
        $sqlProd = "
            SELECT 
                'PRODUCTO' as tipo,
                p.pro_id as id,
                p.pro_nombre as nombre,
                p.pro_precio as precio,
                (SELECT i.img_url 
                 FROM tbl_img_recurso ir 
                 JOIN tbl_imagen i ON ir.img_id = i.img_id 
                 WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' 
                 LIMIT 1) as imagen,
                COALESCE(f.fiditem_puntos, 0) as puntos_actuales
            FROM tbl_producto p
            LEFT JOIN tbl_fidelidad_item f 
                ON p.pro_id = f.pro_id AND f.neg_id = ?
            WHERE p.neg_id = ? 
              AND p.pro_estado = 'A' 
              AND p.pro_venta = 1  -- <--- FILTRO CLAVE: Solo lo que se vende al público
        ";

        // Parte B: Servicios (Todos son vendibles por definición)
        $sqlServ = "
            SELECT 
                'SERVICIO' as tipo,
                s.serv_id as id,
                s.serv_nombre as nombre,
                s.serv_precio as precio,
                (SELECT i.img_url 
                 FROM tbl_img_recurso ir 
                 JOIN tbl_imagen i ON ir.img_id = i.img_id 
                 WHERE ir.img_ref_id = s.serv_id AND ir.img_tipo = 'SERVICIO' 
                 LIMIT 1) as imagen,
                COALESCE(f.fiditem_puntos, 0) as puntos_actuales
            FROM tbl_servicio s
            LEFT JOIN tbl_fidelidad_item f 
                ON s.serv_id = f.serv_id AND f.neg_id = ?
            WHERE s.neg_id = ? AND s.serv_estado = 'A'
        ";

        $sqlFinal = "$sqlProd UNION ALL $sqlServ ORDER BY puntos_actuales DESC, nombre ASC";

        $stmt = $this->db->prepare($sqlFinal);
        $stmt->execute([$neg_id, $neg_id, $neg_id, $neg_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. GUARDAR PUNTOS DE UN ÍTEM ESPECÍFICO
    public function guardarPuntosItem($neg_id, $tipo, $id_item, $puntos) {
        // Definimos qué columna llenar según el tipo
        $colServ = ($tipo === 'SERVICIO') ? $id_item : NULL;
        $colProd = ($tipo === 'PRODUCTO') ? $id_item : NULL;

        // Lógica inteligente: Si existe actualiza, si no inserta.
        // Usamos una llave única compuesta (neg_id, serv_id, pro_id) para evitar duplicados
        
        // Primero verificamos si ya existe registro para ese item
        $sqlCheck = "SELECT fiditem_id FROM tbl_fidelidad_item 
                     WHERE neg_id = ? AND (serv_id = ? OR pro_id = ?)";
        
        // Truco: Pasamos el ID en ambos lados, uno será NULL y no coincidirá, el otro sí.
        // O mejor, hagamos un DELETE previo y un INSERT limpio para evitar líos de índices únicos complejos.
        // ESTRATEGIA MÁS SEGURA Y LIMPIA:
        
        if($puntos <= 0) {
            // Si pone 0, borramos el registro para no llenar basura en la tabla
            $sql = "DELETE FROM tbl_fidelidad_item WHERE neg_id = ? AND (serv_id = ? OR pro_id = ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$neg_id, $colServ, $colProd]);
        } 
        else {
            // Si pone puntos > 0, insertamos o actualizamos
            // Primero intentamos actualizar
            $sqlUpdate = "UPDATE tbl_fidelidad_item SET fiditem_puntos = ? 
                          WHERE neg_id = ? AND (serv_id = ? OR pro_id = ?)";
            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([$puntos, $neg_id, $colServ, $colProd]);
            
            if ($stmt->rowCount() == 0) {
                // Si no actualizó nada (no existía), Insertamos
                $sqlInsert = "INSERT INTO tbl_fidelidad_item (neg_id, serv_id, pro_id, fiditem_puntos, fiditem_estado) 
                              VALUES (?, ?, ?, ?, 'A')";
                $stmt = $this->db->prepare($sqlInsert);
                return $stmt->execute([$neg_id, $colServ, $colProd, $puntos]);
            }
            return true;
        }
    }

    // --- FUNCIÓN CORREGIDA: ACUMULAR PUNTOS SOLO DE LA SUCURSAL QUE ENTREGA ---
    // --- FUNCIÓN CORREGIDA: ACUMULAR PUNTOS SOLO DE PRODUCTOS NORMALES ---
    public function procesarPuntosPorEntregaSucursal($ord_id, $cli_id, $suc_id) {
        
        // 1. Averiguar a qué Negocio pertenece esta Sucursal (Para ver la config)
        $sqlNeg = "SELECT neg_id FROM tbl_sucursal WHERE suc_id = :sid";
        $stmtN = $this->db->prepare($sqlNeg);
        $stmtN->execute([':sid' => $suc_id]);
        $neg_id = $stmtN->fetchColumn();

        if (!$neg_id) return; // Seguridad

        // 2. Verificar si este negocio tiene Fidelidad ACTIVA
        $sqlConf = "SELECT fid_id, fid_dias_vencimiento FROM tbl_fidelidad_config WHERE neg_id = :nid AND fid_activa = 1";
        $stmtC = $this->db->prepare($sqlConf);
        $stmtC->execute([':nid' => $neg_id]);
        $config = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$config) return; 

        $diasVencimiento = $config['fid_dias_vencimiento'] ?? 180;

        // 3. Calcular puntos (SOLO PRODUCTOS NORMALES: prom_id = 0)
        $sqlPuntos = "SELECT SUM(od.odet_cantidad * fi.fiditem_puntos) as total_puntos
                      FROM tbl_orden_detalle od
                      INNER JOIN tbl_fidelidad_item fi ON od.pro_id = fi.pro_id
                      WHERE od.ord_id = :oid_pts 
                        AND od.suc_id = :sid_pts 
                        AND od.prom_id = 0 
                        AND fi.fiditem_estado = 'A'";
        
        $stmtP = $this->db->prepare($sqlPuntos);
        $stmtP->execute([':oid_pts' => $ord_id, ':sid_pts' => $suc_id]);
        $total = intval($stmtP->fetchColumn());

        // 4. Si esa sucursal entregó productos que generen puntos, los sumamos
        if ($total > 0) {
            
            // A. Actualizar/Crear Billetera (Saldos totales)
            $sqlWallet = "INSERT INTO tbl_fidelidad_cliente (neg_id, cli_id, fidcli_total, fidcli_ultima)
                          VALUES (:neg, :cli, :pts, NOW())
                          ON DUPLICATE KEY UPDATE 
                          fidcli_total = fidcli_total + :pts_upd,
                          fidcli_ultima = NOW()";
            
            $this->db->prepare($sqlWallet)->execute([
                ':neg' => $neg_id,
                ':cli' => $cli_id,
                ':pts' => $total,
                ':pts_upd' => $total
            ]);

            // B. Obtener ID de la Billetera para el historial
            $sqlGetId = "SELECT fidcli_id FROM tbl_fidelidad_cliente WHERE neg_id = :neg AND cli_id = :cli";
            $stmtId = $this->db->prepare($sqlGetId);
            $stmtId->execute([':neg' => $neg_id, ':cli' => $cli_id]);
            $fidcli_id = $stmtId->fetchColumn();

            // C. Insertar en Historial de Movimientos (GANANCIA)
            $vencimiento = date('Y-m-d', strtotime("+$diasVencimiento days"));
            
            // Buscamos el nombre de la sucursal para que salga bonito en el historial del cliente
            $nomSuc = $this->db->query("SELECT suc_nombre FROM tbl_sucursal WHERE suc_id = $suc_id")->fetchColumn();

            $sqlMov = "INSERT INTO tbl_fidelidad_mov 
                       (fidcli_id, origen, ref_id, puntos, fidmov_tipo, fidmov_saldo_restante, fidmov_vencimiento, descripcion, fidmov_fecha) 
                       VALUES (:fidcli, 'ORDEN', :ref, :pts, 'GANANCIA', :saldo, :vence, :desc, NOW())";
            
            $this->db->prepare($sqlMov)->execute([
                ':fidcli' => $fidcli_id,
                ':ref'    => $ord_id,
                ':pts'    => $total,
                ':saldo'  => $total,
                ':vence'  => $vencimiento,
                ':desc'   => "Puntos ganados en $nomSuc (Orden #$ord_id)"
            ]);
        }
    }



}
?>