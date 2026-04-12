<?php

declare(strict_types=1);

return [
    'config_profile' => [
        'file' => __DIR__ . '/profiles.stub.php',
        'environment' => 'development',
    ],
    'templates_dir' => __DIR__ . '/../templates',
    'catalog_file' => __DIR__ . '/../templates/catalog.php',
    'storage_dir' => __DIR__ . '/../var',
    'integrations' => [
        'gateway' => dirname(__DIR__) . '/../blackcat-darkmesh-gateway/README.md',
        'database' => dirname(__DIR__) . '/../blackcat-darkmesh-write/README.md',
        'ao' => dirname(__DIR__) . '/../blackcat-darkmesh-ao/README.md',
        'web' => dirname(__DIR__) . '/../blackcat-darkmesh-web/README.md',
    ],
    'security' => [
        'allowed_extensions' => ['md', 'mdx', 'html', 'json', 'yml', 'yaml'],
        'disallow_php' => true,
        'require_integrations' => ['blackcat-darkmesh-gateway'],
    ],
    'telemetry' => [
        'prometheus_file' => __DIR__ . '/../var/metrics.prom',
    ],
];
