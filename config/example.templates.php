<?php

declare(strict_types=1);

return [
    'config_profile' => [
        'file' => dirname(__DIR__) . '/../blackcat-config/config/profiles.php',
        'environment' => 'development',
    ],
    'templates_dir' => __DIR__ . '/../templates',
    'catalog_file' => __DIR__ . '/../templates/catalog.php',
    'storage_dir' => __DIR__ . '/../var',
    'integrations' => [
        'config' => dirname(__DIR__) . '/../blackcat-config/docs/README.md',
        'database' => dirname(__DIR__) . '/../blackcat-database/schema/001_table.mysql.sql',
        'auth' => dirname(__DIR__) . '/../blackcat-auth/docs/ROADMAP.md',
        'orchestrator' => dirname(__DIR__) . '/../blackcat-orchestrator/docs/ROADMAP.md',
    ],
    'security' => [
        'allowed_extensions' => ['md', 'mdx', 'yml', 'yaml'],
        'disallow_php' => true,
        'require_integrations' => ['blackcat-config', 'blackcat-orchestrator'],
    ],
    'telemetry' => [
        'prometheus_file' => __DIR__ . '/../var/metrics.prom',
    ],
];
