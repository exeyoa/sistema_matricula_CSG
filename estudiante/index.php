<?php
require_once __DIR__ . '/../includes/auth.php';
auth_check_rol('Estudiante');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Panel del Estudiante</h1>
    <p>Hola, <strong><?= e($_SESSION['usuario']['nombre']) ?></strong> (rol: <?= e($_SESSION['usuario']['rol']) ?>).</p>
    <p>Sesion iniciada correctamente. Esta pagina valida que <code>auth_check_rol('Estudiante')</code> funciona.</p>
    <a href="/sistema_matricula_CSG/logout.php" class="btn btn-danger">Cerrar sesion</a>
</body>
</html>