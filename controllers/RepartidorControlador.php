<?php
date_default_timezone_set('America/Guayaquil');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RepartidorModelo.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class RepartidorControlador {

    // Middleware de seguridad simple
    private function verificarRepartidor() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 11) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }
    }

    // PANTALLA PRINCIPAL: Dashboard
    public function panel() {
        $this->verificarRepartidor();
        
        $db = new Database();
        $modelo = new RepartidorModelo($db->getConnection());
        $repartidor_id = $_SESSION['usuario_id'];

        // --- CORRECCIÓN DE NOMBRES ---
        // Antes llamaba a métodos incorrectos y guardaba en variables incorrectas.
        
        // 1. Obtener Ofertas (Pedidos Pendientes)
        $ofertas = $modelo->obtenerOfertasDisponibles(); 

        // 2. Obtener Mis Pedidos (Los que ya acepté)
        $misPedidos = $modelo->obtenerMisPedidosEnCurso($repartidor_id);

        // Ahora $ofertas y $misPedidos existen y se pasan a la vista correctamente.
        require_once __DIR__ . '/../views/dashboard/repartidor.php';
    }

    // AJAX: Aceptar Pedido
    public function aceptar_oferta_ajax() {
        $this->verificarRepartidor();
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $ord_id = $data['ord_id'] ?? 0;

        if (!$ord_id) {
            echo json_encode(['success' => false, 'error' => 'ID inválido']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new RepartidorModelo($db->getConnection());
            
            $res = $modelo->aceptarPedido($ord_id, $_SESSION['usuario_id']);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    // AJAX: Consultar Ofertas (Silencioso)
    public function consultar_ofertas_json() {
        $this->verificarRepartidor();
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        try {
            $db = new Database();
            $modelo = new RepartidorModelo($db->getConnection());
            
            $ofertas = $modelo->obtenerOfertasDisponibles();
            $misPedidos = $modelo->obtenerMisPedidosEnCurso($_SESSION['usuario_id']); // También actualizamos los activos

            echo json_encode([
                'success' => true,
                'ofertas' => $ofertas,
                'mis_pedidos' => $misPedidos
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // --- NUEVO: PANTALLA DE RUTA (CONSOLA DEL CONDUCTOR) ---
    public function ruta() {
        $this->verificarRepartidor();
        
        $ord_id = $_GET['id'] ?? null;
        if (!$ord_id) { header('Location: index.php'); exit; }

        $db = new Database();
        
        // 1. Cargar Modelo Repartidor (Para lógica propia)
        $modeloRep = new RepartidorModelo($db->getConnection());

        // 2. Cargar Modelo Público (Para reciclar la consulta de detalles de orden)
        require_once __DIR__ . '/../models/PublicoModelo.php';
        $modeloPub = new PublicoModelo($db->getConnection());

        // Obtener Info Orden
        $sql = "SELECT * FROM tbl_orden WHERE ord_id = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$ord_id]);
        $orden = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validar que sea mi orden
        if ($orden['usu_id_repartidor'] != $_SESSION['usuario_id']) {
            header('Location: index.php'); exit;
        }

        // Obtener Detalles (Productos, Tiendas, Coordenadas)
        $detalles = $modeloPub->obtenerDetallesOrden($ord_id);

        // Cargar la vista en la carpeta correcta
        require_once __DIR__ . '/../views/repartidor/ruta.php';
    }

    // AJAX: ACCIONES DE LOS BOTONES
    public function cambiar_estado_ruta_ajax() {
        $this->verificarRepartidor();
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // DEBUG: Verificar que llega el ID (puedes quitar esto luego)
        if (empty($data['ord_id'])) {
             echo json_encode(['success' => false, 'error' => 'ID de orden no recibido']);
             exit;
        }

        try {
            $db = new Database();
            $modelo = new RepartidorModelo($db->getConnection());

            // 1. FINALIZAR
            if ($data['accion'] === 'ENTREGAR') {
                $res = $modelo->finalizarOrden($data['ord_id']);
                
                if ($res) {
                    echo json_encode(['success' => true, 'msg' => 'Orden finalizada']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la base de datos']);
                }
            }
            // 2. NUEVO: CANCELAR (LIBERAR)
            elseif ($data['accion'] === 'CANCELAR') {
                $res = $modelo->liberarPedido($data['ord_id'], $_SESSION['usuario_id']);
                echo json_encode($res);
            }
            // 3. RECOGER (Si se usara manual, pero ahora es por QR)
            elseif ($data['accion'] === 'RECOGER') {
                $modelo->marcarRecogidoEnSucursal($data['ord_id'], $data['suc_id']);
                echo json_encode(['success' => true]);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function consultar_estado_orden_ajax() {
        // 1. SILENCIADOR DE ERRORES (ESTO ARREGLA EL JSON INVALIDO)
        error_reporting(0);
        ini_set('display_errors', 0);
        
        $this->verificarRepartidor();
        if (ob_get_length()) ob_clean(); // Limpia basura HTML
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $ord_id = $data['ord_id'] ?? null;

        if (!$ord_id) { echo json_encode(['success'=>false]); exit; }

        try {
            $db = new Database();
            require_once __DIR__ . '/../models/PublicoModelo.php';
            $modeloPub = new PublicoModelo($db->getConnection());
            
            $orden = $modeloPub->obtenerOrdenPorId($ord_id, 0); 
            $detalles = $modeloPub->obtenerDetallesOrden($ord_id);

            // LOGICA DE AGRUPACIÓN
            $paradas = [];
            $todoRecogido = true;

            foreach ($detalles as $d) {
                $idSuc = $d['suc_id'];
                if (!isset($paradas[$idSuc])) {
                    $paradas[$idSuc] = [
                        'info' => [
                            'suc_id' => $d['suc_id'],
                            'nombre' => $d['suc_nombre'],
                            'direccion' => $d['suc_direccion'],
                            'lat' => $d['suc_latitud'],
                            'lon' => $d['suc_longitud']
                        ],
                        'productos' => [],
                        'estado_parada' => 'COMPLETADO'
                    ];
                }
                if ($d['odet_estado'] !== 'RECOGIDO') {
                    $paradas[$idSuc]['estado_parada'] = 'PENDIENTE';
                    $todoRecogido = false;
                }
                $paradas[$idSuc]['productos'][] = [
                    'odet_cantidad' => $d['odet_cantidad'],
                    'pro_nombre' => $d['pro_nombre'],
                    'odet_subtotal' => $d['odet_subtotal']
                ];
            }

            echo json_encode([
                'success' => true,
                'paradas' => array_values($paradas),
                'todo_recogido' => $todoRecogido,
                'estado_orden' => $orden['ord_estado']
            ]);

        } catch (Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }  


    public function actualizar_ubicacion_ajax() {
        // 1. SILENCIAR ERRORES DE PHP (Para evitar que rompan el JSON)
        error_reporting(0);
        ini_set('display_errors', 0);

        // 2. Limpieza de buffer
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        // 3. Verificar Sesión
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'error' => 'Sesion caducada']);
            exit;
        }

        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!isset($data['lat']) || !isset($data['lon'])) {
                echo json_encode(['success' => false, 'error' => 'Faltan coordenadas']);
                exit;
            }

            $db = new Database();
            $modelo = new RepartidorModelo($db->getConnection());
            
            $usu_id = $_SESSION['usuario_id'];
            $ord_id = $data['ord_id'] ?? null;
            
            // Guardar en DB
            $res = $modelo->actualizarGPS($usu_id, $data['lat'], $data['lon'], $ord_id);
            
            if ($res) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error BD']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

}
?>