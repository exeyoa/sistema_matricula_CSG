<?php
spl_autoload_register(function ($class) {
    $prefix  = 'PHPMailer\\PHPMailer\\';
    $baseDir = __DIR__ . '/src/';
    $len     = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});