<?php
// Recibir mensaje (Prioridad 1: URL por GET, Prioridad 2: Payload encriptado)
$mensajeError = $_GET['error'] ?? $payload['error'] ?? null;
$mensajeExito = $_GET['msg'] ?? $payload['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | TuLook360</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Kalam:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= asset('recursos/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/login.css') ?>">
</head>
<body>

<div class="split-container">
    <div class="left-panel">
        <div class="brand-logo animate-in" style="animation-delay: 0.1s;">TuLook<span>360</span></div>
        <p class="slogan animate-in" style="animation-delay: 0.2s;">
            Gestiona tu belleza, agenda tus citas y luce espectacular con un solo clic.
        </p>
        
        <div class="feature-ads animate-in" style="animation-delay: 0.3s;">
            
            <div class="ad-item">
                <div class="ad-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="ad-text">
                    <strong>Organiza tu Agenda</strong>
                    No más dobles reservas ni tiempos muertos. Control total de tus horarios.
                </div>
            </div>

            <div class="ad-item">
                <div class="ad-icon"><i class="fa-solid fa-bell"></i></div>
                <div class="ad-text">
                    <strong>Fideliza Clientes</strong>
                    Recordatorios automáticos de citas y promociones personalizadas.
                </div>
            </div>

            <div class="ad-item">
                <div class="ad-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <div class="ad-text">
                    <strong>Gestiona tus Finanzas</strong>
                    Revisa tus ingresos diarios y qué servicios son los más populares.
                </div>
            </div>

        </div>

        <p class="mt-auto small opacity-75 animate-in" style="animation-delay: 0.5s; z-index: 1;">Únete a la red de belleza más grande del país.</p>
    </div>

    <div class="right-panel">
        
        <a href="<?= ruta_vista('index.php', [], false) ?>" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Inicio</a>

        <div class="form-section">
            <div class="form-container animate-in" style="animation-delay: 0.2s;">
                
                <div class="text-center mb-4">
                    <h2 class="fw-800 text-dark">Iniciar Sesión</h2>
                    <p class="text-muted small">Ingresa tus credenciales para acceder al sistema.</p>
                </div>

                <?php if ($mensajeError): ?>
                    <div class="alert alert-danger d-flex align-items-center custom-alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <div><?= htmlspecialchars($mensajeError) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($mensajeExito === 'registro_exitoso'): ?>
                    <div class="alert alert-success d-flex align-items-center custom-alert" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <div>¡Cuenta creada con éxito! Por favor inicia sesión.</div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= ruta_accion('auth', 'login') ?>" id="formLogin">
                    
                    <div class="mb-3 touched-field" id="f_email">
                        <label class="form-label text-uppercase opacity-75 small fw-bold">Correo Electrónico</label>
                        <div class="input-group shadow-sm-custom">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" id="iEmail" class="form-control" placeholder="ejemplo@correo.com" required>
                        </div>
                    </div>

                    <div class="mb-3 touched-field" id="f_pass">
                        <label class="form-label text-uppercase opacity-75 small fw-bold">Contraseña</label>
                        <div class="input-group shadow-sm-custom">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" id="iPass" class="form-control border-end-0" placeholder="******" required>
                            <button class="btn btn-outline-secondary border-start-0 toggle-pass-btn" type="button" id="btnTogglePass">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <a href="<?= ruta_accion('auth', 'recuperarAccount', [], false) ?>" class="text-primary small fw-bold text-decoration-none">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-main btn-next" id="btnSubmit" disabled>
                        ENTRAR AL SISTEMA <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                    </button>

                </form>

            </div>
        </div>

        <div class="social-section">
            <p class="mb-0 small text-muted">
                ¿Aún no tienes cuenta? 
                <a href="<?= ruta_accion('auth', 'registroCliente', [], false) ?>" class="text-primary fw-bold text-decoration-none">Regístrate como Cliente</a> o 
                <a href="<?= ruta_accion('auth', 'registro', [], false) ?>" class="text-dark fw-bold text-decoration-none">Abre tu Negocio</a>
            </p>
        </div>

    </div>
</div>

<script src="<?= asset('recursos/js/login.js') ?>"></script>
</body>
</html>