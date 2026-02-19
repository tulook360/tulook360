<?php
require_once 'models/FidelidadModelo.php';

class FidelidadControlador {
    private $modelo;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->modelo = new FidelidadModelo($this->db->getConnection());
    }

    public function configuracion() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['negocio_id']) || $_SESSION['rol_id'] != 2) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $neg_id = $_SESSION['negocio_id'];
        $config = $this->modelo->obtenerConfiguracion($neg_id);
        
        $estaActivo = $config['fid_activa'] ?? 0;
        $diasVenc = $config['fid_dias_vencimiento'] ?? 180;

        global $pageTitle;
        $pageTitle = "Configurar Fidelidad";
        require_once 'views/fidelidad/configuracion.php';
    }

    // 1. AJAX PARA EL SWITCH (Rápido y Silencioso)
    public function ajax_cambiar_estado() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['negocio_id'])) {
            $input = json_decode(file_get_contents('php://input'), true);
            $neg_id = $_SESSION['negocio_id'];
            $estado = isset($input['activo']) && $input['activo'] == true ? 1 : 0;

            $res = $this->modelo->actualizarEstado($neg_id, $estado);
            echo json_encode(['success' => $res]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    // 2. POST PARA LOS DÍAS (Botón Guardar)
    public function guardar_dias() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['negocio_id'])) {
            $neg_id = $_SESSION['negocio_id'];
            $dias = intval($_POST['dias_vencimiento']);
            
            // Validación básica
            if($dias <= 0) $dias = 180;

            $res = $this->modelo->actualizarDias($neg_id, $dias);

            // IMPORTANTE: Recuperar el token para no perder la sesión al redirigir
            $token = $_GET['token'] ?? '';

            if ($res) {
                // Redirigir CON EL TOKEN
                header("Location: index.php?c=fidelidad&a=configuracion&token=$token&ok=1");
            } else {
                header("Location: index.php?c=fidelidad&a=configuracion&token=$token&err=1");
            }
        }
    }



    // --- NUEVAS FUNCIONES PARA EL CATÁLOGO DE PUNTOS ---

    // 3. VISTA: Mostrar la tabla de asignación
    public function asignar_puntos() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        // Seguridad: Solo Admin de Negocio
        if (!isset($_SESSION['negocio_id']) || $_SESSION['rol_id'] != 2) {
            header('Location: index.php?c=auth&a=login');
            exit;
        }

        $neg_id = $_SESSION['negocio_id'];
        
        // Obtenemos la lista completa mezclada (Productos + Servicios)
        $catalogo = $this->modelo->obtenerCatalogoPuntos($neg_id);

        global $pageTitle;
        $pageTitle = "Asignar Puntos";
        
        // Cargamos la vista (que diseñaremos en el siguiente paso)
        require_once 'views/fidelidad/asignar_puntos.php';
    }

    // 4. AJAX: Guardar los puntos de un ítem individualmente
    public function ajax_guardar_punto_item() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        // Limpiar buffer y header JSON
        ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['negocio_id'])) {
            // Recibimos los datos (JSON)
            $input = json_decode(file_get_contents('php://input'), true);
            
            $neg_id = $_SESSION['negocio_id'];
            $tipo = $input['tipo']; // 'PRODUCTO' o 'SERVICIO'
            $id = intval($input['id']);
            $puntos = intval($input['puntos']); // El valor que escribió el usuario

            if($puntos < 0) $puntos = 0; // Evitar negativos

            // Llamamos al modelo
            $res = $this->modelo->guardarPuntosItem($neg_id, $tipo, $id, $puntos);

            echo json_encode(['success' => $res]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
        }
        exit;
    }
}
?>