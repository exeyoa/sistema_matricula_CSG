<?php
/**
 * PLANTILLA de credenciales (base de datos + SMTP)
 * --------------------------------------------------------------------
 * Este archivo SÍ se sube al repo. Es una plantilla con valores de
 * ejemplo para XAMPP local.
 *
 * INSTRUCCIONES:
 *   1. Copia este archivo como `credenciales.php` en la misma carpeta.
 *   2. Edita `credenciales.php` con tus datos reales.
 *   3. `credenciales.php` está en `.gitignore` y NUNCA debe subirse.
 *
 * En InfinityFree los valores reales vendrán del panel de control
 * del hosting (cPanel / VistaPanel).
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sistema_matricula');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('CORREO_SMTP_HOST',         'smtp.gmail.com');
define('CORREO_SMTP_PUERTO',       587);
define('CORREO_REMITENTE',         'tu_correo@gmail.com');
define('CORREO_APP_PASSWORD',      'tu_app_password_de_gmail');
define('CORREO_REMITENTE_NOMBRE',  'Sistema de Matricula - Colegio Secundario de Guabito');