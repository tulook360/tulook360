<?php
// controllers/ServicioControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../nucleo/helpers.php';
require_once __DIR__ . '/../nucleo/CloudinaryUploader.php';

class ServicioControlador {

    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) { header('Location: ' . ruta_vista('panel.php')); exit; }

        global $pageTitle; $pageTitle = "Mis Servicios";
        $negocioId = $_SESSION['negocio_id'];
        $busqueda = trim($_GET['q'] ?? '');

        $db = new Database(); 
        $modelo = new ServicioModelo($db->getConnection());
        
        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaServicios = $modelo->listar($negocioId, $estadoDb, $busqueda);

        if (!empty($busqueda) && empty($listaServicios)) {
            $otroFiltro = ($filtro === 'activos') ? 'inactivos' : 'activos';
            $otroEstado = ($estadoDb === 'A') ? 'I' : 'A';
            if (!empty($modelo->listar($negocioId, $otroEstado, $busqueda))) {
                $url = ruta_accion('servicio', 'listar', ['filtro' => $otroFiltro]) . '&q=' . urlencode($busqueda);
                set_flash('¡Encontrado!', "En la lista de <b>$otroFiltro</b>.", 'info');
                header("Location: " . $url); exit;
            }
        }
        $filtroActual = $filtro;
        require __DIR__ . '/../views/servicio/listar.php';
    }

    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;
        global $pageTitle; $pageTitle = "Nuevo Servicio";
        
        $db = new Database(); $modelo = new ServicioModelo($db->getConnection());
        $info = $modelo->obtenerDatosFormulario($_SESSION['negocio_id']);
        
        $listaCategorias = $info['categorias'];
        $listaSucursales = $info['sucursales'];
        $listaInsumos    = $info['insumos'];
        
        require __DIR__ . '/../views/servicio/crear.php';
    }

    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $urlsGaleria = [];
        if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
            $files = $_FILES['fotos'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        $url = CloudinaryUploader::subirImagen($files['tmp_name'][$i], 'servicios');
                        if ($url) $urlsGaleria[] = $url;
                    } catch (Exception $e) { continue; }
                }
            }
        }

        $datos = [
            'neg_id'      => $_SESSION['negocio_id'],
            'tser_id'     => $_POST['tser_id'] ?? '',
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'resumen'     => trim($_POST['resumen'] ?? ''),
            'precio'      => $_POST['precio'] ?? 0,
            'duracion'    => $_POST['duracion'] ?? 0,
            'espera'      => $_POST['espera'] ?? 0
        ];

        $sucursalesSeleccionadas = $_POST['sucursales'] ?? [];
        $preciosEspeciales = $_POST['precio_sucursal'] ?? [];
        $asignaciones = [];
        foreach ($sucursalesSeleccionadas as $sucId) {
            $asignaciones[$sucId] = $preciosEspeciales[$sucId] ?? null;
        }

        $insumosRaw = $_POST['insumos'] ?? [];
        $insumosProcesados = [];
        foreach ($insumosRaw as $proId => $cantidad) {
            if (is_numeric($cantidad) && $cantidad > 0) {
                $insumosProcesados[$proId] = $cantidad;
            }
        }

        if (empty($datos['nombre']) || empty($datos['tser_id']) || empty($datos['precio'])) {
            set_flash('Error', 'Datos incompletos.', 'danger');
            header('Location: ' . ruta_accion('servicio', 'crear')); exit;
        }

        $db = new Database(); 
        $modelo = new ServicioModelo($db->getConnection());

        try {
            $modelo->guardar($datos, $asignaciones, $insumosProcesados, $urlsGaleria);
            set_flash('¡Creado!', 'Servicio registrado correctamente.', 'success');
            header('Location: ' . ruta_accion('servicio', 'listar'));
        } catch (Exception $e) {
            set_flash('Error', 'Error: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('servicio', 'crear'));
        }
        exit;
    }

    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;
        global $pageTitle; $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database(); 
        $modelo = new ServicioModelo($db->getConnection());
        $servicio = $modelo->obtenerCompleto($id, $negocioId);

        if (!$servicio) {
            set_flash('Error', 'No encontrado.', 'danger');
            header('Location: ' . ruta_accion('servicio', 'listar')); exit;
        }

        $pageTitle = "Editar: " . $servicio['serv_nombre'];
        $info = $modelo->obtenerDatosFormulario($negocioId);
        
        $listaCategorias = $info['categorias'];
        $listaSucursales = $info['sucursales'];
        $listaInsumos    = $info['insumos'];
        
        require __DIR__ . '/../views/servicio/editar.php';
    }

    public function actualizar($id = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $id = $_POST['id'] ?? $id; 
        $negocioId = $_SESSION['negocio_id'];

        $nuevasUrls = [];
        if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
            $files = $_FILES['fotos'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        $url = CloudinaryUploader::subirImagen($files['tmp_name'][$i], 'servicios');
                        if ($url) $nuevasUrls[] = $url;
                    } catch (Exception $e) { continue; }
                }
            }
        }

        $datos = [
            'neg_id'      => $negocioId,
            'tser_id'     => $_POST['tser_id'],
            'nombre'      => trim($_POST['nombre']),
            'descripcion' => trim($_POST['descripcion']),
            'resumen'     => trim($_POST['resumen']),
            'precio'      => $_POST['precio'],
            'duracion'    => $_POST['duracion'],
            'espera'      => $_POST['espera']
        ];

        $sucursalesSeleccionadas = $_POST['sucursales'] ?? [];
        $preciosEspeciales = $_POST['precio_sucursal'] ?? [];
        $asignaciones = [];
        foreach ($sucursalesSeleccionadas as $sucId) {
            $asignaciones[$sucId] = $preciosEspeciales[$sucId] ?? null;
        }

        $insumosRaw = $_POST['insumos'] ?? [];
        $insumosProcesados = [];
        foreach ($insumosRaw as $proId => $cantidad) {
            if (is_numeric($cantidad) && $cantidad > 0) {
                $insumosProcesados[$proId] = $cantidad;
            }
        }

        $db = new Database(); 
        $modelo = new ServicioModelo($db->getConnection());

        try {
            $modelo->actualizar($id, $datos, $asignaciones, $insumosProcesados, $nuevasUrls);
            set_flash('¡Actualizado!', 'Servicio modificado.', 'success');
            header('Location: ' . ruta_accion('servicio', 'listar'));
        } catch (Exception $e) {
            set_flash('Error', 'Error: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('servicio', 'editar', ['id' => $id]));
        }
        exit;
    }

    // 6. BORRAR FOTO (IDENTICO A PRODUCTOS - USANDO MODELO)
    public function borrar_foto() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['negocio_id'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']); exit;
        }

        $imgId = $_POST['img_id'] ?? 0;
        $url   = $_POST['url'] ?? '';
        $negocioId = $_SESSION['negocio_id'];

        if (!$imgId) { echo json_encode(['success' => false, 'message' => 'Falta ID']); exit; }

        $db = new Database(); 
        $modelo = new ServicioModelo($db->getConnection());
        
        try {
            if ($url) CloudinaryUploader::eliminarImagen($url);
            $modelo->eliminarFoto($imgId, $negocioId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function eliminar($id = null) {
        $id = $id ?? $_GET['id'] ?? null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new ServicioModelo($db->getConnection());
            $modelo->eliminarLogico($id, $_SESSION['negocio_id']);
            set_flash('¡Eliminado!', 'Servicio en papelera.', 'success');
        }
        header('Location: ' . ruta_accion('servicio', 'listar')); exit;
    }

    public function reactivar($id = null) {
        $id = $id ?? $_GET['id'] ?? null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new ServicioModelo($db->getConnection());
            $modelo->reactivar($id, $_SESSION['negocio_id']);
            set_flash('¡Restaurado!', 'Servicio activo.', 'success');
        }
        header('Location: ' . ruta_accion('servicio', 'listar', ['filtro' => 'inactivos'])); exit;
    }
}