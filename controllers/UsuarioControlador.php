<?php
// controllers/UsuarioControlador.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../nucleo/helpers.php';
require_once __DIR__ . '/../nucleo/TimeHelper.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Negocio.php';
require_once __DIR__ . '/../nucleo/CloudinaryUploader.php';
require_once __DIR__ . '/../models/Sucursal.php';

class UsuarioControlador {

    // ====================================================================
    // VISTA DE PERFIL
    // ====================================================================
    public function perfil() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . ruta_vista('login.php', [], false));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Mi Perfil";

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());
        
        $usuario = $modelo->obtenerPerfil($_SESSION['usuario_id']);

        if (!$usuario) {
            header('Location: ' . ruta_accion('auth', 'logout'));
            exit;
        }

        require __DIR__ . '/../views/usuario/perfil.php';
    }


    // ====================================================================
    // AJAX: ACTUALIZAR FOTO DE PERFIL (CLOUDINARY)
    // ====================================================================
    public function actualizarFoto() {
        // 1. Verificar Sesión
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Respuesta JSON por defecto para errores
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
            exit;
        }

        if (!isset($_FILES['fotoPerfil']) || $_FILES['fotoPerfil']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se seleccionó ninguna imagen válida.']);
            exit;
        }

        $db = new Database();
        $conn = $db->getConnection();
        $usuarioModelo = new UsuarioModelo($conn);
        $idUsuario = $_SESSION['usuario_id'];

        try {
            $conn->beginTransaction();

            // A) Obtener URL actual (para borrarla luego)
            $usuarioActual = $usuarioModelo->obtenerPerfil($idUsuario);
            $fotoAnteriorUrl = $usuarioActual['usu_foto'] ?? null;

            // B) Subir nueva foto a Cloudinary (carpeta 'usuarios')
            // Usamos el método estático de CloudinaryUploader
            $nuevaUrl = CloudinaryUploader::subirImagen($_FILES['fotoPerfil']['tmp_name'], 'usuarios');

            if (!$nuevaUrl) {
                throw new Exception("Error al subir la imagen al servidor.");
            }

            // C) Actualizar BD con la nueva URL
            if (!$usuarioModelo->actualizarFotoPerfil($idUsuario, $nuevaUrl)) {
                throw new Exception("Error al guardar la referencia en la base de datos.");
            }

            // D) Borrar foto anterior de Cloudinary (Limpieza)
            if ($fotoAnteriorUrl) {
                CloudinaryUploader::eliminarImagen($fotoAnteriorUrl);
            }

            $conn->commit();
            
            // E) Actualizar sesión para reflejar el cambio inmediatamente
            $_SESSION['usuario_foto'] = $nuevaUrl;

            // F) Respuesta Exitosa
            echo json_encode([
                'success' => true, 
                'message' => 'Foto actualizada correctamente.', 
                'nuevaUrl' => $nuevaUrl
            ]);
            exit; // FINALIZAR EJECUCIÓN

        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit; // FINALIZAR EJECUCIÓN
        }
    }



    // ====================================================================
    // AJAX: ACTUALIZAR DATOS PERSONALES
    // ====================================================================
    public function guardarDato() {
        // 1. Validar Sesión
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            exit;
        }

        // 2. Recibir datos
        $campo = $_POST['campo'] ?? '';
        $valor = trim($_POST['valor'] ?? '');
        $idUsuario = $_SESSION['usuario_id'];

        // 3. Validaciones básicas
        if (empty($valor)) {
            echo json_encode(['success' => false, 'message' => 'El campo no puede estar vacío.']);
            exit;
        }

        // Validación específica para correo
        if ($campo === 'usu_correo' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo inválido.']);
            exit;
        }

        require_once __DIR__ . '/../config/database.php';
        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());

        try {
            // 4. Actualizar
            $modelo->actualizarCampo($idUsuario, $campo, $valor);
            
            // 5. Actualizar variable de sesión si cambiamos el nombre
            if ($campo === 'usu_nombres' || $campo === 'usu_apellidos') {
                // Recargamos el nombre completo en sesión
                $datos = $modelo->obtenerPerfil($idUsuario);
                $_SESSION['usuario_nombre'] = $datos['usu_nombres'] . ' ' . $datos['usu_apellidos'];
            }

            echo json_encode(['success' => true, 'message' => 'Dato actualizado correctamente.']);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }


    // [NUEVO] CAMBIAR CONTRASEÑA
    public function cambiarContrasena() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            exit;
        }

        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';

        // 1. Validar que no estén vacías
        if (empty($pass1) || empty($pass2)) {
            echo json_encode(['success' => false, 'message' => 'Completa ambos campos.']);
            exit;
        }

        // 2. Validar coincidencia
        if ($pass1 !== $pass2) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            exit;
        }

        // 3. Validar seguridad (Mínimo 8, Mayús, Minus, Num, Simbolo)
        // Regex PHP equivalente al de JS
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $pass1)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña no es segura (Mín 8 caracteres, mayúscula, número y símbolo).']);
            exit;
        }

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());

        try {
            // 4. Encriptar y Guardar
            $hash = password_hash($pass1, PASSWORD_BCRYPT);
            $modelo->actualizarContrasena($_SESSION['usuario_id'], $hash);

            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }


    // ====================================================================
    // 1. LISTAR EMPLEADOS (DUEÑO DE NEGOCIO)
    // ====================================================================
    public function listar_empleados($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['negocio_id'])) {
            set_flash('Error', 'No tienes un negocio asignado.', 'danger');
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Mi Equipo";

        $busqueda = trim($_GET['q'] ?? '');
        $negocioId = $_SESSION['negocio_id'];
        
        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());
        
        // Definir estado actual
        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        
        // 1. Buscar en la pestaña ACTUAL
        $listaEmpleados = $modelo->listarPorNegocio($negocioId, $estadoDb, $busqueda);
        
        // --- LÓGICA DE REDIRECCIÓN INTELIGENTE ---
        if (!empty($busqueda) && empty($listaEmpleados)) {
            $otroEstado = ($estadoDb === 'A') ? 'I' : 'A';
            $otroFiltro = ($filtro === 'activos') ? 'inactivos' : 'activos';
            $resultadosOtroLado = $modelo->listarPorNegocio($negocioId, $otroEstado, $busqueda);
            
            if (!empty($resultadosOtroLado)) {
                $urlDestino = ruta_accion('usuario', 'listar_empleados', ['filtro' => $otroFiltro]);
                $urlDestino .= '&q=' . urlencode($busqueda);
                set_flash('¡Encontrado!', "El empleado estaba en la lista de <b>$otroFiltro</b>.", 'info');
                header("Location: " . $urlDestino);
                exit;
            }
        }
        
        $filtroActual = $filtro;

        // Cargar sucursales para el modal de traslado
        require_once __DIR__ . '/../models/Sucursal.php';
        $sucModelo = new SucursalModelo($db->getConnection());
        $listaSucursales = $sucModelo->listar($negocioId); // Asegúrate de tener este modelo/método

        // --- CORRECCIÓN: Nombre de archivo actualizado ---
        require __DIR__ . '/../views/usuario/listar_empleados.php';
    }

    // ====================================================================
    // 2. VISTA CREAR EMPLEADO (CARGA TODO PARA EL SUPER FORMULARIO)
    // ====================================================================
    public function crear_empleado() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['negocio_id'])) {
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Nuevo Colaborador";
        
        $negocioId = $_SESSION['negocio_id'];
        $db = new Database();

        // 1. Roles
        $rolModelo = new RolModelo($db->getConnection());
        $listaRoles = $rolModelo->listarRolesEmpleados();

        // 2. Sucursales
        $sucModelo = new SucursalModelo($db->getConnection());
        $listaSucursales = $sucModelo->listar($negocioId);
        
        // --- NUEVO: Traer días laborables de las sucursales ---
        $horariosJson = $sucModelo->obtenerDiasLaborablesPorNegocio($negocioId);

        // 3. NUEVO: Habilidades (Categorías de Servicio)
        require_once __DIR__ . '/../models/TipoServicio.php';
        $catModelo = new TipoServicioModelo($db->getConnection());
        $listaCategorias = $catModelo->listar($negocioId, 'A');

        require __DIR__ . '/../views/usuario/crear_empleado.php';
    }

    // ====================================================================
    // 3. GUARDAR EMPLEADO (SUPER GUARDADO UNIFICADO)
    // ====================================================================
    public function guardar_empleado() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        // Recoger datos del formulario gigante
        $datos = [
            'neg_id'       => $_SESSION['negocio_id'],
            'suc_id'       => !empty($_POST['suc_id']) ? $_POST['suc_id'] : null,
            'cedula'       => trim($_POST['cedula'] ?? ''), // NUEVO
            'nombres'      => trim($_POST['nombres'] ?? ''),
            'apellidos'    => trim($_POST['apellidos'] ?? ''),
            'correo'       => trim($_POST['correo'] ?? ''),
            'rol_id'       => $_POST['rol_id'] ?? '',
            'password'     => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : '',
            
            // Datos Económicos
            'sueldo'       => !empty($_POST['sueldo']) ? $_POST['sueldo'] : 0,
            'comision'     => !empty($_POST['comision']) ? $_POST['comision'] : 0,
            'nivel'        => $_POST['nivel'] ?? 'Junior',

            // Arrays (Checkboxes)
            'habilidades'  => $_POST['habilidades'] ?? [], // IDs de tipos de servicio
            'dias_trabajo' => $_POST['dias_trabajo'] ?? [] // Lunes, Martes...
        ];

        // Validaciones Mínimas
        if (empty($datos['nombres']) || empty($datos['apellidos']) || empty($datos['rol_id']) || empty($datos['correo']) || empty($datos['password'])) {
            set_flash('Error', 'Faltan datos obligatorios.', 'danger');
            header('Location: ' . ruta_accion('usuario', 'crear_empleado'));
            exit;
        }

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());

        try {
            // Validar correo
            if ($modelo->buscarPorCorreo($datos['correo'])) {
                set_flash('Error', 'El correo ya existe.', 'danger');
                header('Location: ' . ruta_accion('usuario', 'crear_empleado'));
                exit;
            }

            // Llamar al SUPER MÉTODO del modelo
            $modelo->crearEmpleadoCompleto($datos);
            
            set_flash('¡Éxito!', 'Colaborador registrado y configurado correctamente.', 'success');
            header('Location: ' . ruta_accion('usuario', 'listar_empleados'));
            exit;

        } catch (Exception $e) {
            set_flash('Error', 'No se pudo guardar: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('usuario', 'crear_empleado'));
            exit;
        }
    }


    // ====================================================================
    // 4. VISTA EDITAR EMPLEADO (CARGA DATOS PARA EL WIZARD)
    // ====================================================================
    public function editar_empleado($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['negocio_id'])) {
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        global $pageTitle;
        $negocioId = $_SESSION['negocio_id'];
        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());

        // 1. Obtener PERFIL COMPLETO (Info + Habilidades + Horarios)
        $datosCompletos = $modelo->obtenerPerfilCompleto($id, $negocioId);

        if (!$datosCompletos) {
            set_flash('Error', 'Empleado no encontrado.', 'danger');
            header('Location: ' . ruta_accion('usuario', 'listar_empleados'));
            exit;
        }

        // Desempaquetar para la vista
        $empleado = $datosCompletos['info'];
        $misHabilidades = $datosCompletos['habilidades']; // Array de IDs [1, 5]
        $misDias = $datosCompletos['dias']; // Array ['Lunes', 'Martes']

        $pageTitle = "Editar: " . $empleado['usu_nombres'];
        
        // 2. Cargas Auxiliares (Roles, Sucursales, Categorías)
        $rolModelo = new RolModelo($db->getConnection());
        $listaRoles = $rolModelo->listarRolesEmpleados();

        $sucModelo = new SucursalModelo($db->getConnection());
        $listaSucursales = $sucModelo->listar($negocioId);
        // Horarios de sucursales para la lógica JS
        $horariosJson = $sucModelo->obtenerDiasLaborablesPorNegocio($negocioId);

        require_once __DIR__ . '/../models/TipoServicio.php';
        $catModelo = new TipoServicioModelo($db->getConnection());
        $listaCategorias = $catModelo->listar($negocioId, 'A');

        require __DIR__ . '/../views/usuario/editar_empleado.php';
    }

    // ====================================================================
    // 5. ACTUALIZAR EMPLEADO (SUPER UPDATE)
    // ====================================================================
    public function actualizar_empleado() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

        $id = $_POST['id']; // ID del usuario a editar
        $negocioId = $_SESSION['negocio_id'];

        $datos = [
            'neg_id'       => $negocioId,
            'suc_id'       => !empty($_POST['suc_id']) ? $_POST['suc_id'] : null,
            'cedula'       => trim($_POST['cedula'] ?? ''),
            'nombres'      => trim($_POST['nombres'] ?? ''),
            'apellidos'    => trim($_POST['apellidos'] ?? ''),
            'correo'       => trim($_POST['correo'] ?? ''),
            'rol_id'       => $_POST['rol_id'] ?? '',
            // Password solo si escribió algo nuevo
            'password'     => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null,
            
            // Económicos
            'sueldo'       => !empty($_POST['sueldo']) ? $_POST['sueldo'] : 0,
            'comision'     => !empty($_POST['comision']) ? $_POST['comision'] : 0,
            'nivel'        => $_POST['nivel'] ?? 'Junior',

            // Arrays
            'habilidades'  => $_POST['habilidades'] ?? [],
            'dias_trabajo' => $_POST['dias_trabajo'] ?? []
        ];

        if (empty($datos['nombres']) || empty($datos['correo']) || empty($datos['rol_id'])) {
            set_flash('Error', 'Faltan datos obligatorios.', 'danger');
            header('Location: ' . ruta_accion('usuario', 'editar_empleado', ['id' => $id]));
            exit;
        }

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());

        try {
            // Validar duplicado de correo (excluyendo al propio usuario)
            if ($modelo->verificarDuplicadoExcluyendoId('usu_correo', $datos['correo'], $id)) {
                set_flash('Error', 'El correo ya pertenece a otro usuario.', 'danger');
                header('Location: ' . ruta_accion('usuario', 'editar_empleado', ['id' => $id]));
                exit;
            }

            // Validar duplicado de cédula (excluyendo al propio usuario)
            if ($modelo->verificarDuplicadoExcluyendoId('usu_cedula', $datos['cedula'], $id)) {
                set_flash('Error', 'La cédula ya está registrada.', 'danger');
                header('Location: ' . ruta_accion('usuario', 'editar_empleado', ['id' => $id]));
                exit;
            }

            // SUPER UPDATE
            $modelo->actualizarEmpleadoCompleto($id, $datos);
            
            set_flash('¡Actualizado!', 'Perfil de colaborador actualizado correctamente.', 'success');
            header('Location: ' . ruta_accion('usuario', 'listar_empleados'));
            exit;

        } catch (Exception $e) {
            set_flash('Error', 'Error: ' . $e->getMessage(), 'danger');
            header('Location: ' . ruta_accion('usuario', 'editar_empleado', ['id' => $id]));
            exit;
        }
    }

    // ====================================================================
    // 6. ELIMINAR EMPLEADO
    // ====================================================================
    public function eliminar_empleado($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new UsuarioModelo($db->getConnection());
            
            $modelo->eliminarLogico($id, $_SESSION['negocio_id']);
            
            set_flash('¡Desactivado!', 'El colaborador ya no tiene acceso al sistema.', 'success');
        }
        // CORRECCIÓN: Redirigir al nuevo método
        header('Location: ' . ruta_accion('usuario', 'listar_empleados'));
        exit;
    }

    // ====================================================================
    // 7. REACTIVAR EMPLEADO
    // ====================================================================
    public function reactivar_empleado($id) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if ($id && !empty($_SESSION['negocio_id'])) {
            $db = new Database();
            $modelo = new UsuarioModelo($db->getConnection());
            
            $modelo->reactivar($id, $_SESSION['negocio_id']);
            
            set_flash('¡Restaurado!', 'Colaborador activo nuevamente.', 'success');
        }
        // CORRECCIÓN: Redirigir al nuevo método
        header('Location: ' . ruta_accion('usuario', 'listar_empleados', ['filtro' => 'inactivos']));
        exit;
    }


    // ====================================================================
    // SUPER ADMIN: LISTAR NEGOCIOS
    // ====================================================================
    public function listar_negocios($filtro = 'activos') {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!empty($_SESSION['negocio_id'])) {
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        global $pageTitle;
        $pageTitle = "Gestión de Negocios";

        $busqueda = trim($_GET['q'] ?? '');

        $db = new Database();
        $negocioModelo = new NegocioModelo($db->getConnection());

        $estadoDb = ($filtro === 'inactivos') ? 'I' : 'A';
        $listaNegocios = $negocioModelo->listarNegociosConAdmin($estadoDb, $busqueda);

        if (!empty($busqueda) && empty($listaNegocios)) {
            $otroEstado = ($estadoDb === 'A') ? 'I' : 'A';
            $otroFiltro = ($filtro === 'activos') ? 'inactivos' : 'activos';
            $resultadosOtro = $negocioModelo->listarNegociosConAdmin($otroEstado, $busqueda);
            
            if (!empty($resultadosOtro)) {
                $url = ruta_accion('usuario', 'listar_negocios', ['filtro' => $otroFiltro]);
                $url .= '&q=' . urlencode($busqueda);
                set_flash('¡Encontrado!', "El negocio está en la lista de <b>$otroFiltro</b>.", 'info');
                header("Location: " . $url);
                exit;
            }
        }

        $filtroActual = $filtro;
        
        // --- CORRECCIÓN: Nombre de archivo actualizado ---
        require __DIR__ . '/../views/usuario/listar_negocios.php';
    }

    // ====================================================================
    // SUPER ADMIN: VER DETALLE 360
    // ====================================================================
    public function ver_negocio($idNegocio) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['negocio_id'])) exit; 

        global $pageTitle;
        
        $db = new Database();
        $negocioModelo = new NegocioModelo($db->getConnection());

        $reporte = $negocioModelo->obtenerReporteCompleto($idNegocio);
        
        if (!$reporte['info']) {
            set_flash('Error', 'Negocio no encontrado.', 'danger');
            // CORRECCIÓN: Redirigir al nuevo método
            header('Location: ' . ruta_accion('usuario', 'listar_negocios'));
            exit;
        }

        $pageTitle = "Negocio: " . $reporte['info']['neg_nombre'];
        
        require __DIR__ . '/../views/usuario/ver_negocio.php';
    }

    // ====================================================================
    // SUPER ADMIN: DESACTIVAR TODO
    // ====================================================================
    public function desactivar_negocio($idNegocio) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['negocio_id'])) exit; 

        $db = new Database();
        $negocioModelo = new NegocioModelo($db->getConnection());

        try {
            $negocioModelo->desactivarNegocioCompleto($idNegocio);
            set_flash('¡Desactivado!', 'El negocio y todo su personal han sido bloqueados.', 'success');
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
        }
        
        // CORRECCIÓN: Redirigir al nuevo método
        header('Location: ' . ruta_accion('usuario', 'listar_negocios'));
        exit;
    }

    // ====================================================================
    // SUPER ADMIN: REACTIVAR TODO
    // ====================================================================
    public function reactivar_negocio($idNegocio) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!empty($_SESSION['negocio_id'])) {
            header('Location: ' . ruta_vista('panel.php'));
            exit;
        }

        $db = new Database();
        $negocioModelo = new NegocioModelo($db->getConnection());

        try {
            $negocioModelo->reactivarNegocioCompleto($idNegocio);
            set_flash('¡Reactivado!', 'El negocio y todo su personal están activos nuevamente.', 'success');
        } catch (Exception $e) {
            set_flash('Error', $e->getMessage(), 'danger');
        }
        
        // CORRECCIÓN: Redirigir al nuevo método
        header('Location: ' . ruta_accion('usuario', 'listar_negocios', ['filtro' => 'inactivos']));
        exit;
    }

    
}