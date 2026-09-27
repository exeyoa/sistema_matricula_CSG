<?php
require_once __DIR__ . '/includes/auth.php';

if (auth_user()) {
    auth_redirect_by_role();
}

$step   = $_GET['step'] ?? '1';
$error  = flash_get('act_error') ?? '';
$motivo = $_GET['motivo'] ?? '';

if ($motivo === 'expirado') {
    $error = 'Tu sesion de activacion expiro. Empieza de nuevo.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($step === '1') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $cnt = $pdo->prepare('SELECT COUNT(*) FROM intentos_activacion
                              WHERE ip = :ip AND fecha > (NOW() - INTERVAL 15 MINUTE)');
        $cnt->execute([':ip' => $ip]);
        if ((int) $cnt->fetchColumn() >= 10) {
            flash_set('act_error', 'Has excedido el limite de intentos. Espera 15 minutos e intenta de nuevo.');
            header('Location: activar-cuenta.php');
            exit;
        }

        $reg = $pdo->prepare('INSERT INTO intentos_activacion (ip) VALUES (:ip)');
        $reg->execute([':ip' => $ip]);

        $cedula = trim($_POST['cedula'] ?? '');
        if ($cedula === '') {
            flash_set('act_error', 'No fue posible activar una cuenta con esos datos. Verifica la cedula o contacta a la secretaria.');
            header('Location: activar-cuenta.php');
            exit;
        }

        $stmt = $pdo->prepare('SELECT id_usuario FROM usuario
                               WHERE cedula = :c AND estado = :e AND contrasena IS NULL
                               LIMIT 1');
        $stmt->execute([':c' => $cedula, ':e' => 'Inactivo']);
        $user = $stmt->fetch();

        if (!$user) {
            flash_set('act_error', 'No fue posible activar una cuenta con esos datos. Verifica la cedula o contacta a la secretaria.');
            header('Location: activar-cuenta.php');
            exit;
        }

        $_SESSION['activacion_id_usuario'] = (int) $user['id_usuario'];
        header('Location: activar-cuenta.php?step=2');
        exit;
    }

    if ($step === '2') {
        $idUsuario = $_SESSION['activacion_id_usuario'] ?? null;
        if (!$idUsuario) {
            header('Location: activar-cuenta.php?motivo=expirado');
            exit;
        }

        $nueva     = $_POST['nueva'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        if ($nueva === '' || strlen($nueva) < 8) {
            flash_set('act_error', 'La nueva contrasena debe tener al menos 8 caracteres.');
            header('Location: activar-cuenta.php?step=2');
            exit;
        }
        if ($nueva !== $confirmar) {
            flash_set('act_error', 'Las contrasenas no coinciden.');
            header('Location: activar-cuenta.php?step=2');
            exit;
        }

        $hash = password_hash($nueva, PASSWORD_DEFAULT);

        $upd = $pdo->prepare("UPDATE usuario
                              SET contrasena = :h, estado = 'Activo'
                              WHERE id_usuario = :id
                                AND contrasena IS NULL
                                AND estado = 'Inactivo'");
        $upd->execute([':h' => $hash, ':id' => $idUsuario]);

        if ($upd->rowCount() === 0) {
            unset($_SESSION['activacion_id_usuario']);
            flash_set('act_error', 'No fue posible activar la cuenta. Contacta a la secretaria.');
            header('Location: activar-cuenta.php');
            exit;
        }

        unset($_SESSION['activacion_id_usuario']);
        header('Location: login.php?activado=1');
        exit;
    }
}

$enPaso2 = ($step === '2') && !empty($_SESSION['activacion_id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar cuenta - Colegio Secundario de Guabito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .login-wrapper { min-height: 100vh; }
        .login-right.full-col {
            flex: 1 1 100%;
            background: linear-gradient(135deg, #0f3460 0%, #1a8cb8 100%);
        }
        .login-wrapper.single-column .login-right.full-col { background: #fff; }
        .login-form-container.narrow { max-width: 420px; }
    </style>
</head>
<body>
    <div class="login-wrapper <?= $enPaso2 ? 'single-column' : '' ?>">
        <?php if (!$enPaso2): ?>
        <div class="login-left">
            <div class="carrusel" aria-hidden="true">
                <div class="carrusel-slide active" data-slide="0"></div>
                <div class="carrusel-slide" data-slide="1"></div>
                <div class="carrusel-slide" data-slide="2"></div>
                <div class="carrusel-slide" data-slide="3"></div>
                <div class="carrusel-slide" data-slide="4"></div>
            </div>
            <div class="carrusel-overlay" aria-hidden="true"></div>
            <div class="login-brand">
                <div class="logo-placeholder">CSG</div>
                <h1>Colegio Secundario<br>de Guabito</h1>
                <p class="motivacional">"Educar es plantar un arbol que dara sombra a muchas generaciones"</p>
            </div>
            <div class="carrusel-dots" role="tablist" aria-label="Selector de imagen">
                <button type="button" class="dot active" data-index="0" aria-label="Imagen 1"></button>
                <button type="button" class="dot" data-index="1" aria-label="Imagen 2"></button>
                <button type="button" class="dot" data-index="2" aria-label="Imagen 3"></button>
                <button type="button" class="dot" data-index="3" aria-label="Imagen 4"></button>
                <button type="button" class="dot" data-index="4" aria-label="Imagen 5"></button>
            </div>
        </div>
        <?php endif; ?>
        <div class="login-right <?= $enPaso2 ? 'full-col' : '' ?>">
            <div class="login-form-container <?= $enPaso2 ? 'narrow' : '' ?>">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($enPaso2): ?>
                    <h2>Crea tu contrasena</h2>
                    <p class="subtitulo">Define la contrasena con la que vas a iniciar sesion.</p>

                    <form method="POST" action="activar-cuenta.php?step=2" autocomplete="off">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="nueva" class="form-label">Nueva contrasena</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="nueva" name="nueva" required minlength="8" autofocus>
                                <button type="button" class="btn btn-outline-secondary toggle-password" aria-label="Mostrar contrasena">
                                    <i class="bi bi-eye-fill" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar" class="form-label">Confirmar contrasena</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmar" name="confirmar" required minlength="8">
                                <button type="button" class="btn btn-outline-secondary toggle-password" aria-label="Mostrar contrasena">
                                    <i class="bi bi-eye-fill" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Activar mi cuenta</button>
                    </form>

                    <div class="login-extra" style="justify-content: center; margin-top: 1.5rem;">
                        <a href="activar-cuenta.php">Empezar de nuevo</a>
                    </div>

                <?php else: ?>
                    <h2>Activar cuenta</h2>
                    <p class="subtitulo">Ingresa tu cedula para comenzar la activacion.</p>

                    <form method="POST" action="activar-cuenta.php?step=1" autocomplete="off">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cedula</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Continuar</button>
                    </form>

                    <div class="login-extra">
                        <a href="login.php">Ya tengo cuenta</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>