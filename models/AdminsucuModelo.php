<?php
class AdminsucuModelo {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    // 1. BUSCAR ORDEN POR TOKEN (Y CALCULAR TOTAL ESPECÍFICO DE LA SUCURSAL)
    // 1. BUSCAR ORDEN (Incluye suma de puntos a cobrar)
    public function buscarOrdenPorToken($token, $suc_id_actual) {
        $sql = "SELECT o.ord_id, o.ord_codigo, o.ord_fecha, o.ord_total, o.cli_id,
                       o.ord_estado, o.ord_tipo_entrega, 
                       c.usu_nombres, c.usu_apellidos, c.usu_cedula
                FROM tbl_orden o
                INNER JOIN tbl_usuario c ON o.cli_id = c.usu_id
                WHERE o.ord_token_qr = :token"; 

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$orden) return null;

        $sqlCheck = "SELECT COUNT(*) FROM tbl_orden_detalle WHERE ord_id = :oid AND suc_id = :sid";
        $stmt2 = $this->pdo->prepare($sqlCheck);
        $stmt2->execute([':oid' => $orden['ord_id'], ':sid' => $suc_id_actual]);
        $orden['tiene_items_aqui'] = ($stmt2->fetchColumn() > 0);

        // CALCULAMOS DINERO Y PUNTOS ESPECÍFICOS DE ESTA SUCURSAL
        $sqlSumas = "SELECT IFNULL(SUM(odet_subtotal), 0) as dinero, 
                            IFNULL(SUM(odet_puntos_canje), 0) as puntos 
                     FROM tbl_orden_detalle 
                     WHERE ord_id = :oid AND suc_id = :sid AND odet_estado = 'PENDIENTE'";
        $stmt3 = $this->pdo->prepare($sqlSumas);
        $stmt3->execute([':oid' => $orden['ord_id'], ':sid' => $suc_id_actual]);
        $res = $stmt3->fetch(PDO::FETCH_ASSOC);
        
        $orden['total_cobrar_sucursal'] = $res['dinero'];
        $orden['total_puntos_sucursal'] = $res['puntos']; 

        return $orden;
    }

    



    // 2. OBTENER ITEMS (De esta sucursal)
    public function obtenerItemsParaEntregar($ord_id, $suc_id_actual) {
        $sql = "SELECT od.odet_id, od.odet_cantidad, od.odet_estado,
                       p.pro_nombre, p.pro_precio, p.pro_unidad_consumo,
                       (SELECT img_url FROM tbl_imagen i 
                        JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                        WHERE ir.img_tipo = 'PRODUCTO' AND ir.img_ref_id = p.pro_id LIMIT 1) as imagen
                FROM tbl_orden_detalle od
                INNER JOIN tbl_producto p ON od.pro_id = p.pro_id
                WHERE od.ord_id = :oid AND od.suc_id = :sid";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id, ':sid' => $suc_id_actual]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. CONFIRMAR ENTREGA (Cobra dinero y resta puntos de canje)
    public function confirmarEntregaSucursal($ord_id, $suc_id_actual, $listaPagos, $totalEsperado) {
        require_once __DIR__ . '/../nucleo/TimeHelper.php';
        try {
            // A. Obtener datos básicos (Usamos nombres de parámetros únicos para evitar el error HY093)
            $sqlBase = "SELECT o.cli_id, s.neg_id, 
                        (SELECT SUM(odet_puntos_canje) 
                         FROM tbl_orden_detalle 
                         WHERE ord_id = :oid1 AND suc_id = :sid1 AND odet_estado = 'PENDIENTE') as puntos_canje_hoy
                        FROM tbl_orden o 
                        JOIN tbl_sucursal s ON s.suc_id = :sid2
                        WHERE o.ord_id = :oid2";
            
            $st = $this->pdo->prepare($sqlBase);
            $st->execute([
                ':oid1' => $ord_id, 
                ':sid1' => $suc_id_actual,
                ':sid2' => $suc_id_actual,
                ':oid2' => $ord_id
            ]);
            $info = $st->fetch(PDO::FETCH_ASSOC);

            if (!$info) throw new Exception("No se pudo recuperar la información del pedido.");

            $neg_id = $info['neg_id'];
            $cli_id = $info['cli_id'];
            $puntosGasto = intval($info['puntos_canje_hoy']);

            // B. Registrar pagos monetarios
            if ($totalEsperado > 0) {
                $sqlPago = "INSERT INTO tbl_pago (neg_id, ord_id, mp_id, pago_monto, pago_moneda, pago_fecha) VALUES (?, ?, ?, ?, 'USD', NOW())";
                $stP = $this->pdo->prepare($sqlPago);
                foreach($listaPagos as $p) { 
                    $stP->execute([$neg_id, $ord_id, $p['metodo_id'], $p['monto']]); 
                }
            }

            // C. DESCONTAR PUNTOS POR CANJE (Si el producto era de promoción)
            if ($puntosGasto > 0) {
                $stFid = $this->pdo->prepare("SELECT fidcli_id FROM tbl_fidelidad_cliente WHERE neg_id = ? AND cli_id = ?");
                $stFid->execute([$neg_id, $cli_id]);
                $fidId = $stFid->fetchColumn();

                if ($fidId) {
                    $this->pdo->prepare("INSERT INTO tbl_fidelidad_mov (fidcli_id, origen, ref_id, puntos, fidmov_tipo, descripcion) VALUES (?, 'ORDEN', ?, ?, 'GASTO', 'Canje de productos en sucursal')")
                              ->execute([$fidId, $ord_id, $puntosGasto]);
                    $this->pdo->prepare("UPDATE tbl_fidelidad_cliente SET fidcli_total = fidcli_total - ? WHERE fidcli_id = ?")
                              ->execute([$puntosGasto, $fidId]);
                }
            }

            // D. Actualizar Items de esta sucursal a RECOGIDO
            $sqlItems = "UPDATE tbl_orden_detalle SET odet_estado = 'RECOGIDO' WHERE ord_id = ? AND suc_id = ?";
            $this->pdo->prepare($sqlItems)->execute([$ord_id, $suc_id_actual]);

            // E. Actualizar el Estado Global de la Orden
            $pendientes = $this->pdo->query("SELECT COUNT(*) FROM tbl_orden_detalle WHERE ord_id = $ord_id AND odet_estado = 'PENDIENTE'")->fetchColumn();
            $tipo = $this->pdo->query("SELECT ord_tipo_entrega FROM tbl_orden WHERE ord_id = $ord_id")->fetchColumn();
            
            // Si ya no queda nada pendiente, se marca como COMPLETADO (si es retiro) o EN CAMINO (si es domicilio)
            $nuevoEstado = ($pendientes == 0) ? (($tipo === 'RETIRO') ? 'COMPLETADO' : 'EN_CAMINO') : 'EN_CAMINO';
            $this->pdo->prepare("UPDATE tbl_orden SET ord_estado = ? WHERE ord_id = ?")->execute([$nuevoEstado, $ord_id]);

            return ['success' => true, 'estado_global' => $nuevoEstado];

        } catch (Exception $e) { 
            return ['success' => false, 'error' => $e->getMessage()]; 
        }
    }
    

    // 4. AVERIGUAR DÓNDE ESTÁN LOS PRODUCTOS (Esta era la que faltaba y causaba el error)
    public function obtenerDondeEstaElPedido($ord_id) {
        $sql = "SELECT DISTINCT s.suc_nombre, s.suc_direccion 
                FROM tbl_orden_detalle od
                INNER JOIN tbl_sucursal s ON od.suc_id = s.suc_id
                WHERE od.ord_id = :oid AND od.odet_estado = 'PENDIENTE'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // ====================================================================
    // SECCIÓN CITAS (NUEVO)
    // ====================================================================

    // 4. BUSCAR CITA POR TOKEN
    public function buscarCitaPorToken($token, $suc_id) {
        $sql = "SELECT 
                    c.cita_id, c.cita_fecha, c.cita_estado, c.cita_qr_token,
                    c.neg_id, c.cli_id, -- Necesarios para los puntos
                    n.neg_nombre,
                    s.serv_nombre, s.serv_duracion,
                    u.usu_nombres, u.usu_apellidos, u.usu_foto,
                    cli.usu_nombres as cli_nombre, cli.usu_apellidos as cli_apellido,
                    cli.usu_cedula,
                    d.det_precio, d.det_puntos_canje, d.det_estado -- AGREGADO det_puntos_canje
                FROM tbl_cita c
                INNER JOIN tbl_negocio n ON c.neg_id = n.neg_id
                INNER JOIN tbl_cita_det d ON c.cita_id = d.cita_id
                INNER JOIN tbl_servicio s ON d.serv_id = s.serv_id
                INNER JOIN tbl_usuario u ON d.usu_id = u.usu_id
                INNER JOIN tbl_usuario cli ON c.cli_id = cli.usu_id
                WHERE c.cita_qr_token = :token 
                AND c.suc_id = :sid";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token, ':sid' => $suc_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 5. CONFIRMAR CITA (MODO SPLIT PAYMENT + TIMEHELPER)
    public function confirmarCitaSucursal($cita_id, $listaPagos, $totalEsperado) {
        require_once __DIR__ . '/../nucleo/TimeHelper.php';

        try {
            $this->pdo->beginTransaction();

            // 1. OBTENER INFO DE LA CITA (PRECIO Y PUNTOS)
            $sqlInfo = "SELECT c.neg_id, c.cli_id, d.det_puntos_canje 
                        FROM tbl_cita c 
                        JOIN tbl_cita_det d ON c.cita_id = d.cita_id 
                        WHERE c.cita_id = ?";
            $stInfo = $this->pdo->prepare($sqlInfo);
            $stInfo->execute([$cita_id]);
            $infoCita = $stInfo->fetch(PDO::FETCH_ASSOC);

            // ... (Validación de montos monetarios que ya tenías) ...

            // 2. REGISTRAR LOS PAGOS MONETARIOS (Igual que antes)
            // ... (Tu bucle de $stmtInsert->execute) ...

            // 3. --- NUEVA LÓGICA: DESCONTAR PUNTOS SI EXISTEN ---
            if ($infoCita['det_puntos_canje'] > 0) {
                $puntosADescontar = intval($infoCita['det_puntos_canje']);
                $nid = $infoCita['neg_id'];
                $cid = $infoCita['cli_id'];

                // A. Obtener el ID de fidelidad del cliente
                $stFid = $this->pdo->prepare("SELECT fidcli_id FROM tbl_fidelidad_cliente WHERE neg_id = ? AND cli_id = ?");
                $stFid->execute([$nid, $cid]);
                $fidCliId = $stFid->fetchColumn();

                if ($fidCliId) {
                    // B. Insertar Movimiento de GASTO
                    $sqlMov = "INSERT INTO tbl_fidelidad_mov 
                               (fidcli_id, origen, ref_id, puntos, fidmov_tipo, descripcion, fidmov_fecha) 
                               VALUES (?, 'CITA', ?, ?, 'GASTO', 'Canje de puntos en cita', NOW())";
                    $this->pdo->prepare($sqlMov)->execute([$fidCliId, $cita_id, $puntosADescontar]);

                    // C. Restar del Saldo Total
                    $sqlSaldo = "UPDATE tbl_fidelidad_cliente 
                                 SET fidcli_total = fidcli_total - ? 
                                 WHERE fidcli_id = ?";
                    $this->pdo->prepare($sqlSaldo)->execute([$puntosADescontar, $fidCliId]);
                }
            }

            // 4. Actualizar Estados
            $this->pdo->prepare("UPDATE tbl_cita SET cita_estado = 'CONFIRMADO' WHERE cita_id = ?")->execute([$cita_id]);
            $this->pdo->prepare("UPDATE tbl_cita_det SET det_estado = 'CONFIRMADO' WHERE cita_id = ?")->execute([$cita_id]);

            $this->pdo->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    // =========================================================
    // REPORTE CIERRE DE CAJA (CON REFERENCIAS BANCARIAS DETALLADAS)
    // =========================================================
    public function obtenerMovimientosCaja($suc_id, $fecha) {
        $sql = "SELECT 
                    COALESCE(p.cita_id, p.ord_id) as id_transaccion,
                    MAX(p.pago_fecha) as hora_pago,
                    SUM(p.pago_monto) as monto_total,
                    
                    SUM(CASE WHEN mp.mp_nombre LIKE '%Efectivo%' THEN p.pago_monto ELSE 0 END) as total_efectivo,
                    SUM(CASE WHEN mp.mp_nombre NOT LIKE '%Efectivo%' THEN p.pago_monto ELSE 0 END) as total_digital,

                    -- CAMBIO CLAVE: Separadores más seguros (:: y ;;)
                    GROUP_CONCAT(
                        CONCAT(
                            mp.mp_nombre, '::', 
                            COALESCE(p.pago_referencia, 'Sin Ref'), '::', 
                            p.pago_monto
                        ) 
                        SEPARATOR ';;'
                    ) as desglose_data,

                    COUNT(p.pago_id) as cantidad_pagos,

                    CASE 
                        WHEN p.cita_id IS NOT NULL THEN 'SERVICIO'
                        ELSE 'PRODUCTO'
                    END as tipo,
                    
                    COALESCE(MAX(c.cita_qr_token), MAX(o.ord_codigo)) as referencia_origen,
                    MAX(u.usu_nombres) as usu_nombres, 
                    MAX(u.usu_apellidos) as usu_apellidos

                FROM tbl_pago p
                INNER JOIN tbl_metodo_pago mp ON p.mp_id = mp.mp_id
                
                LEFT JOIN tbl_cita c ON p.cita_id = c.cita_id
                LEFT JOIN tbl_usuario u_cita ON c.cli_id = u_cita.usu_id
                
                LEFT JOIN tbl_orden o ON p.ord_id = o.ord_id
                LEFT JOIN tbl_usuario u_orden ON o.cli_id = u_orden.usu_id
                
                LEFT JOIN (SELECT DISTINCT ord_id, suc_id FROM tbl_orden_detalle) od ON p.ord_id = od.ord_id
                
                LEFT JOIN tbl_usuario u ON (CASE WHEN p.cita_id IS NOT NULL THEN u_cita.usu_id ELSE u_orden.usu_id END) = u.usu_id

                WHERE DATE(p.pago_fecha) = :fecha
                AND (
                    (p.cita_id IS NOT NULL AND c.suc_id = :sid1) 
                    OR 
                    (p.ord_id IS NOT NULL AND od.suc_id = :sid2)
                )
                
                GROUP BY CASE WHEN p.cita_id IS NOT NULL THEN CONCAT('C', p.cita_id) ELSE CONCAT('O', p.ord_id) END
                ORDER BY hora_pago DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sid1'  => $suc_id,
            ':sid2'  => $suc_id,
            ':fecha' => $fecha
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}