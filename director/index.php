<?php
require_once __DIR__ . '/../includes/auth.php';
auth_check_rol('Director');

$tituloPagina = 'Inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Director - Colegio Secundario de Guabito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url_base('assets/css/layout.css')) ?>">
</head>
<body class="layout">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="contenido-pagina">
        <div class="placeholder-card">
            <h2>Bienvenido al Panel del Director</h2>
            <p>Hola, <strong><?= e($_SESSION['usuario']['nombre']) ?></strong> (rol: <?= e($_SESSION['usuario']['rol']) ?>).</p>
            <p>El sidebar y el header ya funcionan. Esta pagina es solo un placeholder; el panel completo
            (tarjetas resumen, grafica de matriculas por grado, feed de actividades, CRUDs) viene en el
            siguiente paso.</p>
            <p>Proba el sidebar: clickea los distintos items para navegar. En movil, usa el boton hamburguesa
            arriba a la izquierda.</p>
        </div>
    </main>

    <script src="<?= e(url_base('assets/js/layout.js')) ?>"></script>
</body>
</html>