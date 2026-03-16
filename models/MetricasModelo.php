<?php
// models/MetricasModelo.php
require_once __DIR__ . '/../nucleo/TimeHelper.php';

class MetricasModelo {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // 1. OBTENER INGRESOS TOTALES Y DESGLOSE (RANGO DE FECHAS)
    public function obtenerIngresos($neg_id, $f_ini, $f_fin) {
        $sql = "SELECT 
                    COALESCE(SUM(pago_monto), 0) as total,
                    COALESCE(SUM(CASE WHEN cita_id IS NOT NULL THEN pago_monto ELSE 0 END), 0) as total_servicios,
                    COALESCE(SUM(CASE WHEN ord_id IS NOT NULL THEN pago_monto ELSE 0 END), 0) as total_productos
                FROM tbl_pago 
                WHERE neg_id = :neg_id AND pago_fecha BETWEEN :ini AND :fin";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg_id' => $neg_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Devolvemos un arreglo con los 3 valores
    }

    // 2. OBTENER DATOS PARA LA GRÁFICA (DOS LÍNEAS)
    public function obtenerGraficaIngresos($neg_id, $f_ini, $f_fin) {
        $fechaInicio = substr($f_ini, 0, 10);
        $fechaFin = substr($f_fin, 0, 10);

        if ($fechaInicio === $fechaFin) {
            // Un solo día: Agrupamos por hora
            $sql = "SELECT DATE_FORMAT(pago_fecha, '%H:00') as etiqueta, 
                           SUM(CASE WHEN cita_id IS NOT NULL THEN pago_monto ELSE 0 END) as total_servicios,
                           SUM(CASE WHEN ord_id IS NOT NULL THEN pago_monto ELSE 0 END) as total_productos
                    FROM tbl_pago 
                    WHERE neg_id = :neg_id AND pago_fecha BETWEEN :ini AND :fin 
                    GROUP BY HOUR(pago_fecha) 
                    ORDER BY HOUR(pago_fecha)";
        } else {
            // Varios días: Agrupamos por fecha
            $sql = "SELECT DATE_FORMAT(pago_fecha, '%Y-%m-%d') as etiqueta, 
                           SUM(CASE WHEN cita_id IS NOT NULL THEN pago_monto ELSE 0 END) as total_servicios,
                           SUM(CASE WHEN ord_id IS NOT NULL THEN pago_monto ELSE 0 END) as total_productos
                    FROM tbl_pago 
                    WHERE neg_id = :neg_id AND pago_fecha BETWEEN :ini AND :fin 
                    GROUP BY DATE(pago_fecha) 
                    ORDER BY DATE(pago_fecha)";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg_id' => $neg_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // 3. OBTENER VOLUMEN DE CITAS (SOLO ESTADOS FINALES)
    public function obtenerVolumenCitas($neg_id, $f_ini, $f_fin) {
        $sql = "SELECT 
                    SUM(CASE WHEN cita_estado IN ('CONFIRMADO', 'PERDIDA', 'CANCELADO') THEN 1 ELSE 0 END) as total_validas,
                    SUM(CASE WHEN cita_estado = 'CONFIRMADO' THEN 1 ELSE 0 END) as finalizadas,
                    SUM(CASE WHEN cita_estado = 'PERDIDA' THEN 1 ELSE 0 END) as perdidas,
                    SUM(CASE WHEN cita_estado = 'CANCELADO' THEN 1 ELSE 0 END) as canceladas
                FROM tbl_cita 
                WHERE neg_id = :neg_id AND cita_fecha BETWEEN :ini AND :fin";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg_id' => $neg_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. OBTENER DATOS PARA GRÁFICA DE CITAS (3 LÍNEAS)
    public function obtenerGraficaCitas($neg_id, $f_ini, $f_fin) {
        $fechaInicio = substr($f_ini, 0, 10);
        $fechaFin = substr($f_fin, 0, 10);

        if ($fechaInicio === $fechaFin) {
            $sql = "SELECT DATE_FORMAT(cita_fecha, '%H:00') as etiqueta, 
                           SUM(CASE WHEN cita_estado = 'CONFIRMADO' THEN 1 ELSE 0 END) as finalizadas,
                           SUM(CASE WHEN cita_estado = 'PERDIDA' THEN 1 ELSE 0 END) as perdidas,
                           SUM(CASE WHEN cita_estado = 'CANCELADO' THEN 1 ELSE 0 END) as canceladas
                    FROM tbl_cita 
                    WHERE neg_id = :neg_id 
                    AND cita_estado IN ('CONFIRMADO', 'PERDIDA', 'CANCELADO')
                    AND cita_fecha BETWEEN :ini AND :fin 
                    GROUP BY HOUR(cita_fecha) ORDER BY HOUR(cita_fecha)";
        } else {
            $sql = "SELECT DATE_FORMAT(cita_fecha, '%Y-%m-%d') as etiqueta, 
                           SUM(CASE WHEN cita_estado = 'CONFIRMADO' THEN 1 ELSE 0 END) as finalizadas,
                           SUM(CASE WHEN cita_estado = 'PERDIDA' THEN 1 ELSE 0 END) as perdidas,
                           SUM(CASE WHEN cita_estado = 'CANCELADO' THEN 1 ELSE 0 END) as canceladas
                    FROM tbl_cita 
                    WHERE neg_id = :neg_id 
                    AND cita_estado IN ('CONFIRMADO', 'PERDIDA', 'CANCELADO')
                    AND cita_fecha BETWEEN :ini AND :fin 
                    GROUP BY DATE(cita_fecha) ORDER BY DATE(cita_fecha)";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg_id' => $neg_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. TOP SERVICIOS MÁS RENTABLES (CORREGIDO CON TU DB REAL)
    public function obtenerTopServicios($neg_id, $f_ini, $f_fin) {
        $sql = "SELECT 
                    s.serv_nombre as etiqueta, 
                    SUM(p.pago_monto) as total
                FROM tbl_pago p
                INNER JOIN tbl_cita c ON p.cita_id = c.cita_id
                INNER JOIN tbl_cita_det cd ON c.cita_id = cd.cita_id
                INNER JOIN tbl_servicio s ON cd.serv_id = s.serv_id
                WHERE p.neg_id = :neg_id AND p.pago_fecha BETWEEN :ini AND :fin
                GROUP BY s.serv_id, s.serv_nombre
                ORDER BY total DESC
                LIMIT 5";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':neg_id' => $neg_id, ':ini' => $f_ini, ':fin' => $f_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. OBTENER VENTAS POR CATEGORÍA (SERVICIOS Y PRODUCTOS UNIDOS)
    public function obtenerVentasPorCategoria($neg_id, $f_ini, $f_fin) {
        $sql = "SELECT etiqueta, SUM(total) as total FROM (
                    -- 1. Sumar ventas por Categoría de SERVICIOS
                    SELECT ts.tser_nombre as etiqueta, SUM(cd.det_precio) as total
                    FROM tbl_pago p
                    INNER JOIN tbl_cita_det cd ON p.cita_id = cd.cita_id
                    INNER JOIN tbl_servicio s ON cd.serv_id = s.serv_id
                    INNER JOIN tbl_tipo_servicio ts ON s.tser_id = ts.tser_id
                    WHERE p.neg_id = :neg_id1 AND p.pago_fecha BETWEEN :ini1 AND :fin1
                    GROUP BY ts.tser_id, ts.tser_nombre
                    
                    UNION ALL
                    
                    -- 2. Sumar ventas por Categoría de PRODUCTOS
                    SELECT tp.tpro_nombre as etiqueta, SUM(od.odet_subtotal) as total
                    FROM tbl_pago p
                    INNER JOIN tbl_orden_detalle od ON p.ord_id = od.ord_id
                    INNER JOIN tbl_producto pr ON od.pro_id = pr.pro_id
                    INNER JOIN tbl_tipo_producto tp ON pr.tpro_id = tp.tpro_id
                    WHERE p.neg_id = :neg_id2 AND p.pago_fecha BETWEEN :ini2 AND :fin2
                    GROUP BY tp.tpro_id, tp.tpro_nombre
                ) AS categorias_unidas
                GROUP BY etiqueta
                ORDER BY total DESC";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Pasamos los parámetros dos veces (uno para la parte de servicios y otro para productos)
        $stmt->execute([
            ':neg_id1' => $neg_id, 
            ':ini1' => $f_ini, 
            ':fin1' => $f_fin,
            ':neg_id2' => $neg_id, 
            ':ini2' => $f_ini, 
            ':fin2' => $f_fin
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // REPORTES MAESTROS: NÓMINA GLOBAL (TODOS LOS EMPLEADOS)
    // ====================================================================
    public function reporteComisionesEmpleados($neg_id, $f_ini, $f_fin, $usu_id = null) {
        // EMPEZAMOS DESDE EL USUARIO, NO DESDE LA CITA (LEFT JOIN)
        $sql = "SELECT 
                    u.usu_id,
                    CONCAT(u.usu_nombres, ' ', u.usu_apellidos) as empleado_nombre,
                    u.usu_foto,
                    COALESCE(u.usu_sueldo_base, 0) as sueldo_base,
                    r.rol_nombre,
                    COALESCE(s.suc_nombre, 'Global / Sin Sede') as suc_nombre,
                    COUNT(d.det_id) as total_servicios,
                    COALESCE(SUM(d.det_precio), 0) as produccion_bruta,
                    COALESCE(SUM(d.det_comision_monto), 0) as comision_neta
                FROM tbl_usuario u
                INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
                LEFT JOIN tbl_sucursal s ON u.suc_id = s.suc_id
                -- Solo unimos las citas finalizadas EN EL RANGO de fechas
                LEFT JOIN tbl_cita_det d ON u.usu_id = d.usu_id 
                      AND d.det_estado = 'FINALIZADO'
                      AND DATE(d.det_ini) BETWEEN :ini AND :fin
                WHERE u.neg_id = :neg_id 
                  AND u.usu_estado = 'A' 
                  AND u.rol_id NOT IN (1, 4)"; // Excluimos SuperAdmin y Clientes

        $params = [':neg_id' => $neg_id, ':ini' => $f_ini, ':fin' => $f_fin];

        // Si el dueño filtra un empleado específico:
        if (!empty($usu_id)) {
            $sql .= " AND u.usu_id = :uid";
            $params[':uid'] = $usu_id;
        }

        $sql .= " GROUP BY u.usu_id, empleado_nombre, u.usu_foto, u.usu_sueldo_base, r.rol_nombre, suc_nombre
                  ORDER BY r.rol_id ASC, comision_neta DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER LISTA DE EMPLEADOS PARA EL FILTRO
    public function obtenerEmpleadosNegocio($neg_id) {
        $sql = "SELECT u.usu_id, CONCAT(u.usu_nombres, ' ', u.usu_apellidos) as nombre, r.rol_nombre 
                FROM tbl_usuario u 
                INNER JOIN tbl_rol r ON u.rol_id = r.rol_id 
                WHERE u.neg_id = :nid AND u.usu_estado = 'A' AND u.rol_id NOT IN (1, 4)
                ORDER BY r.rol_id ASC, nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nid' => $neg_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // REPORTES MAESTROS: STOCK GLOBAL POR SUCURSALES
    // ====================================================================
    public function reporteStockGlobal($neg_id, $suc_id = null) {
        // Esta consulta agrupa el inventario. Si no se pasa $suc_id, trae de TODAS las sucursales.
        $sql = "SELECT 
                    s.suc_id, s.suc_nombre,
                    p.pro_id, p.pro_nombre, p.pro_codigo, p.pro_unidad,
                    COALESCE(ps.ps_stock, 0) as stock_fisico,
                    ps.ps_stock_min,
                    COALESCE(p.pro_costo_compra, 0) as costo_unitario,
                    (COALESCE(ps.ps_stock, 0) * COALESCE(p.pro_costo_compra, 0)) as capital_inmovilizado,
                    (SELECT i.img_url FROM tbl_imagen i 
                     INNER JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                     WHERE ir.img_ref_id = p.pro_id AND ir.img_tipo = 'PRODUCTO' LIMIT 1) as pro_foto
                FROM tbl_producto_sucursal ps
                INNER JOIN tbl_producto p ON ps.pro_id = p.pro_id
                INNER JOIN tbl_sucursal s ON ps.suc_id = s.suc_id
                WHERE p.neg_id = :neg_id 
                  AND ps.ps_estado = 'A' 
                  AND s.suc_estado = 'A'";

        $params = [':neg_id' => $neg_id];

        if ($suc_id) {
            $sql .= " AND s.suc_id = :suc_id";
            $params[':suc_id'] = $suc_id;
        }

        $sql .= " ORDER BY s.suc_nombre ASC, p.pro_nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Auxiliar: Listar sucursales para el filtro
    public function obtenerListaSucursales($neg_id) {
        $sql = "SELECT suc_id, suc_nombre FROM tbl_sucursal WHERE neg_id = :nid AND suc_estado = 'A' ORDER BY suc_nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nid' => $neg_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}