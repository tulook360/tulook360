<?php
class RepartidorModelo {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // 1. VER OFERTAS (Con lista de tiendas y distancia real)
    public function obtenerOfertasDisponibles() {
        $sql = "SELECT o.ord_id, o.ord_codigo, o.ord_fecha, o.ord_total, 
                       o.ord_direccion_envio, o.ord_costo_envio,
                       o.ord_referencia, 
                       o.ord_ubicacion_lat, o.ord_ubicacion_lon,
                       (SELECT COUNT(*) FROM tbl_orden_detalle WHERE ord_id = o.ord_id) as total_items
                FROM tbl_orden o
                WHERE o.ord_tipo_entrega = 'DOMICILIO' 
                AND o.ord_estado = 'PENDIENTE' 
                AND o.usu_id_repartidor IS NULL
                ORDER BY o.ord_fecha DESC";
        
        $ordenes = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // ENRIQUECER DATOS
        foreach ($ordenes as &$ord) {
            // A. Calcular Distancia Real
            $ord['distancia_real_km'] = $this->calcularRutaTotal($ord['ord_id'], $ord['ord_ubicacion_lat'], $ord['ord_ubicacion_lon']);
            $ord['capital_necesario'] = $ord['ord_total'] - $ord['ord_costo_envio'];

            // B. Obtener LISTA DE TIENDAS (Nombre y Dirección)
            $sqlTiendas = "SELECT DISTINCT n.neg_nombre, s.suc_direccion 
                           FROM tbl_orden_detalle od
                           JOIN tbl_negocio n ON od.neg_id = n.neg_id
                           JOIN tbl_sucursal s ON od.suc_id = s.suc_id
                           WHERE od.ord_id = ?";
            $stmtT = $this->db->prepare($sqlTiendas);
            $stmtT->execute([$ord['ord_id']]);
            $ord['lista_tiendas'] = $stmtT->fetchAll(PDO::FETCH_ASSOC);
        }

        return $ordenes;
    }

    // FUNCIÓN PRIVADA PARA CALCULAR LA RUTA (TIENDAS -> CLIENTE)
    private function calcularRutaTotal($ordId, $latCliente, $lonCliente) {
        // 1. Obtener coordenadas de TODAS las sucursales involucradas en la orden
        $sql = "SELECT DISTINCT s.suc_latitud, s.suc_longitud 
                FROM tbl_orden_detalle od
                JOIN tbl_sucursal s ON od.suc_id = s.suc_id
                WHERE od.ord_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ordId]);
        $puntos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($puntos)) return "0.0";

        $distanciaTotal = 0;
        $latAnterior = null;
        $lonAnterior = null;

        // 2. Sumar distancias entre tiendas (si hay múltiples paradas)
        foreach ($puntos as $i => $pto) {
            if ($i == 0) {
                // Primer punto (Inicio de ruta de recolección)
                $latAnterior = $pto['suc_latitud'];
                $lonAnterior = $pto['suc_longitud'];
                continue;
            }
            // Distancia de Tienda A -> Tienda B
            $distanciaTotal += $this->haversine($latAnterior, $lonAnterior, $pto['suc_latitud'], $pto['suc_longitud']);
            $latAnterior = $pto['suc_latitud'];
            $lonAnterior = $pto['suc_longitud'];
        }

        // 3. Sumar distancia final: Última Tienda -> Cliente
        if ($latCliente && $lonCliente) {
            $distanciaTotal += $this->haversine($latAnterior, $lonAnterior, $latCliente, $lonCliente);
        }

        return number_format($distanciaTotal, 1); // Devuelve "2.5", "5.0", etc.
    }

    // FÓRMULA MATEMÁTICA PARA DISTANCIA ENTRE DOS COORDENADAS
    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $radioTierra = 6371; // Kilómetros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $radioTierra * $c;
    }

    // 2. VER MIS PEDIDOS ACTIVOS
    public function obtenerMisPedidosEnCurso($repartidor_id) {
        $sql = "SELECT o.ord_id, o.ord_codigo, o.ord_estado, o.ord_direccion_envio, o.ord_total
                FROM tbl_orden o
                WHERE o.usu_id_repartidor = ? 
                AND o.ord_estado IN ('ACEPTADO', 'RECOLECTANDO', 'EN_CAMINO')
                ORDER BY o.ord_fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$repartidor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. ACEPTAR UN PEDIDO
    public function aceptarPedido($ord_id, $repartidor_id) {
        try {
            $this->db->beginTransaction();

            $sqlCheck = "SELECT usu_id_repartidor FROM tbl_orden WHERE ord_id = ? FOR UPDATE";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$ord_id]);
            $actual = $stmtCheck->fetchColumn();

            if ($actual) {
                $this->db->rollBack();
                return ['success' => false, 'error' => '¡Lo siento! Otro repartidor tomó esta orden.'];
            }

            $sqlUpdate = "UPDATE tbl_orden 
                          SET usu_id_repartidor = ?, ord_estado = 'ACEPTADO' 
                          WHERE ord_id = ?";
            $stmtUp = $this->db->prepare($sqlUpdate);
            $stmtUp->execute([$repartidor_id, $ord_id]);

            $this->db->commit();
            return ['success' => true, 'message' => '¡Orden aceptada!'];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function finalizarOrden($ord_id) {
        try {
            // CORRECCIÓN: Usar 'COMPLETADO' porque 'ENTREGADO' no existe en el ENUM de la BD
            $sql = "UPDATE tbl_orden SET ord_estado = 'COMPLETADO' WHERE ord_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ord_id]);
        } catch (Exception $e) {
            return false;
        }
    }



    // NUEVO: LIBERAR PEDIDO (CANCELAR CARRERA)
    public function liberarPedido($ord_id, $repartidor_id) {
        try {
            // Verificar que la orden pertenezca a este repartidor antes de soltarla
            $sqlCheck = "SELECT ord_id FROM tbl_orden WHERE ord_id = ? AND usu_id_repartidor = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$ord_id, $repartidor_id]);
            
            if (!$stmtCheck->fetch()) {
                return ['success' => false, 'error' => 'Esta orden no te pertenece.'];
            }

            // Resetear la orden a PENDIENTE y quitar el repartidor
            $sql = "UPDATE tbl_orden 
                    SET usu_id_repartidor = NULL, 
                        ord_estado = 'PENDIENTE' 
                    WHERE ord_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ord_id]);
            
            return ['success' => true];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // --- NUEVO: GUARDAR EN TABLA TRACKING ---
    public function actualizarGPS($usu_id, $lat, $lon, $ord_id) {
        try {
            // Usamos ON DUPLICATE KEY UPDATE para no llenar la tabla, 
            // solo actualizamos la fila de este repartidor.
            $sql = "INSERT INTO tbl_tracking (usu_id, ord_id, track_lat, track_lon, track_fecha) 
                    VALUES (?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    ord_id = VALUES(ord_id), 
                    track_lat = VALUES(track_lat), 
                    track_lon = VALUES(track_lon), 
                    track_fecha = NOW()";
            
            $stmt = $this->db->prepare($sql);
            // Convertir a null si ord_id viene vacío o 0
            $ord_id = empty($ord_id) ? null : $ord_id;
            
            return $stmt->execute([$usu_id, $ord_id, $lat, $lon]);
        } catch (Exception $e) {
            // Silencioso para no romper el flujo
            return false;
        }
    }
}
?>