<?php
// nucleo/helpers.php

require_once __DIR__ . '/Crypto.php';

function base_url(): string {
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return "http://{$host}{$path}";
}

function asset(string $ruta): string {
    return base_url() . '/' . ltrim($ruta, '/');
}

function ruta_vista(string $vista, array $extra = [], bool $usarLayout = true): string {
    $payload = array_merge(['vista' => $vista, 'layout' => $usarLayout], $extra);
    $token = Crypto::encriptar($payload);
    return "index.php?token=$token";
}

function ruta_accion(string $c, string $a, array $params = [], bool $usarLayout = true): string {
    $payload = [
        'c' => $c,
        'a' => $a,
        'params' => $params,
        'layout' => $usarLayout
    ];
    $token = Crypto::encriptar($payload);
    return "index.php?token=$token";
}

// SISTEMA DE MENSAJES FLASH
function set_flash(string $titulo, string $mensaje, string $tipo = 'success') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $_SESSION['flash_msg'] = [
        'titulo'  => $titulo,
        'mensaje' => $mensaje,
        'tipo'    => $tipo
    ];
    session_write_close(); // Importante para evitar pérdida en redirección
}

function get_flash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']); 
        return $msg;
    }
    return null;
}


// VALIDACIÓN DE PERMISOS (NUEVA VERSIÓN)
function tiene_permiso($controlador, $metodo) {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['rol_id'])) return false;
    
    // Bypass temporal para Super Admin (ID 1) si lo deseas
    //if ($_SESSION['rol_id'] == 1) return true; 

    $rolId = $_SESSION['rol_id'];
    $con = strtolower($controlador);
    $met = strtolower($metodo);

    require_once __DIR__ . '/../config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();

    // Buscamos por las columnas separadas
    $sql = "SELECT COUNT(*) FROM tbl_permiso p
            INNER JOIN tbl_accion a ON p.acc_id = a.acc_id
            WHERE p.rol_id = :rolId 
              AND LOWER(a.acc_controlador) = :con
              AND LOWER(a.acc_metodo) = :met
              AND a.acc_estado = 'A'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':rolId' => $rolId, ':con' => $con, ':met' => $met]);
    
    return $stmt->fetchColumn() > 0;
}