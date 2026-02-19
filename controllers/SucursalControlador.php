<?php
// controllers/SucursalControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../nucleo/helpers.php';
require_once __DIR__ . '/../nucleo/CloudinaryUploader.php'; // [IMPORTANTE] Para subir fotos

class SucursalControlador {

    // 1. LISTAR
    public function listar($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['negocio_id'])) {
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Mis Sucursales";

        // 1. Capturar búsqueda
        $busqueda = trim($_GET['q'] ?? '');
        
        $negocioId = $_SESSION['negocio_id'];
        $db = new Database();
        $modelo = new SucursalModelo($db->getConnection());

        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        
        // 2. Buscar en la pestaña actual
        $listaSucursales = $modelo->listar($negocioId, $estadoDb, $busqueda);
        
        // 3. Lógica de "Búsqueda Inteligente" (Si no está aquí, buscar en la otra pestaña)
        if (!empty($busqueda) && empty($listaSucursales)) {
            $otroEstado = ($estadoDb === 'A') ? 'I' : 'A';
            $otroFiltro = ($filtro === 'activos') ? 'inactivos' : 'activos';
            
            $resultadosOtro = $modelo->listar($negocioId, $otroEstado, $busqueda);
            
            if (!empty($resultadosOtro)) {
                $url = ruta_accion('sucursal', 'listar', ['filtro' => $otroFiltro]);
                $url .= '&q=' . urlencode($busqueda);
                
                set_flash('¡Encontrado!', "La sucursal estaba en la lista de <b>$otroFiltro</b>.", 'info');
                header("Location: " . $url);
                exit;
            }
        }
        
        $filtroActual = $filtro;
        require __DIR__ . '/../views/sucursal/listar.php';
    }

    // 2. CREAR (Vista)
    public function crear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) { header('Location: ' . ruta_vista('panel.php')); exit; }

        global $pageTitle;
        $pageTitle = "Nueva Sucursal";

        require __DIR__ . '/../views/sucursal/crear.php';
    }

    // 3. GUARDAR (Proceso Completo: Info + Foto + Horarios)
    public function guardar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ruta_accion('sucursal', 'listar')); exit; }

        $db = new Database();
        $conn = $db->getConnection();
        $modelo = new SucursalModelo($conn);

        // --- 1. PROCESAR FOTO ---
        $urlFoto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            try {
                $urlFoto = CloudinaryUploader::subirImagen($_FILES['foto']['tmp_name'], 'foto_fachada_sucursales');
            } catch (Exception $e) {
                set_flash('Error Imagen', $e->getMessage(), 'danger');
                header('Location: ' . ruta_accion('sucursal', 'crear'));
                exit;
            }
        }

        // --- 2. DATOS BÁSICOS ---
        $lat = $_POST['latitud'] ?? '';
        $lon = $_POST['longitud'] ?? '';
        
        $datos = [
            'neg_id'     => $_SESSION['negocio_id'],
            'nombre'     => trim($_POST['nombre'] ?? ''),
            'direccion'  => trim($_POST['direccion'] ?? ''),
            'telefono'   => trim($_POST['telefono'] ?? ''),
            'correo'     => trim($_POST['correo'] ?? ''),
            'referencia' => trim($_POST['referencia'] ?? ''),
            'latitud'    => ($lat === '') ? null : $lat,
            'longitud'   => ($lon === '') ? null : $lon,
            'foto'       => $urlFoto
        ];

        // --- 3. TRANSACCIÓN (Para que se guarde todo o nada) ---
        try {
            $conn->beginTransaction(); // Iniciamos bloque seguro

            // A. Guardar Sucursal
            $nuevoId = $modelo->guardar($datos); 
            
            if (!$nuevoId) {
                throw new Exception("No se pudo registrar la sucursal.");
            }

            // B. Guardar Horarios (Si vienen en el POST)
            // Esperamos un array así: $_POST['horarios'] = [
            //    ['dia'=>'Lunes', 'apertura'=>'09:00', 'cierre'=>'18:00', 'descanso'=>0], ...
            // ]
            if (isset($_POST['horarios']) && is_array($_POST['horarios'])) {
                $modelo->guardarHorarios($nuevoId, $_POST['horarios']);
            }

            $conn->commit(); // Confirmamos todo
            
            set_flash('¡Creado!', 'Sucursal y horarios registrados exitosamente.', 'success');
            header('Location: ' . ruta_accion('sucursal', 'listar'));
            exit;

        } catch (Exception $e) {
            $conn->rollBack(); // Si falla algo, deshacemos todo
            
            // Si subimos foto pero falló la BD, sería ideal borrarla de Cloudinary (Opcional por ahora)
            set_flash('Error', 'Error al guardar: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('sucursal', 'crear'));
            exit;
        }
    }

    // 4. EDITAR (Ahora cargamos también los horarios para mostrarlos)
    public function editar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['negocio_id'])) exit;

        global $pageTitle;
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $modelo = new SucursalModelo($db->getConnection());

        $sucursal = $modelo->obtenerPorId($id, $negocioId);

        if (!$sucursal) {
            set_flash('Error', 'Sucursal no encontrada.', 'danger');
            header('Location: ' . ruta_accion('sucursal', 'listar'));
            exit;
        }

        // [NUEVO] Cargamos los horarios existentes
        $horariosDb = $modelo->obtenerHorarios($id);

        $pageTitle = "Editar: " . $sucursal['suc_nombre'];
        require __DIR__ . '/../views/sucursal/editar.php';
    }

    // 5. ACTUALIZAR (Incluye actualización de Horarios)
    public function actualizar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $id = $_POST['id'];
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $conn = $db->getConnection();
        $modelo = new SucursalModelo($conn);

        // ... (Lógica de foto igual que antes) ...
        $sucursalActual = $modelo->obtenerPorId($id, $negocioId);
        $urlFoto = $sucursalActual['suc_foto'];

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            try {
                $nuevaUrl = CloudinaryUploader::subirImagen($_FILES['foto']['tmp_name'], 'foto_fachada_sucursales');
                if ($nuevaUrl) {
                    if ($urlFoto) CloudinaryUploader::eliminarImagen($urlFoto);
                    $urlFoto = $nuevaUrl;
                }
            } catch (Exception $e) {
                set_flash('Error Imagen', $e->getMessage(), 'danger');
                header('Location: ' . ruta_accion('sucursal', 'editar', ['id' => $id]));
                exit;
            }
        }

        $lat = $_POST['latitud'] ?? '';
        $lon = $_POST['longitud'] ?? '';

        $datos = [
            'neg_id'     => $negocioId,
            'nombre'     => trim($_POST['nombre']),
            'direccion'  => trim($_POST['direccion']),
            'telefono'   => trim($_POST['telefono']),
            'correo'     => trim($_POST['correo']),
            'referencia' => trim($_POST['referencia']),
            'latitud'    => ($lat === '') ? null : $lat,
            'longitud'   => ($lon === '') ? null : $lon,
            'foto'       => $urlFoto
        ];

        try {
            $conn->beginTransaction();

            // A. Actualizar Datos Básicos
            $modelo->actualizar($id, $datos);

            // B. Actualizar Horarios (Borrar viejos e insertar nuevos)
            if (isset($_POST['horarios']) && is_array($_POST['horarios'])) {
                $modelo->guardarHorarios($id, $_POST['horarios']);
            }

            $conn->commit();

            set_flash('¡Actualizado!', 'Datos y horarios guardados.', 'success');
            header('Location: ' . ruta_accion('sucursal', 'listar'));
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            set_flash('Error', 'Error técnico: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('sucursal', 'editar', ['id' => $id]));
            exit;
        }
    }

    // 6. ELIMINAR
    public function eliminar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new SucursalModelo($db->getConnection());
            $modelo->eliminarLogico($id, $_SESSION['negocio_id']);
            set_flash('¡Desactivada!', 'Sucursal movida a la papelera.', 'success');
        }
        header('Location: ' . ruta_accion('sucursal', 'listar'));
        exit;
    }

    // 7. REACTIVAR
    public function reactivar($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new SucursalModelo($db->getConnection());
            $modelo->reactivar($id, $_SESSION['negocio_id']);
            set_flash('¡Restaurada!', 'La sucursal está activa nuevamente.', 'success');
        }
        header('Location: ' . ruta_accion('sucursal', 'listar', ['filtro' => 'inactivos']));
        exit;
    }
}