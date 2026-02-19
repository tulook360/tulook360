<?php
require_once 'models/PromocionModelo.php';

class PromocionControlador {
    private $modelo;

    public function __construct() {
        $db = new Database();
        $this->modelo = new PromocionModelo($db->getConnection());
        
        // Seguridad básica
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            header('Location: index.php'); exit;
        }
        // Recuperar ID de negocio si falta
        if (!isset($_SESSION['neg_id']) && isset($_SESSION['usuario_id'])) {
            $stmt = $db->getConnection()->prepare("SELECT neg_id FROM tbl_usuario WHERE usu_id = :uid");
            $stmt->execute([':uid' => $_SESSION['usuario_id']]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) $_SESSION['neg_id'] = $res['neg_id'];
        }
    }

    // --- AQUÍ ESTABA EL ERROR DE LA PANTALLA ROJA ---
    // Agregamos $id = null para que si usas un token con ID, la función lo acepte y lo ignore.
    public function listar($filtro = 'activos', $id = null) { 
        if (!isset($_SESSION['neg_id'])) { echo "Error de sesión"; exit; }

        $filtroActual = $filtro; 
        $neg_id = $_SESSION['neg_id'];

        $promos = $this->modelo->listarPorNegocio($neg_id, $filtroActual);
        
        require_once 'views/promociones/listar.php';
    }

    // ... (ajax_cargar_items, crear, guardar_promo_ajax se quedan igual) ...
    public function ajax_cargar_items() {
        $items = $this->modelo->obtenerItemsDisponibles($_SESSION['neg_id'], $_GET['tipo'] ?? 'SERVICIO');
        echo json_encode(['success' => true, 'data' => $items]);
    }

    public function crear() {
        require_once 'views/promociones/crear.php';
    }

    public function guardar_promo_ajax() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) { echo json_encode(['success' => false]); exit; }
        $data['neg_id'] = $_SESSION['neg_id'];
        echo json_encode($this->modelo->guardarCompleto($data));
    }


    public function editar($id = null) {
        if (!$id) $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: " . ruta_accion('promocion', 'listar')); exit; }

        $promo = $this->modelo->obtenerPorId($id);
        if (!$promo) die("Promoción no encontrada.");
        
        include "views/promociones/editar.php";
    }

    // --- Y ESTA ES LA PROTECCIÓN PARA EL AJAX ---
    public function actualizar_promo_ajax($id = null, $token = null) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        if (!tiene_permiso('promocion', 'editar')) {
            echo json_encode(['success' => false, 'error' => 'No tienes permiso.']);
            exit;
        }

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data && !empty($_POST)) $data = $_POST;
            if (empty($data)) throw new Exception("Sin datos.");

            $data['neg_id'] = $_SESSION['neg_id'];
            
            // Validaciones vitales
            $data['fecha_ini'] = !empty($data['fecha_ini']) ? $data['fecha_ini'] : date('Y-m-d');
            
            // Corrección para CUPOS vs FECHAS
            if (isset($data['tipo_vigencia']) && $data['tipo_vigencia'] === 'CUPOS') {
                $data['fecha_fin'] = null;
            } else {
                $data['fecha_fin'] = !empty($data['fecha_fin']) ? $data['fecha_fin'] : null;
            }

            $data['limite'] = !empty($data['limite']) ? intval($data['limite']) : 0;
            $data['puntos_req'] = !empty($data['puntos_req']) ? intval($data['puntos_req']) : 0;
            $data['precio_oferta'] = !empty($data['precio_oferta']) ? floatval($data['precio_oferta']) : 0;

            $res = $this->modelo->actualizarCompleto($data);
            echo json_encode($res);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    public function eliminar_promo_ajax() {
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        $res = $this->modelo->desactivar($data['id'], $_SESSION['neg_id']);
        echo json_encode(['success' => $res]);
        exit;
    }

    public function reanudar_promo_ajax() {
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        
        // Si es por cupos, la fecha fin puede ser null
        $f_fin = !empty($data['f_fin']) ? $data['f_fin'] : null;
        
        $res = $this->modelo->reanudar($data['id'], $_SESSION['neg_id'], $data['f_ini'], $f_fin);
        echo json_encode(['success' => $res]);
        exit;
    }
}