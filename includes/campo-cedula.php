<?php
$name  = isset($name) && $name !== '' ? $name : 'cedula';
$value = isset($value) ? (string)$value : '';

$provincias = [
    ['00', 'Provincia'],
    ['01', 'Bocas del Toro'],
    ['02', 'Cocle'],
    ['03', 'Colon'],
    ['04', 'Chiriqui'],
    ['05', 'Darien'],
    ['06', 'Herrera'],
    ['07', 'Los Santos'],
    ['08', 'Panama'],
    ['09', 'Veraguas'],
    ['10', 'Panama Oeste'],
    ['11', 'Guna Yala'],
    ['12', 'Ngabe-Bugle'],
    ['13', 'Embera-Wounaan'],
];

$tipos = [
    ['00', 'Tipo'],
    ['N',  'N - Naturalizado'],
    ['E',  'E - Extranjero'],
    ['EC', 'EC'],
    ['PE', 'PE - Panameno Exterior'],
    ['AV', 'AV - Avecindado'],
    ['PI', 'PI'],
];
?>
<div class="campo-cedula" data-cedula-name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
    <select class="cedula-prov" aria-label="Provincia">
        <?php foreach ($provincias as $p): ?>
            <option value="<?= htmlspecialchars($p[0], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($p[0] === '00' ? $p[1] : $p[0] . ' - ' . $p[1], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <select class="cedula-tipo" aria-label="Tipo especial">
        <?php foreach ($tipos as $t): ?>
            <option value="<?= htmlspecialchars($t[0], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($t[1], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" inputmode="numeric" pattern="[0-9]*" class="cedula-libro" placeholder="Libro" maxlength="5" aria-label="Libro">
    <input type="text" inputmode="numeric" pattern="[0-9]*" class="cedula-tomo" placeholder="Tomo" maxlength="5" aria-label="Tomo">
    <input type="hidden" name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
    <div class="cedula-error" role="alert">Completa provincia (o tipo especial) + libro + tomo.</div>
</div>