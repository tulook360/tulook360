<?php
// Recibir mensaje enviado desde el controlador
$mensajeError = $payload['error'] ?? null;
// Recibir mensaje de éxito (ej: registro completado)
$mensajeExito = $_GET['msg'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · TuLook360</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Kalam:wght@300;400;700&family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= asset('recursos/css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('recursos/css/login.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card__main">
                <div class="auth-card__brand">
                    <span>✂</span>
                    <span>TuLook360</span>
                </div>

                <h1 class="auth-card__title titulo kalam">Iniciar sesión</h1>
                
                <?php if ($mensajeError): ?>
                    <div class="alerta-error">
                        <?= htmlspecialchars($mensajeError) ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensajeExito === 'registro_exitoso'): ?>
                    <div class="alerta-error" style="background: #e1fcf0; color: #00b894; border-left-color: #00b894;">
                        ¡Cuenta creada! Por favor inicia sesión.
                    </div>
                <?php endif; ?>

                <p class="auth-card__subtitle">
                    Accede a tu panel para gestionar citas, servicios y clientes.
                </p>

                <form class="form" method="POST" action="<?= ruta_accion('auth', 'login') ?>" id="formLogin">
                    <div class="form-field">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="tu-correo@ejemplo.com">
                        <small class="msg-error"></small>
                    </div>

                    <div class="form-field form-field-pass">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control input-password" placeholder="••••••••">
                        <i class="fa-solid fa-eye-slash toggle-pass" id="togglePassword"></i>
                        <small class="msg-error"></small>

                        <div class="auth-aux-row">
                            <label class="form-check">
                                <input type="checkbox" name="recordar" value="1">
                                <span>Recordarme</span>
                            </label>
                            <div class="form-link">
                                <a href="#">¿Olvidaste tu contraseña?</a>
                            </div>
                        </div>
                    </div>


                    <div class="form-actions" style="margin-top: 0.6rem;">
                        <button type="submit" class="btn btn--primario">
                            Entrar al sistema
                        </button>
                        
                        <a href="<?= ruta_vista('index.php', [], false) ?>" class="btn btn--secundario">Regresar</a>
                    </div>

                    <p class="auth-extra-note">
                        ¿Todavía no tienes cuenta?
                        <a href="<?= ruta_accion('auth', 'registro', [], false) ?>">Crear una cuenta</a>
                    </p>
                </form>
            </div>

            <aside class="auth-card__side">
                <div>
                    <div class="auth-side-logo">
                        <img src="<?= asset('recursos/img/logo.png') ?>" alt="Logo" class="auth-side-logo__img">
                    </div>
                    
                    <h2 class="auth-card__side-title">
                        Organiza tu peluquería desde un solo lugar
                    </h2>
                    <ul class="auth-card__side-list">
                        <li>Revisa tus citas del día en segundos.</li>
                        <li>Controla qué servicios ofreces y sus precios.</li>
                        <li>Evita dobles reservas y tiempos muertos.</li>
                    </ul>
                </div>

                <p class="auth-card__side-footer">
                    Después de iniciar sesión te llevaremos a tu panel principal.
                </p>
            </aside>
        </div>
    </div>

    <script src="<?= asset('recursos/js/login.js') ?>"></script>
</body>
</html>