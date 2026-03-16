<?php
// Ajuste de zona horaria
date_default_timezone_set('America/Guayaquil');

require_once 'config/database.php';
require_once 'models/EspecialistaModelo.php';
require_once 'nucleo/helpers.php';

class EspecialistaControlador {
    private $modelo;

    public function __construct() {
        $db = new Database();
        $this->modelo = new EspecialistaModelo($db->getConnection());
    }

    // PANTALLA PRINCIPAL
    public function agenda() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad Rol 10
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 10) {
            header('Location: index.php'); exit;
        }

        $id = $_SESSION['usuario_id'];
        $urlCambio = ruta_accion('especialista', 'cambiar_estado');

        // 1. Citas HOY (Con receta y stock)
        $citasHoy = $this->modelo->obtenerAgendaDelDia($id);
        foreach ($citasHoy as &$c) {
            // MODIFICADO: Se pasa el suc_id de la cita al modelo para verificar stock
            $c['receta'] = $this->modelo->obtenerInsumosPorServicio($c['serv_id'], $c['suc_id']);
        }
        unset($c); 

        // 2. Citas FUTURAS (Con receta)
        $citasFuturas = $this->modelo->obtenerCitasFuturas($id);
        foreach ($citasFuturas as &$f) {
            // Para futuras no es crítico el stock actual, usamos un 0 temporal o podríamos ajustar el modelo de futuras también.
            // Por ahora, con 0 funciona porque no se muestra el botón de iniciar.
            $f['receta'] = $this->modelo->obtenerInsumosPorServicio($f['serv_id'], 0);
        }
        unset($f);

        require_once 'views/especialista/agenda.php';
    }

    // ACCIÓN AJAX: CAMBIAR ESTADO
    public function cambiar_estado() {
        // Limpiamos buffer por si acaso
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if(empty($data['id']) || empty($data['accion'])) {
            echo json_encode(['success'=>false, 'error'=>'Datos incompletos']); 
            exit;
        }

        $estado = ($data['accion'] == 'start') ? 'EN_ATENCION' : 'FINALIZADO';
        
        try {
            // Intentamos actualizar (El modelo puede lanzar Exception por falta de stock)
            if($this->modelo->actualizarEstado($data['id'], $estado)) {
                echo json_encode(['success'=>true]);
            } else {
                echo json_encode(['success'=>false, 'error'=>'No se pudo actualizar el estado en la base de datos.']);
            }
        } catch (Exception $e) {
            // AQUÍ CAPTURAMOS EL MENSAJE "STOCK INSUFICIENTE"
            echo json_encode(['success'=>false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // ACCIÓN AJAX: OBTENER GANANCIAS PARA EL DASHBOARD
    public function mis_ganancias_ajax() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión no válida']); exit;
        }

        $usu_id = $_SESSION['usuario_id'];
        $f_ini = $_GET['f_ini'] ?? date('Y-m-01');
        $f_fin = $_GET['f_fin'] ?? date('Y-m-d');

        try {
            $resumen = $this->modelo->obtenerMetricasComisiones($usu_id, $f_ini, $f_fin);
            $historial = $this->modelo->obtenerHistorialComisiones($usu_id, $f_ini, $f_fin);

            echo json_encode([
                'success' => true,
                'totales' => [
                    'comision' => floatval($resumen['total_comision'] ?? 0),
                    'servicios' => intval($resumen['total_servicios'] ?? 0),
                    'generado' => floatval($resumen['total_generado'] ?? 0)
                ],
                'historial' => $historial
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cargar ganancias']);
        }
        exit;
    }
}