<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../nucleo/helpers.php'; // Para usar ruta_vista()
require_once __DIR__ . '/../models/Negocio.php';
require_once __DIR__ . '/../nucleo/CloudinaryUploader.php';
require_once __DIR__ . '/../nucleo/TimeHelper.php'; // <--- IMPORTANTE
require_once __DIR__ . '/../nucleo/BrevoMailer.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthControlador {

    public function login() {
        // 1. Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . ruta_vista('login.php'));
            exit;
        }

        $correo = trim($_POST['email'] ?? '');
        $clave  = trim($_POST['password'] ?? '');

        if ($correo === '' || $clave === '') {
            $msg = urlencode('Por favor complete todos los campos.');
            header("Location: " . ruta_vista('login.php', [], false) . "&error=$msg");
            exit;
        }

        // 2. Conexión a BD
        $db = new Database();
        $pdo = $db->getConnection();

        // 3. Buscar usuario por correo (Sin filtrar estado para detectar bloqueos)
        $sql = "SELECT u.*, r.rol_nombre 
                FROM tbl_usuario u
                INNER JOIN tbl_rol r ON u.rol_id = r.rol_id
                WHERE u.usu_correo = :correo 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Verificaciones de Existencia y Estado
        if (!$usuario) {
            $msg = urlencode('Credenciales incorrectas.');
            header("Location: " . ruta_vista('login.php', [], false) . "&error=$msg");
            exit;
        }

        // Verificamos si la cuenta está bloqueada o inactiva
        if ($usuario['usu_estado'] === 'B') {
            $msg = urlencode('Tu cuenta ha sido bloqueada por seguridad (3 intentos fallidos). Contacta a soporte.');
            header("Location: " . ruta_vista('login.php', [], false) . "&error=$msg");
            exit;
        } elseif ($usuario['usu_estado'] !== 'A') {
            $msg = urlencode('Este usuario se encuentra inactivo.');
            header("Location: " . ruta_vista('login.php', [], false) . "&error=$msg");
            exit;
        }

        // Cargamos el modelo de Usuario para usar las funciones de intentos
        require_once __DIR__ . '/../models/Usuario.php';
        $modeloUsuario = new UsuarioModelo($pdo);

        // Verificar contraseña usando el hash
        if (!password_verify($clave, $usuario['usu_contrasena'])) {
            
            // Registramos el error y vemos cuántos lleva
            $intentosActuales = $modeloUsuario->registrarIntentoFallido($usuario['usu_id']);
            
            if ($intentosActuales >= 3) {
                // Bloqueamos la cuenta
                $modeloUsuario->bloquearUsuario($usuario['usu_id']);
                $msg = urlencode('Demasiados intentos. Tu cuenta ha sido bloqueada por seguridad.');
            } else {
                // Le avisamos cuántos intentos le quedan
                $restantes = 3 - $intentosActuales;
                $msg = urlencode("Contraseña incorrecta. Te quedan $restantes intento(s).");
            }
            
            // Redirigir enviando el error de forma segura en la URL
            header("Location: " . ruta_vista('login.php', [], false) . "&error=$msg");
            exit;
        }

        // Reseteamos los intentos a 0 si logra entrar
        if ($usuario['usu_intentos'] > 0) {
            $modeloUsuario->resetearIntentos($usuario['usu_id']);
        }

        // 5. Login Exitoso: Guardar datos en Sesión
        $_SESSION['usuario_id']     = $usuario['usu_id'];
        $_SESSION['usuario_nombre'] = $usuario['usu_nombres'] . ' ' . $usuario['usu_apellidos'];
        $_SESSION['usuario_rol']    = $usuario['rol_nombre'];
        $_SESSION['rol_id']         = $usuario['rol_id'];
        $_SESSION['negocio_id']     = $usuario['neg_id'];
        $_SESSION['usuario_foto']   = $usuario['usu_foto']; 

        // --- OBTENER BRANDING DEL NEGOCIO ---
        if ($usuario['neg_id']) {
            $sqlBrand = "SELECT n.neg_nombre, n.neg_logo, t.tneg_icono 
                         FROM tbl_negocio n
                         INNER JOIN tbl_tipo_negocio t ON n.tneg_id = t.tneg_id
                         WHERE n.neg_id = :nid LIMIT 1";
            
            $stmtB = $pdo->prepare($sqlBrand);
            $stmtB->execute([':nid' => $usuario['neg_id']]);
            $branding = $stmtB->fetch(PDO::FETCH_ASSOC);

            if ($branding) {
                $_SESSION['app_brand_name'] = $branding['neg_nombre'];
                $_SESSION['app_brand_icon'] = $branding['tneg_icono'];
                $_SESSION['app_brand_logo'] = $branding['neg_logo']; 
            } else {
                $_SESSION['app_brand_name'] = 'Tu Negocio';
                $_SESSION['app_brand_icon'] = 'fa-store';
                $_SESSION['app_brand_logo'] = null;
            }
        } else {
            $_SESSION['app_brand_name'] = 'TuLook360';
            $_SESSION['app_brand_icon'] = 'fa-scissors';
            $_SESSION['app_brand_logo'] = null;
        }

        // 6. Redirigir al Panel
        header('Location: ' . ruta_accion('auth', 'panel'));
        exit;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: ' . ruta_vista('login.php'));
        exit;
    }

    // ====================================================================
    // PANEL DE INICIO (DISTRIBUIDOR DE VISTAS SEGÚN ROL)
    // ====================================================================
    public function panel() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Verificar si está logueado
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . ruta_vista('login.php', [], false));
            exit;
        }

        $rolId = $_SESSION['rol_id'];
        $userId = $_SESSION['usuario_id'];
        
        // 2. RECUPERAR SUCURSAL ACTUALIZADA DESDE LA BD
        // Esto es vital: Si el dueño me acaba de asignar al "Norte", 
        // necesito saberlo AHORA, no usar el dato viejo de cuando inicié sesión hace 3 horas.
        $db = new Database();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("SELECT suc_id FROM tbl_usuario WHERE usu_id = :uid");
        $stmt->execute([':uid' => $userId]);
        $sucursalId = $stmt->fetchColumn(); // Obtiene solo el valor de la columna

        // Actualizamos la sesión para el resto del sistema
        $_SESSION['suc_id'] = $sucursalId; 

        // 3. Lógica de Distribución (Switch Maestro)
        // Definimos el título base, pero cada vista puede sobreescribirlo
        global $pageTitle;
        
        switch ($rolId) {
            case 1: 
                // --- SUPER ADMIN DEL SISTEMA ---
                $pageTitle = "Panel Super Admin";
                require __DIR__ . '/../views/dashboard/super_admin.php';
                break;

            case 2: 
                // --- DUEÑO DE NEGOCIO ---
                $pageTitle = "Panel Gerencial";
                require __DIR__ . '/../views/dashboard/negocio.php';
                break;
            case 4: 
                // --- CLIENTE FINAL ---
                $pageTitle = "Mi Perfil";
                require __DIR__ . '/../views/dashboard/cliente.php';
                break;

            case 8: 
                // --- ADMINISTRADOR DE SUCURSAL ---
                $pageTitle = "Operaciones de Sucursal";
                
                if (empty($sucursalId)) {
                    // Caso Borde: Es Admin pero no tiene sede asignada
                    $mensajeAlerta = "⚠️ No tienes una sucursal asignada. Por favor contacta al dueño.";
                    require __DIR__ . '/../views/dashboard/sin_asignacion.php'; 
                } else {
                    // Carga el panel operativo real
                    require __DIR__ . '/../views/dashboard/sucursal.php';
                }
                break;

            case 10: // Especialista
                if (empty($sucursalId)) {
                    // Caso Borde: Es Admin pero no tiene sede asignada
                    $mensajeAlerta = "⚠️ No tienes una sucursal asignada. Por favor contacta al dueño.";
                    require __DIR__ . '/../views/dashboard/sin_asignacion.php'; 
                } else {
                    // Carga el panel operativo real
                    require __DIR__ . '/../views/dashboard/especialista.php';
                }
                break;
            case 9:  // Recepcionista
                if (empty($sucursalId)) {
                    $mensajeAlerta = "⚠️ No tienes una sucursal asignada.";
                    require __DIR__ . '/../views/dashboard/sin_asignacion.php'; 
                } else {
                    // Crea una vista para recepcionista o usa la de sucursal
                    require __DIR__ . '/../views/dashboard/sucursal.php'; 
                }
                break;

            case 11: // Repartidor
                $pageTitle = "Zona de Repartos";

                // 1. IMPORTAR EL MODELO DE REPARTIDOR (Vital para que funcione)
                require_once __DIR__ . '/../models/RepartidorModelo.php';
                
                // 2. CONECTAR Y CONSULTAR
                // Como ya estamos dentro del Auth, aprovechamos para buscar los datos aquí mismo
                $dbRep = new Database(); // Usamos nombres distintos para no chocar variables
                $modeloRep = new RepartidorModelo($dbRep->getConnection());
                
                // 3. LLENAR LAS VARIABLES QUE LA VISTA NECESITA
                $ofertas = $modeloRep->obtenerOfertasDisponibles();
                $misPedidos = $modeloRep->obtenerMisPedidosEnCurso($_SESSION['usuario_id']);

                // 4. CARGAR LA VISTA (Ahora sí lleva datos)
                require __DIR__ . '/../views/dashboard/repartidor.php';
                break;
            default: 
                // --- RESTO DEL PERSONAL ---
                $pageTitle = "Mi Área de Trabajo";
                
                if (empty($sucursalId)) {
                    $mensajeAlerta = "⚠️ Aún no tienes una sucursal asignada para trabajar hoy.";
                    require __DIR__ . '/../views/dashboard/sin_asignacion.php';
                } else {
                    require __DIR__ . '/../views/dashboard/empleado.php';
                }
                break;
        }
    }



    // ==========================================================
    // 1. VISTA DE REGISTRO (Pública)
    // ==========================================================
    public function registro() {
        global $pageTitle;
        $pageTitle = "Registrar mi Negocio"; // Título de la pestaña

        $db = new Database();
        $modelo = new NegocioModelo($db->getConnection());
        
        // Obtenemos la lista para el selector visual
        $tiposNegocio = $modelo->obtenerTipos();

        require __DIR__ . '/../views/registro.php';
    }

    // ==========================================================
    // 2. PROCESAR EL REGISTRO
    // ==========================================================
    public function guardarRegistro() {
        // Forzamos respuesta JSON siempre
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        // 1. Recoger datos
        $pass        = $_POST['admin_pass'] ?? '';
        $confirmPass = $_POST['confirm_pass'] ?? '';
        $correo      = trim($_POST['admin_correo'] ?? '');

        // 2. Validaciones básicas (Backend)
        if (empty($_POST['negocio_nombre']) || empty($_POST['admin_nombres'])) {
            echo json_encode(['success' => false, 'msg' => 'Faltan datos obligatorios.']);
            exit;
        }

        if ($pass !== $confirmPass) {
            echo json_encode(['success' => false, 'msg' => 'Las contraseñas no coinciden.']);
            exit;
        }

        // 3. Validar Correo Duplicado
        $db = new Database();
        $modelo = new NegocioModelo($db->getConnection());

        if ($modelo->verificarCorreo($correo)) {
            echo json_encode(['success' => false, 'msg' => 'El correo ya está registrado.']);
            exit;
        }

        // 4. Subida de Logo (Cloudinary)
        $urlLogo = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            try {
                if (class_exists('CloudinaryUploader')) {
                    $urlLogo = CloudinaryUploader::subirImagen($_FILES['logo']['tmp_name']);
                }
            } catch (Exception $e) { 
                // Si falla logo, seguimos sin logo
            }
        }

        // 5. Preparar array
        $datos = [
            'tneg_id'           => $_POST['tneg_id'] ?? null,
            'negocio_nombre'    => trim($_POST['negocio_nombre'] ?? ''),
            'negocio_fundacion' => $_POST['negocio_fundacion'] ?? date('Y-m-d'),
            'negocio_logo'      => $urlLogo,
            'admin_cedula'      => trim($_POST['admin_cedula'] ?? ''),
            'admin_nombres'     => trim($_POST['admin_nombres'] ?? ''),
            'admin_apellidos'   => trim($_POST['admin_apellidos'] ?? ''),
            'admin_correo'      => $correo,
            // CORRECCIÓN: Encriptamos la contraseña antes de enviarla al modelo
            'admin_pass'        => password_hash($pass, PASSWORD_BCRYPT) 
        ];

        // 6. Guardar y Auto-Login
        try {
            // A) Registrar
            $modelo->registrarNuevoNegocio($datos);

            // B) Auto-Login (Buscar usuario creado)
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("SELECT u.*, r.rol_nombre FROM tbl_usuario u JOIN tbl_rol r ON u.rol_id = r.rol_id WHERE u.usu_correo = :c LIMIT 1");
            $stmt->execute([':c' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['usuario_id']     = $usuario['usu_id'];
                $_SESSION['usuario_nombre'] = $usuario['usu_nombres'] . ' ' . $usuario['usu_apellidos'];
                $_SESSION['usuario_rol']    = $usuario['rol_nombre'];
                $_SESSION['rol_id']         = $usuario['rol_id'];
                $_SESSION['negocio_id']     = $usuario['neg_id'];
                $_SESSION['usuario_foto']   = $usuario['usu_foto'];
                
                // Branding Sesión
                $_SESSION['app_brand_name'] = $datos['negocio_nombre'];
                $_SESSION['app_brand_logo'] = $urlLogo;

                // RESPUESTA EXITOSA PARA EL MODAL
                echo json_encode([
                    'success' => true, 
                    'msg' => '¡Negocio creado exitosamente!',
                    'redirect' => ruta_accion('auth', 'panel') // URL a donde ir después del OK
                ]);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'msg' => 'Error BD: ' . $e->getMessage()]);
            exit;
        }
    }

    // Helper para devolver errores al formulario limpiamente
    private function redirigirError($mensaje) {
        $msg = urlencode($mensaje);
        // Asegúrate que la ruta coincida con como llamas a tu vista de registro
        header('Location: ' . ruta_accion('auth', 'registro', [], false) . '&error=' . $msg);
        exit;
    }

    // --- NUEVA FUNCIÓN: VALIDACIÓN AJAX DE CORREO ---
    // Esta función es llamada silenciosamente por JavaScript
    public function verificar_correo_ajax() {
        // Limpiamos lo que llega
        $correo = trim($_GET['email'] ?? '');
        
        if (empty($correo)) {
            echo json_encode(['existe' => false]);
            exit;
        }

        $db = new Database();
        $modelo = new NegocioModelo($db->getConnection());
        
        // Usamos la función que creamos antes en el Modelo
        $existe = $modelo->verificarCorreo($correo);

        // Devolvemos JSON puro para que JS lo entienda
        header('Content-Type: application/json');
        echo json_encode(['existe' => $existe]);
        exit;
    }


    // ==========================================================
    // VISTA DE REGISTRO PARA CLIENTES (USUARIO FINAL)
    // ==========================================================
    public function registroCliente() {
        global $pageTitle;
        $pageTitle = "Crear mi Cuenta | TuLook360";
        // Cargamos la vista directamente desde la raíz de views
        require __DIR__ . '/../views/registro_cliente.php';
    }

    // ==========================================================
    // PROCESAR REGISTRO DE CLIENTE
    // ==========================================================
    public function guardarCliente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        
        $nombres   = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $cedula    = trim($_POST['cedula'] ?? ''); // <--- RECIBIMOS CÉDULA
        $correo    = trim($_POST['email'] ?? '');
        $pass      = $_POST['password'] ?? '';

        // 1. Validaciones básicas
        if (empty($nombres) || empty($cedula) || empty($correo) || empty($pass)) {
            echo json_encode(['success' => false, 'msg' => 'Complete todos los campos obligatorios.']);
            exit;
        }

        $db = new Database();
        $pdo = $db->getConnection();
        require_once __DIR__ . '/../models/Usuario.php';
        $modelo = new UsuarioModelo($pdo);

        // 2. Validar duplicados (Cédula y Correo)
        if ($modelo->buscarPorCorreo($correo)) {
            echo json_encode(['success' => false, 'msg' => 'Este correo ya está registrado.']);
            exit;
        }
        if ($modelo->buscarPorCedula($cedula)) {
            echo json_encode(['success' => false, 'msg' => 'Esta cédula ya está registrada.']);
            exit;
        }

        // 3. Procesar Foto (Si la subió)
        $urlFoto = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            try {
                // Especificamos la ruta exacta: Carpeta Principal / Subcarpeta
                $urlFoto = CloudinaryUploader::subirImagen($_FILES['foto_perfil']['tmp_name'], 'usuarios');
            } catch (Exception $e) {
                // Si falla la subida, continuamos sin foto o avisamos
            }
        }

        // 4. Preparar datos para el modelo
        $datos = [
            'neg_id'    => null, 
            'rol_id'    => 4, // Rol Cliente
            'suc_id'    => null,
            'cedula'    => $cedula,
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'correo'    => $correo,
            'password'  => password_hash($pass, PASSWORD_BCRYPT),
            'foto'      => $urlFoto,
            'fecha_reg' => TimeHelper::now()
        ];

        if ($modelo->guardar($datos)) {
            // Auto-login
            $usuario = $modelo->buscarPorCorreo($correo);
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            $_SESSION['usuario_id']     = $usuario['usu_id'];
            $_SESSION['usuario_nombre'] = $usuario['usu_nombres'] . ' ' . $usuario['usu_apellidos'];
            $_SESSION['rol_id']         = 4;
            $_SESSION['usuario_rol']    = 'Cliente';
            $_SESSION['negocio_id']     = null;
            $_SESSION['app_brand_name'] = 'TuLook360';
            $_SESSION['usuario_foto']   = $urlFoto;

            echo json_encode(['success' => true, 'redirect' => ruta_accion('auth', 'panel')]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'No se pudo guardar la información.']);
        }
        exit;
    }

    


    // ====================================================================
    // SISTEMA DE RECUPERACIÓN Y DESBLOQUEO (VISTA 3 PASOS - CÓDIGO OTP)
    // ====================================================================

    // 1. Carga la vista principal vacía
    public function recuperarAccount() {
        require __DIR__ . '/../views/recuperar.php';
    }

    // 2. Generar y enviar el código de 6 dígitos
    public function solicitarRecuperacionAjax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $correo = trim($_POST['email'] ?? '');
        if (empty($correo)) {
            echo json_encode(['success' => false, 'msg' => 'Ingresa un correo.']);
            exit;
        }

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());
        $usuario = $modelo->buscarPorCorreo($correo);

        if (!$usuario) {
            echo json_encode(['success' => true, 'msg' => 'Si el correo existe, enviaremos el código.']);
            exit;
        }

        // Generar código numérico de 6 dígitos (Ej: 482910)
        $codigoOTP = sprintf("%06d", mt_rand(100000, 999999));
        
        if ($modelo->crearTokenRecuperacion($usuario['usu_id'], $codigoOTP)) {
            // Le pasamos el código al enviador de Brevo en lugar del enlace
            $enviado = BrevoMailer::enviarRecuperacion($usuario['usu_correo'], $usuario['usu_nombres'], $codigoOTP);

            if ($enviado) {
                echo json_encode(['success' => true, 'msg' => 'Código enviado. Revisa tu correo.']);
            } else {
                echo json_encode(['success' => false, 'msg' => 'Error al enviar el correo.']);
            }
        } else {
            echo json_encode(['success' => false, 'msg' => 'Error de base de datos.']);
        }
        exit;
    }

    // 3. Validar si el código introducido por el usuario es correcto
    public function verificarCodigoRecuperacionAjax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $codigo = trim($_POST['codigo'] ?? '');
        if (empty($codigo)) {
            echo json_encode(['success' => false, 'msg' => 'Ingresa el código.']);
            exit;
        }

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());
        $datos = $modelo->validarToken($codigo);

        if ($datos) {
            echo json_encode(['success' => true, 'nombre' => $datos['usu_nombres']]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'El código es incorrecto o ha expirado.']);
        }
        exit;
    }

    // 4. Guardar la nueva contraseña usando el código verificado
    public function guardarNuevaPasswordAjax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $codigo = trim($_POST['codigo'] ?? ''); // Ahora recibimos el código
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';

        if (empty($codigo) || empty($pass1) || $pass1 !== $pass2) {
            echo json_encode(['success' => false, 'msg' => 'Las contraseñas no coinciden.']);
            exit;
        }

        $db = new Database();
        $modelo = new UsuarioModelo($db->getConnection());
        $datosToken = $modelo->validarToken($codigo);

        if (!$datosToken) {
            echo json_encode(['success' => false, 'msg' => 'Código inválido o expirado.']);
            exit;
        }

        $hash = password_hash($pass1, PASSWORD_BCRYPT);
        
        if ($modelo->aplicarRecuperacionYDesbloqueo($datosToken['usu_id'], $hash, $codigo)) {
            echo json_encode([
                'success' => true, 
                'msg' => '¡Contraseña actualizada!',
                'redirect' => ruta_vista('login.php', [], false)
            ]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Error al guardar.']);
        }
        exit;
    }
}