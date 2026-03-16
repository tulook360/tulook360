<?php
class EspecialistaModelo {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. OBTENER AGENDA
    // 1. OBTENER AGENDA DEL DÍA (CORREGIDO EL BUG DE MEDIANOCHE)
    public function obtenerAgendaDelDia($usu_id) {
        $hoy = date('Y-m-d');
        
        // Usamos Fecha y Hora completa para que no falle en las madrugadas
        $fechaHoraFiltro = date('Y-m-d H:i:s', strtotime('-30 minutes')); 

        $sql = "SELECT 
                    d.det_id, d.serv_id, d.det_ini, d.det_fin, d.det_estado, d.det_precio,
                    s.serv_nombre, s.serv_duracion,
                    c.cita_notas,
                    c.suc_id,
                    c.cli_id,
                    c.neg_id,
                    u.usu_nombres, u.usu_apellidos, u.usu_foto, u.usu_telefono
                FROM tbl_cita_det d
                INNER JOIN tbl_cita c ON d.cita_id = c.cita_id
                INNER JOIN tbl_servicio s ON d.serv_id = s.serv_id
                INNER JOIN tbl_usuario u ON c.cli_id = u.usu_id
                WHERE d.usu_id = :uid 
                  AND DATE(d.det_ini) = :hoy
                  -- Comparamos la Fecha y Hora exacta contra el filtro
                  AND (d.det_estado = 'EN_ATENCION' OR d.det_ini >= :fechaHora)
                  AND d.det_estado IN ('CONFIRMADO', 'RESERVADO', 'EN_ATENCION')
                ORDER BY d.det_ini ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $usu_id, 
            ':hoy' => $hoy, 
            ':fechaHora' => $fechaHoraFiltro
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER FUTURAS
    public function obtenerCitasFuturas($usu_id) {
        $manana = date('Y-m-d', strtotime('+1 day')); 
        $sql = "SELECT 
                    d.det_id, d.serv_id, d.det_ini, d.det_fin, d.det_estado,
                    s.serv_nombre, s.serv_duracion,
                    c.cita_notas,
                    u.usu_nombres, u.usu_apellidos, u.usu_foto
                FROM tbl_cita_det d
                INNER JOIN tbl_cita c ON d.cita_id = c.cita_id
                INNER JOIN tbl_servicio s ON d.serv_id = s.serv_id
                INNER JOIN tbl_usuario u ON c.cli_id = u.usu_id
                WHERE d.usu_id = :uid 
                  AND DATE(d.det_ini) >= :manana
                  AND d.det_estado IN ('CONFIRMADO', 'RESERVADO')
                ORDER BY d.det_ini ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usu_id, ':manana' => $manana]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. RECETA
    public function obtenerInsumosPorServicio($serv_id, $suc_id) {
        if(empty($serv_id)) return [];
        $suc_id = $suc_id ?: 0;

        $sql = "SELECT 
                    p.pro_id, p.pro_nombre, p.pro_unidad_consumo, p.pro_contenido, 
                    i.si_cantidad,
                    (SELECT img.img_url FROM tbl_imagen img 
                     INNER JOIN tbl_img_recurso ir ON img.img_id = ir.img_id 
                     WHERE ir.img_tipo = 'PRODUCTO' AND ir.img_ref_id = p.pro_id LIMIT 1) as pro_foto,
                    COALESCE(ps.ps_stock, 0) as ps_stock
                FROM tbl_servicio_insumo i
                INNER JOIN tbl_producto p ON i.pro_id = p.pro_id
                LEFT JOIN tbl_producto_sucursal ps ON p.pro_id = ps.pro_id AND ps.suc_id = :suc_id
                WHERE i.serv_id = :sid";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sid' => $serv_id, ':suc_id' => $suc_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. ACTUALIZAR ESTADO + DESCUENTO INVENTARIO + ACUMULACIÓN PUNTOS + CÁLCULO DE COMISIÓN
    public function actualizarEstado($det_id, $nuevoEstado) {
        try {
            $this->pdo->beginTransaction();

            // OBTENER DATOS CLAVE DE LA CITA Y EL USUARIO
            $sqlInfo = "SELECT c.suc_id, c.neg_id, c.cli_id, d.serv_id, c.cita_id, d.det_precio, d.usu_id
                        FROM tbl_cita_det d 
                        INNER JOIN tbl_cita c ON d.cita_id = c.cita_id 
                        WHERE d.det_id = ?";
            $stmtInfo = $this->pdo->prepare($sqlInfo);
            $stmtInfo->execute([$det_id]);
            $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
            
            if (!$info) throw new Exception("Error: No se encontró la cita.");

            $suc_id  = $info['suc_id'];
            $neg_id  = $info['neg_id'];
            $cli_id  = $info['cli_id'];
            $serv_id = $info['serv_id'];
            $cita_id = $info['cita_id'];
            $precio_cobrado = floatval($info['det_precio']);
            $especialista_id = $info['usu_id'];

            // VERIFICAR SI ESTA CITA ES UNA PROMOCIÓN
            $sqlCheckPromo = "SELECT COUNT(*) FROM tbl_promocion_historial 
                             WHERE hist_ref_tipo = 'CITA' AND hist_ref_id = ?";
            $stmtCheck = $this->pdo->prepare($sqlCheckPromo);
            $stmtCheck->execute([$cita_id]);
            $esPromocion = ($stmtCheck->fetchColumn() > 0);

            // --- LÓGICA A: INICIAR ATENCIÓN (DESCONTAR INSUMOS) ---
            if ($nuevoEstado === 'EN_ATENCION') {
                $insumos = $this->obtenerInsumosPorServicio($serv_id, $suc_id);

                foreach ($insumos as $insumo) {
                    $pro_id = $insumo['pro_id'];
                    $cantidadNecesaria = floatval($insumo['si_cantidad']) * floatval($insumo['pro_contenido']);

                    $sqlStock = "SELECT ps_stock, ps_stock_consumo FROM tbl_producto_sucursal 
                                 WHERE pro_id = :pid AND suc_id = :sid FOR UPDATE"; 
                    $stmtStock = $this->pdo->prepare($sqlStock);
                    $stmtStock->execute([':pid' => $pro_id, ':sid' => $suc_id]);
                    $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);

                    if (!$stock) throw new Exception("Producto no inventariado: {$insumo['pro_nombre']}");

                    $cerrado = floatval($stock['ps_stock']);
                    $abierto = floatval($stock['ps_stock_consumo']);
                    $contenido = floatval($insumo['pro_contenido']);
                    $totalDisponible = ($cerrado * $contenido) + $abierto;

                    if ($totalDisponible < ($cantidadNecesaria - 0.0001)) {
                        throw new Exception("Stock insuficiente: {$insumo['pro_nombre']}");
                    }

                    $nuevoAbierto = $abierto - $cantidadNecesaria;
                    $nuevoCerrado = $cerrado;

                    if ($nuevoAbierto < 0) {
                        $faltante = abs($nuevoAbierto);
                        $cajasAbrir = ceil($faltante / $contenido);
                        $nuevoCerrado = $cerrado - $cajasAbrir;
                        $nuevoAbierto = $nuevoAbierto + ($cajasAbrir * $contenido);
                    }

                    $sqlUpd = "UPDATE tbl_producto_sucursal SET ps_stock = :nc, ps_stock_consumo = :na 
                               WHERE pro_id = :pid AND suc_id = :sid";
                    $this->pdo->prepare($sqlUpd)->execute([':nc' => $nuevoCerrado, ':na' => $nuevoAbierto, ':pid' => $pro_id, ':sid' => $suc_id]);

                    $sqlMov = "INSERT INTO tbl_mov_inventario (neg_id, suc_id, pro_id, mov_tipo, mov_cantidad, mov_tabla, mov_ref_id, mov_fecha) 
                               VALUES (:nid, :sid, :pid, 'CONSUMO_CITA', :cant, 'tbl_cita', :ref, NOW())";
                    $this->pdo->prepare($sqlMov)->execute([':nid' => $neg_id, ':sid' => $suc_id, ':pid' => $pro_id, ':cant' => $cantidadNecesaria, ':ref' => $cita_id]);
                }
            }

            // --- LÓGICA B: FINALIZAR ATENCIÓN (ACUMULAR PUNTOS + CALCULAR COMISIÓN) ---
            if ($nuevoEstado === 'FINALIZADO') {
                
                // 1. PUNTOS DE FIDELIDAD
                $sqlConfig = "SELECT fid_activa, fid_dias_vencimiento 
                              FROM tbl_fidelidad_config WHERE neg_id = ?";
                $stmtConf = $this->pdo->prepare($sqlConfig);
                $stmtConf->execute([$neg_id]);
                $config = $stmtConf->fetch(PDO::FETCH_ASSOC);

                if ($config && $config['fid_activa'] == 1 && !$esPromocion) {
                    $sqlPuntos = "SELECT fiditem_puntos FROM tbl_fidelidad_item 
                                  WHERE neg_id = ? AND serv_id = ? AND fiditem_estado = 'A'";
                    $stmtPts = $this->pdo->prepare($sqlPuntos);
                    $stmtPts->execute([$neg_id, $serv_id]);
                    $itemPuntos = $stmtPts->fetch(PDO::FETCH_ASSOC);

                    $puntosGanados = $itemPuntos ? intval($itemPuntos['fiditem_puntos']) : 0;

                    if ($puntosGanados > 0) {
                        $diasVigencia = intval($config['fid_dias_vencimiento']);
                        $fechaVencimiento = date('Y-m-d', strtotime("+$diasVigencia days"));

                        $sqlCheckCli = "INSERT INTO tbl_fidelidad_cliente (neg_id, cli_id, fidcli_total, fidcli_ultima) 
                                        VALUES (?, ?, 0, NOW()) 
                                        ON DUPLICATE KEY UPDATE fidcli_ultima = NOW()";
                        $this->pdo->prepare($sqlCheckCli)->execute([$neg_id, $cli_id]);

                        $sqlGetFidCli = "SELECT fidcli_id FROM tbl_fidelidad_cliente WHERE neg_id = ? AND cli_id = ?";
                        $stmtFidCli = $this->pdo->prepare($sqlGetFidCli);
                        $stmtFidCli->execute([$neg_id, $cli_id]);
                        $fidCliId = $stmtFidCli->fetchColumn();

                        if ($fidCliId) {
                            $stmtMovPts = $this->pdo->prepare("INSERT INTO tbl_fidelidad_mov 
                                (fidcli_id, origen, ref_id, puntos, fidmov_tipo, fidmov_saldo_restante, fidmov_vencimiento, descripcion, fidmov_fecha) 
                                VALUES (?, 'CITA', ?, ?, 'GANANCIA', ?, ?, 'Puntos por Servicio', NOW())");
                            
                            $stmtMovPts->execute([$fidCliId, $cita_id, $puntosGanados, $puntosGanados, $fechaVencimiento]);

                            $sqlUpdateSaldo = "UPDATE tbl_fidelidad_cliente 
                                               SET fidcli_total = fidcli_total + ? 
                                               WHERE fidcli_id = ?";
                            $this->pdo->prepare($sqlUpdateSaldo)->execute([$puntosGanados, $fidCliId]);
                        }
                    }
                }

                // ========================================================
                // 2. NUEVO: CONGELAMIENTO DE COMISIÓN
                // ========================================================
                
                // Obtener el porcentaje actual de comisión del empleado
                $sqlPorcentaje = "SELECT usu_comision_porcentaje FROM tbl_usuario WHERE usu_id = ?";
                $stmtPorc = $this->pdo->prepare($sqlPorcentaje);
                $stmtPorc->execute([$especialista_id]);
                $porcentaje = floatval($stmtPorc->fetchColumn());

                // Calcular el monto en dólares de la comisión
                $montoComision = ($precio_cobrado * $porcentaje) / 100;

                // Actualizar el estado de la cita Y GUARDAR LA COMISIÓN CONGELADA
                $sqlEstado = "UPDATE tbl_cita_det 
                              SET det_estado = :est, 
                                  det_comision_porc = :porc, 
                                  det_comision_monto = :monto 
                              WHERE det_id = :id";
                
                $this->pdo->prepare($sqlEstado)->execute([
                    ':est' => $nuevoEstado, 
                    ':porc' => $porcentaje,
                    ':monto' => $montoComision,
                    ':id' => $det_id
                ]);

            } else {
                // SI NO ES 'FINALIZADO', SOLO CAMBIAMOS EL ESTADO NORMALMENTE
                $sqlEstado = "UPDATE tbl_cita_det SET det_estado = :est WHERE det_id = :id";
                $this->pdo->prepare($sqlEstado)->execute([':est' => $nuevoEstado, ':id' => $det_id]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e; 
        }
    }

    // 5. OBTENER TOTALES DE COMISIONES
    public function obtenerMetricasComisiones($usu_id, $f_ini, $f_fin) {
        $sql = "SELECT 
                    COUNT(det_id) as total_servicios,
                    SUM(det_precio) as total_generado,
                    SUM(det_comision_monto) as total_comision
                FROM tbl_cita_det 
                WHERE usu_id = :uid 
                  AND det_estado = 'FINALIZADO'
                  AND DATE(det_ini) BETWEEN :ini AND :fin";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usu_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 6. OBTENER HISTORIAL DETALLADO
    public function obtenerHistorialComisiones($usu_id, $f_ini, $f_fin) {
        $sql = "SELECT 
                    d.det_ini,
                    s.serv_nombre,
                    d.det_precio,
                    d.det_comision_porc,
                    d.det_comision_monto
                FROM tbl_cita_det d
                INNER JOIN tbl_servicio s ON d.serv_id = s.serv_id
                WHERE d.usu_id = :uid 
                  AND d.det_estado = 'FINALIZADO'
                  AND DATE(d.det_ini) BETWEEN :ini AND :fin
                ORDER BY d.det_ini DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $usu_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>