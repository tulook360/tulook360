<?php
// controllers/AccionControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Accion.php';
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class AccionControlador {

    // 1. LISTAR
    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $pageTitle;
        $pageTitle = "Gestión de Acciones";

        $db = new Database();
        $modelo = new AccionModelo($db->getConnection());

        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaAcciones = $modelo->listar($estadoDb);
        $filtroActual = $filtro;

        require __DIR__ . '/../views/accion/listar.php';
    }

    // 2. CREAR
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $pageTitle;
        $pageTitle = "Nueva Acción";

        $db = new Database();
        $menuModelo = new MenuModelo($db->getConnection());
        $accionModelo = new AccionModelo($db->getConnection());
        
        $listaCarpetas = $menuModelo->listar('A');
        $listaPadres = $accionModelo->obtenerPadres();

        require __DIR__ . '/../views/accion/crear.php';
    }

    // 3. GUARDAR (Lógica Corregida)
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $db = new Database();
        $modelo = new AccionModelo($db->getConnection());

        $tipoAccion = $_POST['tipo_accion'] ?? 'padre';
        $padreId    = ($tipoAccion === 'hija') ? ($_POST['padre_id'] ?? null) : null;
        
        $controlador = '';
        if ($padreId) {
            $padre = $modelo->obtenerPorId($padreId);
            if($padre) $controlador = $padre['acc_controlador'];
        } else {
            $controlador = trim($_POST['controlador'] ?? '');
        }

        // --- CORRECCIÓN CRÍTICA AQUÍ ---
        // Leemos el valor numérico directo del input hidden (0 o 1)
        $visible = (int)($_POST['visible'] ?? 0);
        
        // Si es PADRE, forzamos visible = 1 (siempre debe verse en menú)
        if ($tipoAccion === 'padre') {
            $visible = 1;
        }

        // Lógica de menú: Solo guardamos menú si es visible
        $menuId = ($visible === 1) ? ($_POST['menu_id'] ?? '') : null;
        $menuId = ($menuId === '') ? null : $menuId;

        $datos = [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'controlador' => $controlador,
            'metodo'      => trim($_POST['metodo'] ?? ''),
            'icono'       => trim($_POST['icono'] ?? 'fa-circle'),
            'zona'        => $_POST['zona'] ?? 'NEG',
            'visible'     => $visible,
            'menu_id'     => $menuId,
            'padre_id'    => $padreId
        ];
        
        if (empty($datos['nombre']) || empty($datos['metodo']) || empty($datos['controlador'])) {
            set_flash('Error', 'Datos incompletos.', 'danger');
            header('Location: ' . ruta_accion('accion', 'crear'));
            exit;
        }

        try {
            $modelo->guardar($datos);
            set_flash('¡Creado!', 'Acción registrada correctamente.', 'success');
            header('Location: ' . ruta_accion('accion', 'listar'));
            exit;
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    // 4. EDITAR
    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $pageTitle;
        
        $db = new Database();
        $accionModelo = new AccionModelo($db->getConnection());
        $menuModelo = new MenuModelo($db->getConnection());

        $accion = $accionModelo->obtenerPorId($id);
        if (!$accion) {
            header('Location: ' . ruta_accion('accion', 'listar'));
            exit;
        }
        
        $pageTitle = "Editar: " . $accion['acc_nombre'];
        $listaCarpetas = $menuModelo->listar('A');
        $listaPadres = $accionModelo->obtenerPadres();

        require __DIR__ . '/../views/accion/editar.php';
    }

    // 5. ACTUALIZAR (Lógica Corregida)
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $db = new Database();
        $modelo = new AccionModelo($db->getConnection());

        $id = $_POST['id'];
        $tipoAccion = $_POST['tipo_accion'] ?? 'padre';
        $padreId    = ($tipoAccion === 'hija') ? ($_POST['padre_id'] ?? null) : null;
        
        $controlador = '';
        if ($padreId) {
            $padre = $modelo->obtenerPorId($padreId);
            if($padre) $controlador = $padre['acc_controlador'];
        } else {
            $controlador = trim($_POST['controlador'] ?? '');
        }

        // --- CORRECCIÓN CRÍTICA AQUÍ TAMBIÉN ---
        $visible = (int)($_POST['visible'] ?? 0);
        
        if ($tipoAccion === 'padre') {
            $visible = 1;
        }

        // Si lo ocultas ($visible = 0), forzamos menu_id a NULL
        $menuId = ($visible === 1) ? ($_POST['menu_id'] ?? '') : null;
        $menuId = ($menuId === '') ? null : $menuId;

        $datos = [
            'nombre'      => trim($_POST['nombre']),
            'controlador' => $controlador,
            'metodo'      => trim($_POST['metodo']),
            'icono'       => trim($_POST['icono']),
            'zona'        => $_POST['zona'],
            'visible'     => $visible,
            'menu_id'     => $menuId,
            'padre_id'    => $padreId
        ];
        
        if (empty($datos['nombre']) || empty($datos['metodo']) || empty($datos['controlador'])) {
            set_flash('Error', 'Datos incompletos al actualizar.', 'danger');
            header('Location: ' . ruta_accion('accion', 'editar', ['id'=>$id]));
            exit;
        }

        try {
            $modelo->actualizar($id, $datos);
            set_flash('¡Actualizado!', 'Cambios guardados correctamente.', 'success');
            header('Location: ' . ruta_accion('accion', 'listar'));
            exit;
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    // ... (eliminar y reactivar siguen igual) ...
    public function eliminar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id) {
            $db = new Database();
            $modelo = new AccionModelo($db->getConnection());
            $modelo->eliminarLogico($id);
            set_flash('¡Eliminado!', 'Acción desactivada.', 'success');
        }
        header('Location: ' . ruta_accion('accion', 'listar'));
        exit;
    }
    public function reactivar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id) {
            $db = new Database();
            $modelo = new AccionModelo($db->getConnection());
            $modelo->reactivar($id);
            set_flash('¡Restaurado!', 'Acción reactivada.', 'success');
        }
        header('Location: ' . ruta_accion('accion', 'listar', ['filtro' => 'inactivos']));
        exit;
    }
}