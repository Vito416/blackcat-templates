<?php

declare(strict_types=1);

return [
    [
        'id' => 'module_readme',
        'name' => 'Module README',
        'description' => 'Standard README describing config, CLI, telemetry, and integration hooks.',
        'file' => __DIR__ . '/module-readme.md',
        'tags' => ['docs', 'bootstrap'],
        'integrations' => ['blackcat-config', 'blackcat-database', 'blackcat-orchestrator'],
        'placeholders' => [
            ['token' => 'MODULE_NAME', 'description' => 'Display name for the repository.', 'required' => true],
            ['token' => 'DESCRIPTION', 'description' => 'One-line description/mission.', 'required' => true],
            ['token' => 'CLI_USAGE', 'description' => 'CLI example to surface in README.', 'default' => 'php bin/<repo>'],
            ['token' => 'TELEMETRY_DESC', 'description' => 'How telemetry is exposed (Prometheus endpoints, etc).', 'default' => 'Exports Prometheus metrics.'],
        ],
    ],
    [
        'id' => 'module_roadmap',
        'name' => 'Module Roadmap',
        'description' => 'Stage-by-stage roadmap stub referencing integration partners.',
        'file' => __DIR__ . '/module-roadmap.md',
        'tags' => ['docs', 'roadmap'],
        'integrations' => ['blackcat-config', 'blackcat-orchestrator', 'blackcat-governance'],
        'placeholders' => [
            ['token' => 'MODULE_NAME', 'description' => 'Repository display name.', 'required' => true],
            ['token' => 'VISION', 'description' => 'North-star goal for the module.', 'required' => true],
            ['token' => 'STAGE1_FOCUS', 'description' => 'Stage 1 focus description.', 'required' => true],
            ['token' => 'STAGE2_FOCUS', 'description' => 'Stage 2 focus description.', 'required' => true],
            ['token' => 'COMPLIANCE_OWNER', 'description' => 'Person/alias accountable for compliance sign-off.', 'default' => 'governance@blackcat'],
        ],
    ],
];
