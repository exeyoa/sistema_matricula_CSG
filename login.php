<?php
require_once __DIR__ . '/includes/auth.php';

if (auth_user()) {
    auth_redirect_by_role();
}

$error    = flash_get('login_error') ?? '';
$motivo   = $_GET['motivo'] ?? '';
$cedulaOld = $_POST['cedula'] ?? '';

if ($motivo === 'inactividad') {
    $error = 'Tu sesion expiro por inactividad. Inicia sesion de nuevo.';
} elseif ($motivo === 'sin_permiso') {
    $error = 'No tienes permiso para acceder a esa seccion.';
} elseif ($motivo === 'logout') {
    $error = '';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-left">
                <div class="carrusel" aria-hidden="true">
                    <div class="carrusel-slide active" data-slide="0"></div>
                    <div class="carrusel-slide" data-slide="1"></div>
                    <div class="carrusel-slide" data-slide="2"></div>
                    <div class="carrusel-slide" data-slide="3"></div>
                    <div class="carrusel-slide" data-slide="4"></div>
                </div>
                <div class="carrusel-overlay" aria-hidden="true"></div>

                <div class="login-left-content">
                    <i class="bi bi-mortarboard-fill icono-birrete-blanco" aria-hidden="true"></i>
                    <h1>Sistema de<br>Matr&iacute;cula</h1>
                    <p class="frase-motivacional">Tu futuro comienza con<br>una buena educaci&oacute;n</p>
                </div>

                <div class="carrusel-dots" role="tablist" aria-label="Selector de imagen de fondo">
                    <button type="button" class="dot active" data-index="0" aria-label="Imagen 1"></button>
                    <button type="button" class="dot" data-index="1" aria-label="Imagen 2"></button>
                    <button type="button" class="dot" data-index="2" aria-label="Imagen 3"></button>
                    <button type="button" class="dot" data-index="3" aria-label="Imagen 4"></button>
                    <button type="button" class="dot" data-index="4" aria-label="Imagen 5"></button>
                </div>

                <svg class="curva-decorativa" viewBox="0 0 80 600" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,0 C40,150 60,300 40,450 C20,540 50,580 80,600 L80,0 Z" fill="rgba(26,140,184,0.85)"/>
                </svg>
            </div>

            <div class="login-right">
                <div class="login-right-content">
                    <i class="bi bi-mortarboard-fill icono-birrete-azul" aria-hidden="true"></i>
                    <h2>Sistema de Matr&iacute;cula</h2>
                    <p class="subtitulo-instituto">Instituto Profesional y T&eacute;cnico<br>Bocas del Toro</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert-login" role="alert"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" autocomplete="off" class="login-form">
                        <?= csrf_field() ?>

                        <div class="campo-login">
                            <i class="bi bi-person-fill icono-campo" aria-hidden="true"></i>
                            <input type="text" id="cedula" name="cedula" placeholder="Ingresa tu c&eacute;dula" value="<?= e($cedulaOld) ?>" required autofocus>
                        </div>

                        <div class="campo-login">
                            <i class="bi bi-lock-fill icono-campo" aria-hidden="true"></i>
                            <input type="password" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required>
                            <button type="button" class="toggle-password" aria-label="Mostrar contrasena">
                                <i class="bi bi-eye-fill" aria-hidden="true"></i>
                            </button>
                        </div>

                        <button type="submit" class="btn-login">Iniciar sesi&oacute;n</button>
                    </form>

                    <a href="recuperar-password.php" class="link-recuperar">&iquest;Olvidaste tu contraseña?</a>

                    <p class="footer-login">&copy; 2025. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>