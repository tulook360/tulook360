<?php
class PedidoModelo {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /// 1. OBTENER EL CATÁLOGO (CON CÁLCULO DE STOCK VIRTUAL)
    public function obtenerCatalogo($negocioId) {
        // Fórmula: Stock Físico (tbl_producto) - Suma de pedidos pendientes (tbl_pedido_detalle + tbl_pedido_stock)
        $sql = "SELECT p.pro_id, p.pro_nombre, p.pro_codigo, 
                       p.pro_unidad, p.pro_contenido, p.pro_unidad_consumo,
                       tp.tpro_nombre as cat_nombre,
                       
                       -- CÁLCULO DEL STOCK VIRTUAL --
                       (p.pro_stock - IFNULL((
                            SELECT SUM(d.det_cant_solicitada)
                            FROM tbl_pedido_detalle d
                            INNER JOIN tbl_pedido_stock s ON d.ped_id = s.ped_id
                            WHERE d.pro_id = p.pro_id 
                            AND s.ped_estado = 'PENDIENTE'
                        ), 0)) as stock_virtual,

                       -- FOTO --
                       (SELECT i.img_url 
                        FROM tbl_imagen i 
                        INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                        WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' 
                        ORDER BY i.img_principal DESC LIMIT 1) as pro_foto

                FROM tbl_producto p
                LEFT JOIN tbl_tipo_producto tp ON p.tpro_id = tp.tpro_id
                WHERE p.neg_id = :nid 
                  AND p.pro_estado = 'A'
                ORDER BY p.pro_nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nid' => $negocioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Limpieza: Asignamos el stock virtual al campo 'pro_stock' que usa la vista
        // Si el cálculo da negativo (error de datos), ponemos 0.
        foreach ($resultados as &$prod) {
            $prod['pro_stock'] = max(0, floatval($prod['stock_virtual']));
        }

        return $resultados;
    }

    // 2. EL PORTERO: VERIFICAR DISPONIBILIDAD EXACTA (Nuevo)
    public function verificarStockDisponible($proId) {
        // Hacemos la misma resta matemática para un solo producto
        $sql = "SELECT p.pro_stock,
                       IFNULL((
                            SELECT SUM(d.det_cant_solicitada)
                            FROM tbl_pedido_detalle d
                            JOIN tbl_pedido_stock s ON d.ped_id = s.ped_id
                            WHERE d.pro_id = p.pro_id AND s.ped_estado = 'PENDIENTE'
                       ), 0) as comprometido
                FROM tbl_producto p
                WHERE p.pro_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $proId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return 0;

        // Retornamos lo que realmente queda libre
        return max(0, floatval($row['pro_stock']) - floatval($row['comprometido']));
    }

    // 3. GUARDAR EL PEDIDO (Igual que antes)
    public function guardarPedido($datosHeader, $itemsCarrito) {
        try {
            $this->pdo->beginTransaction();

            $codigo = 'PED-' . strtoupper(substr(uniqid(), -5));

            $sqlHead = "INSERT INTO tbl_pedido_stock 
                        (neg_id, suc_id, usu_id_solicita, ped_fecha_solicitud, ped_estado, ped_codigo) 
                        VALUES (:neg, :suc, :usu, NOW(), 'PENDIENTE', :cod)";
            
            $stmtH = $this->pdo->prepare($sqlHead);
            $stmtH->execute([
                ':neg' => $datosHeader['neg_id'],
                ':suc' => $datosHeader['suc_id'],
                ':usu' => $datosHeader['usu_id'],
                ':cod' => $codigo
            ]);

            $pedidoId = $this->pdo->lastInsertId();

            $sqlDet = "INSERT INTO tbl_pedido_detalle (ped_id, pro_id, det_cant_solicitada) 
                       VALUES (:pid, :prod, :cant)";
            $stmtD = $this->pdo->prepare($sqlDet);

            foreach ($itemsCarrito as $item) {
                $stmtD->execute([
                    ':pid'  => $pedidoId,
                    ':prod' => $item['id'],
                    ':cant' => $item['cantidad']
                ]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // 3. LISTAR HISTORIAL DE LA SUCURSAL
    public function listarPorSucursal($sucId) {
        // Hacemos un sub-query para contar cuántos productos tiene cada pedido
        $sql = "SELECT p.*, 
                       (SELECT COUNT(*) FROM tbl_pedido_detalle d WHERE d.ped_id = p.ped_id) as total_items,
                       u.usu_nombres, u.usu_apellidos
                FROM tbl_pedido_stock p
                INNER JOIN tbl_usuario u ON p.usu_id_solicita = u.usu_id
                WHERE p.suc_id = :suc
                ORDER BY p.ped_fecha_solicitud DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':suc' => $sucId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. OBTENER DETALLE DE UN PEDIDO ESPECÍFICO
    public function obtenerDetallesPedido($pedId) {
        // CAMBIO: Agregamos d.det_cantidad_despachada y d.det_observacion
        $sql = "SELECT p.pro_nombre, p.pro_unidad, p.pro_contenido, p.pro_unidad_consumo,
                    d.det_cant_solicitada, 
                    d.det_cantidad_despachada, 
                    d.det_observacion,
                    (SELECT i.img_url 
                        FROM tbl_imagen i 
                        INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                        WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' 
                        ORDER BY i.img_principal DESC LIMIT 1) as pro_foto
                FROM tbl_pedido_detalle d
                INNER JOIN tbl_producto p ON d.pro_id = p.pro_id
                WHERE d.ped_id = :pid";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $pedId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. CANCELAR UN PEDIDO (Solo si está pendiente)
    public function cancelarPedido($pedId, $sucId) {
        // Verificamos que sea de la sucursal y esté pendiente
        $sql = "UPDATE tbl_pedido_stock 
                SET ped_estado = 'CANCELADO' 
                WHERE ped_id = :pid 
                  AND suc_id = :sid 
                  AND ped_estado = 'PENDIENTE'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $pedId, ':sid' => $sucId]);

        // Verificamos si se actualizó alguna fila (si no, es que no era pendiente o no era tuyo)
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // 6. [DUEÑO] LISTAR TODOS LOS PEDIDOS DEL NEGOCIO
    public function listarPedidosGlobales($negocioId) {
        $sql = "SELECT p.*, 
                       s.suc_nombre, -- Necesitamos saber de qué sucursal viene
                       u.usu_nombres, u.usu_apellidos,
                       (SELECT COUNT(*) FROM tbl_pedido_detalle d WHERE d.ped_id = p.ped_id) as total_items
                FROM tbl_pedido_stock p
                INNER JOIN tbl_sucursal s ON p.suc_id = s.suc_id
                INNER JOIN tbl_usuario u ON p.usu_id_solicita = u.usu_id
                WHERE p.neg_id = :nid
                ORDER BY FIELD(p.ped_estado, 'PENDIENTE', 'PARCIAL', 'APROBADO', 'RECHAZADO', 'CANCELADO'), p.ped_fecha_solicitud ASC";
        // El ORDER BY FIELD pone los PENDIENTES primero arriba.
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nid' => $negocioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. [DUEÑO] OBTENER DETALLE CON STOCK DISPONIBLE
    public function obtenerDetalleParaDespacho($pedId) {
        // Aquí la clave es traer p.pro_stock (Lo que hay en bodega central)
        $sql = "SELECT d.det_id, d.det_cant_solicitada, 
                       p.pro_id, p.pro_nombre, p.pro_unidad, p.pro_stock as stock_bodega,
                       (SELECT i.img_url FROM tbl_imagen i 
                        INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                        WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' 
                        LIMIT 1) as pro_foto
                FROM tbl_pedido_detalle d
                INNER JOIN tbl_producto p ON d.pro_id = p.pro_id
                WHERE d.ped_id = :pid";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $pedId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. [DUEÑO] PROCESAR EL DESPACHO (Lógica: Descuenta Global -> Pone En Camino)
    public function procesarDespacho($negocioId, $datos) {
        try {
            $this->pdo->beginTransaction(); // --- INICIO TRANSACCIÓN ---

            $pedId = $datos['ped_id'];
            $items = $datos['items'];

            foreach ($items as $item) {
                $detId = $item['det_id'];
                $cantidadEnviar = floatval($item['cantidad']);
                $observacion = $item['observacion'];

                // 2. Obtener datos originales del detalle (Producto ID)
                $stmtDet = $this->pdo->prepare("SELECT pro_id FROM tbl_pedido_detalle WHERE det_id = :did");
                $stmtDet->execute([':did' => $detId]);
                $prod = $stmtDet->fetch(PDO::FETCH_ASSOC);
                
                if (!$prod) throw new Exception("Producto no encontrado en el detalle.");
                $proId = $prod['pro_id'];

                // 3. DESCONTAR DE BODEGA GLOBAL (tbl_producto)
                // OJO: Solo restamos aquí. No sumamos a la sucursal todavía.
                $sqlGlobal = "UPDATE tbl_producto 
                              SET pro_stock = pro_stock - :cant1 
                              WHERE pro_id = :pid AND pro_stock >= :cant2";
                
                $stmtGlobal = $this->pdo->prepare($sqlGlobal);
                $stmtGlobal->execute([
                    ':cant1' => $cantidadEnviar, 
                    ':cant2' => $cantidadEnviar,
                    ':pid' => $proId
                ]);

                if ($stmtGlobal->rowCount() == 0 && $cantidadEnviar > 0) {
                    throw new Exception("Stock insuficiente en bodega global para el producto ID: $proId");
                }

                // --- AQUÍ BORRÉ EL INSERT A TBL_PRODUCTO_SUCURSAL ---
                // La mercadería ahora está "viajando", no ha llegado.

                // 5. ACTUALIZAR DETALLE DEL PEDIDO (Lo que realmente salió)
                $sqlUpdDet = "UPDATE tbl_pedido_detalle 
                              SET det_cantidad_despachada = :enviado, 
                                  det_observacion = :obs 
                              WHERE det_id = :did";
                $this->pdo->prepare($sqlUpdDet)->execute([
                    ':enviado' => $cantidadEnviar,
                    ':obs' => $observacion,
                    ':did' => $detId
                ]);
            }

            // 6. ACTUALIZAR ESTADO DEL PEDIDO A 'EN CAMINO'
            $sqlEstado = "UPDATE tbl_pedido_stock 
                          SET ped_estado = 'EN CAMINO', 
                              ped_fecha_respuesta = NOW() 
                          WHERE ped_id = :pid";
            $this->pdo->prepare($sqlEstado)->execute([':pid' => $pedId]);

            $this->pdo->commit(); // --- GUARDADO FINAL ---
            return true;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack(); // --- DESHACER TODO SI HAY ERROR ---
            }
            throw $e;
        }
    }

    // 9. FINALIZAR RECEPCIÓN (SUMAR STOCK A SUCURSAL)
    public function finalizarRecepcionSucursal($pedId, $sucId) {
        try {
            $this->pdo->beginTransaction();

            // A. Verificar estado y pertenencia
            $stmtCheck = $this->pdo->prepare("SELECT ped_estado FROM tbl_pedido_stock WHERE ped_id = ? AND suc_id = ?");
            $stmtCheck->execute([$pedId, $sucId]);
            $pedido = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) throw new Exception("Pedido no encontrado o no pertenece a tu sucursal.");
            if ($pedido['ped_estado'] == 'RECIBIDO') throw new Exception("Este pedido ya fue recibido.");

            // B. Obtener productos para sumar al inventario
            // Usamos cantidad_despachada. Si es 0 o nulo, asumimos que enviaron lo solicitado (fallback)
            $sqlItems = "SELECT pro_id, det_cant_solicitada, det_cantidad_despachada 
                        FROM tbl_pedido_detalle WHERE ped_id = ?";
            $stmtItems = $this->pdo->prepare($sqlItems);
            $stmtItems->execute([$pedId]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // C. Bucle para actualizar/insertar en tbl_producto_sucursal (o donde guardes stock de sucursal)
            $sqlUpsert = "INSERT INTO tbl_producto_sucursal (suc_id, pro_id, ps_stock) 
                        VALUES (:suc, :pro, :cant) 
                        ON DUPLICATE KEY UPDATE ps_stock = ps_stock + :cant_upd";
            
            $stmtStock = $this->pdo->prepare($sqlUpsert);

            foreach ($items as $item) {
                // Lógica: Si Matriz puso cuánto despachó, usamos eso. Si no, usamos lo solicitado.
                $cantidadReal = floatval($item['det_cantidad_despachada']);
                if ($cantidadReal <= 0) {
                    $cantidadReal = floatval($item['det_cant_solicitada']);
                }

                if ($cantidadReal > 0) {
                    $stmtStock->execute([
                        ':suc'      => $sucId,
                        ':pro'      => $item['pro_id'],
                        ':cant'     => $cantidadReal,     // Para el INSERT inicial
                        ':cant_upd' => $cantidadReal      // Para el UPDATE si ya existe
                    ]);
                }
            }

            // D. Cerrar el pedido
            $stmtClose = $this->pdo->prepare("UPDATE tbl_pedido_stock SET ped_estado = 'RECIBIDO', ped_fecha_recepcion = NOW() WHERE ped_id = ?");
            $stmtClose->execute([$pedId]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ====================================================================
    // 10. STOCK INTELIGENTE (MODO HÍBRIDO: CERRADO + ABIERTO)
    // ====================================================================
    public function obtenerStockSucursal($sucId, $negocioId) {
        $sql = "SELECT p.pro_id, p.pro_nombre, p.pro_codigo, p.pro_unidad,
                       p.pro_contenido, p.pro_unidad_consumo, -- Necesarios para la barra de progreso
                       
                       -- Stock Físico (Cajas/Unidades cerradas)
                       COALESCE(ps.ps_stock, 0) as stock_cerrado,
                       
                       -- Stock Consumo (Sobras en la unidad abierta)
                       COALESCE(ps.ps_stock_consumo, 0) as stock_abierto,
                       
                       -- Foto
                       (SELECT i.img_url FROM tbl_imagen i 
                        INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                        WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' 
                        LIMIT 1) as pro_foto

                FROM tbl_producto p
                LEFT JOIN tbl_producto_sucursal ps 
                       ON p.pro_id = ps.pro_id AND ps.suc_id = :sid
                
                WHERE p.neg_id = :nid 
                  AND p.pro_estado = 'A' 

                -- ORDENAR: Primero lo agotado, luego lo que se está acabando
                ORDER BY (stock_cerrado = 0) DESC, stock_cerrado ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sid' => $sucId, ':nid' => $negocioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}