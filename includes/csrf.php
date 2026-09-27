<?php
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): bool {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postToken    = $_POST['csrf_token'] ?? '';
    if ($sessionToken === '' || $postToken === '' || !hash_equals($sessionToken, $postToken)) {
        http_response_code(419);
        die('Token CSRF invalido o expirado. Recarga la pagina e intenta de nuevo.');
    }
    return true;
}