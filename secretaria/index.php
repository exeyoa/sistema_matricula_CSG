<?php
require_once __DIR__ . '/../includes/auth.php';
auth_check_rol('Secretaria');

$tituloPagina = 'Inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Secretaria - Colegio Secundario de Guabito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url_base('assets/css/layout.css')) ?>">
</head>
<body class="layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="contenido-pagina">
        <div class="placeholder-card">
            <h2>Bienvenido al Panel de Secretaria</h2>
            <p>Hola, <strong><?= e($_SESSION['usuario']['nombre']) ?></strong> (rol: <?= e($_SESSION['usuario']['rol']) ?>).</p>
            <p>Sidebar y header funcionando. El panel completo viene en el siguiente paso.</p>
        </div>
    </main>

    <script src="<?= e(url_base('assets/js/layout.js')) ?>"></script>
</body>
</html>