<?php
function menu_por_rol(string $rol): array {
    $base = '/sistema_matricula_CSG';
    $menus = [
        'Director' => [
            ['icon' => 'bi-house-door',      'label' => 'Inicio',         'url' => $base . '/director/'],
            ['icon' => 'bi-people',          'label' => 'Usuarios',       'url' => $base . '/director/usuarios.php'],
            ['icon' => 'bi-person-badge',    'label' => 'Profesores',     'url' => $base . '/director/profesores.php'],
            ['icon' => 'bi-mortarboard',     'label' => 'Estudiantes',    'url' => $base . '/director/estudiantes.php'],
            ['icon' => 'bi-book',            'label' => 'Materias',       'url' => $base . '/director/materias.php'],
            ['icon' => 'bi-collection',      'label' => 'Grupos',         'url' => $base . '/director/grupos.php'],
            ['icon' => 'bi-graph-up',        'label' => 'Reportes',       'url' => $base . '/director/reportes.php'],
            ['icon' => 'bi-gear',            'label' => 'Configuracion',  'url' => $base . '/director/configuracion.php'],
        ],
        'Secretaria' => [
            ['icon' => 'bi-house-door',      'label' => 'Inicio',         'url' => $base . '/secretaria/'],
            ['icon' => 'bi-mortarboard',     'label' => 'Estudiantes',    'url' => $base . '/secretaria/estudiantes.php'],
            ['icon' => 'bi-person-badge',    'label' => 'Profesores',     'url' => $base . '/secretaria/profesores.php'],
            ['icon' => 'bi-book',            'label' => 'Materias',       'url' => $base . '/secretaria/materias.php'],
            ['icon' => 'bi-collection',      'label' => 'Grupos',         'url' => $base . '/secretaria/grupos.php'],
            ['icon' => 'bi-clipboard-check', 'label' => 'Matriculas',     'url' => $base . '/secretaria/matriculas.php'],
            ['icon' => 'bi-inbox',           'label' => 'Solicitudes',    'url' => $base . '/secretaria/solicitudes.php'],
            ['icon' => 'bi-graph-up',        'label' => 'Reportes',       'url' => $base . '/secretaria/reportes.php'],
        ],
        'Profesor' => [
            ['icon' => 'bi-house-door',      'label' => 'Inicio',         'url' => $base . '/profesor/'],
            ['icon' => 'bi-book',            'label' => 'Mis Materias',   'url' => $base . '/profesor/materias.php'],
            ['icon' => 'bi-people',          'label' => 'Mis Estudiantes','url' => $base . '/profesor/estudiantes.php'],
            ['icon' => 'bi-pencil-square',   'label' => 'Notas',          'url' => $base . '/profesor/notas.php'],
            ['icon' => 'bi-calendar-week',   'label' => 'Horario',        'url' => $base . '/profesor/horario.php'],
            ['icon' => 'bi-person-circle',   'label' => 'Perfil',         'url' => $base . '/profesor/perfil.php'],
        ],
        'Estudiante' => [
            ['icon' => 'bi-house-door',      'label' => 'Inicio',         'url' => $base . '/estudiante/'],
            ['icon' => 'bi-book',            'label' => 'Mis Materias',   'url' => $base . '/estudiante/materias.php'],
            ['icon' => 'bi-pencil-square',   'label' => 'Mis Notas',      'url' => $base . '/estudiante/notas.php'],
            ['icon' => 'bi-calendar-week',   'label' => 'Mi Horario',     'url' => $base . '/estudiante/horario.php'],
            ['icon' => 'bi-person-circle',   'label' => 'Perfil',         'url' => $base . '/estudiante/perfil.php'],
        ],
    ];
    return $menus[$rol] ?? [];
}

function url_base(string $path = ''): string {
    return '/sistema_matricula_CSG' . ($path !== '' ? '/' . ltrim($path, '/') : '');
}