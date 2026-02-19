<?php
// controllers/ProductoControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../nucleo/helpers.php';
require_once __DIR__ . '/../nucleo/CloudinaryUploader.php';

class ProductoControlador {

    // ====================================================================
    // 1. LISTAR
    // ====================================================================
    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) { header('Location: ' . ruta_vista('panel.php')); exit; }

        global $pageTitle; $pageTitle = "Inventario Global";
        $negocioId = $_SESSION['negocio_id'];
        $busqueda = trim($_GET['q'] ?? '');

        $db = new Database(); $modelo = new ProductoModelo($db->getConnection());
        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaProductos = $modelo->listar($negocioId, $estadoDb, $busqueda);

        // Búsqueda inteligente (busca en la otra pestaña si no encuentra en la actual)
        if (!empty($busqueda) && empty($listaProductos)) {
            $otroFiltro = ($filtro === 'activos') ? 'inactivos' : 'activos';
            $otroEstado = ($estadoDb === 'A') ? 'I' : 'A';
            if (!empty($modelo->listar($negocioId, $otroEstado, $busqueda))) {
                $url = ruta_accion('producto', 'listar', ['filtro' => $otroFiltro]) . '&q=' . urlencode($busqueda);
                set_flash('¡Encontrado!', "El producto está en <b>$otroFiltro</b>.", 'info');
                header("Location: " . $url); exit;
            }
        }
        
        $filtroActual = $filtro;
        require __DIR__ . '/../views/producto/listar.php';
    }

    // ====================================================================
    // 2. CREAR (VISTA)
    // ====================================================================
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;
        
        global $pageTitle; $pageTitle = "Nuevo Producto";
        
        $db = new Database(); $modelo = new ProductoModelo($db->getConnection());
        $listaCategorias = $modelo->obtenerCategorias($_SESSION['negocio_id']);
        
        require __DIR__ . '/../views/producto/crear.php';
    }

    // ====================================================================
    // 3. GUARDAR (PROCESO CON FACTOR DE CONVERSIÓN)
    // ====================================================================
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        // A. Subir Fotos (Galería)
        $urlsGaleria = [];
        if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
            $files = $_FILES['fotos'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        $url = CloudinaryUploader::subirImagen($files['tmp_name'][$i], 'productos');
                        if ($url) $urlsGaleria[] = $url;
                    } catch (Exception $e) { continue; }
                }
            }
        }

        // B. Recoger Datos del Formulario
        $datos = [
            'neg_id'      => $_SESSION['negocio_id'],
            'tpro_id'     => $_POST['tpro_id'] ?? '',
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio'      => $_POST['precio'] ?? 0,
            'costo'       => $_POST['costo'] ?? 0,
            'stock'       => $_POST['stock'] ?? 0, // Stock Global
            'codigo'      => trim($_POST['codigo'] ?? ''),
            
            // [NUEVO] Lógica de Conversión (Caja vs Unidad)
            'unidad'      => $_POST['unidad'] ?? 'unidad',         // Unidad de Compra (Grande)
            'contenido'   => $_POST['contenido'] ?? 1,             // Cuánto trae (Factor)
            'unidad_consumo' => $_POST['unidad_consumo'] ?? 'unidad', // Unidad de Uso (Pequeña)

            'insumo'      => isset($_POST['insumo']) ? 1 : 0,
            'venta'       => isset($_POST['venta']) ? 1 : 0
        ];

        // Validaciones básicas
        if (empty($datos['nombre']) || empty($datos['tpro_id']) || empty($datos['codigo'])) {
            set_flash('Error', 'Nombre, Categoría y Código son obligatorios.', 'danger');
            header('Location: ' . ruta_accion('producto', 'crear'));
            exit;
        }

        $db = new Database(); $modelo = new ProductoModelo($db->getConnection());

        // Validar Código Único
        if ($modelo->existeCodigo($datos['codigo'], $datos['neg_id'])) {
            set_flash('Error', 'El código ingresado ya existe en otro producto.', 'danger');
            header('Location: ' . ruta_accion('producto', 'crear'));
            exit;
        }

        try {
            $modelo->guardar($datos, $urlsGaleria);
            set_flash('¡Creado!', 'Producto registrado en bodega.', 'success');
            header('Location: ' . ruta_accion('producto', 'listar'));
            exit;
        } catch (Exception $e) {
            set_flash('Error', 'Error al guardar: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('producto', 'crear'));
            exit;
        }
    }

    // ====================================================================
    // 4. EDITAR (VISTA)
    // ====================================================================
    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;
        
        global $pageTitle; $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database(); $modelo = new ProductoModelo($db->getConnection());
        $producto = $modelo->obtenerCompleto($id, $negocioId);

        if (!$producto) {
            set_flash('Error', 'Producto no encontrado.', 'danger');
            header('Location: ' . ruta_accion('producto', 'listar'));
            exit;
        }

        $pageTitle = "Editar: " . $producto['pro_nombre'];
        $listaCategorias = $modelo->obtenerCategorias($negocioId);
        
        require __DIR__ . '/../views/producto/editar.php';
    }

    // ====================================================================
    // 5. ACTUALIZAR (PROCESO)
    // ====================================================================
    public function actualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $id = $_POST['id'];
        $negocioId = $_SESSION['negocio_id'];

        // A. Nuevas fotos (Append)
        $nuevasUrls = [];
        if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
            $files = $_FILES['fotos'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        $url = CloudinaryUploader::subirImagen($files['tmp_name'][$i], 'productos');
                        if ($url) $nuevasUrls[] = $url;
                    } catch (Exception $e) { continue; }
                }
            }
        }

        // B. Recoger Datos Actualizados
        $datos = [
            'neg_id'      => $negocioId,
            'tpro_id'     => $_POST['tpro_id'],
            'nombre'      => trim($_POST['nombre']),
            'descripcion' => trim($_POST['descripcion']),
            'precio'      => $_POST['precio'],
            'costo'       => $_POST['costo'],
            'stock'       => $_POST['stock'],
            'codigo'      => trim($_POST['codigo']),
            
            // [NUEVO] Actualizar Factor de Conversión
            'unidad'      => $_POST['unidad'],
            'contenido'   => $_POST['contenido'] ?? 1,
            'unidad_consumo' => $_POST['unidad_consumo'] ?? 'unidad',

            'insumo'      => isset($_POST['insumo']) ? 1 : 0,
            'venta'       => isset($_POST['venta']) ? 1 : 0
        ];

        $db = new Database(); $modelo = new ProductoModelo($db->getConnection());

        // Validar Código al editar (Excluyendo el propio producto)
        if ($modelo->existeCodigo($datos['codigo'], $datos['neg_id'], $id)) {
            set_flash('Error', 'El código ya pertenece a otro producto.', 'danger');
            header('Location: ' . ruta_accion('producto', 'editar', ['id' => $id]));
            exit;
        }

        try {
            $modelo->actualizar($id, $datos, $nuevasUrls);
            set_flash('¡Actualizado!', 'Producto modificado.', 'success');
            header('Location: ' . ruta_accion('producto', 'listar'));
            exit;
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('producto', 'editar', ['id' => $id]));
            exit;
        }
    }

    // ====================================================================
    // 6. HERRAMIENTAS (Borrar Foto, Eliminar, Reactivar)
    // ====================================================================
    public function borrar_foto() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['negocio_id'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']); exit;
        }

        $imgId = $_POST['img_id'] ?? 0;
        $url   = $_POST['url'] ?? '';
        $negocioId = $_SESSION['negocio_id'];

        if (!$imgId) { echo json_encode(['success' => false]); exit; }

        $db = new Database(); $modelo = new ProductoModelo($db->getConnection());
        try {
            if ($url) CloudinaryUploader::eliminarImagen($url);
            $modelo->eliminarFoto($imgId, $negocioId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function eliminar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new ProductoModelo($db->getConnection());
            $modelo->eliminarLogico($id, $_SESSION['negocio_id']);
            set_flash('¡Eliminado!', 'Producto desactivado.', 'success');
        }
        header('Location: ' . ruta_accion('producto', 'listar')); exit;
    }

    public function reactivar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new ProductoModelo($db->getConnection());
            $modelo->reactivar($id, $_SESSION['negocio_id']);
            set_flash('¡Restaurado!', 'Producto activo.', 'success');
        }
        header('Location: ' . ruta_accion('producto', 'listar', ['filtro' => 'inactivos'])); exit;
    }
}