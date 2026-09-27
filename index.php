<?php
require_once __DIR__ . '/includes/auth.php';

if (auth_user()) {
    auth_redirect_by_role();
}
header('Location: /sistema_matricula_CSG/login.php');
exit;