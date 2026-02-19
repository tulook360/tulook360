<?php
// views/layouts/header.php

if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Seguridad
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol_id'])) {
    header("Location: " . ruta_vista('login.php', [], false)); 
    exit;
}

require_once __DIR__ . '/../../nucleo/Menu.php';
require_once __DIR__ . '/../../nucleo/helpers.php';

// 2. Cargar Menú (Este ya viene filtrado por la BD)
$menuItems = Menu::obtener($_SESSION['rol_id']);
$estructuraMenu = $menuItems;

// 3. Datos de Sesión
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuarioRol    = $_SESSION['usuario_rol'] ?? 'Rol';
$usuarioFoto   = $_SESSION['usuario_foto'] ?? null;

// 4. Datos del Branding (Negocio)
$brandName = $_SESSION['app_brand_name'] ?? 'TuLook360';
$brandIcon = $_SESSION['app_brand_icon'] ?? 'fa-scissors';
$brandLogo = $_SESSION['app_brand_logo'] ?? null; 

// Logo por defecto del sistema (para Super Admin)
$systemLogo = asset('recursos/img/logo.png');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Panel' ?> | <?= htmlspecialchars($brandName) ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Kalam:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('recursos/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/listar.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/formularios.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/accion.css') ?>">
</head>
<body class="body-locked">

<div class="menu-overlay" id="menuOverlay"></div>

<div class="layout">
    
    <aside class="sidebar" id="sidebar">
        
        <div class="sidebar-header">
            <div class="brand kalam" style="display: flex; align-items: center; gap: 12px;">
                <i class="fa-solid <?= $brandIcon ?>"></i>
                <span><?= htmlspecialchars($brandName) ?></span>
            </div>
            <button class="btn-close-sidebar" id="closeSidebar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <nav class="sidebar-menu">
            
            <?php 
                // Determinamos qué imagen mostrar
                $imagenMostrar = null;
                if ($brandLogo) {
                    $imagenMostrar = $brandLogo; // Logo del negocio
                } elseif (empty($_SESSION['negocio_id'])) {
                    $imagenMostrar = $systemLogo; // Logo de TuLook360 (Super Admin)
                }
            ?>

            <?php if ($imagenMostrar): ?>
                <div style="text-align: center;">
                    <img src="<?= htmlspecialchars($imagenMostrar) ?>" alt="Logo" 
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; 
                                border: 4px solid rgba(255,255,255,0.2); 
                                box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                </div>
            <?php endif; ?>

            <?php foreach ($estructuraMenu['sueltas'] as $link): ?>
                <?php 
                    $href = str_contains($link['url'], '.php') ? ruta_vista($link['url']) : ruta_accion(explode('/', $link['url'])[0], explode('/', $link['url'])[1]);
                ?>
                <a href="<?= $href ?>" class="menu-link single">
                    <i class="fa-solid <?= $link['icono'] ?>"></i>
                    <span><?= htmlspecialchars($link['titulo']) ?></span>
                </a>
            <?php endforeach; ?>

            <?php if (!empty($estructuraMenu['sueltas']) && !empty($estructuraMenu['carpetas'])): ?>
                <div class="menu-divider"></div>
            <?php endif; ?>

            <?php foreach ($estructuraMenu['carpetas'] as $nombreCarpeta => $contenido): ?>
                <div class="nav-folder">
                    <div class="nav-folder-header" onclick="toggleFolder(this)">
                        <div style="display:flex; gap:12px; align-items:center;">
                            <i class="fa-solid fa-folder-open"></i> <span><?= htmlspecialchars($nombreCarpeta) ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-right arrow"></i>
                    </div>
                    
                    <div class="nav-folder-items">
                        <?php foreach ($contenido['items'] as $sublink): ?>
                            <?php 
                                $href = str_contains($sublink['url'], '.php') ? ruta_vista($sublink['url']) : ruta_accion(explode('/', $sublink['url'])[0], explode('/', $sublink['url'])[1]);
                            ?>
                            <a href="<?= $href ?>" class="menu-link sub-link">
                                <i class="fa-solid <?= $sublink['icono'] ?>"></i>
                                <span><?= htmlspecialchars($sublink['titulo']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <button class="mobile-toggle" id="mobileToggle"><i class="fa-solid fa-bars"></i></button>
            
            <div class="user-wrapper">
                
                <div class="user-info">
                    <div class="user-text">
                        <div class="name"><?= htmlspecialchars($usuarioNombre) ?></div>
                        <div class="role"><?= htmlspecialchars($usuarioRol) ?> "<?= htmlspecialchars($brandName) ?>"</div>
                    </div>

                    <?php if ($usuarioFoto): ?>
                        <img src="<?= htmlspecialchars($usuarioFoto) ?>" class="user-avatar" 
                             style="object-fit: cover; padding: 0; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <div class="user-avatar">
                            <?= strtoupper(substr($usuarioNombre, 0, 2)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (tiene_permiso('usuario', 'perfil')): ?>
                    <a href="<?= ruta_accion('usuario', 'perfil') ?>" class="btn-logout-nav" title="Mi Perfil" style="color: var(--color-primario); background: #fff0f6;">
                        <i class="fa-solid fa-user"></i>
                    </a>
                <?php endif; ?>

                <?php if (tiene_permiso('auth', 'logout')): ?>
                    <a href="<?= ruta_accion('auth', 'logout') ?>" class="btn-logout-nav" title="Cerrar Sesión">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                <?php endif; ?>
                
            </div>

        </header>
        <div class="page-content">