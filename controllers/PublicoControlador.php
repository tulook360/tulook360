<?php
date_default_timezone_set('America/Guayaquil'); 

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PublicoModelo.php';
require_once __DIR__ . '/../nucleo/helpers.php';
require_once __DIR__ . '/../nucleo/Crypto.php';

class PublicoControlador {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        // --- EJECUTAR LIMPIEZA AUTOMÁTICA DE PROMOS ---
        $modelo->limpiarPromocionesVencidas();
        $horaActual = date('H:i:s');

        $busqueda = trim($_GET['q'] ?? '');
        
        // --- 1. GESTIÓN DE SEMILLA ---
        if (!isset($_SESSION['semilla_servicios'])) {
            $_SESSION['semilla_servicios'] = uniqid();
        }
        $semilla = $_SESSION['semilla_servicios'];

        // --- 2. CARGAR DATOS ---
        $logosNegocios = $modelo->obtenerLogosNegocios();
        $categorias    = $modelo->obtenerCategorias();
        $promociones   = $modelo->obtenerPromocionesVigentes(15); // Traemos 15 promos
        $servicios     = $modelo->obtenerServiciosHome($busqueda);
        $listaProductos = $modelo->obtenerProductosDashboard(0, 8, $semilla);

        // --- 3. URLS PARA AJAX (¡¡AQUÍ ESTÁ LA CLAVE!!) ---
        // Generamos TOKENS FRESCOS para todo el sistema aquí
        $urlListarNegocios      = ruta_accion('publico', 'listar_negocios_ajax'); 
        $urlVerPerfil           = ruta_accion('publico', 'ver_perfil_negocio_ajax');
        $urlVisitarNegocio      = ruta_accion('publico', 'negocio');
        $urlVerServicio         = ruta_accion('publico', 'ver_detalle_servicio_ajax');
        $urlCargarMas           = ruta_accion('publico', 'cargar_mas_servicios_ajax');
        $urlCargarMasProductos  = ruta_accion('publico', 'cargar_mas_productos_ajax');
        $urlVerHorarios         = ruta_accion('publico', 'ver_horarios_disponibles_ajax');
        $urlBuscar              = ruta_accion('publico', 'buscar_ajax'); // <--- NUEVA
        
        // URLs del Carrito (NUEVAS)
        $urlVerCarrito          = ruta_accion('publico', 'ver_carrito_ajax');
        $urlActualizarCarrito   = ruta_accion('publico', 'actualizar_carrito_ajax');
        $urlEliminarCarrito     = ruta_accion('publico', 'eliminar_carrito_ajax');
        $urlAgregarCarrito      = ruta_accion('publico', 'agregar_carrito_ajax');
        $urlInfoProducto        = ruta_accion('publico', 'ver_info_producto_modal_ajax');

        // ... (El resto de tu lógica de horarios se queda igual) ...
        foreach ($servicios as &$ser) {
             // ... tu lógica de abierto/cerrado ...
        }

       require __DIR__ . '/../views/dashboard/cliente.php';;
    }


    // BÚSQUEDA VIVA AJAX
    public function buscar_ajax() {
        // 1. Limpiar Buffer (Por seguridad, para que no se cuele HTML)
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json'); // Forzar cabecera JSON

        $q = trim($_GET['q'] ?? '');

        // Validación simple
        if (strlen($q) < 2) {
            echo json_encode([]); 
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // Llamamos al modelo
            $resultados = $modelo->buscarVivo($q);
            
            echo json_encode($resultados);

        } catch (Exception $e) {
            // Enviar error como JSON válido
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }



    // --- NUEVOS ENDPOINTS PARA NEGOCIOS ---

    // AJAX: Obtener lista de negocios (con o sin búsqueda)
    public function listar_negocios_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $busqueda = trim($_GET['q'] ?? '');
            
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            $negocios = $modelo->obtenerDirectorioNegocios($busqueda);
            
            echo json_encode(['success' => true, 'data' => $negocios]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // AJAX: Cargar TODO el perfil para el Modal Lateral
    public function ver_perfil_negocio_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        // CAPTURAMOS EL ID DEL USUARIO DESDE LA SESIÓN
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID requerido']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // PASAMOS EL ID DEL CLIENTE COMO SEGUNDO PARÁMETRO
            $fullData = $modelo->obtenerPerfilNegocioFull($id, $cli_id);
            
            if ($fullData) {
                echo json_encode(['success' => true, 'data' => $fullData]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Negocio no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    // PAGINA DE PERFIL COMPLETO DEL NEGOCIO (MODO DEPURACIÓN)
    public function negocio() {
        // 1. ACTIVAR ERRORES DE PHP (Para ver si hay error de sintaxis o fatal)
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // 2. VERIFICAR QUE LLEGA EL ID
        $id = $_GET['id'] ?? null;

        if (!$id) {
            // EN LUGAR DE REDIRECCIONAR, MOSTRAMOS EL ERROR EN PANTALLA
            echo "<div style='background:red; color:white; padding:20px; font-family:sans-serif;'>";
            echo "<h1>⚠️ ERROR CRÍTICO: FALTA EL ID</h1>";
            echo "<p>El controlador no recibió ningún ID en la URL.</p>";
            echo "<p><strong>Datos recibidos (GET):</strong></p><pre>";
            print_r($_GET);
            echo "</pre></div>";
            exit; // Detenemos todo aquí para que lo leas
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // 3. INTENTAR OBTENER DATOS
            $perfil = $modelo->obtenerPerfilNegocioFull($id);

            if (!$perfil) {
                // SI EL ID LLEGÓ, PERO LA CONSULTA NO TRAJO NADA
                echo "<div style='background:orange; color:white; padding:20px; font-family:sans-serif;'>";
                echo "<h1>⚠️ ALERTA: NEGOCIO NO ENCONTRADO</h1>";
                echo "<p>El ID recibido fue: <strong>" . htmlspecialchars($id) . "</strong></p>";
                echo "<p>Pero la base de datos devolvió vacío. Revisa que el negocio exista y tenga estado 'A'.</p>";
                echo "</div>";
                exit;
            }

            // 4. INTENTAR CARGAR LA VISTA
            $rutaVista = __DIR__ . '/../views/publico/negocio_perfil.php';
            
            if (file_exists($rutaVista)) {
                require $rutaVista;
            } else {
                echo "<div style='background:purple; color:white; padding:20px; font-family:sans-serif;'>";
                echo "<h1>⚠️ ERROR DE ARCHIVO</h1>";
                echo "<p>No encuentro el archivo de la vista en:</p>";
                echo "<code>" . $rutaVista . "</code>";
                echo "</div>";
            }

        } catch (Exception $e) {
            // SI EXPLOTA LA BASE DE DATOS O PHP
            echo "<div style='background:black; color:red; padding:20px; font-family:sans-serif;'>";
            echo "<h1>☠️ EXCEPCIÓN DEL SISTEMA</h1>";
            echo "<h3>" . $e->getMessage() . "</h3>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            echo "</div>";
        }
    }


    // AJAX: Cargar Detalle de Servicio para Modal
    public function ver_detalle_servicio_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $id = $_GET['id'] ?? null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        
        // El modelo ahora hace todo el trabajo sucio
        $data = $modelo->obtenerDetalleServicioModal($id, $cli_id);
        
        echo json_encode(['success' => ($data ? true : false), 'data' => $data]);
        exit;
    }


    // AJAX: Calcular Horarios Disponibles (Algoritmo)
    public function ver_horarios_disponibles_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $serv_id = $_GET['servicio'] ?? null;
        $usu_id  = $_GET['especialista'] ?? null;
        $fecha   = $_GET['fecha'] ?? null;

        if (!$serv_id || !$usu_id || !$fecha) {
            echo json_encode(['success' => false, 'error' => 'Faltan datos']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());

            $horas = $modelo->obtenerHorariosDisponibles($serv_id, $usu_id, $fecha);

            // LOGICA: Si el modelo dice "DESCANSO"
            if ($horas === "DESCANSO") {
                echo json_encode([
                    'success' => true, 
                    'horas' => [], 
                    'descanso' => true, // Bandera ACTIVADA
                    'mensaje' => 'El especialista no labora este día.'
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'horas' => $horas,
                    'descanso' => false 
                ]);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    // AJAX: Cargar más servicios (Paginación Infinita con Semilla)
    public function cargar_mas_servicios_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $limit  = 8;
        
        // RECUPERAMOS LA SEMILLA DE LA SESIÓN
        // Así el "Ver Más" sabe el orden exacto que tiene el usuario en pantalla
        $semilla = $_SESSION['semilla_servicios'] ?? 'backup_seed';

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // Pasamos la semilla al modelo
            $servicios = $modelo->obtenerServiciosDashboard($offset, $limit, $semilla);
            
            echo json_encode(['success' => true, 'data' => $servicios]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // AJAX: Procesar el guardado de la reserva (COMPATIBLE CON MÓVIL)
    public function guardar_reserva_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? $data['cli_id'] ?? null;

        if (!$cli_id) {
            echo json_encode(['success' => false, 'error' => 'Usuario no identificado']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());

            $params = [
                'neg_id'          => $data['neg_id'],
                'suc_id'          => $data['suc_id'],
                'cli_id'          => $cli_id,
                'serv_id'         => $data['serv_id'],
                'especialista_id' => $data['especialista_id'],
                'fecha_completa'  => $data['fecha'] . ' ' . $data['hora'] . ':00',
                'precio'          => $data['precio'],
                'notas'           => $data['notas'] ?? '',
                'qr_token'        => $data['qr_token'] ?? '',
                'prom_id'         => $data['prom_id'] ?? null, // <-- CAPTURAMOS EL ID DE PROMO
                'puntos'          => $data['puntos'] ?? 0
            ];

            $res = $modelo->registrarCitaCompleta($params);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // AJAX: Cargar más productos (Paginación Infinita)
    public function cargar_mas_productos_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start(); // <--- IMPORTANTE

        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        $limit  = 8;
        $semilla = $_SESSION['semilla_servicios'] ?? 'backup_seed';

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            $productos = $modelo->obtenerProductosDashboard($offset, $limit, $semilla);
            
            echo json_encode(['success' => true, 'data' => $productos]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    // --------------------------------------------------------------
    // AJAX: Obtener datos para el Modal de Compra Universal
    // --------------------------------------------------------------
    public function ver_info_producto_modal_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $pro_id = $_GET['id'] ?? null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;
        
        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        
        // Pasamos pro_id y cli_id
        $data = $modelo->obtenerInfoProductoModal($pro_id, $cli_id);
        
        if ($data) {
            // Formatear presentación
            $presentacion = floatval($data['pro_contenido']) . ' ' . $data['pro_unidad_consumo'];
            if ($data['pro_unidad'] !== 'Unidad') {
                $presentacion = $data['pro_unidad'] . ' de ' . $presentacion;
            }
            $data['txt_presentacion'] = $presentacion;

            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        }
        exit;
    }

    // --------------------------------------------------------------
    // AJAX: Agregar al Carrito (Acción del botón "Confirmar" del Modal)
    // --------------------------------------------------------------
    public function agregar_carrito_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        // 1. Validar Sesión de Cliente
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        if (!$cli_id) {
            // Código especial 'NO_LOGIN' para que el JS sepa que debe abrir el login
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión', 'code' => 'NO_LOGIN']);
            exit;
        }

        // 2. Recibir datos del JS
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $pro_id = $data['pro_id'] ?? null;
        // Si prom_id viene vacío, 0 o falso, lo convertimos en NULL real para la base de datos
        $prom_id = (!empty($data['prom_id'])) ? $data['prom_id'] : 0;
        $cantidad = $data['cantidad'] ?? 1;

        if (!$pro_id) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // Llamar al modelo
            $res = $modelo->agregarAlCarrito($cli_id, $pro_id, $cantidad, $prom_id);
            
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // --------------------------------------------------------------
    // AJAX: Ver contenido del carrito (Badge y Lista)
    // --------------------------------------------------------------
    public function ver_carrito_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        if (!$cli_id) {
            echo json_encode(['success' => false, 'error' => 'No login']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            $items = $modelo->obtenerCarrito($cli_id);
            
            // Calcular totales
            $totalGlobal = 0;
            $cantidadTotal = 0;
            
            foreach($items as $i) {
                $totalGlobal += $i['subtotal'];
                $cantidadTotal += $i['car_cantidad']; // O count($items) si prefieres contar productos únicos
            }

            echo json_encode([
                'success' => true, 
                'items' => $items,
                'total' => $totalGlobal,
                'count' => count($items) // Cantidad de items distintos para el badge
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // --------------------------------------------------------------
    // AJAX: Actualizar Cantidad (+ / -)
    // --------------------------------------------------------------
    public function actualizar_carrito_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;
        if (!$cli_id) { echo json_encode(['success' => false]); exit; }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $car_id = $data['car_id'] ?? 0;
        $cambio = $data['cambio'] ?? 0; // +1 o -1

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        
        $res = $modelo->actualizarCantidadCarrito($car_id, $cli_id, $cambio);
        echo json_encode(['success' => $res]);
        exit;
    }

    // --------------------------------------------------------------
    // AJAX: Eliminar Item
    // --------------------------------------------------------------
    public function eliminar_carrito_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $car_id = $data['car_id'] ?? 0;

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        
        $res = $modelo->eliminarItemCarrito($car_id, $cli_id);
        echo json_encode(['success' => $res]);
        exit;
    }


    // ==========================================================
    // PÁGINA DE CHECKOUT (VISTA)
    // ==========================================================
    public function checkout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Validar Sesión
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }
        $cli_id = $_SESSION['usuario_id'];

        // 2. Obtener Items del Carrito
        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        $items = $modelo->obtenerCarrito($cli_id);

        if (empty($items)) {
            // Si intenta entrar al checkout sin productos, regresar
            header('Location: index.php'); 
            exit;
        }

        // 3. Calcular Totales Iniciales
        $subtotal = 0;
        foreach ($items as $i) { $subtotal += $i['subtotal']; }
        
        // URL para procesar (Tokenizada)
        $urlProcesar = ruta_accion('publico', 'procesar_compra_ajax');

        require_once __DIR__ . '/../views/publico/checkout.php';
    }

    // ==========================================================
    // PROCESAR COMPRA (AJAX)
    // ==========================================================
    public function procesar_compra_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;
        if (!$cli_id) { echo json_encode(['success'=>false, 'error'=>'Sesión expirada']); exit; }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            $items = $modelo->obtenerCarrito($cli_id);
            if (empty($items)) { throw new Exception("El carrito está vacío"); }

            // --- CORRECCIÓN: Calcular subtotal respetando promociones ($0 si es canje) ---
            $subtotal = 0;
            foreach ($items as $i) { 
                $subtotal += floatval($i['subtotal']); // Usamos el subtotal que ya calculó el modelo con prom_id
            }

            $costoEnvio = 0.00;
            if ($data['tipo_entrega'] === 'DOMICILIO') {
                $calculo = $modelo->calcularCostoEnvioReal($items, $data['lat'], $data['lon']);
                $costoEnvio = $calculo['costo'];
            }
            $total = $subtotal + $costoEnvio;

            $codigoOrden = 'ORD-' . strtoupper(substr(uniqid(), -5));
            $tokenQr = Crypto::encriptar(['ord' => $codigoOrden, 'cli' => $cli_id]);

            $datosOrden = [
                'cli_id'       => $cli_id,
                'codigo'       => $codigoOrden,
                'token'        => $tokenQr,
                'subtotal'     => $subtotal,
                'envio'        => $costoEnvio,
                'total'        => $total,
                'tipo_entrega' => $data['tipo_entrega'],
                'direccion'    => $data['direccion'] ?? '',
                'referencia'   => $data['referencia'] ?? '',
                'lat'          => $data['lat'] ?? 0,
                'lon'          => $data['lon'] ?? 0
            ];

            $res = $modelo->registrarOrden($datosOrden, $items);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    // PÁGINA: MIS PEDIDOS (HISTORIAL)
    public function mis_pedidos() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        
        // Obtenemos todo el historial
        $todos = $modelo->obtenerHistorialPedidos($_SESSION['usuario_id']);
        
        $activos = [];
        $historial = [];

        foreach ($todos as $p) {
            // VERIFICAMOS SI YA CALIFICÓ
            $p['ya_calificado'] = $modelo->ordenYaCalificada($p['ord_id']); // <--- ESTO ES NUEVO

            // Incluimos ACEPTADO para que no se mueva al historial cuando un repartidor la toma
            if (in_array($p['ord_estado'], ['PENDIENTE', 'ACEPTADO', 'EN_CAMINO', 'LISTO_RETIRO', 'RETIRO', 'PAGADO'])) {
                $activos[] = $p;
            } else {
                $historial[] = $p;
            }
        }
        require_once __DIR__ . '/../views/publico/mis_pedidos.php';
    }


    // ==========================================================
    // PÁGINA: DETALLE DE ORDEN (EL MAPA Y EL QR)
    // ==========================================================
    public function orden_detalle() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Validar Login
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }
        
        $ord_id = $_GET['id'] ?? null;
        if (!$ord_id) { header('Location: index.php?c=publico&a=mis_pedidos'); exit; }

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());

        // 2. Obtener Datos de la Orden (Cabecera)
        $orden = $modelo->obtenerOrdenPorId($ord_id, $_SESSION['usuario_id']);

        if (!$orden) {
            header('Location: index.php?c=publico&a=mis_pedidos'); 
            exit;
        }

        // 3. Obtener Detalles (Productos y Sucursales)
        $detalles = $modelo->obtenerDetallesOrden($ord_id);

        // --- DESVÍO DE TRÁFICO: SI ES DOMICILIO, USA LA VISTA ROBUSTA ---
        if ($orden['ord_tipo_entrega'] === 'DOMICILIO') {
            require_once __DIR__ . '/../views/publico/orden_delivery.php'; // <--- NUEVO ARCHIVO
        } else {
            // SI ES RETIRO, USA LA VISTA SENCILLA QUE YA HICIMOS
            require_once __DIR__ . '/../views/publico/orden_detalle.php';
        }
    }


    // PÁGINA: MIS CITAS (LÓGICA CORREGIDA SIN DUPLICADOS)
    public function mis_citas() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php?c=auth&a=login'); exit;
        }

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        $cli_id = $_SESSION['usuario_id'];

        // 1. Limpieza de citas viejas
        $modelo->actualizarCitasPerdidas($cli_id);

        // 2. Obtener todas de la base de datos
        $todas = $modelo->obtenerMisCitas($cli_id);

        // 3. CLASIFICACIÓN ESTRICTA (Cajas Separadas)
        $citas_activas = [];   // En Atención (Lo que pasa YA)
        $citas_proximas = [];  // Confirmado o Reservado (Futuro)
        $citas_historial = []; // Pasado (Finalizado, Perdida, No Asistió)

        foreach ($todas as $c) {
            $est = $c['det_estado'];

            if ($est === 'EN_ATENCION') {
                $citas_activas[] = $c;
            } 
            elseif ($est === 'CONFIRMADO' || $est === 'RESERVADO') {
                $citas_proximas[] = $c;
            } 
            else {
                // Aquí caen: FINALIZADO, PERDIDA, NO_ASISTIO, CANCELADO
                $citas_historial[] = $c;
            }
        }

        // 4. ORDENAMIENTO VISUAL
        // Las activas y próximas: Primero lo más urgente (Fecha ASC)
        usort($citas_activas, function($a, $b) { return strtotime($a['det_ini']) - strtotime($b['det_ini']); });
        usort($citas_proximas, function($a, $b) { return strtotime($a['det_ini']) - strtotime($b['det_ini']); });
        
        // Historial: Primero lo más reciente (Fecha DESC)
        usort($citas_historial, function($a, $b) { return strtotime($b['det_ini']) - strtotime($a['det_ini']); });

        require_once __DIR__ . '/../views/publico/mis_citas.php';
    }

    // AJAX: GUARDAR CALIFICACIÓN
    public function guardar_calificacion_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        if (!$cli_id) {
            echo json_encode(['success' => false, 'error' => 'Sesión expirada']); exit;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // AQUI ESTA EL CAMBIO: Ya no obligamos. Si viene vacío, es 0.
        $data['voto_servicio']     = !empty($data['voto_servicio']) ? $data['voto_servicio'] : 0;
        $data['voto_especialista'] = !empty($data['voto_especialista']) ? $data['voto_especialista'] : 0;
        $data['voto_negocio']      = !empty($data['voto_negocio']) ? $data['voto_negocio'] : 0;
        
        $data['comentario'] = $data['comentario'] ?? '';

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());

            $data['cli_id'] = $cli_id;
            $res = $modelo->guardarCalificacion($data);

            if ($res) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al guardar.']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    // ====================================================================
    // AJAX: MONITOR DE RUTA (GPS) + DATOS PARA FACTURA
    // ====================================================================
    public function consultar_estado_ruta_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $ord_id = $_GET['id'] ?? null;
        if (!$ord_id) { echo json_encode(['success' => false, 'error' => 'ID faltante']); exit; }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());

            // 1. Datos Generales
            $orden = $modelo->obtenerOrdenPorId($ord_id, $cli_id);
            if (!$orden) { echo json_encode(['success' => false, 'error' => 'Orden no encontrada']); exit; }
            
            $detalles = $modelo->obtenerDetallesOrden($ord_id);
            
            // --- NUEVO: OBTENER LOS PAGOS REALIZADOS ---
            $todosLosPagos = $modelo->obtenerPagosOrden($ord_id); 

            // 2. Coordenadas Cliente
            $latCliente = $orden['ord_ubicacion_lat'] ?: -0.180653; 
            $lonCliente = $orden['ord_ubicacion_lon'] ?: -78.467834;

            $paradas = [];
            
            foreach ($detalles as $d) {
                $idSuc = $d['suc_id'];
                $idNeg = $d['neg_id']; // Necesitamos el ID del negocio para filtrar pagos
                
                if (!isset($paradas[$idSuc])) {
                    $dist = sqrt(pow($d['suc_latitud'] - $latCliente, 2) + pow($d['suc_longitud'] - $lonCliente, 2));
                    
                    // FILTRAR PAGOS: Solo los que pertenecen a ESTE negocio
                    $pagosDelNegocio = array_filter($todosLosPagos, function($p) use ($idNeg) {
                        return $p['neg_id'] == $idNeg;
                    });

                    $paradas[$idSuc] = [
                        'suc_id' => $idSuc,
                        'neg_id' => $idNeg, // Guardamos esto por si acaso
                        'lat' => $d['suc_latitud'], 
                        'lon' => $d['suc_longitud'], 
                        'distancia' => $dist,
                        'estado_parada' => 'COMPLETADO',
                        'info' => [
                            'nombre'    => $d['suc_nombre'],
                            'neg_nombre'=> $d['neg_nombre'], // Nombre del negocio principal
                            'logo'      => $d['neg_logo'],   // <--- AQUÍ VA EL LOGO
                            'neg_id'    => $d['neg_id'],
                            'direccion' => $d['suc_direccion']
                        ],
                        'productos' => [],
                        'pagos' => array_values($pagosDelNegocio) // <--- AQUÍ VA EL DETALLE DE PAGO
                    ];
                }

                if ($d['odet_estado'] !== 'RECOGIDO') {
                    $paradas[$idSuc]['estado_parada'] = 'PENDIENTE';
                }

                $paradas[$idSuc]['productos'][] = $d;
            }

            usort($paradas, function($a, $b) { return $a['distancia'] <=> $b['distancia']; });

            // ... (Resto de lógica del GPS igual) ...
            $latInicio = $latCliente;
            $lonInicio = $lonCliente;
            $puntosRuta = [];
            foreach ($paradas as $suc) {
                if ($suc['estado_parada'] === 'COMPLETADO') {
                    $latInicio = $suc['lat'];
                    $lonInicio = $suc['lon'];
                } else {
                    $puntosRuta[] = ['lat' => $suc['lat'], 'lon' => $suc['lon']];
                }
            }

            echo json_encode([
                'success' => true,
                'inicio' => ['lat' => $latInicio, 'lon' => $lonInicio],
                'ruta_pendiente' => $puntosRuta,
                'paradas' => $paradas,
                'estado_orden' => $orden['ord_estado'],
                'codigo_orden' => $orden['ord_codigo'],
                'fecha_orden'  => $orden['ord_fecha'],

                'costo_envio' => $orden['ord_costo_envio'],
                'total_pagar' => $orden['ord_total']
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    // AJAX: CANCELAR PEDIDO
    public function cancelar_pedido_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        if (!$cli_id) {
            echo json_encode(['success' => false, 'error' => 'Sesión expirada']);
            exit;
        }

        // Recibir ID por POST (JSON)
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $ord_id = $data['ord_id'] ?? null;

        if (!$ord_id) {
            echo json_encode(['success' => false, 'error' => 'ID faltante']);
            exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());

            // Llamar a la lógica de re-stocking
            $res = $modelo->cancelarOrdenUsuario($ord_id, $cli_id);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }


    public function procesar_resena_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;
        if (!$cli_id) { echo json_encode(['success'=>false, 'error'=>'Sesión expirada']); exit; }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());

            $paquete = [
                'cli_id' => $cli_id,
                'ord_id' => $data['ord_id'],
                'comentario' => $data['comentario'] ?? '',
                'negocios' => $data['negocios'] ?? [],
                'productos' => $data['productos'] ?? []
            ];

            $res = $modelo->guardarResenaMasiva($paquete);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
        }
        exit;
    }

    // AJAX: COTIZAR ENVÍO EN TIEMPO REAL
    public function cotizar_envio_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // VALIDACIÓN DE COORDENADAS
        if (empty($data['lat']) || empty($data['lon'])) {
             echo json_encode(['success' => false, 'error' => 'Coordenadas no recibidas']);
             exit;
        }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // Traer items del carrito (Ahora ya tendrán neg_id)
            $items = $modelo->obtenerCarrito($cli_id);
            
            // Calcular con los datos del JS
            $resultado = $modelo->calcularCostoEnvioReal($items, $data['lat'], $data['lon']);
            
            echo json_encode(['success' => true, 'costo' => $resultado['costo'], 'kms' => $resultado['kms']]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // AJAX: CEREBRO DEL RASTREO CLIENTE (VERSIÓN COMPLETA)
    public function consultar_ubicacion_driver_ajax() {
        error_reporting(0);
        ini_set('display_errors', 0);
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $ord_id = $_GET['id'] ?? null;
        if (!$ord_id) { echo json_encode(['success'=>false]); exit; }

        try {
            $db = new Database();
            $modelo = new PublicoModelo($db->getConnection());
            
            // 1. Obtener Info Básica y Posición
            $info = $modelo->obtenerEstadoYPosicionPedido($ord_id);
            
            // 2. Obtener Datos Financieros y QR (Reusamos obtenerOrdenPorId)
            // Asumimos que el usuario en sesión es el dueño, pero para el AJAX de rastreo 
            // a veces solo necesitamos validar el ID. 
            // NOTA: Si info existe, usamos esos datos para buscar la orden completa.
            $ordenFull = $modelo->obtenerOrdenPorId($ord_id, $_SESSION['usuario_id']);

            // 3. Obtener Paradas
            $detalles = $modelo->obtenerDetallesOrden($ord_id);
            
            $paradas = [];
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
                }
                // Agregamos productos para mostrar en el resumen
                $paradas[$idSuc]['productos'][] = [
                    'cantidad' => $d['odet_cantidad'],
                    'nombre' => $d['pro_nombre']
                ];
            }

            if ($info && $ordenFull) {
                echo json_encode([
                    'success' => true,
                    'estado'  => $info['ord_estado'],
                    'lat_driver' => $info['track_lat'],
                    'lon_driver' => $info['track_lon'],
                    'chofer' => $info['usu_nombres'],
                    'foto_chofer' => $info['usu_foto'], // <--- FOTO DEL CHOFER
                    
                    'costo_envio' => $ordenFull['ord_costo_envio'],
                    
                    'subtotal'    => $ordenFull['ord_subtotal'],
                    'total_pagar' => $ordenFull['ord_total'], // <--- TOTAL
                    'qr_token' => $ordenFull['ord_token_qr'], // <--- TOKEN QR
                    'paradas' => array_values($paradas)
                ]);
            } else {
                echo json_encode(['success' => false]);
            }

        } catch (Exception $e) {
            echo json_encode(['success'=>false]);
        }
        exit;
    }

    public function cancelar_cita_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cli_id = $_SESSION['usuario_id'] ?? null;
        if (!$cli_id) { echo json_encode(['success' => false, 'error' => 'Sesión expirada']); exit; }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $db = new Database();
        $modelo = new PublicoModelo($db->getConnection());
        echo json_encode($modelo->cancelarCitaUsuario($data['cita_id'], $cli_id));
        exit;
    }
}