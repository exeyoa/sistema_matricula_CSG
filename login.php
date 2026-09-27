<?php
require_once __DIR__ . '/includes/auth.php';

if (auth_user()) {
    auth_redirect_by_role();
}

$error    = flash_get('login_error') ?? '';
$ok       = '';
$motivo   = $_GET['motivo'] ?? '';
$cedulaOld = $_POST['cedula'] ?? '';

if ($motivo === 'inactividad') {
    $error = 'Tu sesion expiro por inactividad. Inicia sesion de nuevo.';
} elseif ($motivo === 'sin_permiso') {
    $error = 'No tienes permiso para acceder a esa seccion.';
} elseif ($motivo === 'logout') {
    $error = '';
}

if (isset($_GET['activado']) && $_GET['activado'] === '1') {
    $ok = 'Cuenta activada correctamente, ya puedes iniciar sesion.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $cedula    = trim($_POST['cedula'] ?? '');
    $password  = $_POST['contrasena'] ?? '';
    $cedulaOld = $cedula;

    if ($cedula === '' || $password === '') {
        $error = 'Cedula o contrasena incorrecta.';
    } else {
        $stmt = $pdo->prepare('SELECT id_usuario, cedula, nombre, apellido, contrasena, estado, id_rol, intentos_fallidos, bloqueado_hasta
                               FROM usuario WHERE cedula = :cedula LIMIT 1');
        $stmt->execute([':cedula' => $cedula]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['contrasena'] ?? '')) {
            if ($user) {
                $intentos = (int)$user['intentos_fallidos'] + 1;
                $bloqueadoHasta = null;
                if ($intentos >= 5) {
                    $bloqueadoHasta = date('Y-m-d H:i:s', time() + 900);
                    $intentos = 0;
                }
                $upd = $pdo->prepare('UPDATE usuario SET intentos_fallidos = :i, bloqueado_hasta = :b WHERE id_usuario = :id');
                $upd->execute([':i' => $intentos, ':b' => $bloqueadoHasta, ':id' => $user['id_usuario']]);
            }
            $error = 'Cedula o contrasena incorrecta.';
        } else {
            if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
                $error = 'Cuenta bloqueada temporalmente. Intenta de nuevo mas tarde.';
            } elseif ($user['estado'] !== 'Activo') {
                $error = 'Tu cuenta aun no esta activa. Contacta al Director.';
            } else {
                $rolStmt = $pdo->prepare('SELECT nombre FROM rol WHERE id_rol = :id LIMIT 1');
                $rolStmt->execute([':id' => $user['id_rol']]);
                $rolNombre = $rolStmt->fetchColumn();

                $reset = $pdo->prepare('UPDATE usuario SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = :id');
                $reset->execute([':id' => $user['id_usuario']]);

                session_regenerate_id(true);
                $_SESSION['usuario'] = [
                    'id_usuario' => (int)$user['id_usuario'],
                    'cedula'     => $user['cedula'],
                    'nombre'     => $user['nombre'],
                    'apellido'   => $user['apellido'],
                    'rol'        => $rolNombre,
                ];
                $_SESSION['ultima_actividad'] = time();
                $_SESSION['csrf_token']       = bin2hex(random_bytes(32));

                $base = '/sistema_matricula_CSG/' . strtolower($rolNombre) . '/';
                header('Location: ' . $base);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Matricula - Colegio Secundario de Guabito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-wrapper">
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
        <div class="login-right">
            <div class="login-form-container">
                <h2>Sistema de Matricula</h2>
                <p class="subtitulo">Inicia sesion para continuar</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if ($ok !== ''): ?>
                    <div class="alert alert-success" role="alert"><?= e($ok) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php" autocomplete="off">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cedula</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" value="<?= e($cedulaOld) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contrasena</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" aria-label="Mostrar contrasena">
                                <i class="bi bi-eye-fill" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>

                <div class="login-extra">
                    <a href="recuperar-password.php">Olvide mi contrasena</a>
                    <a href="solicitud-matricula.php">Solicitar matricula</a>
                </div>
                <div class="login-extra-2">
                    <a href="activar-cuenta.php">¿Es tu primera vez? Activa tu cuenta aqui</a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
