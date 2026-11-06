<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'StockFlow\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    if (str_starts_with($relative, 'Api\\Controllers\\')) {
        $relative = substr($relative, strlen('Api\\Controllers\\'));
        $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'controllers';
    } else {
        $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app';
    }
    $file = $base . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});
