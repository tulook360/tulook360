<?php
// controllers/PermisoControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Permiso.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class PermisoControlador {

    // 1. LISTAR (Corregido para Aislamiento)
    public function listar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        global $pageTitle;
        $pageTitle = "Gestión de Permisos";

        // Identificar quién soy
        $negocioId = $_SESSION['negocio_id'] ?? null;

        $db = new Database();
        $modelo = new PermisoModelo($db->getConnection());

        // Le pasamos mi ID al modelo para que filtre
        $listaRoles = $modelo->listarRolesConConteo($negocioId);

        require __DIR__ . '/../views/permiso/listar.php';
    }

    // 2. VER (SOLO LECTURA)
    public function ver($id) { 
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $pageTitle;
        $pageTitle = "Detalle de Permisos";

        $db = new Database();
        $pdo = $db->getConnection();
        $permisoModelo = new PermisoModelo($pdo);

        // Validar existencia del rol (básico)
        $stmt = $pdo->prepare("SELECT * FROM tbl_rol WHERE rol_id = ?");
        $stmt->execute([$id]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rol) {
            header('Location: ' . ruta_accion('permiso', 'listar'));
            exit;
        }

        // VERIFICAR SI SOY SUPER ADMIN
        // Si negocio_id es NULL, soy Super Admin -> Veo todo (true)
        // Si tengo negocio_id, soy Admin Negocio -> Veo restringido (false)
        $soySuperAdmin = empty($_SESSION['negocio_id']);

        $permisosActuales = $permisoModelo->obtenerPermisosDelRol($id);
        
        // Pasamos el flag de Super Admin
        $accionesAgrupadas = $permisoModelo->obtenerAccionesAgrupadas($soySuperAdmin);

        // Verificamos archivo vista
        if (file_exists(__DIR__ . '/../views/permiso/ver.php')) {
            require __DIR__ . '/../views/permiso/ver.php';
        } else {
            echo "Error: Falta vista ver.php";
        }
    }

    // 3. GESTIONAR (EDITAR)
    public function gestionar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        global $pageTitle;
        $pageTitle = "Editar Permisos";

        $db = new Database();
        $pdo = $db->getConnection();
        $permisoModelo = new PermisoModelo($pdo);

        $stmt = $pdo->prepare("SELECT * FROM tbl_rol WHERE rol_id = ?");
        $stmt->execute([$id]);
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rol) {
            header('Location: ' . ruta_accion('permiso', 'listar'));
            exit;
        }

        // VERIFICAR SI SOY SUPER ADMIN
        $soySuperAdmin = empty($_SESSION['negocio_id']);

        $permisosActuales = $permisoModelo->obtenerPermisosDelRol($id);
        
        // Pasamos el flag para que el modelo decida si mostrar SIS o no
        $accionesAgrupadas = $permisoModelo->obtenerAccionesAgrupadas($soySuperAdmin);

        require __DIR__ . '/../views/permiso/gestionar.php';
    }

    // 4. GUARDAR
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header('Location: ' . ruta_accion('permiso', 'listar'));
             exit;
        }

        $rolId = $_POST['rol_id'] ?? 0;
        $acciones = $_POST['acciones'] ?? [];

        $db = new Database();
        $modelo = new PermisoModelo($db->getConnection());
        
        try {
            $modelo->guardarPermisos($rolId, $acciones);
            set_flash('¡Guardado!', 'Permisos actualizados correctamente.');
            
            header('Location: ' . ruta_accion('permiso', 'ver', ['id' => $rolId]));
            exit;
            
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}