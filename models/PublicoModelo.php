<?php
class PublicoModelo {
    private $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function obtenerLogosNegocios() {
        return $this->pdo->query("SELECT neg_logo, neg_nombre FROM tbl_negocio WHERE neg_estado = 'A' AND neg_logo IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias() {
        return $this->pdo->query("SELECT tneg_nombre, tneg_icono FROM tbl_tipo_negocio WHERE tneg_estado = 'A'")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerServiciosHome($busqueda = '') {
        $sql = "SELECT s.serv_id, s.serv_nombre, s.serv_precio, s.serv_duracion, s.serv_descripcion, 
                    n.neg_nombre, n.neg_logo, n.neg_id,
                    
                    -- CÁLCULO DEL PROMEDIO DE CALIFICACIÓN
                    (
                        SELECT AVG(cal.cal_valor_servicio)
                        FROM tbl_calificacion cal
                        INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id
                        WHERE cd.serv_id = s.serv_id 
                        AND cal.cal_origen = 'CITA'
                    ) as rating_promedio,
                    
                    -- CONTEO DE VOTOS
                    (
                        SELECT COUNT(*)
                        FROM tbl_calificacion cal
                        INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id
                        WHERE cd.serv_id = s.serv_id 
                        AND cal.cal_origen = 'CITA'
                    ) as votos_total,

                    -- PUNTOS DE FIDELIDAD (NUEVO)
                    CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados

                FROM tbl_servicio s 
                JOIN tbl_negocio n ON s.neg_id = n.neg_id 
                -- Join para verificar si la fidelidad está activa en el negocio
                LEFT JOIN tbl_fidelidad_config fc ON n.neg_id = fc.neg_id AND fc.fid_activa = 1
                -- Join para traer los puntos específicos del servicio
                LEFT JOIN tbl_fidelidad_item fi ON s.serv_id = fi.serv_id AND fi.fiditem_estado = 'A'

                WHERE s.serv_estado = 'A' AND n.neg_estado = 'A'";
        
        if ($busqueda) { 
            $sql .= " AND (s.serv_nombre LIKE :q OR n.neg_nombre LIKE :q)"; 
        }
        
        $sql .= " ORDER BY RAND() LIMIT 12";
        
        $stmt = $this->pdo->prepare($sql);
        if ($busqueda) { 
            $stmt->execute([':q' => "%$busqueda%"]); 
        } else { 
            $stmt->execute(); 
        }
        
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($servicios as &$ser) {
            $ser['imagenes'] = $this->obtenerImagenes($ser['serv_id'], 'SERVICIO');
        }
        return $servicios;
    }

    public function obtenerProductosHome($busqueda = '') {
        $sql = "SELECT p.pro_id, p.pro_nombre, p.pro_precio, p.pro_descripcion, n.neg_nombre, n.neg_logo
                FROM tbl_producto p 
                JOIN tbl_negocio n ON p.neg_id = n.neg_id 
                WHERE p.pro_estado = 'A' AND p.pro_venta = 1 AND n.neg_estado = 'A'";
        if ($busqueda) { $sql .= " AND (p.pro_nombre LIKE :q OR n.neg_nombre LIKE :q)"; }
        $sql .= " ORDER BY RAND() LIMIT 12";
        $stmt = $this->pdo->prepare($sql);
        if ($busqueda) { $stmt->execute([':q' => "%$busqueda%"]); } else { $stmt->execute(); }
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productos as &$pro) {
            $pro['imagenes'] = $this->obtenerImagenes($pro['pro_id'], 'PRODUCTO');
        }
        return $productos;
    }

    private function obtenerImagenes($refId, $tipo) {
        $sql = "SELECT i.img_url FROM tbl_imagen i JOIN tbl_img_recurso ir ON i.img_id = ir.img_id WHERE ir.img_tipo = :tipo AND ir.img_ref_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tipo' => $tipo, ':id' => $refId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    // BÚSQUEDA HÍBRIDA (Servicios + Productos)
    public function buscarVivo($termino) {
        $resultados = [];
        $termLike = "%$termino%"; // El término original (ej: "cortes")
        
        // --- LÓGICA INTELIGENTE DE PLURALES ---
        $raiz = $termino;
        // Si termina en 's' y tiene más de 3 letras, intentamos buscar sin la 's'
        if(strlen($termino) > 3 && strtolower(substr($termino, -1)) === 's') {
            $raiz = substr($termino, 0, -1); // "cortes" se vuelve "corte"
        }
        $raizLike = "%$raiz%"; // La raíz calculada (ej: "corte")

        // A. BUSCAR SERVICIOS (CON RATING Y BÚSQUEDA POR RAÍZ)
        $sqlS = "SELECT s.serv_id as id, s.serv_nombre as titulo, s.serv_precio as precio, 
                        s.serv_duracion as meta, n.neg_nombre as negocio, 'servicio' as tipo,
                        
                        -- LÓGICA DE PUNTOS (CORREGIDA)
                        CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados,

                        (SELECT AVG(cal.cal_valor_servicio) FROM tbl_calificacion cal INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id WHERE cd.serv_id = s.serv_id AND cal.cal_origen = 'CITA') as rating_promedio,
                        (SELECT COUNT(*) FROM tbl_calificacion cal INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id WHERE cd.serv_id = s.serv_id AND cal.cal_origen = 'CITA') as votos_total

                FROM tbl_servicio s 
                JOIN tbl_negocio n ON s.neg_id = n.neg_id
                LEFT JOIN tbl_fidelidad_config fc ON n.neg_id = fc.neg_id AND fc.fid_activa = 1
                LEFT JOIN tbl_fidelidad_item fi ON s.serv_id = fi.serv_id AND fi.fiditem_estado = 'A'
                WHERE s.serv_estado = 'A' AND n.neg_estado = 'A'
                AND (s.serv_nombre LIKE :q1 OR n.neg_nombre LIKE :q2 OR s.serv_nombre LIKE :raiz1 OR n.neg_nombre LIKE :raiz2) 
                LIMIT 5";
        
        $stmtS = $this->pdo->prepare($sqlS);
        $stmtS->execute([
            ':q1'    => $termLike, 
            ':q2'    => $termLike,
            ':raiz1' => $raizLike,
            ':raiz2' => $raizLike
        ]); 
        $resS = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        // B. BUSCAR PRODUCTOS (CON RATING Y STOCK)
        $sqlP = "SELECT p.pro_id as id, p.pro_nombre as titulo, p.pro_precio as precio, 
                        'Stock' as meta, n.neg_nombre as negocio, 'producto' as tipo,
                        
                        -- LÓGICA DE PUNTOS (CORREGIDA)
                        CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados,

                        (SELECT IFNULL(SUM(ps.ps_stock), 0) FROM tbl_producto_sucursal ps INNER JOIN tbl_sucursal s ON ps.suc_id = s.suc_id WHERE ps.pro_id = p.pro_id AND ps.ps_estado = 'A' AND s.suc_estado = 'A') as stock_real,
                        (SELECT AVG(cal.cal_valor_producto) FROM tbl_calificacion cal WHERE cal.cal_ref_id = p.pro_id AND cal.cal_origen = 'PRODUCTO') as rating_promedio,
                        (SELECT COUNT(*) FROM tbl_calificacion cal WHERE cal.cal_ref_id = p.pro_id AND cal.cal_origen = 'PRODUCTO' AND cal.cal_valor_producto > 0) as votos_total

                FROM tbl_producto p 
                JOIN tbl_negocio n ON p.neg_id = n.neg_id
                LEFT JOIN tbl_fidelidad_config fc ON n.neg_id = fc.neg_id AND fc.fid_activa = 1
                LEFT JOIN tbl_fidelidad_item fi ON p.pro_id = fi.pro_id AND fi.fiditem_estado = 'A'
                WHERE p.pro_estado = 'A' AND p.pro_venta = 1 AND n.neg_estado = 'A'
                AND (p.pro_nombre LIKE :q1 OR n.neg_nombre LIKE :q2 OR p.pro_nombre LIKE :raiz1 OR n.neg_nombre LIKE :raiz2)
                HAVING stock_real > 0 
                LIMIT 5";

        $stmtP = $this->pdo->prepare($sqlP);
        $stmtP->execute([
            ':q1'    => $termLike, 
            ':q2'    => $termLike,
            'raiz1'  => $raizLike,
            'raiz2'  => $raizLike
        ]);
        $resP = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // C. UNIR Y AGREGAR FOTOS
        $combinado = array_merge($resS, $resP);
        
        foreach($combinado as &$item) {
            $tipoImg = ($item['tipo'] === 'servicio') ? 'SERVICIO' : 'PRODUCTO';
            $item['imagen'] = $this->obtenerImagenPrincipal($item['id'], $tipoImg);
        }

        return $combinado;
    }

    // Helper privado para sacar 1 foto (Si no existe en tu modelo, agrégalo)
    private function obtenerImagenPrincipal($id, $tipo) {
        $sql = "SELECT i.img_url FROM tbl_imagen i 
                JOIN tbl_img_recurso ir ON i.img_id = ir.img_id 
                WHERE ir.img_tipo = :t AND ir.img_ref_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':t' => $tipo, ':id' => $id]);
        return $stmt->fetchColumn() ?: 'https://via.placeholder.com/150?text=Sin+Foto';
    }



    // --- NUEVAS FUNCIONES PARA SECCIÓN DE NEGOCIOS ---

    // 1. LISTADO SIMPLE (CORREGIDO: Solo columnas existentes)
    public function obtenerDirectorioNegocios($busqueda = '') {
        // Solo pedimos las columnas que SI existen en tu tabla
        $sql = "SELECT neg_id, neg_nombre, neg_logo 
                FROM tbl_negocio 
                WHERE neg_estado = 'A'"; // Solo activos
        
        if ($busqueda) {
            // Solo filtramos por nombre (descripcion no existe)
            $sql .= " AND (neg_nombre LIKE :q)";
        }
        
        $sql .= " ORDER BY neg_nombre ASC"; 
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($busqueda) {
            $stmt->execute([':q' => "%$busqueda%"]);
        } else {
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. SUPER CONSULTA PARA EL MODAL (CORREGIDA SEGÚN TU BD)
    public function obtenerPerfilNegocioFull($idNegocio, $idCliente = null) {
        $data = [];

        // A) INFO BÁSICA (Mantenemos igual)
        $sqlNeg = "SELECT n.neg_id, n.neg_nombre, n.neg_logo, n.neg_fundacion, n.neg_envio_costo,
                    
                    -- NUEVO: Traemos el estado real del sistema de puntos
                    IFNULL(fc.fid_activa, 0) as fidelidad_activa,

                    -- Promedio y votos (Mantenemos igual)
                    (SELECT IFNULL(AVG(cal.cal_valor_negocio), 0) FROM tbl_calificacion cal WHERE cal.neg_id = n.neg_id AND cal.cal_valor_negocio > 0) as rating_negocio,
                    (SELECT COUNT(*) FROM tbl_calificacion cal WHERE cal.neg_id = n.neg_id AND cal.cal_valor_negocio > 0) as votos_negocio

                FROM tbl_negocio n 
                -- Hacemos un JOIN con la configuración de fidelidad
                LEFT JOIN tbl_fidelidad_config fc ON n.neg_id = fc.neg_id
                WHERE n.neg_id = :id AND n.neg_estado = 'A'";
        
        $stmt = $this->pdo->prepare($sqlNeg);
        $stmt->execute([':id' => $idNegocio]);
        $data['info'] = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data['info']) return null;

        // B) SUCURSALES (Mantenemos igual)
        $sqlSuc = "SELECT suc_id, suc_nombre, suc_direccion, suc_telefono, suc_latitud, suc_longitud, suc_foto FROM tbl_sucursal WHERE neg_id = :id AND suc_estado = 'A'";
        $stmt = $this->pdo->prepare($sqlSuc);
        $stmt->execute([':id' => $idNegocio]);
        $data['sucursales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // C) SERVICIOS CON PUNTOS
        $sqlServ = "SELECT s.serv_id, s.serv_nombre, s.serv_descripcion, s.serv_precio, s.serv_duracion,
                    CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados,
                    (SELECT AVG(cal.cal_valor_servicio) FROM tbl_calificacion cal INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id WHERE cd.serv_id = s.serv_id AND cal.cal_origen = 'CITA') as rating_promedio,
                    (SELECT COUNT(*) FROM tbl_calificacion cal INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id WHERE cd.serv_id = s.serv_id AND cal.cal_origen = 'CITA') as votos_total
                    FROM tbl_servicio s 
                    LEFT JOIN tbl_fidelidad_config fc ON s.neg_id = fc.neg_id AND fc.fid_activa = 1
                    LEFT JOIN tbl_fidelidad_item fi ON s.serv_id = fi.serv_id AND fi.fiditem_estado = 'A'
                    WHERE s.neg_id = :id AND s.serv_estado = 'A'";
        $stmt = $this->pdo->prepare($sqlServ);
        $stmt->execute([':id' => $idNegocio]);
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($servicios as &$s) { $imgs = $this->obtenerImagenes($s['serv_id'], 'SERVICIO'); $s['imagen'] = !empty($imgs) ? $imgs[0] : null; }
        $data['servicios'] = $servicios;

        // D) PRODUCTOS CON PUNTOS (Mantenemos igual)
        $sqlProd = "SELECT p.pro_id, p.pro_nombre, p.pro_descripcion, p.pro_precio,
                    CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados,
                    (SELECT IFNULL(SUM(ps.ps_stock), 0) FROM tbl_producto_sucursal ps INNER JOIN tbl_sucursal s ON ps.suc_id = s.suc_id WHERE ps.pro_id = p.pro_id AND ps.ps_estado = 'A' AND s.suc_estado = 'A') as stock_real,
                    (SELECT IFNULL(AVG(cal.cal_valor_producto), 0) FROM tbl_calificacion cal WHERE cal.cal_ref_id = p.pro_id AND cal.cal_origen = 'PRODUCTO') as rating_producto
                    FROM tbl_producto p 
                    LEFT JOIN tbl_fidelidad_config fc ON p.neg_id = fc.neg_id AND fc.fid_activa = 1
                    LEFT JOIN tbl_fidelidad_item fi ON p.pro_id = fi.pro_id AND fi.fiditem_estado = 'A'
                    WHERE p.neg_id = :id AND p.pro_estado = 'A' AND p.pro_venta = 1 HAVING stock_real > 0";
        $stmt = $this->pdo->prepare($sqlProd);
        $stmt->execute([':id' => $idNegocio]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productos as &$p) { $imgs = $this->obtenerImagenes($p['pro_id'], 'PRODUCTO'); $p['imagen'] = !empty($imgs) ? $imgs[0] : null; }
        $data['productos'] = $productos;

        // E) NUEVO: OBTENER SALDO DEL CLIENTE EN ESTE NEGOCIO
        $data['puntos_cliente'] = 0;
        if ($idCliente) {
            $sqlPuntos = "SELECT fidcli_total FROM tbl_fidelidad_cliente WHERE neg_id = :nid AND cli_id = :cid";
            $stmtP = $this->pdo->prepare($sqlPuntos);
            $stmtP->execute([':nid' => $idNegocio, ':cid' => $idCliente]);
            $data['puntos_cliente'] = $stmtP->fetchColumn() ?: 0;
        }

        return $data;
    }


    // ====================================================================
    // DETALLE SERVICIO AJAX (Para Modal)
    // ====================================================================
    public function obtenerDetalleServicioModal($idServicio, $idCliente = null) {
        $data = [];

        // 1. Info del Servicio
        $sql = "SELECT s.*, n.neg_nombre, n.neg_logo FROM tbl_servicio s 
                JOIN tbl_negocio n ON s.neg_id = n.neg_id 
                WHERE s.serv_id = :id AND s.serv_estado = 'A'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idServicio]);
        $data['info'] = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data['info']) return null;

        // 2. Traer puntos del cliente de una vez (AQUÍ ESTÁ EL TRUCO)
        $data['puntos_cliente'] = 0;
        if ($idCliente) {
            $sqlP = "SELECT fidcli_total FROM tbl_fidelidad_cliente WHERE neg_id = :nid AND cli_id = :cid";
            $stP = $this->pdo->prepare($sqlP);
            $stP->execute([':nid' => $data['info']['neg_id'], ':cid' => $idCliente]);
            $data['puntos_cliente'] = $stP->fetchColumn() ?: 0;
        }

        // ... El resto (imágenes, sucursales, especialistas) que ya tenías
        $data['imagenes'] = $this->obtenerImagenes($idServicio, 'SERVICIO');
        $data['sucursales'] = $this->pdo->query("SELECT suc.* FROM tbl_sucursal suc JOIN tbl_servicio_sucursal ss ON suc.suc_id = ss.suc_id WHERE ss.serv_id = $idServicio AND suc.suc_estado = 'A'")->fetchAll(PDO::FETCH_ASSOC);
        
        // Carga rápida de especialistas (simplificada para el ejemplo, usa la tuya)
        $sqlEsp = "SELECT DISTINCT u.* FROM tbl_usuario u INNER JOIN tbl_empleado_habilidad h ON u.usu_id = h.usu_id INNER JOIN tbl_servicio s ON h.tser_id = s.tser_id WHERE s.serv_id = $idServicio AND u.usu_estado = 'A'";
        $data['especialistas'] = $this->pdo->query($sqlEsp)->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }


    // --- LÓGICA DE RESERVA INTELIGENTE ---
    public function obtenerHorariosDisponibles($serv_id, $usu_id, $fecha) {
        // 1. ZONA HORARIA
        date_default_timezone_set('America/Guayaquil');

        // Obtener datos del servicio
        $sqlServ = "SELECT serv_duracion, serv_espera FROM tbl_servicio WHERE serv_id = :id";
        $stmtS = $this->pdo->prepare($sqlServ);
        $stmtS->execute([':id' => $serv_id]);
        $servicio = $stmtS->fetch(PDO::FETCH_ASSOC);
        
        if(!$servicio) return []; 
        
        $duracionTotal = intval($servicio['serv_duracion']) + intval($servicio['serv_espera']);

        // 2. OBTENER EL NÚMERO DEL DÍA (1=Lunes ... 7=Domingo)
        // Esto coincide perfectamente con tu base de datos ahora.
        $numDia = date('N', strtotime($fecha)); 

        // 3. CONSULTA DIRECTA POR NÚMERO
        // Buscamos: es el empleado X y el día N.
        $sqlHor = "SELECT hor_ini, hor_fin FROM tbl_empleado_horario 
                WHERE usu_id = :uid AND hor_dia = :dia";
        
        $stmtH = $this->pdo->prepare($sqlHor);
        $stmtH->execute([':uid' => $usu_id, ':dia' => $numDia]);
        $horario = $stmtH->fetch(PDO::FETCH_ASSOC);

        // 4. LÓGICA DE DESCANSO
        // Si NO encuentra fila (false) O las horas están vacías -> DESCANSO
        if(!$horario || empty($horario['hor_ini']) || $horario['hor_ini'] == '00:00:00') {
            return "DESCANSO"; 
        }

        // 5. CALCULAR HUECOS (Igual que antes)
        $sqlCitas = "SELECT det_ini, det_fin FROM tbl_cita_det 
                    WHERE usu_id = :uid 
                    AND DATE(det_ini) = :fecha 
                    AND det_estado != 'CANCELADO'";
        $stmtC = $this->pdo->prepare($sqlCitas);
        $stmtC->execute([':uid' => $usu_id, ':fecha' => $fecha]);
        $citasOcupadas = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        $horaInicio = strtotime($fecha . ' ' . $horario['hor_ini']);
        $horaFin    = strtotime($fecha . ' ' . $horario['hor_fin']);
        $bloqueSegundos = $duracionTotal * 60;
        $intervaloBusqueda = 15 * 60; 

        $horariosDisponibles = [];
        // 1. Obtener la fecha y hora actual real según la zona horaria seteada
        $fechaActual = date('Y-m-d');
        $ahoraTimestamp = time(); 

        // 2. Si la fecha solicitada es HOY, verificar si el negocio ya cerró
        $horaCierreReal = strtotime($fecha . ' ' . $horario['hor_fin']);
        if ($fecha == $fechaActual && $ahoraTimestamp >= $horaCierreReal) {
            return []; // Ya cerraron por hoy, no devolver ninguna hora
        }

        for ($tiempo = $horaInicio; $tiempo <= ($horaFin - $bloqueSegundos); $tiempo += $intervaloBusqueda) {
            
            // 3. Filtro estricto: Si es hoy, saltar cualquier hora menor a "ahora + 15 min"
            if ($fecha == $fechaActual && $tiempo < ($ahoraTimestamp + 900)) { 
                continue; 
            }

            $inicioPosible = $tiempo;
            $finPosible    = $tiempo + $bloqueSegundos;
            $estaLibre     = true;

            foreach ($citasOcupadas as $cita) {
                $citaIni = strtotime($cita['det_ini']);
                $citaFin = strtotime($cita['det_fin']);

                if ($inicioPosible < $citaFin && $finPosible > $citaIni) {
                    $estaLibre = false;
                    break; 
                }
            }

            if ($estaLibre) {
                $horariosDisponibles[] = date('H:i', $inicioPosible);
            }
        }

        return $horariosDisponibles;
    }


    // ====================================================================
    // SERVICIOS DASHBOARD (ALEATORIO CON SEMILLA)
    // ====================================================================
    public function obtenerServiciosDashboard($offset = 0, $limit = 16, $semilla = '') {
        if(empty($semilla)) $semilla = 'default';

        $sql = "SELECT s.serv_id, s.serv_nombre, s.serv_precio, s.serv_duracion, 
                    s.serv_descripcion, n.neg_nombre, n.neg_logo, n.neg_id,
                    
                    -- CÁLCULO DEL PROMEDIO
                    (
                        SELECT AVG(cal.cal_valor_servicio)
                        FROM tbl_calificacion cal
                        INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id
                        WHERE cd.serv_id = s.serv_id 
                        AND cal.cal_origen = 'CITA'
                    ) as rating_promedio,

                    -- CONTEO DE VOTOS
                    (
                        SELECT COUNT(*)
                        FROM tbl_calificacion cal
                        INNER JOIN tbl_cita_det cd ON cal.cal_ref_id = cd.cita_id
                        WHERE cd.serv_id = s.serv_id 
                        AND cal.cal_origen = 'CITA'
                    ) as votos_total,

                    -- PUNTOS DE FIDELIDAD (NUEVO)
                    CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados

                FROM tbl_servicio s 
                JOIN tbl_negocio n ON s.neg_id = n.neg_id 
                -- Join para verificar si la fidelidad está activa en el negocio
                LEFT JOIN tbl_fidelidad_config fc ON n.neg_id = fc.neg_id AND fc.fid_activa = 1
                -- Join para traer los puntos específicos del servicio
                LEFT JOIN tbl_fidelidad_item fi ON s.serv_id = fi.serv_id AND fi.fiditem_estado = 'A'

                WHERE s.serv_estado = 'A' AND n.neg_estado = 'A'
                ORDER BY MD5(CONCAT(s.serv_id, :seed)) 
                LIMIT $limit OFFSET $offset"; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':seed', $semilla); 
        $stmt->execute();
        
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($servicios as &$ser) {
            $ser['imagenes'] = $this->obtenerImagenes($ser['serv_id'], 'SERVICIO');
        }
        return $servicios;
    }


    // --- GUARDAR RESERVA FINAL ---
    public function registrarCitaCompleta($datos) {
        try {
            $this->pdo->beginTransaction();

            // 1. DETERMINAR ESTADO INICIAL
            // Si el precio es 0 y tiene puntos de canje, confirmamos de una vez
            $puntosCanje = intval($datos['puntos'] ?? 0);
            $precioReal = floatval($datos['precio'] ?? 0);
            $estadoInicial = ($precioReal <= 0 && $puntosCanje > 0) ? 'CONFIRMADO' : 'RESERVADO';

            // 2. Insertar en tbl_cita (Cabecera)
            $sqlCita = "INSERT INTO tbl_cita (
                            neg_id, suc_id, cli_id, cita_fecha, 
                            cita_estado, cita_origen, cita_notas, cita_qr_token
                        ) VALUES (
                            :neg_id, :suc_id, :cli_id, :fecha, 
                            :estado, 'WEB', :notas, :qr_token
                        )";

            $stmt = $this->pdo->prepare($sqlCita);
            $stmt->execute([
                ':neg_id'   => $datos['neg_id'],
                ':suc_id'   => $datos['suc_id'],
                ':cli_id'   => $datos['cli_id'],
                ':fecha'    => $datos['fecha_completa'],
                ':estado'   => $estadoInicial, // Usamos el estado calculado
                ':notas'    => $datos['notas'],
                ':qr_token' => $datos['qr_token']
            ]);
            
            $cita_id = $this->pdo->lastInsertId();

            // 3. Calcular Hora Fin
            $sqlServ = "SELECT serv_duracion, serv_espera FROM tbl_servicio WHERE serv_id = :sid";
            $stmtS = $this->pdo->prepare($sqlServ);
            $stmtS->execute([':sid' => $datos['serv_id']]);
            $serv = $stmtS->fetch(PDO::FETCH_ASSOC);
            
            $minutosTotales = intval($serv['serv_duracion']) + intval($serv['serv_espera']);
            $hora_fin = date('Y-m-d H:i:s', strtotime($datos['fecha_completa'] . " + $minutosTotales minutes"));

            // 4. Insertar en tbl_cita_det (Incluyendo puntos de canje)
            $sqlDet = "INSERT INTO tbl_cita_det (
                            cita_id, serv_id, usu_id, det_ini, det_fin, 
                            det_duracion, det_precio, det_puntos_canje, det_estado
                        ) VALUES (
                            :cid, :sid, :uid, :ini, :fin, 
                            :dur, :pre, :pts, :estado
                        )";
            $stmtD = $this->pdo->prepare($sqlDet);
            $stmtD->execute([
                ':cid'    => $cita_id,
                ':sid'    => $datos['serv_id'],
                ':uid'    => $datos['especialista_id'],
                ':ini'    => $datos['fecha_completa'],
                ':fin'    => $hora_fin,
                ':dur'    => $minutosTotales,
                ':pre'    => $precioReal,
                ':pts'    => $puntosCanje,
                ':estado' => $estadoInicial
            ]);

            // --- 5. LÓGICA DE DESCUENTO AUTOMÁTICO DE PUNTOS (SOLO SI PASÓ A CONFIRMADO) ---
            if ($estadoInicial === 'CONFIRMADO') {
                // A. Buscar ID de fidelidad del cliente
                $sqlFid = "SELECT fidcli_id FROM tbl_fidelidad_cliente WHERE neg_id = :nid AND cli_id = :cid";
                $stFid = $this->pdo->prepare($sqlFid);
                $stFid->execute([':nid' => $datos['neg_id'], ':cid' => $datos['cli_id']]);
                $fidCliId = $stFid->fetchColumn();

                if ($fidCliId) {
                    // B. Registrar Movimiento de GASTO
                    $sqlMov = "INSERT INTO tbl_fidelidad_mov 
                               (fidcli_id, origen, ref_id, puntos, fidmov_tipo, descripcion, fidmov_fecha) 
                               VALUES (?, 'CITA', ?, ?, 'GASTO', 'Canje directo por puntos (Web)', NOW())";
                    $this->pdo->prepare($sqlMov)->execute([$fidCliId, $cita_id, $puntosCanje]);

                    // C. Restar del Saldo Total
                    $sqlSaldo = "UPDATE tbl_fidelidad_cliente SET fidcli_total = fidcli_total - ? WHERE fidcli_id = ?";
                    $this->pdo->prepare($sqlSaldo)->execute([$puntosCanje, $fidCliId]);
                }
            }

            // 6. Registro en Historial de Promoción (Si aplica)
            if (!empty($datos['prom_id'])) {
                $sqlHist = "INSERT INTO tbl_promocion_historial (prom_id, cli_id, neg_id, hist_ref_tipo, hist_ref_id) 
                            VALUES (:pid, :cid, :nid, 'CITA', :rid)";
                $this->pdo->prepare($sqlHist)->execute([
                    ':pid' => $datos['prom_id'], ':cid' => $datos['cli_id'], ':nid' => $datos['neg_id'], ':rid' => $cita_id
                ]);

                $this->pdo->prepare("UPDATE tbl_promocion SET prom_usos_actuales = prom_usos_actuales + 1 WHERE prom_id = :pid")
                          ->execute([':pid' => $datos['prom_id']]);
            }

            $this->pdo->commit();
            return ['success' => true, 'cita_id' => $cita_id, 'estado' => $estadoInicial];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    // ====================================================================
    // PRODUCTOS DASHBOARD (SOLO LOS QUE TIENEN STOCK EN ALGUNA SUCURSAL)
    // ====================================================================
    public function obtenerProductosDashboard($offset = 0, $limit = 8, $semilla = '') {
        if(empty($semilla)) $semilla = 'default_seed';

        $sql = "SELECT p.pro_id, p.pro_nombre, p.pro_precio, p.pro_descripcion, 
                        p.pro_unidad, p.pro_contenido, p.pro_unidad_consumo,
                        n.neg_nombre, n.neg_logo, n.neg_id,
                        
                        -- LÓGICA DE PUNTOS DE FIDELIDAD
                        CASE WHEN fc.fid_id IS NOT NULL THEN fi.fiditem_puntos ELSE 0 END as puntos_ganados,

                        -- STOCK REAL (Suma de sucursales)
                        (
                            SELECT IFNULL(SUM(ps.ps_stock), 0) 
                            FROM tbl_producto_sucursal ps
                            INNER JOIN tbl_sucursal s ON ps.suc_id = s.suc_id
                            WHERE ps.pro_id = p.pro_id 
                            AND ps.ps_estado = 'A' AND s.suc_estado = 'A'
                        ) as stock_real,

                        -- PROMEDIO DE CALIFICACIÓN
                        (
                            SELECT AVG(cal.cal_valor_producto)
                            FROM tbl_calificacion cal
                            WHERE cal.cal_ref_id = p.pro_id 
                            AND cal.cal_origen = 'PRODUCTO'
                        ) as rating_promedio,

                        -- CONTEO DE VOTOS
                        (
                            SELECT COUNT(*)
                            FROM tbl_calificacion cal
                            WHERE cal.cal_ref_id = p.pro_id 
                            AND cal.cal_origen = 'PRODUCTO'
                            AND cal.cal_valor_producto > 0
                        ) as votos_total

                FROM tbl_producto p 
                JOIN tbl_negocio n ON p.neg_id = n.neg_id 
                -- Fidelidad: Verificar si está activa y traer puntos del producto
                LEFT JOIN tbl_fidelidad_config fc ON n.neg_id = fc.neg_id AND fc.fid_activa = 1
                LEFT JOIN tbl_fidelidad_item fi ON p.pro_id = fi.pro_id AND fi.fiditem_estado = 'A'

                WHERE p.pro_estado = 'A' AND p.pro_venta = 1 AND n.neg_estado = 'A'
                HAVING stock_real > 0 
                ORDER BY MD5(CONCAT(p.pro_id, :seed)) 
                LIMIT $limit OFFSET $offset"; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':seed', $semilla); 
        $stmt->execute();
        
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($productos as &$prod) {
            $prod['imagenes'] = $this->obtenerImagenes($prod['pro_id'], 'PRODUCTO');
        }
        return $productos;
    }

    // ====================================================================
    // MODAL DE COMPRA: OBTENER INFO CON STOCK REAL SUMADO
    // ====================================================================
    public function obtenerInfoProductoModal($pro_id, $idCliente = null) {
        // Obtenemos info básica + stock real sumado de sucursales
        $sql = "SELECT p.*, n.neg_id, n.neg_nombre, n.neg_logo, n.neg_envio_costo,
                       (SELECT IFNULL(SUM(ps.ps_stock), 0) 
                        FROM tbl_producto_sucursal ps 
                        INNER JOIN tbl_sucursal s ON ps.suc_id = s.suc_id 
                        WHERE ps.pro_id = p.pro_id AND ps.ps_estado = 'A' AND s.suc_estado = 'A') as pro_stock
                FROM tbl_producto p 
                JOIN tbl_negocio n ON p.neg_id = n.neg_id 
                WHERE p.pro_id = :id AND p.pro_estado = 'A'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $pro_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            // --- NUEVO: Traer saldo de puntos del cliente ---
            $producto['puntos_cliente'] = 0;
            if ($idCliente) {
                $sqlP = "SELECT fidcli_total FROM tbl_fidelidad_cliente WHERE neg_id = :nid AND cli_id = :cid";
                $stP = $this->pdo->prepare($sqlP);
                $stP->execute([':nid' => $producto['neg_id'], ':cid' => $idCliente]);
                $producto['puntos_cliente'] = $stP->fetchColumn() ?: 0;
            }

            $fotos = $this->obtenerImagenes($pro_id, 'PRODUCTO');
            $producto['imagenes'] = !empty($fotos) ? $fotos : ['recursos/img/sin_foto.png'];
        }

        return $producto;
    }

    // ====================================================================
    // CARRITO: AGREGAR O ACTUALIZAR
    // ====================================================================
    public function agregarAlCarrito($cli_id, $pro_id, $cantidad, $prom_id = 0) {
        try {
            // La clave única que creaste en la DB (cli_id, pro_id, prom_id) se encarga de separar las filas
            $sql = "INSERT INTO tbl_carrito (cli_id, pro_id, prom_id, car_cantidad) 
                    VALUES (:cli, :pro, :prom, :cant) 
                    ON DUPLICATE KEY UPDATE car_cantidad = car_cantidad + :cant_update";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':cli' => $cli_id,
                ':pro' => $pro_id,
                ':prom' => $prom_id, // <--- NUEVO
                ':cant' => $cantidad,
                ':cant_update' => $cantidad 
            ]);

            return ['success' => true, 'message' => 'Producto agregado al carrito'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error DB: ' . $e->getMessage()];
        }
    }

    // ====================================================================
    // CARRITO: OBTENER LISTADO DE PRODUCTOS (CORREGIDO)
    // ====================================================================
    public function obtenerCarrito($cli_id) {
        $sql = "SELECT c.*, p.pro_nombre, p.pro_precio, n.neg_nombre, n.neg_id,
                       pr.prom_modalidad, pr.prom_precio_oferta, f.puntos_necesarios,
                       -- NUEVO: Traemos el saldo actual que tiene el cliente en ESTE negocio
                       IFNULL(fc.fidcli_total, 0) as saldo_puntos_actual
                FROM tbl_carrito c
                JOIN tbl_producto p ON c.pro_id = p.pro_id
                JOIN tbl_negocio n ON p.neg_id = n.neg_id
                LEFT JOIN tbl_promocion pr ON c.prom_id = pr.prom_id
                LEFT JOIN tbl_fidelidad_canje f ON pr.prom_id = f.prom_id
                -- Join para saber el saldo de puntos del cliente en este negocio específico
                LEFT JOIN tbl_fidelidad_cliente fc ON n.neg_id = fc.neg_id AND fc.cli_id = :uid
                WHERE c.cli_id = :uid2 -- Usamos uid2 para evitar conflictos de parámetros
                ORDER BY c.car_fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $cli_id, ':uid2' => $cli_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['imagen'] = $this->obtenerImagenPrincipal($item['pro_id'], 'PRODUCTO');
            $precioUnitario = (intval($item['prom_id']) > 0) ? $item['prom_precio_oferta'] : $item['pro_precio'];
            $item['subtotal'] = floatval($precioUnitario) * intval($item['car_cantidad']);
        }
        return $items;
    }

    // ====================================================================
    // CARRITO: ACTUALIZAR CANTIDAD (+1 o -1)
    // ====================================================================
    public function actualizarCantidadCarrito($car_id, $cli_id, $cambio) {
        // Usamos :cambio_check para la validación para evitar duplicidad de nombres
        $sql = "UPDATE tbl_carrito SET car_cantidad = car_cantidad + :cambio 
                WHERE car_id = :id AND cli_id = :cli AND (car_cantidad + :cambio_check) >= 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':cambio'       => $cambio, 
            ':id'           => $car_id, 
            ':cli'          => $cli_id,
            ':cambio_check' => $cambio // Enviamos el mismo valor con el otro nombre
        ]);
        
        return $stmt->rowCount() > 0;
    }
    // ====================================================================
    // CARRITO: ELIMINAR ITEM
    // ====================================================================
    public function eliminarItemCarrito($car_id, $cli_id) {
        $sql = "DELETE FROM tbl_carrito WHERE car_id = :id AND cli_id = :cli";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $car_id, ':cli' => $cli_id]);
        return $stmt->rowCount() > 0;
    }



    // ====================================================================
    // CHECKOUT: REGISTRO CON ASIGNACIÓN DE STOCK GEOGRÁFICA
    // ====================================================================
    public function registrarOrden($datos, $itemsCarrito) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar Cabecera
            $sqlOrd = "INSERT INTO tbl_orden (
                            cli_id, ord_codigo, ord_token_qr, 
                            ord_subtotal, ord_costo_envio, ord_total,
                            ord_tipo_entrega, ord_direccion_envio, ord_referencia, 
                            ord_ubicacion_lat, ord_ubicacion_lon, ord_estado
                        ) VALUES (
                            :cli, :cod, :token, 
                            :sub, :envio, :tot,
                            :tipo, :dir, :ref,
                            :lat, :lon, 'PENDIENTE'
                        )";
            
            $stmt = $this->pdo->prepare($sqlOrd);
            $stmt->execute([
                ':cli'   => $datos['cli_id'],
                ':cod'   => $datos['codigo'],
                ':token' => $datos['token'],
                ':sub'   => $datos['subtotal'],
                ':envio' => $datos['envio'],
                ':tot'   => $datos['total'],
                ':tipo'  => $datos['tipo_entrega'],
                ':dir'   => $datos['direccion'] ?? null,
                ':ref'   => $datos['referencia'] ?? null,
                ':lat'   => $datos['lat'],
                ':lon'   => $datos['lon']
            ]);
            
            $ord_id = $this->pdo->lastInsertId();

            // 2. Procesar Items y heredar datos de Promoción
            foreach ($itemsCarrito as $item) {
                $sucursalAsignada = $this->obtenerSucursalOptima($item['pro_id'], $item['car_cantidad'], $datos['lat'], $datos['lon']);
                if (!$sucursalAsignada) throw new Exception("Sin stock para: " . $item['pro_nombre']);

                $ptsCanje = (intval($item['prom_id']) > 0) ? (intval($item['puntos_necesarios']) * intval($item['car_cantidad'])) : 0;

                $sqlDet = "INSERT INTO tbl_orden_detalle 
                        (ord_id, pro_id, prom_id, neg_id, suc_id, odet_cantidad, odet_precio_unitario, odet_subtotal, odet_puntos_canje) 
                        VALUES (:oid, :pid, :prom, :nid, :sid, :cant, :pre, :sub, :pts)";
                
                $this->pdo->prepare($sqlDet)->execute([
                    ':oid'  => $ord_id,
                    ':pid'  => $item['pro_id'],
                    ':prom' => intval($item['prom_id']),
                    ':nid'  => $item['neg_id'],
                    ':sid'  => $sucursalAsignada,
                    ':cant' => $item['car_cantidad'],
                    ':pre'  => (intval($item['prom_id']) > 0) ? $item['prom_precio_oferta'] : $item['pro_precio'],
                    ':sub'  => $item['subtotal'],
                    ':pts'  => $ptsCanje
                ]);

                // --- BLOQUE NUEVO: REGISTRAR USO DE CUPOS ---
                if (intval($item['prom_id']) > 0) {
                    // 1. Insertar en el historial para que la barra de progreso se mueva
                    $sqlHist = "INSERT INTO tbl_promocion_historial (prom_id, cli_id, neg_id, hist_ref_tipo, hist_ref_id) 
                                VALUES (:pid, :cid, :nid, 'ORDEN', :oid)";
                    $this->pdo->prepare($sqlHist)->execute([
                        ':pid' => $item['prom_id'],
                        ':cid' => $datos['cli_id'],
                        ':nid' => $item['neg_id'],
                        ':oid' => $ord_id
                    ]);

                    // 2. Aumentar el contador de usos en la tabla de promociones
                    // NOTA: Sumamos la cantidad comprada (ej: si compró 2, se usan 2 cupos)
                    $this->pdo->prepare("UPDATE tbl_promocion SET prom_usos_actuales = prom_usos_actuales + :cant WHERE prom_id = :pid")
                            ->execute([':cant' => $item['car_cantidad'], ':pid' => $item['prom_id']]);
                }

                // Descontar Stock físico
                $this->pdo->prepare("UPDATE tbl_producto_sucursal SET ps_stock = ps_stock - ? WHERE pro_id = ? AND suc_id = ?")
                        ->execute([$item['car_cantidad'], $item['pro_id'], $sucursalAsignada]);
            }

            $this->vaciarCarrito($datos['cli_id']);
            $this->pdo->commit();
            
            return ['success' => true, 'ord_id' => $ord_id, 'codigo' => $datos['codigo']];

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ====================================================================
    // HELPER: ALGORITMO DE CERCANÍA (Busca dónde hay stock y elige el más cerca)
    // ====================================================================
    private function obtenerSucursalOptima($prodId, $cantidad, $latUser, $lonUser) {
        // 1. Buscar todas las sucursales que tienen stock suficiente de este producto
        $sql = "SELECT s.suc_id, s.suc_latitud, s.suc_longitud 
                FROM tbl_producto_sucursal ps
                INNER JOIN tbl_sucursal s ON ps.suc_id = s.suc_id
                WHERE ps.pro_id = :pid 
                  AND ps.ps_stock >= :cant 
                  AND ps.ps_estado = 'A' 
                  AND s.suc_estado = 'A'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $prodId, ':cant' => $cantidad]);
        $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sucursales)) {
            return null; // No hay stock en ningún lado
        }

        // Si el usuario no dio ubicación (o es 0,0), devolvemos la primera que encontremos (Fallback)
        if (empty($latUser) || empty($lonUser)) {
            return $sucursales[0]['suc_id'];
        }

        // 2. Calcular distancias y encontrar la menor
        $mejorSucursalId = null;
        $menorDistancia = 999999999; // Infinito

        foreach ($sucursales as $suc) {
            // Si la sucursal no tiene coords, la ignoramos o la dejamos al final
            if (empty($suc['suc_latitud']) || empty($suc['suc_longitud'])) continue;

            // Fórmula simple de distancia euclidiana (suficiente para distancias cortas en ciudad)
            // Distancia = Raíz((x2-x1)² + (y2-y1)²)
            $dist = sqrt(pow($suc['suc_latitud'] - $latUser, 2) + pow($suc['suc_longitud'] - $lonUser, 2));

            if ($dist < $menorDistancia) {
                $menorDistancia = $dist;
                $mejorSucursalId = $suc['suc_id'];
            }
        }

        // Si falló el cálculo (ej. sucursales sin coords), devolver la primera por defecto
        return $mejorSucursalId ?: $sucursales[0]['suc_id'];
    }

    // ====================================================================
    // VACIAR CARRITO (Se llama al finalizar la compra)
    // ====================================================================
    public function vaciarCarrito($cli_id) {
        $sql = "DELETE FROM tbl_carrito WHERE cli_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $cli_id]);
        return true;
    }


    // ====================================================================
    // HISTORIAL: Obtener pedidos del cliente
    // ====================================================================
    public function obtenerHistorialPedidos($cli_id) {
        $sql = "SELECT o.ord_id, o.ord_codigo, o.ord_fecha, o.ord_costo_envio,
                       o.ord_estado, o.ord_tipo_entrega,
                       (SELECT COUNT(*) FROM tbl_orden_detalle WHERE ord_id = o.ord_id) as items,
                       
                       -- CALCULAMOS EL DINERO REAL: Suma de subtotales de productos + costo de envío
                       (SELECT IFNULL(SUM(odet_subtotal), 0) FROM tbl_orden_detalle WHERE ord_id = o.ord_id) + o.ord_costo_envio as total_dinero_real,
                       
                       -- CALCULAMOS LOS PUNTOS REALES: Suma de puntos de canje de los productos
                       (SELECT IFNULL(SUM(odet_puntos_canje), 0) FROM tbl_orden_detalle WHERE ord_id = o.ord_id) as total_puntos_orden,
                       
                       (SELECT COUNT(*) FROM tbl_calificacion 
                        WHERE cal_origen = 'ORDEN' AND cal_ref_id = o.ord_id) as tiene_calificacion

                FROM tbl_orden o
                WHERE o.cli_id = :cli
                ORDER BY o.ord_fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cli' => $cli_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GUARDAR CALIFICACIÓN MULTI-NEGOCIO
    public function guardarResenaCompleta($datos, $calificacionesNegocios, $productos) {
        try {
            $this->pdo->beginTransaction();

            // 1. CALIFICAR A LOS NEGOCIOS (Bucle)
            $sqlOrd = "INSERT INTO tbl_calificacion 
                       (neg_id, suc_id, cli_id, cal_origen, cal_ref_id, cal_valor_negocio, cal_comentario, cal_fecha) 
                       VALUES (:nid, 0, :cli, 'ORDEN', :oid, :val, :com, NOW())";
            $stmt = $this->pdo->prepare($sqlOrd);

            foreach($calificacionesNegocios as $calNeg) {
                $stmt->execute([
                    ':nid' => $calNeg['neg_id'],
                    ':cli' => $datos['cli_id'],
                    ':oid' => $datos['ord_id'],
                    ':val' => $calNeg['rating'],
                    ':com' => $datos['comentario'] // El comentario va para todos por ahora, o podrías separarlo
                ]);
            }

            // 2. CALIFICAR PRODUCTOS
            $sqlProd = "INSERT INTO tbl_calificacion 
                        (neg_id, suc_id, cli_id, cal_origen, cal_ref_id, cal_valor_servicio, cal_fecha) 
                        VALUES (:nid, 0, :cli, 'PRODUCTO', :pid, :val, NOW())";
            $stmtP = $this->pdo->prepare($sqlProd);

            foreach($productos as $prod) {
                if(intval($prod['rating']) > 0) {
                    $stmtP->execute([
                        ':nid' => $prod['neg_id'], // Usamos el ID de negocio que viene con el producto
                        ':cli' => $datos['cli_id'],
                        ':pid' => $prod['pro_id'],
                        ':val' => $prod['rating']
                    ]);
                }
            }

            $this->pdo->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Obtener UNA orden específica (con seguridad de cliente)
    public function obtenerOrdenPorId($ord_id, $cli_id) {
        $sql = "SELECT * FROM tbl_orden WHERE ord_id = :oid AND cli_id = :cli";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id, ':cli' => $cli_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ====================================================================
    // DETALLE ORDEN (CORREGIDO: SIN TABLA FANTASMA)
    // ====================================================================
    public function obtenerDetallesOrden($ord_id) {
        // AGREGADO: n.neg_logo
        $sql = "SELECT od.*, 
                       p.pro_nombre, p.pro_descripcion, 
                       n.neg_nombre, n.neg_logo, n.neg_id,  
                       s.suc_nombre, s.suc_direccion, s.suc_latitud, s.suc_longitud
                FROM tbl_orden_detalle od
                INNER JOIN tbl_producto p ON od.pro_id = p.pro_id
                INNER JOIN tbl_negocio n ON od.neg_id = n.neg_id
                INNER JOIN tbl_sucursal s ON od.suc_id = s.suc_id
                WHERE od.ord_id = :oid";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // (Esto se queda igual, trae la foto del producto)
        foreach ($items as &$item) {
            $fotos = $this->obtenerImagenes($item['pro_id'], 'PRODUCTO');
            $item['imagen'] = !empty($fotos) ? $fotos[0] : 'recursos/img/sin_foto.png';
        }

        return $items;
    }


    // --- SUBPROCESO CRÍTICO: LIMPIEZA DE CITAS VENCIDAS (CON TOLERANCIA) ---
    public function actualizarCitasPerdidas($cli_id) {
        // HORA DE CORTE: Hora actual MENOS 5 minutos.
        // Ejemplo: Si son las 14:05, el corte es 14:00.
        // Solo las citas de las 14:00 o antes se verán afectadas.
        $corte = date('Y-m-d H:i:s', strtotime('-5 minutes'));

        try {
            $this->pdo->beginTransaction();

            // CASO A: NO PAGÓ (RESERVADO -> PERDIDA)
            // Si estaba reservado y pasaron 5 min de la hora -> Se pierde el turno.
            $sqlA = "UPDATE tbl_cita_det d
                     INNER JOIN tbl_cita c ON d.cita_id = c.cita_id
                     SET d.det_estado = 'PERDIDA'
                     WHERE c.cli_id = :uid 
                     AND d.det_ini < :corte 
                     AND d.det_estado = 'RESERVADO'";
            
            $stmtA = $this->pdo->prepare($sqlA);
            $stmtA->execute([':uid' => $cli_id, ':corte' => $corte]);

            // CASO B: SÍ PAGÓ PERO NO LLEGÓ (CONFIRMADO -> NO_ASISTIO)
            // Si estaba confirmado y pasaron 5 min -> Se marca como inasistencia (para reclamos).
            $sqlB = "UPDATE tbl_cita_det d
                     INNER JOIN tbl_cita c ON d.cita_id = c.cita_id
                     SET d.det_estado = 'NO_ASISTIO'
                     WHERE c.cli_id = :uid 
                     AND d.det_ini < :corte 
                     AND d.det_estado = 'CONFIRMADO'";
            
            $stmtB = $this->pdo->prepare($sqlB);
            $stmtB->execute([':uid' => $cli_id, ':corte' => $corte]);

            // ACTUALIZAR CABECERAS (OPCIONAL PERO RECOMENDADO PARA CONSISTENCIA)
            // Si la cabecera está vencida y reservada -> PERDIDA
            $sqlCabA = "UPDATE tbl_cita SET cita_estado = 'PERDIDA' 
                        WHERE cli_id = :uid AND cita_fecha < :corte AND cita_estado = 'RESERVADO'";
            $this->pdo->prepare($sqlCabA)->execute([':uid' => $cli_id, ':corte' => $corte]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }
    }

    // --- OBTENER TODAS (MODIFICADO: AHORA DETECTA SI YA CALIFICASTE) ---
    // --- OBTENER TODAS (CORREGIDO: Parámetros únicos) ---
    public function obtenerMisCitas($cli_id) {
        $sql = "SELECT 
                    c.cita_id, c.cita_fecha, c.cita_estado, c.cita_qr_token, c.cita_notas,
                    n.neg_nombre, n.neg_logo, n.neg_id,
                    suc.suc_id, suc.suc_nombre, suc.suc_direccion, suc.suc_latitud, suc.suc_longitud,
                    s.serv_nombre, s.serv_duracion, s.serv_descripcion,
                    u.usu_nombres as esp_nombre, u.usu_apellidos as esp_apellido, u.usu_foto as esp_foto,
                    d.det_precio, d.det_duracion, d.det_ini, d.det_fin, d.det_estado,
                    -- Verificar si ya existe calificación (Usamos :uid1)
                    (SELECT COUNT(*) FROM tbl_calificacion cal 
                     WHERE cal.cal_ref_id = c.cita_id 
                     AND cal.cal_origen = 'CITA' 
                     AND cal.cli_id = :uid1) as ya_calificado
                FROM tbl_cita c
                INNER JOIN tbl_negocio n ON c.neg_id = n.neg_id
                INNER JOIN tbl_sucursal suc ON c.suc_id = suc.suc_id
                INNER JOIN tbl_cita_det d ON c.cita_id = d.cita_id
                INNER JOIN tbl_servicio s ON d.serv_id = s.serv_id
                INNER JOIN tbl_usuario u ON d.usu_id = u.usu_id
                -- Filtro principal (Usamos :uid2)
                WHERE c.cli_id = :uid2";

        $stmt = $this->pdo->prepare($sql);
        // Pasamos el mismo ID a ambos marcadores
        $stmt->execute([':uid1' => $cli_id, ':uid2' => $cli_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- GUARDAR LA TRIPLE CALIFICACIÓN ---
    // --- GUARDAR CALIFICACIÓN Y ACTUALIZAR PROMEDIO DEL ESPECIALISTA ---
    public function guardarCalificacion($datos) {
        try {
            // Iniciamos transacción para que todo se guarde o nada se guarde
            $this->pdo->beginTransaction();

            // 1. INSERTAR EL DETALLE (La reseña en sí)
            $sql = "INSERT INTO tbl_calificacion 
                    (neg_id, suc_id, cli_id, cal_origen, cal_ref_id, cal_valor_servicio, cal_valor_especialista, cal_valor_negocio, cal_comentario, cal_fecha)
                    VALUES 
                    (:neg, :suc, :cli, 'CITA', :ref, :val_serv, :val_esp, :val_neg, :com, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':neg' => $datos['neg_id'],
                ':suc' => $datos['suc_id'],
                ':cli' => $datos['cli_id'],
                ':ref' => $datos['cita_id'],
                ':val_serv' => $datos['voto_servicio'],
                ':val_esp'  => $datos['voto_especialista'],
                ':val_neg'  => $datos['voto_negocio'],
                ':com'      => $datos['comentario']
            ]);

            // 2. ACTUALIZAR PROMEDIO DEL ESPECIALISTA
            // Primero: Buscamos quién es el especialista de esa cita y sus stats actuales
            $sqlEsp = "SELECT u.usu_id, u.usu_calificacion, u.usu_votos 
                       FROM tbl_cita_det d 
                       INNER JOIN tbl_usuario u ON d.usu_id = u.usu_id 
                       WHERE d.cita_id = :cid LIMIT 1";
            
            $stmtEsp = $this->pdo->prepare($sqlEsp);
            $stmtEsp->execute([':cid' => $datos['cita_id']]);
            $esp = $stmtEsp->fetch(PDO::FETCH_ASSOC);

            if ($esp) {
                // Datos actuales
                $votoNuevo      = floatval($datos['voto_especialista']); // Ej: 5
                $promedioActual = floatval($esp['usu_calificacion']);    // Ej: 4.5
                $votosTotales   = intval($esp['usu_votos']);             // Ej: 10

                // FÓRMULA MATEMÁTICA DE PROMEDIO PONDERADO:
                // ((PromedioViejo * CantidadVieja) + NuevoVoto) / (CantidadVieja + 1)
                $nuevoTotalVotos = $votosTotales + 1;
                $nuevoPromedio   = (($promedioActual * $votosTotales) + $votoNuevo) / $nuevoTotalVotos;

                // Actualizamos la tabla de usuario con los nuevos números
                $sqlUpd = "UPDATE tbl_usuario 
                           SET usu_calificacion = :cal, 
                               usu_votos = :vot 
                           WHERE usu_id = :uid";
                
                $this->pdo->prepare($sqlUpd)->execute([
                    ':cal' => number_format($nuevoPromedio, 2, '.', ''), // Guardamos con 2 decimales (Ej: 4.85)
                    ':vot' => $nuevoTotalVotos,
                    ':uid' => $esp['usu_id']
                ]);
            }

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // ====================================================================
    // CANCELAR PEDIDO Y DEVOLVER STOCK (RE-STOCKING)
    // ====================================================================
    public function cancelarOrdenUsuario($ord_id, $cli_id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Verificar estado actual y pertenencia
            $sqlCheck = "SELECT ord_estado FROM tbl_orden WHERE ord_id = :oid AND cli_id = :uid FOR UPDATE";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':oid' => $ord_id, ':uid' => $cli_id]);
            $estado = $stmtCheck->fetchColumn();

            if (!$estado) {
                throw new Exception("Orden no encontrada.");
            }
            if ($estado !== 'PENDIENTE') {
                throw new Exception("No se puede cancelar una orden en estado: " . $estado);
            }

            // 2. Obtener los productos para devolverlos (Saber qué y a qué sucursal)
            $sqlDet = "SELECT pro_id, suc_id, odet_cantidad FROM tbl_orden_detalle WHERE ord_id = :oid";
            $stmtDet = $this->pdo->prepare($sqlDet);
            $stmtDet->execute([':oid' => $ord_id]);
            $items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            // 3. Devolver Stock (Loop de Re-stocking)
            $sqlRestock = "UPDATE tbl_producto_sucursal 
                           SET ps_stock = ps_stock + :cant 
                           WHERE pro_id = :pid AND suc_id = :sid";
            $stmtRestock = $this->pdo->prepare($sqlRestock);

            foreach ($items as $item) {
                $stmtRestock->execute([
                    ':cant' => $item['odet_cantidad'],
                    ':pid'  => $item['pro_id'],
                    ':sid'  => $item['suc_id']
                ]);
            }

            // 4. Cambiar estado de la Orden
            $sqlUpdate = "UPDATE tbl_orden SET ord_estado = 'CANCELADO' WHERE ord_id = :oid";
            $this->pdo->prepare($sqlUpdate)->execute([':oid' => $ord_id]);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Pedido cancelado y stock restaurado.'];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // OBTENER PAGOS DE UNA ORDEN
    public function obtenerPagosOrden($ord_id) {
        $sql = "SELECT p.neg_id, p.pago_monto, mp.mp_nombre 
                FROM tbl_pago p 
                JOIN tbl_metodo_pago mp ON p.mp_id = mp.mp_id 
                WHERE p.ord_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI UNA ORDEN YA FUE CALIFICADA
    public function ordenYaCalificada($ord_id) {
        // Si existe al menos un registro de tipo ORDEN para este ID, ya se calificó
        $sql = "SELECT COUNT(*) FROM tbl_calificacion WHERE cal_origen = 'ORDEN' AND cal_ref_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id]);
        return $stmt->fetchColumn() > 0;
    }

    // GUARDAR RESEÑA MASIVA (CORREGIDO: Columna Específica para Producto)
    public function guardarResenaMasiva($datos) {
        try {
            $this->pdo->beginTransaction();

            // 1. GUARDAR CALIFICACIÓN DEL NEGOCIO (Igual que antes)
            // Origen: ORDEN | Columna: cal_valor_negocio
            $sqlNeg = "INSERT INTO tbl_calificacion 
                       (neg_id, suc_id, cli_id, cal_origen, cal_ref_id, cal_valor_negocio, cal_comentario, cal_fecha) 
                       VALUES (:nid, :sid, :cli, 'ORDEN', :oid, :val, :com, NOW())";
            $stmtNeg = $this->pdo->prepare($sqlNeg);

            foreach($datos['negocios'] as $neg) {
                $stmtNeg->execute([
                    ':nid' => $neg['id'],
                    ':sid' => $neg['suc_id'],
                    ':cli' => $datos['cli_id'],
                    ':oid' => $datos['ord_id'],
                    ':val' => $neg['rating'],
                    ':com' => $datos['comentario']
                ]);
            }

            // 2. GUARDAR CALIFICACIÓN DE PRODUCTOS (CAMBIO AQUI)
            // Origen: PRODUCTO | Columna: cal_valor_producto (NUEVA)
            $sqlProd = "INSERT INTO tbl_calificacion 
                        (neg_id, suc_id, cli_id, cal_origen, cal_ref_id, cal_valor_producto, cal_fecha) 
                        VALUES (:nid, :sid, :cli, 'PRODUCTO', :pid, :val, NOW())";
            
            $stmtProd = $this->pdo->prepare($sqlProd);

            foreach($datos['productos'] as $prod) {
                if(intval($prod['rating']) > 0) {
                    $stmtProd->execute([
                        ':nid' => $prod['neg_id'],
                        ':sid' => $prod['suc_id'],
                        ':cli' => $datos['cli_id'],
                        ':pid' => $prod['pro_id'],
                        ':val' => $prod['rating'] // <-- Esto ahora va a cal_valor_producto
                    ]);
                }
            }

            $this->pdo->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ====================================================================
    // CÁLCULO DE ENVÍO DINÁMICO (Haversine)
    // ====================================================================
    public function calcularCostoEnvioReal($itemsCarrito, $latCliente, $lonCliente) {
        $tarifaBase = 1.50;
        $precioPorKm = 0.35;
        $distanciaTotalKm = 0;

        // 1. Agrupar productos por Negocio (Para no calcular 10 veces la misma ruta)
        $negociosInvolucrados = [];
        foreach ($itemsCarrito as $item) {
            if (isset($item['neg_id'])) {
                $negociosInvolucrados[$item['neg_id']] = $item['pro_id'];
            }
        }

        // 2. Para cada negocio, buscar la sucursal MÁS CERCANA al cliente
        // (Asumimos que el sistema asignará esa sucursal luego)
        foreach ($negociosInvolucrados as $negId => $proId) {
            
            // Buscamos sucursales de este negocio con stock
            $sql = "SELECT s.suc_latitud, s.suc_longitud 
                    FROM tbl_sucursal s
                    JOIN tbl_producto_sucursal ps ON s.suc_id = ps.suc_id
                    WHERE s.neg_id = :nid AND ps.pro_id = :pid AND ps.ps_stock > 0
                    AND s.suc_latitud IS NOT NULL";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nid' => $negId, ':pid' => $proId]);
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sucursales)) continue;

            // Encontrar la más cercana
            $minDist = 99999;
            foreach ($sucursales as $suc) {
                $d = $this->calcularDistancia($latCliente, $lonCliente, $suc['suc_latitud'], $suc['suc_longitud']);
                if ($d < $minDist) $minDist = $d;
            }
            
            // Sumamos la distancia de este tramo (Tienda -> Cliente)
            // Nota: En rutas múltiples, sería Tienda A -> Tienda B -> Cliente, pero sumar directo es una buena aproximación de cobro.
            $distanciaTotalKm += ($minDist != 99999) ? $minDist : 0;
        }

        // 3. Aplicar Fórmula
        $costoFinal = $tarifaBase + ($distanciaTotalKm * $precioPorKm);
        
        return [
            'costo' => number_format($costoFinal, 2, '.', ''),
            'kms'   => number_format($distanciaTotalKm, 1)
        ];
    }

    // Fórmula Matemática (Distancia entre 2 coordenadas)
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) {
        $radioTierra = 6371; // Km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $radioTierra * $c;
    }

    // --- CONSULTAR ESTADO Y GPS DEL DRIVER (PARA EL CLIENTE) ---
    public function obtenerEstadoYPosicionPedido($ord_id) {
        // Unimos Orden con Tracking para saber coordenadas del chofer asignado
        $sql = "SELECT o.ord_estado, o.ord_ruta_optima, 
                       t.track_lat, t.track_lon,
                       u.usu_nombres, u.usu_foto
                FROM tbl_orden o
                LEFT JOIN tbl_tracking t ON o.usu_id_repartidor = t.usu_id
                LEFT JOIN tbl_usuario u ON o.usu_id_repartidor = u.usu_id
                WHERE o.ord_id = :oid";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':oid' => $ord_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // ====================================================================
    // OBTENER PROMOCIONES ACTIVAS (PARA EL CARRUSEL)
    // ====================================================================

    // ====================================================================
    // OBTENER PROMOCIONES (SUPER CONSULTA DETALLADA)
    // ====================================================================
    public function obtenerPromocionesVigentes($limit = 10) {
        $fechaHoy = date('Y-m-d');
        
        $sql = "SELECT 

                    s.serv_id, prod.pro_id,

                    -- 1. DATOS DE LA PROMOCIÓN
                    p.prom_id, p.prom_nombre, p.prom_desc, p.prom_modalidad, 
                    p.prom_precio_oferta, p.prom_ini, p.prom_fin, p.prom_limite_usos,
                    
                    -- 2. DATOS DEL NEGOCIO
                    n.neg_id, n.neg_nombre, n.neg_logo,

                    -- 3. DATOS DE PUNTOS (Si aplica)
                    fc.puntos_necesarios,

                    -- 4. IDENTIFICACIÓN DEL ÍTEM (PRODUCTO O SERVICIO)
                    CASE 
                        WHEN s.serv_id IS NOT NULL THEN 'SERVICIO' 
                        ELSE 'PRODUCTO' 
                    END as tipo_item,

                    COALESCE(s.serv_nombre, prod.pro_nombre) as nombre_item,
                    
                    -- 5. PRECIO REAL (Para mostrar el tachado)
                    COALESCE(s.serv_precio, prod.pro_precio, 0) as precio_real,

                    -- 6. IMAGEN (Buscamos la foto del servicio o producto)
                    (
                        SELECT i.img_url 
                        FROM tbl_imagen i
                        JOIN tbl_img_recurso ir ON i.img_id = ir.img_id
                        WHERE (ir.img_tipo = 'SERVICIO' AND ir.img_ref_id = s.serv_id)
                           OR (ir.img_tipo = 'PRODUCTO' AND ir.img_ref_id = prod.pro_id)
                        ORDER BY i.img_orden ASC LIMIT 1
                    ) as foto_item,

                    -- 7. CONTROL DE STOCK (Contamos cuántos se han vendido)
                    (SELECT COUNT(*) FROM tbl_promocion_historial h WHERE h.prom_id = p.prom_id) as total_usos

                FROM tbl_promocion p
                INNER JOIN tbl_negocio n ON p.neg_id = n.neg_id
                
                -- JOIN PARA ENCONTRAR EL ÍTEM ASOCIADO
                LEFT JOIN tbl_promocion_serv ps ON p.prom_id = ps.prom_id
                LEFT JOIN tbl_servicio s ON ps.serv_id = s.serv_id
                LEFT JOIN tbl_promocion_prod pp ON p.prom_id = pp.prom_id
                LEFT JOIN tbl_producto prod ON pp.pro_id = prod.pro_id

                -- JOIN PARA SACAR LOS PUNTOS (Si es mixto o canje)
                LEFT JOIN tbl_fidelidad_canje fc ON p.prom_id = fc.prom_id

                WHERE p.prom_estado = 'A' 
                  AND n.neg_estado = 'A'
                  -- Validar Fechas (Aceptamos indefinidas 0000-00-00)
                  AND (p.prom_fin IS NULL OR p.prom_fin = '0000-00-00 00:00:00' OR DATE(p.prom_fin) >= :hoy)
                
                -- Validar Stock (Si tiene límite, que no se haya superado)
                HAVING (p.prom_limite_usos = 0 OR total_usos < p.prom_limite_usos)
                
                ORDER BY RAND()
                LIMIT $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':hoy' => $fechaHoy]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // --- SUBPROCESO AUTOMÁTICO: LIMPIEZA DE PROMOCIONES ---
    public function limpiarPromocionesVencidas() {
        $hoy = date('Y-m-d H:i:s');
        
        // Regla: Desactivar si la fecha fin ya pasó O si los usos actuales llegaron al límite
        // (Solo si prom_limite_usos es mayor a 0)
        $sql = "UPDATE tbl_promocion 
                SET prom_estado = 'I' 
                WHERE prom_estado = 'A' 
                AND (
                    (prom_fin IS NOT NULL AND prom_fin != '0000-00-00 00:00:00' AND prom_fin < :hoy)
                    OR 
                    (prom_limite_usos > 0 AND prom_usos_actuales >= prom_limite_usos)
                )";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':hoy' => $hoy]);
    }

    public function cancelarCitaUsuario($cita_id, $cli_id) {
        try {
            $this->pdo->beginTransaction();

            // 1. Obtener info de la cita para ver si hay puntos que devolver
            $sql = "SELECT c.neg_id, d.det_puntos_canje, d.det_estado 
                    FROM tbl_cita c 
                    JOIN tbl_cita_det d ON c.cita_id = d.cita_id 
                    WHERE c.cita_id = :cid AND c.cli_id = :uid";
            $st = $this->pdo->prepare($sql);
            $st->execute([':cid' => $cita_id, ':uid' => $cli_id]);
            $cita = $st->fetch(PDO::FETCH_ASSOC);

            if (!$cita) throw new Exception("Cita no encontrada.");
            if (in_array($cita['det_estado'], ['FINALIZADO', 'CANCELADO', 'EN_ATENCION'])) {
                throw new Exception("Esta cita ya no puede ser cancelada.");
            }

            // 2. Devolver puntos si usó canje
            if (intval($cita['det_puntos_canje']) > 0) {
                $puntos = intval($cita['det_puntos_canje']);
                $stFid = $this->pdo->prepare("SELECT fidcli_id FROM tbl_fidelidad_cliente WHERE neg_id = ? AND cli_id = ?");
                $stFid->execute([$cita['neg_id'], $cli_id]);
                $fidId = $stFid->fetchColumn();

                if ($fidId) {
                    // Movimiento de REEMBOLSO
                    $this->pdo->prepare("INSERT INTO tbl_fidelidad_mov (fidcli_id, origen, ref_id, puntos, fidmov_tipo, descripcion) VALUES (?, 'CITA', ?, ?, 'GANANCIA', 'Reembolso por cancelación de cita')")
                              ->execute([$fidId, $cita_id, $puntos]);
                    // Sumar de nuevo al saldo
                    $this->pdo->prepare("UPDATE tbl_fidelidad_cliente SET fidcli_total = fidcli_total + ? WHERE fidcli_id = ?")
                              ->execute([$puntos, $fidId]);
                }
            }

            // 3. Cambiar estados a CANCELADO
            $this->pdo->prepare("UPDATE tbl_cita SET cita_estado = 'CANCELADO' WHERE cita_id = ?")->execute([$cita_id]);
            $this->pdo->prepare("UPDATE tbl_cita_det SET det_estado = 'CANCELADO' WHERE cita_id = ?")->execute([$cita_id]);

            $this->pdo->commit();
            return ['success' => true];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}