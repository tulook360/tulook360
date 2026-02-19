<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PedidoModelo.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class InventarioControlador {

    // VISTA: Muestra el catálogo y el carrito visual
    public function solicitar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad: Solo Admin Sucursal con Sucursal Asignada
        if (empty($_SESSION['suc_id'])) {
            set_flash('Error', 'No tienes una sucursal asignada para hacer pedidos.', 'warning');
            header('Location: ' . ruta_accion('auth', 'panel'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Solicitar Insumos";

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        // Obtener productos para que el usuario elija
        $catalogo = $modelo->obtenerCatalogo($_SESSION['negocio_id']);

        require __DIR__ . '/../views/inventario/solicitar_stock.php';
    }


    // PROCESO: GUARDAR PEDIDO (Con Validación Anti-Robo de Stock)
    public function procesar_pedido() {
        ob_clean();
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $items = $data['items'] ?? [];

        if (empty($items)) {
            echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
            exit;
        }

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        // ---------------------------------------------------------
        // FASE 1: EL PORTERO (Validación de Seguridad)
        // ---------------------------------------------------------
        // Antes de guardar, verificamos ítem por ítem si TODAVÍA hay stock.
        foreach ($items as $item) {
            $stockRealAhora = $modelo->verificarStockDisponible($item['id']);
            
            if ($item['cantidad'] > $stockRealAhora) {
                // ALERTA ROJA: Alguien pidió antes que tú.
                echo json_encode([
                    'success' => false, 
                    'message' => "¡Ups! El stock de '{$item['nombre']}' cambió mientras pedías. Solo quedan {$stockRealAhora} disponibles."
                ]);
                exit; // Matamos el proceso, no se guarda nada.
            }
        }

        // ---------------------------------------------------------
        // FASE 2: GUARDAR (Si pasó el portero, todo es seguro)
        // ---------------------------------------------------------
        $datosHeader = [
            'neg_id' => $_SESSION['negocio_id'],
            'suc_id' => $_SESSION['suc_id'],
            'usu_id' => $_SESSION['usuario_id']
        ];

        try {
            $modelo->guardarPedido($datosHeader, $items);
            echo json_encode(['success' => true, 'message' => '¡Solicitud enviada exitosamente!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error técnico: ' . $e->getMessage()]);
        }
        exit;
    }

    // VISTA: Historial de pedidos de la sucursal
    public function mis_pedidos() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['suc_id'])) {
            header('Location: ' . ruta_accion('auth', 'panel'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Mis Solicitudes de Stock";

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());
        
        // Obtener la lista
        $misPedidos = $modelo->listarPorSucursal($_SESSION['suc_id']);

        require __DIR__ . '/../views/inventario/mis_pedidos.php';
    }

    // AJAX: Devuelve los productos de un pedido para el Modal
    public function ver_detalle_ajax() {
        // Limpieza de buffer por seguridad
        ob_clean();
        header('Content-Type: application/json');

        // Validar sesión y datos
        if (empty($_SESSION['suc_id']) || empty($_POST['ped_id'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());
        
        try {
            $detalles = $modelo->obtenerDetallesPedido($_POST['ped_id']);
            echo json_encode(['success' => true, 'datos' => $detalles]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // AJAX: Cancelar Pedido
    public function cancelar_pedido() {
        ob_clean();
        header('Content-Type: application/json');

        if (empty($_SESSION['suc_id']) || empty($_POST['ped_id'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        if ($modelo->cancelarPedido($_POST['ped_id'], $_SESSION['suc_id'])) {
            echo json_encode(['success' => true, 'message' => 'Pedido cancelado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo cancelar. Quizás ya fue procesado por el dueño.']);
        }
        exit;
    }

    // VISTA: Bandeja de entrada del Dueño
    public function revision_pedidos() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad: Solo Rol 2 (Admin Negocio)
        if ($_SESSION['rol_id'] != 2) {
            header('Location: ' . ruta_accion('auth', 'panel'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Gestión de Pedidos";

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());
        
        // Traemos todos los pedidos de sus sucursales
        $pedidos = $modelo->listarPedidosGlobales($_SESSION['negocio_id']);

        require __DIR__ . '/../views/inventario/revision_pedidos.php';
    }

    // AJAX: Cargar datos para el Modal de Despacho
    public function cargar_despacho() {
        ob_clean();
        header('Content-Type: application/json');

        if (empty($_POST['ped_id'])) {
            echo json_encode(['success' => false, 'message' => 'ID faltante']);
            exit;
        }

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        try {
            $items = $modelo->obtenerDetalleParaDespacho($_POST['ped_id']);
            echo json_encode(['success' => true, 'items' => $items]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // AJAX: Guardar el despacho (Recibe JSON)
    public function guardar_despacho() {
        ob_clean();
        header('Content-Type: application/json');

        // Leer JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['ped_id']) || empty($data['items'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        try {
            $modelo->procesarDespacho($_SESSION['negocio_id'], $data);
            echo json_encode(['success' => true, 'message' => 'Despacho realizado y stock actualizado.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // AJAX: Confirmar Recepción e Ingresar Stock
    public function confirmar_recepcion() {
        // 1. Limpiamos cualquier basura anterior
        ob_clean();
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        // 2. Validar sesión (usamos suc_id directo como en tus otras funciones)
        if (empty($_SESSION['suc_id']) || empty($_POST['ped_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión caducada o datos incompletos']);
            exit;
        }

        $pedId = $_POST['ped_id'];
        $sucId = $_SESSION['suc_id']; 

        // 3. INSTANCIAR EL MODELO (ESTO TE FALTABA)
        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        try {
            // Llamamos al modelo para que haga la magia
            $res = $modelo->finalizarRecepcionSucursal($pedId, $sucId);

            if ($res) {
                echo json_encode(['success' => true, 'message' => 'Stock ingresado correctamente a tu sucursal.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo procesar. Verifica si ya fue recibido.']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }


    public function mi_stock() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['suc_id'])) {
            header('Location: ' . ruta_accion('auth', 'panel'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Mi Inventario";

        $db = new Database();
        $modelo = new PedidoModelo($db->getConnection());

        $miInventario = $modelo->obtenerStockSucursal($_SESSION['suc_id'], $_SESSION['negocio_id']);

        // --- CORRECCIÓN MATEMÁTICA ---
        $totalProductos = count($miInventario);
        $criticos = 0;
        $agotados = 0;

        foreach ($miInventario as $p) {
            $cerrado = floatval($p['stock_cerrado']);
            $abierto = floatval($p['stock_abierto']);
            $contenido = floatval($p['pro_contenido']);

            // 1. Primero verificamos si está TOTALMENTE AGOTADO
            if ($cerrado <= 0 && $abierto <= 0) {
                $agotados++;
            } 
            // 2. Si NO está agotado, verificamos si es CRÍTICO (Solo abierto o poca cantidad)
            elseif ($cerrado <= 0 && $abierto <= ($contenido * 0.25)) {
                $criticos++;
            }
            // Los que sobran son los ÓPTIMOS (No necesitamos contarlos, se calculan con resta)
        }

        require __DIR__ . '/../views/inventario/mi_stock.php';
    }
}