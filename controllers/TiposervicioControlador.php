<?php
// controllers/TipoServicioControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TipoServicio.php'; // Importamos el nuevo modelo
require_once __DIR__ . '/../nucleo/helpers.php';

class TiposervicioControlador {

    // 1. LISTAR
    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Seguridad: Solo Admin de Negocio
        if (empty($_SESSION['negocio_id'])) {
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Categorías de Servicios";

        $negocioId = $_SESSION['negocio_id'];
        $db = new Database();
        $modelo = new TipoServicioModelo($db->getConnection());

        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaCategorias = $modelo->listar($negocioId, $estadoDb);
        
        $filtroActual = $filtro;
        
        // Cargamos la vista (que crearemos luego)
        require __DIR__ . '/../views/tipo_servicio/listar.php';
    }

    // 2. CREAR (Vista)
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;

        global $pageTitle;
        $pageTitle = "Nueva Categoría";

        require __DIR__ . '/../views/tipo_servicio/crear.php';
    }

    // 3. GUARDAR (Proceso)
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ruta_accion('tipoServicio', 'listar'));
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $negocioId = $_SESSION['negocio_id'];

        if (empty($nombre)) {
            set_flash('Error', 'El nombre de la categoría es obligatorio.', 'danger');
            header('Location: ' . ruta_accion('tipoServicio', 'crear'));
            exit;
        }

        $db = new Database();
        $modelo = new TipoServicioModelo($db->getConnection());

        try {
            $modelo->guardar($nombre, $negocioId);
            set_flash('¡Creado!', 'Categoría registrada correctamente.', 'success');
            header('Location: ' . ruta_accion('tipoServicio', 'listar'));
            exit;
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('tipoServicio', 'crear'));
            exit;
        }
    }

    // 4. EDITAR (Vista)
    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;

        global $pageTitle;
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $modelo = new TipoServicioModelo($db->getConnection());

        $categoria = $modelo->obtenerPorId($id, $negocioId);

        if (!$categoria) {
            set_flash('Error', 'Categoría no encontrada.', 'danger');
            header('Location: ' . ruta_accion('tipoServicio', 'listar'));
            exit;
        }

        $pageTitle = "Editar: " . $categoria['tser_nombre'];
        require __DIR__ . '/../views/tipo_servicio/editar.php';
    }

    // 5. ACTUALIZAR (Proceso)
    public function actualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $negocioId = $_SESSION['negocio_id'];

        if (empty($nombre)) {
            set_flash('Error', 'El nombre no puede estar vacío.', 'danger');
            header('Location: ' . ruta_accion('tipoServicio', 'editar', ['id' => $id]));
            exit;
        }

        $db = new Database();
        $modelo = new TipoServicioModelo($db->getConnection());

        try {
            $modelo->actualizar($id, $nombre, $negocioId);
            set_flash('¡Actualizado!', 'Cambios guardados.', 'success');
            header('Location: ' . ruta_accion('tipoServicio', 'listar'));
            exit;
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('tipoServicio', 'editar', ['id' => $id]));
            exit;
        }
    }

    // 6. ELIMINAR
    public function eliminar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new TipoServicioModelo($db->getConnection());
            $modelo->eliminarLogico($id, $_SESSION['negocio_id']);
            set_flash('¡Desactivada!', 'Categoría movida a la papelera.', 'success');
        }
        header('Location: ' . ruta_accion('tipoServicio', 'listar'));
        exit;
    }

    // 7. REACTIVAR
    public function reactivar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new TipoServicioModelo($db->getConnection());
            $modelo->reactivar($id, $_SESSION['negocio_id']);
            set_flash('¡Restaurada!', 'Categoría activa nuevamente.', 'success');
        }
        header('Location: ' . ruta_accion('tipoServicio', 'listar', ['filtro' => 'inactivos']));
        exit;
    }

    // AJAX: Guardar Categoría Rápida (Para el Wizard de Servicios)
    public function guardar_categoria_ajax() {
        ob_clean(); // Limpieza vital
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // 1. Validar Sesión
        if (empty($_SESSION['negocio_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
            exit;
        }

        // 2. Recibir Datos (JSON o POST normal)
        // Como usaremos FormData en JS, viene en $_POST
        $nombre = trim($_POST['nombre'] ?? '');

        if (empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Escribe un nombre']);
            exit;
        }

        $db = new Database();
        $modelo = new TipoServicioModelo($db->getConnection());

        try {
            // Guardamos
            if ($modelo->guardar($nombre, $_SESSION['negocio_id'])) {
                // Necesitamos el ID del registro nuevo para seleccionarlo en el front
                // Usamos lastInsertId del PDO (accedemos vía la conexión del modelo si es posible,
                // o hacemos una consulta rápida para traer el último creado con ese nombre).
                
                // Opción segura: Buscar el ID recién creado
                $dbPDO = $db->getConnection();
                $nuevoId = $dbPDO->lastInsertId();

                echo json_encode([
                    'success' => true, 
                    'id' => $nuevoId, 
                    'nombre' => $nombre
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo guardar']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}