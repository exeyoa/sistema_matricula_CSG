<?php
require_once __DIR__ . '/menu.php';

$rol      = $_SESSION['usuario']['rol'] ?? '';
$items    = menu_por_rol($rol);
$current  = $_SERVER['REQUEST_URI'] ?? '';
$avatarIniciales = '';
if (!empty($_SESSION['usuario']['nombre'])) {
    $n = trim($_SESSION['usuario']['nombre']);
    $avatarIniciales = strtoupper(mb_substr($n, 0, 1));
    if (!empty($_SESSION['usuario']['apellido'])) {
        $avatarIniciales .= strtoupper(mb_substr(trim($_SESSION['usuario']['apellido']), 0, 1));
    }
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-avatar">
        <div class="avatar-circulo"><?= e($avatarIniciales) ?></div>
        <div class="avatar-info">
            <div class="avatar-nombre"><?= e($_SESSION['usuario']['nombre'] ?? '') ?> <?= e($_SESSION['usuario']['apellido'] ?? '') ?></div>
            <div class="avatar-rol"><?= e($rol) ?></div>
        </div>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($items as $item):
            $active = (strpos($current, $item['url']) === 0);
            $exactHome = ($item['url'] === url_base($rol . '/')) && (rtrim($current, '/') === rtrim($item['url'], '/') || basename($current) === basename($item['url']));
            $isActive = $exactHome || ($active && $item['url'] !== url_base($rol . '/'));
        ?>
            <a href="<?= e($item['url']) ?>" class="sidebar-link <?= $isActive ? 'active' : '' ?>">
                <i class="bi <?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= e(url_base('logout.php')) ?>" class="sidebar-link logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar sesion</span>
        </a>
    </div>
</aside>

<div class="sidebar-backdrop" id="sidebar-backdrop"></div>