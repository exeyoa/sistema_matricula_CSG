<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

const INACTIVIDAD_MAX = 300;

function auth_user(): ?array {
    return $_SESSION['usuario'] ?? null;
}

function auth_check(): void {
    if (empty($_SESSION['usuario']['id_usuario'])) {
        header('Location: /sistema_matricula_CSG/login.php');
        exit;
    }
    if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > INACTIVIDAD_MAX)) {
        session_unset();
        session_destroy();
        header('Location: /sistema_matricula_CSG/login.php?motivo=inactividad');
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
}

function auth_check_rol(string $rolEsperado): void {
    auth_check();
    $rolSesion = strtolower($_SESSION['usuario']['rol'] ?? '');
    if ($rolSesion !== strtolower($rolEsperado)) {
        header('Location: /sistema_matricula_CSG/login.php?motivo=sin_permiso');
        exit;
    }
}

function auth_redirect_by_role(): void {
    if (empty($_SESSION['usuario']['rol'])) {
        header('Location: /sistema_matricula_CSG/login.php');
        exit;
    }
    $rol = strtolower($_SESSION['usuario']['rol']);
    $base = '/sistema_matricula_CSG/' . $rol . '/';
    header('Location: ' . $base);
    exit;
}

function e(string $valor): string {
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

function flash_set(string $clave, string $mensaje): void {
    $_SESSION['flash'][$clave] = $mensaje;
}

function flash_get(string $clave): ?string {
    if (!isset($_SESSION['flash'][$clave])) {
        return null;
    }
    $msg = $_SESSION['flash'][$clave];
    unset($_SESSION['flash'][$clave]);
    return $msg;
}