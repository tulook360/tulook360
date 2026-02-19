<?php
date_default_timezone_set('America/Guayaquil');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AdminsucuModelo.php';
require_once __DIR__ . '/../models/FidelidadModelo.php';
if (file_exists(__DIR__ . '/../nucleo/helpers.php')) require_once __DIR__ . '/../nucleo/helpers.php';

class AdminsucuControlador {

    public function escanear_qr() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Seguridad básica
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 8 || empty($_SESSION['suc_id'])) {
            header('Location: index.php?c=auth&a=login'); exit;
        }

        if (function_exists('ruta_accion')) {
            $urlBuscar = ruta_accion('adminsucu', 'buscar_orden_ajax');
            $urlConfirmar = ruta_accion('adminsucu', 'confirmar_entrega_ajax');
        } else {
            $urlBuscar = "index.php?c=adminsucu&a=buscar_orden_ajax";
            $urlConfirmar = "index.php?c=adminsucu&a=confirmar_entrega_ajax";
        }
        require_once __DIR__ . '/../views/sucursal/escanear_qr.php';
    }

    public function buscar_orden_ajax() {
        while (ob_get_level()) ob_end_clean(); // LIMPIEZA EXTREMA
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        $suc_id = $_SESSION['suc_id'] ?? 0;
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $token = $data['token'] ?? '';

        try {
            $db = new Database();
            $modelo = new AdminsucuModelo($db->getConnection());

            $orden = $modelo->buscarOrdenPorToken($token, $suc_id);
            if (!$orden) {
                echo json_encode(['success' => false, 'error' => 'Código QR no válido']);
                exit;
            }

            // CASO: NO ES AQUÍ
            if (!$orden['tiene_items_aqui']) {
                $lugares = $modelo->obtenerDondeEstaElPedido($orden['ord_id']);
                echo json_encode([
                    'success' => true,
                    'tipo_respuesta' => 'WRONG_BRANCH',
                    'orden' => $orden,
                    'lugares' => $lugares
                ]);
                exit;
            }

            // CASO: SI ES AQUÍ
            $items = $modelo->obtenerItemsParaEntregar($orden['ord_id'], $suc_id);
            echo json_encode([
                'success' => true,
                'tipo_respuesta' => 'OK',
                'orden' => $orden,
                'items' => $items
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function confirmar_entrega_ajax() {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        $suc_id = $_SESSION['suc_id'] ?? 0;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $ord_id = $data['ord_id'] ?? 0;
        $pagos  = $data['pagos'] ?? [];
        $total  = $data['total'] ?? 0;

        if ($ord_id <= 0) { echo json_encode(['success' => false, 'error' => 'ID inválido']); exit; }
        if (empty($pagos)) { echo json_encode(['success' => false, 'error' => 'Faltan pagos']); exit; }

        try {
            $db = new Database();
            $pdo = $db->getConnection();
            
            // --- INICIO DE TRANSACCIÓN MAESTRA ---
            $pdo->beginTransaction();

            // 1. Instanciar modelos (Compartiendo la misma conexión)
            $modeloAdmin = new AdminsucuModelo($pdo);
            require_once __DIR__ . '/../models/FidelidadModelo.php';
            $modeloFidelidad = new FidelidadModelo($pdo);

            // 2. Procesar la Entrega (Pagos y Estado)
            // IMPORTANTE: Asegúrate que confirmarEntregaSucursal NO tenga sus propios beginTransaction/commit
            // Si los tiene, PHP anidará, pero si falla algo aquí, el rollback de abajo cancelará todo.
            $resEntrega = $modeloAdmin->confirmarEntregaSucursal($ord_id, $suc_id, $pagos, $total);

            if (!$resEntrega['success']) {
                throw new Exception($resEntrega['error'] ?? "Error al confirmar entrega");
            }

            // 3. Procesar Puntos
            $stmtCli = $pdo->prepare("SELECT cli_id FROM tbl_orden WHERE ord_id = :oid");
            $stmtCli->execute([':oid' => $ord_id]);
            $cli_id = $stmtCli->fetchColumn();

            if ($cli_id) {
                // AHORA PASAMOS LA SUCURSAL EXACTA PARA EVITAR SUMAR PUNTOS DE OTRAS SUCURSALES
                $modeloFidelidad->procesarPuntosPorEntregaSucursal($ord_id, $cli_id, $suc_id);
            }

            // --- SI TODO SALIÓ BIEN, GUARDAMOS TODO ---
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Entrega confirmada y puntos asignados']);

        } catch (Exception $e) {
            // --- SI ALGO FALLA, DESHACEMOS TODO (INCLUIDA LA ENTREGA) ---
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'error' => 'Error Crítico: ' . $e->getMessage()]);
        }
        exit;
    }



    // --- GESTIÓN DE CITAS ---

    public function escanear_cita() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Validar Rol Admin Sucursal (8)
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 8 || empty($_SESSION['suc_id'])) {
            header('Location: index.php?c=auth&a=login'); exit;
        }

        // URLs para AJAX
        $urlBuscar = ruta_accion('adminsucu', 'buscar_cita_ajax');
        $urlConfirmar = ruta_accion('adminsucu', 'confirmar_cita_ajax');

        require_once __DIR__ . '/../views/sucursal/escanear_cita.php';
    }

    public function buscar_cita_ajax() {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $suc_id = $_SESSION['suc_id'] ?? 0;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $token = $data['token'] ?? '';

        try {
            $db = new Database();
            $modelo = new AdminsucuModelo($db->getConnection());
            
            $cita = $modelo->buscarCitaPorToken($token, $suc_id);

            if ($cita) {
                echo json_encode(['success' => true, 'cita' => $cita]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Cita no encontrada en esta sucursal.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // 3. AJAX: CONFIRMAR (AHORA RECIBE PAGOS)
    public function confirmar_cita_ajax() {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $cita_id = $data['cita_id'] ?? 0;
        $pagos = $data['pagos'] ?? []; // Array: [{metodo_id, monto, referencia}, ...]
        $total = $data['total'] ?? 0;

        if (empty($pagos) || $total <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos de pago inválidos.']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new AdminsucuModelo($db->getConnection());
            
            // Enviamos todo al modelo
            $res = $modelo->confirmarCitaSucursal($cita_id, $pagos, $total);
            
            echo json_encode($res);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }



    // PANTALLA: CIERRE DE CAJA
    public function cierre_caja() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 8 || empty($_SESSION['suc_id'])) {
            header('Location: index.php?c=auth&a=login'); exit;
        }

        $suc_id = $_SESSION['suc_id'];
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $db = new Database();
        $modelo = new AdminsucuModelo($db->getConnection());
        
        $movimientos = $modelo->obtenerMovimientosCaja($suc_id, $fecha);

        // CÁLCULOS DE RESUMEN (Simplificado gracias al nuevo SQL)
        $totalGeneral = 0;
        $totalEfectivo = 0;
        $totalDigital = 0;

        foreach ($movimientos as $m) {
            $totalGeneral  += floatval($m['monto_total']);
            $totalEfectivo += floatval($m['total_efectivo']);
            $totalDigital  += floatval($m['total_digital']);
        }

        require_once __DIR__ . '/../views/sucursal/cierre_caja.php';
    }
}