<?php
// controllers/MetricasControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/MetricasModelo.php';

class MetricasControlador {
    
    private $modelo;
    private $negocio_id;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->negocio_id = $_SESSION['neg_id'] ?? $_SESSION['negocio_id'] ?? null;
        
        if (!$this->negocio_id) {
            echo json_encode(['success' => false, 'message' => 'Sesión de negocio no detectada.']);
            exit;
        }
        $db = new Database();
        $this->modelo = new MetricasModelo($db->getConnection());
    }

    public function resumen_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $f_ini = $_GET['f_ini'] ?? date('Y-m-01');
            $f_fin = $_GET['f_fin'] ?? date('Y-m-d');
            $f_ini_full = $f_ini . ' 00:00:00';
            $f_fin_full = $f_fin . ' 23:59:59';

            $ingresos = $this->modelo->obtenerIngresos($this->negocio_id, $f_ini_full, $f_fin_full);
            $datosGrafica = $this->modelo->obtenerGraficaIngresos($this->negocio_id, $f_ini_full, $f_fin_full);

            echo json_encode([
                'success' => true,
                'total' => number_format((float)$ingresos['total'], 2, '.', ''),
                'servicios' => number_format((float)$ingresos['total_servicios'], 2, '.', ''),
                'productos' => number_format((float)$ingresos['total_productos'], 2, '.', ''),
                'grafica' => $datosGrafica
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
        exit;
    }



    // ENDPOINT: Volumen de Citas (3 Estados)
    public function citas_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $f_ini = $_GET['f_ini'] ?? date('Y-m-01');
            $f_fin = $_GET['f_fin'] ?? date('Y-m-d');
            $f_ini_full = $f_ini . ' 00:00:00';
            $f_fin_full = $f_fin . ' 23:59:59';

            $citas = $this->modelo->obtenerVolumenCitas($this->negocio_id, $f_ini_full, $f_fin_full);
            $datosGrafica = $this->modelo->obtenerGraficaCitas($this->negocio_id, $f_ini_full, $f_fin_full);

            echo json_encode([
                'success' => true,
                'total' => $citas['total_validas'] ?? 0, // Suma exacta
                'finalizadas' => $citas['finalizadas'] ?? 0,
                'perdidas' => $citas['perdidas'] ?? 0,
                'canceladas' => $citas['canceladas'] ?? 0,
                'grafica' => $datosGrafica
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
        exit;
    }

    public function top_servicios_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        try {
            // Captura de fechas desde el JS
            $f_ini = ($_GET['f_ini'] ?? date('Y-m-01')) . ' 00:00:00';
            $f_fin = ($_GET['f_fin'] ?? date('Y-m-d')) . ' 23:59:59';

            $datos = $this->modelo->obtenerTopServicios($this->negocio_id, $f_ini, $f_fin);

            echo json_encode(['success' => true, 'datos' => $datos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
        exit;
    }

    public function ventas_cat_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        try {
            $f_ini = ($_GET['f_ini'] ?? date('Y-m-01')) . ' 00:00:00';
            $f_fin = ($_GET['f_fin'] ?? date('Y-m-d')) . ' 23:59:59';

            $datos = $this->modelo->obtenerVentasPorCategoria($this->negocio_id, $f_ini, $f_fin);

            echo json_encode(['success' => true, 'datos' => $datos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
        exit;
    }


    public function reportes() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 2) {
            header('Location: ' . ruta_accion('auth', 'panel'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Reportes y Nómina";

        $db = new Database();
        $modelo = new MetricasModelo($db->getConnection());
        
        // Datos para los filtros
        $sucursales = $modelo->obtenerListaSucursales($this->negocio_id);
        $empleados = $modelo->obtenerEmpleadosNegocio($this->negocio_id); // <-- NUEVO

        require __DIR__ . '/../views/metricas/reportes.php';
    }

    public function reportes_comisiones_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $f_ini = $_GET['f_ini'] ?? date('Y-m-01');
            $f_fin = $_GET['f_fin'] ?? date('Y-m-d');
            $usu_id = !empty($_GET['usu_id']) ? intval($_GET['usu_id']) : null; // <-- NUEVO FILTRO

            $datos = $this->modelo->reporteComisionesEmpleados($this->negocio_id, $f_ini, $f_fin, $usu_id);

            // --- MAGIA MATEMÁTICA: CÁLCULO DE MESES ---
            $fecha1 = new DateTime($f_ini);
            $fecha2 = new DateTime($f_fin);
            $mesesMultiplicador = (($fecha2->format('Y') - $fecha1->format('Y')) * 12) + ($fecha2->format('m') - $fecha1->format('m')) + 1;
            if ($mesesMultiplicador < 1) $mesesMultiplicador = 1; 

            foreach ($datos as &$empleado) {
                $empleado['sueldo_base'] = floatval($empleado['sueldo_base']) * $mesesMultiplicador;
            }

            echo json_encode(['success' => true, 'datos' => $datos, 'meses_calculados' => $mesesMultiplicador]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ====================================================================
    // AJAX: REPORTE DE INVENTARIO POR SUCURSAL
    // ====================================================================
    public function reportes_stock_ajax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $suc_id = !empty($_GET['suc_id']) ? intval($_GET['suc_id']) : null;

            $datos = $this->modelo->reporteStockGlobal($this->negocio_id, $suc_id);
            echo json_encode(['success' => true, 'datos' => $datos]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}