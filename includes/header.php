<?php
require_once __DIR__ . '/menu.php';

$rol   = $_SESSION['usuario']['rol'] ?? '';
$nombre = $_SESSION['usuario']['nombre'] ?? '';
$apellido = $_SESSION['usuario']['apellido'] ?? '';
$fechaFmt = date('l, d \\d\\e F \\d\\e Y');

$dias = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Mi&eacute;rcoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'S&aacute;bado'];
$meses = ['January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril','May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto','September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'];
$fechaHumana = $dias[date('l')] . ', ' . date('d') . ' de ' . $meses[date('F')] . ' de ' . date('Y');

$notificaciones = 0;
if (in_array($rol, ['Secretaria', 'Director'], true)) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitud_matricula WHERE estado = 'Pendiente'");
        $stmt->execute();
        $notificaciones = (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $notificaciones = 0;
    }
}

$tituloPagina = $tituloPagina ?? 'Panel';
?>
<header class="app-header">
    <button type="button" class="hamburger" id="hamburger" aria-label="Abrir menu">
        <i class="bi bi-list"></i>
    </button>

    <div class="app-header-titulo">
        <h1><?= e($tituloPagina) ?></h1>
        <span class="app-header-fecha"><?= $fechaHumana ?></span>
    </div>

    <div class="app-header-acciones">
        <button type="button" class="campana" id="campana" aria-label="Notificaciones">
            <i class="bi bi-bell-fill"></i>
            <?php if ($notificaciones > 0): ?>
                <span class="campana-badge"><?= $notificaciones > 99 ? '99+' : (int)$notificaciones ?></span>
            <?php endif; ?>
        </button>
    </div>
</header>

<div class="saludo">
    ¡Hola, <strong><?= e($nombre) ?></strong>!
</div>