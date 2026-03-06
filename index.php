<?php
// index.php - Router Principal BLINDADO

require __DIR__ . '/config/env.php';
require __DIR__ . '/nucleo/Crypto.php';
require __DIR__ . '/nucleo/helpers.php';

session_start();

// 1. RECIBIR TOKEN O PARÁMETROS PÚBLICOS
$token = $_GET['token'] ?? null;

// --- EXCEPCIÓN DE SEGURIDAD (PUERTA TRASERA PARA AJAX PÚBLICO) ---
// Capturamos la acción que viene por URL
$accionSolicitada = $_GET['a'] ?? $_GET['m'] ?? '';

// Agregamos 'buscar_ajax' para permitir búsqueda sin login
$accionesPublicasPermitidas = [
    'verificar_correo_ajax', 
    'guardarRegistro', 
    'guardarCliente',
    'buscar_ajax',
    'listar_negocios_ajax',
    'solicitarRecuperacionAjax',
    'verificarCodigoRecuperacionAjax',
    'guardarNuevaPasswordAjax'
];

$esVerificacionPublica = (
    !$token && 
    isset($_GET['c']) && 
    ($_GET['c'] === 'auth' || $_GET['c'] === 'publico') && // <--- CAMBIO 1: Aceptamos ambos
    in_array($accionSolicitada, $accionesPublicasPermitidas)
);

if ($esVerificacionPublica) {
    // Creamos un payload manual para saltarnos la encriptación
    $payload = [
        'c' => $_GET['c'], // <--- CAMBIO 2: Usamos el que viene por URL (auth o publico)
        'a' => $accionSolicitada,
        'layout' => false // JSON puro
    ];
}
elseif (!$token) {
    // Si no hay token y no es la excepción, mostramos el Home
    require __DIR__ . '/views/index.php'; 
    exit;
} 
else {
    // Flujo normal con Token
    $payload = Crypto::desencriptar($token);
    
    if (!$payload) {
        http_response_code(400);
        
        // DETECCIÓN INTELIGENTE: Si piden JSON, respondemos JSON
        $pideJson = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) 
                    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

        if ($pideJson) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido o caducado.']);
        } else {
            echo "Solicitud inválida.";
        }
        exit;
    }
}

// 2. SOBRESCRIBIR RUTA (AJAX / URL)
// OJO: Agregué soporte para 'a' (action) que es lo que usas en el JS
if (isset($_GET['c']) && (isset($_GET['m']) || isset($_GET['a']))) {
    $payload['c'] = $_GET['c'];
    $payload['a'] = $_GET['m'] ?? $_GET['a']; // Prioridad a 'm', sino usa 'a'
}

// --- CORRECCIÓN IMPORTANTE: MANTENER MAYÚSCULAS ---
// Guardamos el nombre original para buscar el archivo correctamente (ej: TipoServicio)
$c_raw = $payload['c'] ?? 'inicio'; 
$c = strtolower($c_raw); // Versión minúscula para comparaciones
$a = $payload['a'] ?? 'index'; // La acción

$usarLayout = $payload['layout'] ?? true;

// =========================================================
// 🚨 LISTA BLANCA DE AJAX (JSON PURO)
// =========================================================
$accionesAjax = [
    'procesar_pedido',
    'actualizarFoto',
    'guardarDato',
    'cambiarContrasena',
    'ver_detalle_ajax',
    'cancelar_pedido',
    'cargar_despacho',
    'guardar_despacho',
    'confirmar_recepcion',
    'guardar_categoria_ajax',
    'borrar_foto',
    'verificar_correo_ajax',
    'buscar_ajax',
    'listar_negocios_ajax',
    'ver_perfil_negocio_ajax',
    'ver_detalle_servicio_ajax',
    'cargar_mas_servicios_ajax',
    'ver_info_producto_modal_ajax',
    'agregar_carrito_ajax',
    'ver_carrito_ajax',
    'actualizar_carrito_ajax',
    'eliminar_carrito_ajax',
    'eliminar_carrito_ajax',
    'procesar_compra_ajax',
    'buscar_cita_ajax',
    'confirmar_cita_ajax',
    'confirmar_cita_ajax',
    'ajax_cambiar_estado',
    'ajax_guardar_punto_item',
    'guardar_calificacion_ajax',
    'consultar_estado_ruta_ajax',
    'cancelar_pedido_ajax',
    'procesar_resena_ajax',
    'consultar_ofertas_json',
    'aceptar_oferta_ajax',
    'cotizar_envio_ajax',
    'actualizar_ubicacion_ajax',
    'ajax_cargar_items',
    'guardar_promo_ajax',
    'eliminar_promo_ajax',
    'cambiar_estado_promo_ajax',
    'cancelar_cita_ajax',
    'reanudar_promo_ajax',
    'solicitarRecuperacionAjax',
    'verificarCodigoRecuperacionAjax',
    'guardarNuevaPasswordAjax'
];

// SI ES AJAX, ASEGURARNOS DE NO CARGAR LAYOUT HTML
$esAjax = in_array($a, $accionesAjax);

if ($esAjax) {
    $usarLayout = false;
    header('Content-Type: application/json'); // Forzamos cabecera JSON
}

// 3. BUFFER
ob_start();
$encontrado = false;

try {
    // A) VISTA DIRECTA
    if (isset($payload['vista'])) {
        $vista = $payload['vista'];
        if (str_contains($vista, '..')) throw new Exception("Ruta inválida");
        $archivoVista = __DIR__ . "/views/$vista";
        if (file_exists($archivoVista)) {
            require $archivoVista;
            $encontrado = true;
        }
    }
    // B) CONTROLADOR
    elseif (isset($payload['c']) && isset($payload['a'])) {
        
        $rutaActual = "$c/$a";
        
        // --- CANDADO DE SEGURIDAD ---
        $rutasPublicas = [
            'auth/login',
            'auth/validar', 
            'auth/salir', 
            'auth/registro', 
            'auth/guardarRegistro',
            'auth/verificar_correo_ajax',
            'publico/buscar_ajax',
            'auth/registroCliente',
            'auth/guardarCliente',
            'publico/consultar_estado_ruta_ajax',
            'publico/cancelar_pedido_ajax',
            'publico/procesar_resena_ajax',
            'auth/recuperarAccount', 
            'auth/solicitarRecuperacionAjax', 
            'auth/verificarCodigoRecuperacionAjax', // <--- ¡ESTA ES LA QUE FALTABA!
            'auth/guardarNuevaPasswordAjax' 
        ];

        if (!in_array($rutaActual, $rutasPublicas)) {
            if (!isset($_SESSION['usuario_id'])) {
                if($esAjax) { echo json_encode(['success'=>false, 'message'=>'Sesión expirada']); exit; }
                header('Location: index.php'); exit;
            }

            // Validar permiso (probamos con minúscula y con original por si acaso)
            if (!tiene_permiso($c, $a) && !tiene_permiso($c_raw, $a)) { 
                ob_clean();
                if($esAjax) {
                    echo json_encode(['success'=>false, 'message'=>'No tienes permiso para esta acción.']);
                    exit;
                }
                require __DIR__ . '/views/403.php';
                $usarLayout = true;
                goto render; 
            }
        }

        // --- CARGA DE CONTROLADOR (CORREGIDA) ---
        
        // 1. Intentamos con el nombre Exacto (TipoServicioControlador)
        $controladorNombre = ucfirst($c_raw) . 'Controlador'; 
        $archivo = __DIR__ . "/controllers/$controladorNombre.php";

        // 2. Si no existe, probamos con minúsculas (Fallback)
        if (!file_exists($archivo)) {
            $controladorNombre = ucfirst($c) . 'Controlador';
            $archivo = __DIR__ . "/controllers/$controladorNombre.php";
        }

        $accion = $a; 
        $params = $payload['params'] ?? [];

        if (file_exists($archivo)) {
            require_once $archivo;
            if (class_exists($controladorNombre)) {
                $obj = new $controladorNombre();
                
                if (method_exists($obj, $accion)) {
                    // LIMPIEZA TOTAL ANTES DE EJECUTAR AJAX
                    if ($esAjax) ob_clean(); 
                    
                    call_user_func_array([$obj, $accion], $params);
                    
                    if ($esAjax) exit; // MATAR SCRIPT SI ES AJAX (Evita HTML al final)
                    $encontrado = true;
                } else {
                    if($esAjax) { echo json_encode(['success'=>false, 'message'=>"Método '$accion' no encontrado."]); exit; }
                }
            }
        } else {
            // Si no encuentra el archivo y es AJAX, devuelve error JSON, NO HTML
            if($esAjax) { echo json_encode(['success'=>false, 'message'=>"Controlador no encontrado: $controladorNombre"]); exit; }
        }
    }

    if (!$encontrado) {
        ob_clean();
        if($esAjax) { echo json_encode(['success'=>false, 'message'=>"Ruta no encontrada (404)."]); exit; }
        require __DIR__ . '/views/error.php'; // 404
        $usarLayout = true;
    }

} catch (Throwable $e) {
    ob_clean();
    if($esAjax) {
        echo json_encode(['success'=>false, 'message'=>'Error del Sistema: ' . $e->getMessage()]);
        exit;
    }
    echo "<div style='padding:50px;'><h1 style='color:red;'>Error del Sistema</h1><p>".$e->getMessage()."</p></div>";
    exit;
}

// 4. RENDERIZAR (Solo llega aquí si NO es AJAX)
// 4. RENDERIZAR DINÁMICO
render:
$layout_contenido_html = ob_get_clean();

if ($usarLayout) {
    // Detectamos si es Cliente (Rol 4)
    $esCliente = (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 4);

    if ($esCliente) {
        // Layout Premium para Clientes
        require __DIR__ . '/views/layouts/header_cliente.php';
        echo $layout_contenido_html;
        require __DIR__ . '/views/layouts/footer_cliente.php';
    } else {
        // Layout Tradicional para Negocios/Admin
        require __DIR__ . '/views/layouts/header.php';
        echo $layout_contenido_html;
        require __DIR__ . '/views/layouts/footer.php';
    }
} else {
    // Respuesta sin layout (AJAX o Vistas limpias)
    echo $layout_contenido_html;
}