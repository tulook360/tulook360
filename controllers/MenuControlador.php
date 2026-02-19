<?php
// controllers/MenuControlador.php

// 1. Importamos dependencias necesarias
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../nucleo/helpers.php';

class MenuControlador {

    // ==========================================================
    // 1. LISTAR CON FILTRO
    // ==========================================================
    // CORRECCIÓN: Ahora acepta el argumento $filtro con un valor por defecto
    public function listar($filtro = 'activos') {

        // [AGREGAR ESTO AL INICIO]
        global $pageTitle;
        $pageTitle = "Gestión de Menús";

        
        $db = new Database();
        $pdo = $db->getConnection();
        $modelo = new MenuModelo($pdo);

        // Determinamos el estado para la BD
        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';

        $listaMenus = $modelo->listar($estadoDb);

        // Pasamos la variable a la vista (SOLO UNA VEZ)
        $filtroActual = $filtro; 
        
        require __DIR__ . '/../views/menu/listar.php'; 
    }

    // ==========================================================
    // 2. MOSTRAR FORMULARIO (Vista Crear)
    // ==========================================================
    public function crear() {

        //Titulo de la pagina
        global $pageTitle;
        $pageTitle = "Crear nuevo menú";

        // Solo carga la vista con el formulario HTML
        require __DIR__ . '/../views/menu/crear.php';
    }

    // ==========================================================
    // 3. PROCESAR GUARDADO (Recibe $_POST)
    // ==========================================================
    public function guardar() {
        // A) Seguridad: Solo aceptamos peticiones POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ruta_accion('menu', 'listar'));
            exit;
        }

        // B) Recibir y limpiar datos
        $nombre = trim($_POST['nombre'] ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');

        // C) Validación Básica
        if (empty($nombre)) {
            // Si falta el nombre, regresamos al formulario con error (básico)
            // En el futuro podemos pasar errores por sesión
            header('Location: ' . ruta_accion('menu', 'crear'));
            exit;
        }

        // D) Guardar en Base de Datos
        $db = new Database();
        $modelo = new MenuModelo($db->getConnection());
        
        try {
            $modelo->guardar($nombre, $desc);

            //Enviar mensaje de exito
            set_flash('¡Creado!', 'La carpeta se ha creado correctamente.');
            
            // E) Redirigir al listado (Éxito)
            header('Location: ' . ruta_accion('menu', 'listar'));
            exit;

        } catch (Exception $e) {
            // Si falla algo (ej: base de datos caída), mostrar error
            die("Error al guardar: " . $e->getMessage());
        }
    }




    // ==========================================================
    // 4. VISTA EDICIÓN (Recibe el ID por URL encriptada)
    // ==========================================================
    public function editar($id) {


        //Titulo de la pagina
        global $pageTitle;
        $pageTitle = "Editar menú";


        $db = new Database();
        $modelo = new MenuModelo($db->getConnection());
        
        // Buscamos el menú
        $menu = $modelo->obtenerPorId($id);

        if (!$menu) {
            // Si no existe el ID, redirigimos al listado
            header('Location: ' . ruta_accion('menu', 'listar'));
            exit;
        }

        // Cargamos la vista pasando los datos
        require __DIR__ . '/../views/menu/editar.php';
    }

    // ==========================================================
    // 5. PROCESAR ACTUALIZACIÓN (POST)
    // ==========================================================
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ruta_accion('menu', 'listar'));
            exit;
        }

        // Recibir datos
        $id     = $_POST['id'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $desc   = trim($_POST['descripcion'] ?? '');
        $estado = $_POST['estado'] ?? 'A'; // A = Activo, I = Inactivo

        if (empty($nombre) || empty($id)) {
            header('Location: ' . ruta_accion('menu', 'listar'));
            exit;
        }

        $db = new Database();
        $modelo = new MenuModelo($db->getConnection());

        $modelo->actualizar($id, $nombre, $desc, $estado);

        // MENSAJE DE ÉXITO
        set_flash('¡Actualizado!', 'Los cambios se guardaron con éxito.');

        header('Location: ' . ruta_accion('menu', 'listar'));
        exit;
    }

    // ==========================================================
    // 6. ELIMINAR (Procesa la petición)
    // ==========================================================
    // CORRECCIÓN: Agregamos $id como argumento de la función
    public function eliminar($id) {
        
        // Ya no necesitamos $_GET['id'] porque el Router nos lo pasa directo
        
        if ($id) {
            $db = new Database();
            $modelo = new MenuModelo($db->getConnection());
            $modelo->eliminarLogico($id);

            // MENSAJE DE ÉXITO
            set_flash('¡Eliminado!', 'El menú se ha desactivado correctamente.');
        }

        // Redirigir siempre al listar
        header('Location: ' . ruta_accion('menu', 'listar'));
        exit;
    }

    // ==========================================================
    // 7. REACTIVAR (Restaurar eliminado)
    // ==========================================================
    public function reactivar($id) {
        if ($id) {
            $db = new Database();
            $modelo = new MenuModelo($db->getConnection());
            $modelo->reactivar($id);
            
            set_flash('¡Restaurado!', 'El menú ha sido reactivado correctamente.', 'success');
        }
        // Redirigimos a la lista de inactivos para que vea que ya no está ahí (o a activos)
        header('Location: ' . ruta_accion('menu', 'listar', ['filtro' => 'inactivos']));
        exit;
    }
}