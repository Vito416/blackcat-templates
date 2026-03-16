<?php
declare(strict_types=1);

$prefix = 'BlackCat\\Templates\\';
$baseDir = __DIR__ . '/';

spl_autoload_register(static function (string $class) use ($prefix, $baseDir): void {
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});
