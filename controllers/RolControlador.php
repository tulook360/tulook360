<?php
// controllers/RolControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class RolControlador {

    // 1. LISTAR
    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        global $pageTitle;
        $pageTitle = "Gestión de Roles";

        $negocioId = $_SESSION['negocio_id'] ?? null;

        $db = new Database();
        $modelo = new RolModelo($db->getConnection());

        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaRoles = $modelo->listar($negocioId, $estadoDb);
        
        $filtroActual = $filtro;

        require __DIR__ . '/../views/rol/listar.php';
    }

    // 2. CREAR (Vista)
    public function crear() {
        global $pageTitle;
        $pageTitle = "Nuevo Rol";
        require __DIR__ . '/../views/rol/crear.php';
    }

    // 3. GUARDAR (Proceso)
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ruta_accion('rol', 'listar'));
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $negocioId = $_SESSION['negocio_id'] ?? null;

        if (empty($nombre)) {
            set_flash('Error', 'El nombre del rol es obligatorio.', 'danger');
            header('Location: ' . ruta_accion('rol', 'crear'));
            exit;
        }

        $db = new Database();
        $modelo = new RolModelo($db->getConnection());
        
        try {
            $modelo->guardar($nombre, $negocioId);
            
            // Mensaje de Éxito
            set_flash('¡Creado!', 'El rol ha sido registrado correctamente.', 'success');
            
            header('Location: ' . ruta_accion('rol', 'listar'));
            exit; // Importante: Detener ejecución

        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    // 4. EDITAR (Vista)
    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $pageTitle;
        
        $negocioId = $_SESSION['negocio_id'] ?? null;
        $db = new Database();
        $modelo = new RolModelo($db->getConnection());

        $rol = $modelo->obtenerPorId($id, $negocioId);

        if (!$rol) {
            set_flash('Acceso Denegado', 'No tienes permisos para editar este rol.', 'danger');
            header('Location: ' . ruta_accion('rol', 'listar'));
            exit;
        }
        
        $pageTitle = "Editar: " . $rol['rol_nombre'];
        require __DIR__ . '/../views/rol/editar.php';
    }

    // 5. ACTUALIZAR (Proceso)
    public function actualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ruta_accion('rol', 'listar'));
            exit;
        }

        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $negocioId = $_SESSION['negocio_id'] ?? null;

        if (empty($nombre)) {
            set_flash('Error', 'El nombre no puede estar vacío.', 'danger');
            header('Location: ' . ruta_accion('rol', 'editar', ['id' => $id]));
            exit;
        }

        $db = new Database();
        $modelo = new RolModelo($db->getConnection());
        $modelo->actualizar($id, $nombre, $negocioId);

        set_flash('¡Actualizado!', 'Los cambios del rol se guardaron con éxito.', 'success');
        header('Location: ' . ruta_accion('rol', 'listar'));
        exit; // Importante
    }

    // 6. ELIMINAR (Lógico)
    public function eliminar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $negocioId = $_SESSION['negocio_id'] ?? null;

        if ($id) {
            $db = new Database();
            $modelo = new RolModelo($db->getConnection());
            $modelo->eliminarLogico($id, $negocioId);
            
            set_flash('¡Desactivado!', 'El rol se ha enviado a la papelera.', 'success');
        }
        header('Location: ' . ruta_accion('rol', 'listar'));
        exit; // Importante
    }

    // 7. REACTIVAR
    public function reactivar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $negocioId = $_SESSION['negocio_id'] ?? null;

        if ($id) {
            $db = new Database();
            $modelo = new RolModelo($db->getConnection());
            $modelo->reactivar($id, $negocioId);
            
            set_flash('¡Restaurado!', 'El rol está activo y visible nuevamente.', 'success');
        }
        header('Location: ' . ruta_accion('rol', 'listar', ['filtro' => 'inactivos']));
        exit; // Importante
    }
}