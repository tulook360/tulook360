<?php
// controllers/CompraControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/CompraModelo.php';
require_once __DIR__ . '/../models/Producto.php'; // Necesitamos buscar productos para agregarlos
require_once __DIR__ . '/../nucleo/helpers.php';

class CompraControlador {

    // ====================================================================
    // 1. LISTAR (Historial de Compras)
    // ====================================================================
    public function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) { header('Location: ' . ruta_vista('panel.php')); exit; }

        global $pageTitle; 
        $pageTitle = "Ingresos de Mercadería";
        
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $modelo = new CompraModelo($db->getConnection());
        
        // Obtener historial
        $listaCompras = $modelo->listarHistorial($negocioId);
        
        require __DIR__ . '/../views/compra/listar.php';
    }

    // ====================================================================
    // 2. CREAR (Vista del Formulario "Carrito de Entrada")
    // ====================================================================
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;

        global $pageTitle; 
        $pageTitle = "Registrar Compra";
        
        // Necesitamos la lista de productos para el buscador del autocompletado
        // Ojo: Si son muchos productos, lo ideal sería hacerlo por AJAX.
        // Por ahora cargamos los activos para llenar el <select> o datalist.
        $db = new Database();
        $prodModelo = new ProductoModelo($db->getConnection());
        $listaProductos = $prodModelo->listar($_SESSION['negocio_id'], 'A'); 

        require __DIR__ . '/../views/compra/crear.php';
    }

    // ====================================================================
    // 3. GUARDAR (Procesar la Compra)
    // ====================================================================
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $negocioId = $_SESSION['negocio_id'];
        $usuarioId = $_SESSION['usuario_id']; // El que registra (Admin o encargado)

        // A. Recoger Datos de Cabecera
        $datosCabecera = [
            'neg_id'      => $negocioId,
            'usu_id'      => $usuarioId,
            'proveedor'   => trim($_POST['proveedor'] ?? 'Consumidor Final'),
            'tipo_doc'    => $_POST['tipo_doc'] ?? 'NOTA_VENTA',
            'numero_doc'  => trim($_POST['numero_doc'] ?? ''),
            'observacion' => trim($_POST['observacion'] ?? ''),
            'total'       => $_POST['total_compra'] ?? 0 // Total calculado en JS
        ];

        // B. Recoger el Detalle (JSON del carrito)
        $detalleJson = $_POST['detalle_json'] ?? '[]';
        $arrayDetalles = json_decode($detalleJson, true);

        // C. Validaciones Básicas
        if (empty($arrayDetalles) || !is_array($arrayDetalles)) {
            set_flash('Error', 'No has agregado ningún producto a la compra.', 'danger');
            header('Location: ' . ruta_accion('compra', 'crear'));
            exit;
        }

        if ($datosCabecera['total'] <= 0) {
            set_flash('Error', 'El total de la compra no puede ser cero.', 'danger');
            header('Location: ' . ruta_accion('compra', 'crear'));
            exit;
        }

        // D. Manejo de la Foto (Evidencia)
        $archivoFoto = $_FILES['evidencia'] ?? null;

        // Validación de Seguridad: Si es "SIN_SOPORTE" (Calle), exigimos foto
        if ($datosCabecera['tipo_doc'] === 'SIN_SOPORTE' && (empty($archivoFoto['name']))) {
            set_flash('Alerta de Seguridad', 'Para compras informales, la <b>FOTO DEL PRODUCTO</b> es obligatoria.', 'warning');
            header('Location: ' . ruta_accion('compra', 'crear'));
            exit;
        }

        // E. Guardar usando el Modelo Transaccional
        $db = new Database();
        $modelo = new CompraModelo($db->getConnection());

        try {
            $idCompra = $modelo->registrarCompra($datosCabecera, $arrayDetalles, $archivoFoto);
            
            set_flash('¡Éxito!', 'Ingreso de mercadería registrado correctamente. Stock actualizado.', 'success');
            header('Location: ' . ruta_accion('compra', 'listar'));
            exit;

        } catch (Exception $e) {
            set_flash('Error Crítico', $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('compra', 'crear'));
            exit;
        }
    }

    // ====================================================================
    // 4. BUSCAR PRODUCTO (AJAX para el formulario)
    // ====================================================================
    // Esto servirá si decidimos hacer un buscador dinámico en la vista
    public function buscar_ajax() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        
        $q = $_GET['q'] ?? '';
        $negocioId = $_SESSION['negocio_id'];

        if (strlen($q) < 2) { echo json_encode([]); exit; }

        $db = new Database();
        $prodModelo = new ProductoModelo($db->getConnection());
        
        // Reutilizamos el listar con filtro de búsqueda
        // Nota: listar($negocioId, $estado, $busqueda)
        $resultados = $prodModelo->listar($negocioId, 'A', $q);
        
        // Filtramos solo los datos necesarios para el JSON
        $json = array_map(function($p) {
            return [
                'id' => $p['pro_id'],
                'text' => $p['pro_nombre'] . ' - ' . $p['pro_unidad'], // "Shampoo - Botella"
                'costo' => $p['pro_costo_compra'] ?? 0,
                'codigo' => $p['pro_codigo']
            ];
        }, $resultados);

        echo json_encode($json);
    }



    // ... dentro de CompraControlador ...

    // ====================================================================
    // 4. VER DETALLE (AJAX)
    // ====================================================================
    public function ver_detalle() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (empty($_SESSION['negocio_id'])) { 
            echo json_encode(['error' => 'Sesión expirada']); exit; 
        }

        $idCompra = $_GET['id'] ?? 0;
        
        $db = new Database();
        $modelo = new CompraModelo($db->getConnection());
        
        try {
            $detalles = $modelo->obtenerDetalleCompra($idCompra);
            echo json_encode(['success' => true, 'datos' => $detalles]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}