<?php
// api/productos/ConsultarRastreoApp.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

// Recibimos los parámetros por GET (Ideal para la App Móvil)
$ord_id = $_GET['ord_id'] ?? null;
$cli_id = $_GET['cli_id'] ?? null;

if (!$ord_id || !$cli_id) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros (ord_id o cli_id)']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // 1. OBTENER ORDEN PRINCIPAL (Usando tu función existente)
    $ordenFull = $modelo->obtenerOrdenPorId($ord_id, $cli_id);
    if (!$ordenFull) {
        echo json_encode(['success' => false, 'error' => 'Orden no encontrada o no tienes permisos']);
        exit;
    }

    // 2. OBTENER INFO DEL CONDUCTOR Y ESTADO (Usando tu función existente)
    $info = $modelo->obtenerEstadoYPosicionPedido($ord_id);

    // 3. OBTENER DETALLES Y PAGOS (Usando tus funciones existentes)
    $detalles = $modelo->obtenerDetallesOrden($ord_id);
    $todosLosPagos = $modelo->obtenerPagosOrden($ord_id);

    // 4. LÓGICA DE AGRUPACIÓN Y COORDENADAS
    $latCliente = $ordenFull['ord_ubicacion_lat'] ?: -0.180653; 
    $lonCliente = $ordenFull['ord_ubicacion_lon'] ?: -78.467834;

    $paradasMap = [];
    foreach ($detalles as $d) {
        $idSuc = $d['suc_id'];
        $idNeg = $d['neg_id'];
        
        if (!isset($paradasMap[$idSuc])) {
            $dist = sqrt(pow($d['suc_latitud'] - $latCliente, 2) + pow($d['suc_longitud'] - $lonCliente, 2));
            
            // Filtrar pagos que corresponden solo a este negocio
            $pagosDelNegocio = array_filter($todosLosPagos, function($p) use ($idNeg) {
                return $p['neg_id'] == $idNeg;
            });

            $paradasMap[$idSuc] = [
                'info' => [
                    'suc_id' => $idSuc,
                    'neg_id' => $idNeg,
                    'nombre' => $d['suc_nombre'],
                    'neg_nombre' => $d['neg_nombre'],
                    'logo' => $d['neg_logo'],
                    'direccion' => $d['suc_direccion'],
                    'lat' => $d['suc_latitud'],
                    'lon' => $d['suc_longitud'],
                    'distancia' => $dist
                ],
                'productos' => [],
                'pagos' => array_values($pagosDelNegocio),
                'estado_parada' => 'COMPLETADO' // Por defecto
            ];
        }
        
        if ($d['odet_estado'] !== 'RECOGIDO') {
            $paradasMap[$idSuc]['estado_parada'] = 'PENDIENTE';
        }
        
        $paradasMap[$idSuc]['productos'][] = [
            'cantidad' => $d['odet_cantidad'],
            'nombre' => $d['pro_nombre'],
            'estado' => $d['odet_estado']
        ];
    }

    // Ordenar las paradas
    $paradas = array_values($paradasMap);
    usort($paradas, function($a, $b) { 
        return $a['info']['distancia'] <=> $b['info']['distancia']; 
    });

    // 5. CÁLCULO DE LA RUTA EN EL MAPA
    $latInicio = $latCliente;
    $lonInicio = $lonCliente;
    $puntosRuta = [];
    
    foreach ($paradas as $suc) {
        if ($suc['estado_parada'] === 'COMPLETADO') {
            $latInicio = $suc['info']['lat'];
            $lonInicio = $suc['info']['lon'];
        } else {
            $puntosRuta[] = ['lat' => $suc['info']['lat'], 'lon' => $suc['info']['lon']];
        }
    }

    // 6. ENVIAR JSON FINAL
    echo json_encode([
        'success' => true,
        'estado'  => $info ? $info['ord_estado'] : $ordenFull['ord_estado'],
        'tipo_entrega' => $ordenFull['ord_tipo_entrega'],
        
        // Datos del Chofer
        'lat_driver' => $info['track_lat'] ?? null,
        'lon_driver' => $info['track_lon'] ?? null,
        'chofer' => $info['usu_nombres'] ?? null,
        'foto_chofer' => $info['usu_foto'] ?? null,
        
        // Datos Financieros
        'costo_envio' => $ordenFull['ord_costo_envio'],
        'subtotal'    => $ordenFull['ord_subtotal'],
        'total_pagar' => $ordenFull['ord_total'],
        
        // Token y Ruta
        'qr_token' => $ordenFull['ord_token_qr'],
        'inicio' => ['lat' => $latInicio, 'lon' => $lonInicio],
        'ruta_pendiente' => $puntosRuta,
        'paradas' => $paradas
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>