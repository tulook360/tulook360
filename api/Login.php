<?php
// api/Login.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../nucleo/Crypto.php';
require_once __DIR__ . '/../nucleo/Menu.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos (soporta JSON o Form-Data)
$input = json_decode(file_get_contents("php://input"), true);
$correo = trim($input['email'] ?? $_POST['email'] ?? '');
$clave  = trim($input['password'] ?? $_POST['password'] ?? '');

if (empty($correo) || empty($clave)) {
    echo json_encode(['success' => false, 'message' => 'Credenciales incompletas']);
    exit;
}

try {
    $db = new Database();
    $modeloUsuario = new UsuarioModelo($db->getConnection());
    $usuario = $modeloUsuario->buscarPorCorreo($correo);

    // 1. Validar Usuario y Contraseña
    if (!$usuario || !password_verify($clave, $usuario['usu_contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
        exit;
    }

    if ($usuario['usu_estado'] !== 'A') {
        echo json_encode(['success' => false, 'message' => 'Tu cuenta está inactiva']);
        exit;
    }

    // 2. Obtener Permisos (Lógica de Menu.php para el Rol)
    // Esto es vital para que la App MAUI sepa qué mostrar
    $permisos = Menu::obtener((int)$usuario['rol_id']);

    // 3. Preparar Respuesta (Payload)
    $userData = [
        'id'        => $usuario['usu_id'],
        'nombres'   => $usuario['usu_nombres'],
        'apellidos' => $usuario['usu_apellidos'],
        'foto'      => $usuario['usu_foto'],
        'rol_id'    => (int)$usuario['rol_id'],
        'negocio_id'=> $usuario['neg_id'],
        'sucursal_id'=> $usuario['suc_id']
    ];

    // Encriptamos la sesión móvil usando tu Crypto.php para máxima seguridad
    $tokenMovil = Crypto::encriptar([
        'user' => $userData,
        'iat'  => time(), // Tiempo de emisión
        'exp'  => time() + (60 * 60 * 24 * 30) // Expira en 30 días
    ]);

    echo json_encode([
        'success'  => true,
        'message'  => '¡Bienvenido a TuLook360!',
        'token'    => $tokenMovil,
        'user'     => $userData,
        'permisos' => $permisos
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}