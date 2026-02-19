<?php
// controllers/TipoProductoControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TipoProducto.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class TipoProductoControlador {

    // 1. LISTAR
    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) { header('Location: ' . ruta_vista('panel.php')); exit; }

        global $pageTitle; $pageTitle = "Tipos de Producto";
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $modelo = new TipoProductoModelo($db->getConnection());

        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaTipos = $modelo->listar($negocioId, $estadoDb);
        $filtroActual = $filtro;
        
        require __DIR__ . '/../views/tipo_producto/listar.php';
    }

    // 2. CREAR (Vista normal)
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;
        global $pageTitle; $pageTitle = "Nuevo Tipo";
        require __DIR__ . '/../views/tipo_producto/crear.php';
    }

    // 3. GUARDAR (Post normal)
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            set_flash('Error', 'Nombre obligatorio.', 'danger');
            header('Location: ' . ruta_accion('tipoProducto', 'crear')); exit;
        }

        $db = new Database();
        $modelo = new TipoProductoModelo($db->getConnection());

        try {
            $modelo->guardar($nombre, $_SESSION['negocio_id']);
            set_flash('¡Listo!', 'Categoría creada.', 'success');
            header('Location: ' . ruta_accion('tipoProducto', 'listar'));
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('tipoProducto', 'crear'));
        }
    }

    // 4. GUARDAR AJAX (ESTA ES LA QUE USA TU MODAL)
    public function guardar_categoria_ajax() {
        ob_clean(); // Limpiar buffer
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['negocio_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión expirada']); exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Escribe un nombre']); exit;
        }

        $db = new Database();
        $conn = $db->getConnection();
        $modelo = new TipoProductoModelo($conn);

        try {
            if ($modelo->guardar($nombre, $_SESSION['negocio_id'])) {
                // Obtenemos el ID recién creado para devolverlo al JS
                $nuevoId = $conn->lastInsertId();
                echo json_encode(['success' => true, 'id' => $nuevoId, 'nombre' => $nombre]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo guardar']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ==========================================================
    // 4. EDITAR (Vista)
    // ==========================================================
    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;

        global $pageTitle;
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $modelo = new TipoProductoModelo($db->getConnection());

        $tipo = $modelo->obtenerPorId($id, $negocioId);

        if (!$tipo) {
            set_flash('Error', 'Categoría no encontrada.', 'danger');
            header('Location: ' . ruta_accion('tipoProducto', 'listar'));
            exit;
        }

        $pageTitle = "Editar: " . $tipo['tpro_nombre'];
        require __DIR__ . '/../views/tipo_producto/editar.php';
    }

    // ==========================================================
    // 5. ACTUALIZAR (Proceso)
    // ==========================================================
    public function actualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $negocioId = $_SESSION['negocio_id'];

        if (empty($nombre)) {
            set_flash('Error', 'El nombre no puede estar vacío.', 'danger');
            header('Location: ' . ruta_accion('tipoProducto', 'editar', ['id' => $id]));
            exit;
        }

        $db = new Database();
        $modelo = new TipoProductoModelo($db->getConnection());

        try {
            $modelo->actualizar($id, $nombre, $negocioId);
            set_flash('¡Actualizado!', 'Cambios guardados.', 'success');
            header('Location: ' . ruta_accion('tipoProducto', 'listar'));
            exit;
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('tipoProducto', 'editar', ['id' => $id]));
            exit;
        }
    }

    // ==========================================================
    // 6. ELIMINAR (Papelera)
    // ==========================================================
    public function eliminar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new TipoProductoModelo($db->getConnection());
            $modelo->eliminarLogico($id, $_SESSION['negocio_id']);
            set_flash('¡Eliminado!', 'Categoría movida a la papelera.', 'success');
        }
        header('Location: ' . ruta_accion('tipoProducto', 'listar'));
        exit;
    }

    // ==========================================================
    // 7. REACTIVAR
    // ==========================================================
    public function reactivar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new TipoProductoModelo($db->getConnection());
            $modelo->reactivar($id, $_SESSION['negocio_id']);
            set_flash('¡Restaurado!', 'Categoría activa nuevamente.', 'success');
        }
        header('Location: ' . ruta_accion('tipoProducto', 'listar', ['filtro' => 'inactivos']));
        exit;
    }
}