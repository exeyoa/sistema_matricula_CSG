<?php
require_once __DIR__ . '/includes/auth.php';

if (auth_user()) {
    auth_redirect_by_role();
}

$step  = $_GET['step'] ?? '1';
$error = flash_get('rec_error') ?? '';
$ok    = flash_get('rec_ok') ?? '';
$link  = flash_get('rec_link') ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($step === '1') {
        $cedula = trim($_POST['cedula'] ?? '');
        if ($cedula === '') {
            flash_set('rec_error', 'Ingresa tu cedula.');
            header('Location: recuperar-password.php');
            exit;
        }

        $stmt = $pdo->prepare('SELECT id_usuario FROM usuario WHERE cedula = :c LIMIT 1');
        $stmt->execute([':c' => $cedula]);
        $user = $stmt->fetch();

        if (!$user) {
            flash_set('rec_error', 'Si la cedula existe, se genero un enlace de recuperacion.');
            header('Location: recuperar-password.php?step=1');
            exit;
        }

        $token    = bin2hex(random_bytes(32));
        $expira   = date('Y-m-d H:i:s', time() + 3600);

        $del = $pdo->prepare('DELETE FROM token_recuperacion WHERE id_usuario = :id AND usado = 0');
        $del->execute([':id' => $user['id_usuario']]);

        $ins = $pdo->prepare('INSERT INTO token_recuperacion (id_usuario, token, fecha_expiracion) VALUES (:id, :t, :e)');
        $ins->execute([':id' => $user['id_usuario'], ':t' => $token, ':e' => $expira]);

        $link = 'http://localhost/sistema_matricula_CSG/recuperar-password.php?step=2&token=' . $token;
        flash_set('rec_ok', 'Se genero un enlace de recuperacion. En produccion se enviara por email.');
        flash_set('rec_link', $link);
        header('Location: recuperar-password.php?step=1');
        exit;
    }

    if ($step === '2') {
        $token       = trim($_POST['token'] ?? '');
        $nueva       = $_POST['nueva'] ?? '';
        $confirmar   = $_POST['confirmar'] ?? '';

        if ($nueva === '' || strlen($nueva) < 8) {
            flash_set('rec_error', 'La nueva contrasena debe tener al menos 8 caracteres.');
            header('Location: recuperar-password.php?step=2&token=' . urlencode($token));
            exit;
        }
        if ($nueva !== $confirmar) {
            flash_set('rec_error', 'Las contrasenas no coinciden.');
            header('Location: recuperar-password.php?step=2&token=' . urlencode($token));
            exit;
        }

        $stmt = $pdo->prepare('SELECT id_usuario, fecha_expiracion, usado FROM token_recuperacion WHERE token = :t LIMIT 1');
        $stmt->execute([':t' => $token]);
        $tok = $stmt->fetch();

        if (!$tok || $tok['usado'] || strtotime($tok['fecha_expiracion']) < time()) {
            flash_set('rec_error', 'El enlace es invalido o expiro. Solicita uno nuevo.');
            header('Location: recuperar-password.php');
            exit;
        }

        $hash = password_hash($nueva, PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        $upd = $pdo->prepare('UPDATE usuario SET contrasena = :h WHERE id_usuario = :id');
        $upd->execute([':h' => $hash, ':id' => $tok['id_usuario']]);

        $used = $pdo->prepare('UPDATE token_recuperacion SET usado = 1 WHERE id_usuario = :id');
        $used->execute([':id' => $tok['id_usuario']]);
        $pdo->commit();

        flash_set('rec_ok', 'Contrasena actualizada. Inicia sesion con tu nueva contrasena.');
        header('Location: login.php');
        exit;
    }
}

$tokenGet = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contrasena - Colegio Secundario de Guabito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper single-column">
        <div class="login-right full">
            <div class="login-form-container narrow">

                <?php if ($step === '1'): ?>
                    <h2>Recuperar contrasena</h2>
                    <p class="subtitulo">Ingresa tu cedula para generar un enlace de recuperacion.</p>

                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                    <?php if ($ok): ?>
                        <div class="alert alert-success">
                            <?= e($ok) ?><br>
                            <small>Enlace temporal (desarrollo): <a href="<?= e($link) ?>">abrir</a></small>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="recuperar-password.php?step=1">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cedula</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Generar enlace</button>
                    </form>

                <?php else: ?>
                    <h2>Nueva contrasena</h2>
                    <p class="subtitulo">Define tu nueva contrasena.</p>

                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

                    <form method="POST" action="recuperar-password.php?step=2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="token" value="<?= e($tokenGet) ?>">
                        <div class="mb-3">
                            <label for="nueva" class="form-label">Nueva contrasena</label>
                            <input type="password" class="form-control" id="nueva" name="nueva" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="confirmar" class="form-label">Confirmar contrasena</label>
                            <input type="password" class="form-control" id="confirmar" name="confirmar" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Cambiar contrasena</button>
                    </form>
                <?php endif; ?>

                <div class="login-extra">
                    <a href="login.php">Volver al inicio de sesion</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>