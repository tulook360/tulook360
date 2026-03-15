<?php
// api/productos/ConsultarDeliveryApp.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PublicoModelo.php';

// Recibimos los parámetros necesarios para la App Móvil
$ord_id = $_GET['ord_id'] ?? null;
$cli_id = $_GET['cli_id'] ?? null;

if (!$ord_id || !$cli_id) {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros requeridos']);
    exit;
}

try {
    $db = new Database();
    $modelo = new PublicoModelo($db->getConnection());

    // 1. Obtener Info de la Orden Completa
    $ordenFull = $modelo->obtenerOrdenPorId($ord_id, $cli_id);
    if (!$ordenFull) {
        echo json_encode(['success' => false, 'error' => 'Orden no encontrada o acceso denegado']);
        exit;
    }

    // 2. Obtener Info Básica y Posición del Chofer (usando función del modelo)
    $infoDriver = $modelo->obtenerEstadoYPosicionPedido($ord_id);

    // 3. Obtener Detalles (Paradas y Productos)
    $detalles = $modelo->obtenerDetallesOrden($ord_id);
    
    // Coordenadas del Cliente (Destino Final)
    $latCliente = floatval($ordenFull['ord_ubicacion_lat'] ?: -0.180653); 
    $lonCliente = floatval($ordenFull['ord_ubicacion_lon'] ?: -78.467834);

    // 4. Lógica de Paradas (Mismo algoritmo que la Web)
    $paradasMap = [];
    foreach ($detalles as $d) {
        $idSuc = $d['suc_id'];
        
        if (!isset($paradasMap[$idSuc])) {
            $paradasMap[$idSuc] = [
                'info' => [
                    'suc_id' => $idSuc,
                    'neg_id' => $d['neg_id'],
                    'nombre' => $d['suc_nombre'],
                    'direccion' => $d['suc_direccion'],
                    'lat' => floatval($d['suc_latitud']),
                    'lon' => floatval($d['suc_longitud'])
                ],
                'productos' => [],
                'estado_parada' => 'COMPLETADO' // Por defecto
            ];
        }
        
        if ($d['odet_estado'] !== 'RECOGIDO') {
            $paradasMap[$idSuc]['estado_parada'] = 'PENDIENTE';
        }
        
        $paradasMap[$idSuc]['productos'][] = [
            'pro_id' => $d['pro_id'],
            'cantidad' => $d['odet_cantidad'],
            'nombre' => $d['pro_nombre'],
            'estado' => $d['odet_estado'] // Agregado para saber qué poner en verde en MAUI
        ];
    }

    // Convertir a array indexado y ordenar por distancia al cliente
    $paradas = array_values($paradasMap);
    usort($paradas, function($a, $b) use ($latCliente, $lonCliente) { 
        $distA = pow($a['info']['lat'] - $latCliente, 2) + pow($a['info']['lon'] - $lonCliente, 2);
        $distB = pow($b['info']['lat'] - $latCliente, 2) + pow($b['info']['lon'] - $lonCliente, 2);
        return $distA <=> $distB;
    });

    // 5. Determinar la posición inicial del Driver (O última tienda visitada)
    $latChofer = (!empty($infoDriver['track_lat'])) ? floatval($infoDriver['track_lat']) : null;
    $lonChofer = (!empty($infoDriver['track_lon'])) ? floatval($infoDriver['track_lon']) : null;

    $ultimaTiendaCompletada = null;
    $pendientes = [];

    foreach ($paradas as $p) {
        if ($p['estado_parada'] === 'COMPLETADO') {
            $ultimaTiendaCompletada = ['lat' => $p['info']['lat'], 'lon' => $p['info']['lon']];
        } else {
            $pendientes[] = $p;
        }
    }

    $estadoActualOrden = $infoDriver ? $infoDriver['ord_estado'] : $ordenFull['ord_estado'];

    // --- EL FIX CRÍTICO DE LA UBICACIÓN ---
    // Si no hay coords del chofer, pero la orden está activa, le damos una posición lógica
    if (!$latChofer || !$lonChofer) {
        if ($ultimaTiendaCompletada) {
            $latChofer = $ultimaTiendaCompletada['lat'];
            $lonChofer = $ultimaTiendaCompletada['lon'];
        } else if (count($pendientes) > 0 && in_array($estadoActualOrden, ['ACEPTADO', 'EN_CAMINO', 'RECOLECTANDO'])) {
            // Si el chofer aceptó pero su GPS aún no carga, lo ubicamos en la 1ra tienda pendiente
            $latChofer = $pendientes[0]['info']['lat'];
            $lonChofer = $pendientes[0]['info']['lon'];
        }
    }

    // 6. Calcular Ruta Pendiente (Ruta Activa Roja + Ruta Planificada Morada)
    $rutaActiva = []; 
    $rutaPlanificada = []; 

    if ($latChofer && $lonChofer) {
        if (count($pendientes) > 0) {
            // Ordenar pendientes por distancia al CHOFER para que visite la más cercana primero
            usort($pendientes, function($a, $b) use ($latChofer, $lonChofer) {
                $distA = pow($a['info']['lat'] - $latChofer, 2) + pow($a['info']['lon'] - $lonChofer, 2);
                $distB = pow($b['info']['lat'] - $latChofer, 2) + pow($b['info']['lon'] - $lonChofer, 2);
                return $distA <=> $distB;
            });

            // Ruta Activa: Chofer -> Siguiente Tienda
            $rutaActiva[] = ['lat' => $latChofer, 'lon' => $lonChofer];
            $rutaActiva[] = ['lat' => $pendientes[0]['info']['lat'], 'lon' => $pendientes[0]['info']['lon']];

            // Ruta Planificada: Desde la siguiente tienda -> demás tiendas
            foreach ($pendientes as $p) {
                $rutaPlanificada[] = ['lat' => $p['info']['lat'], 'lon' => $p['info']['lon']];
            }
        } else {
            // Si ya recogió todo, la Ruta Activa es Chofer -> Cliente
            $rutaActiva[] = ['lat' => $latChofer, 'lon' => $lonChofer];
            $rutaActiva[] = ['lat' => $latCliente, 'lon' => $lonCliente];
        }
        // Siempre, el último punto de la ruta planificada es el cliente
        $rutaPlanificada[] = ['lat' => $latCliente, 'lon' => $lonCliente];
    }

    // 7. ARMAR JSON FINAL PARA MAUI
    echo json_encode([
        'success' => true,
        'estado_orden' => $estadoActualOrden,
        'qr_token' => $ordenFull['ord_token_qr'],
        'destino_cliente' => ['lat' => $latCliente, 'lon' => $lonCliente],
        
        // Cuidamos que no mande arrays vacíos si el pedido aún está PENDIENTE sin chofer
        'posicion_chofer' => ($latChofer && $lonChofer) ? ['lat' => $latChofer, 'lon' => $lonChofer] : null,
        
        'chofer' => $infoDriver['usu_nombres'] ?? null,
        'foto_chofer' => $infoDriver['usu_foto'] ?? null,
        
        'ruta_activa' => $rutaActiva,
        'ruta_planificada' => $rutaPlanificada,
        'paradas' => $paradas,
        
        'direccion_cliente' => $ordenFull['ord_direccion_envio'],
        'referencia_cliente' => $ordenFull['ord_referencia']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>