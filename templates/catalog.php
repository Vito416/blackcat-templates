<?php

declare(strict_types=1);

return [
    [
        'id' => 'module_readme',
        'name' => 'Module README',
        'description' => 'Standard README describing config, CLI, telemetry, and integration hooks.',
        'file' => __DIR__ . '/module-readme.md',
        'tags' => ['docs', 'bootstrap'],
        'integrations' => ['blackcat-darkmesh-gateway', 'blackcat-darkmesh-write', 'blackcat-darkmesh-ao'],
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
        'integrations' => ['blackcat-darkmesh-gateway', 'blackcat-darkmesh-write', 'blackcat-darkmesh-ao'],
        'placeholders' => [
            ['token' => 'MODULE_NAME', 'description' => 'Repository display name.', 'required' => true],
            ['token' => 'VISION', 'description' => 'North-star goal for the module.', 'required' => true],
            ['token' => 'STAGE1_FOCUS', 'description' => 'Stage 1 focus description.', 'required' => true],
            ['token' => 'STAGE2_FOCUS', 'description' => 'Stage 2 focus description.', 'required' => true],
            ['token' => 'COMPLIANCE_OWNER', 'description' => 'Person/alias accountable for compliance sign-off.', 'default' => 'governance@blackcat'],
        ],
    ],
    [
        'id' => 'gateway_search_variant_signal',
        'name' => 'Gateway Search Variant - Signal',
        'description' => 'Glass-like search UX with ambient gradient for discovery-heavy gateways.',
        'file' => __DIR__ . '/gateway-search-variant-signal.html',
        'tags' => ['gateway', 'search', 'ux', 'public-template'],
        'integrations' => ['blackcat-darkmesh-gateway', 'blackcat-darkmesh-ao', 'blackcat-darkmesh-write'],
        'placeholders' => [
            ['token' => 'SITE_TITLE', 'description' => 'Public title shown to end users.', 'required' => true],
            ['token' => 'SITE_TAGLINE', 'description' => 'Short subtitle under the title.', 'default' => 'Discover verified pages across the mesh.'],
            ['token' => 'GATEWAY_ORIGIN', 'description' => 'Gateway origin used for API calls.', 'required' => true],
            ['token' => 'SEARCH_ACTION', 'description' => 'Template action used for route resolution.', 'default' => 'public.resolve-route'],
        ],
    ],
    [
        'id' => 'gateway_search_variant_bastion',
        'name' => 'Gateway Search Variant - Bastion',
        'description' => 'Bold, high-contrast operational console style for power users.',
        'file' => __DIR__ . '/gateway-search-variant-bastion.html',
        'tags' => ['gateway', 'search', 'ux', 'public-template'],
        'integrations' => ['blackcat-darkmesh-gateway', 'blackcat-darkmesh-ao', 'blackcat-darkmesh-write'],
        'placeholders' => [
            ['token' => 'SITE_TITLE', 'description' => 'Public title shown to end users.', 'required' => true],
            ['token' => 'SITE_TAGLINE', 'description' => 'Short subtitle under the title.', 'default' => 'Route requests safely through the gateway boundary.'],
            ['token' => 'GATEWAY_ORIGIN', 'description' => 'Gateway origin used for API calls.', 'required' => true],
            ['token' => 'SEARCH_ACTION', 'description' => 'Template action used for route resolution.', 'default' => 'public.resolve-route'],
        ],
    ],
    [
        'id' => 'gateway_search_variant_horizon',
        'name' => 'Gateway Search Variant - Horizon',
        'description' => 'Editorial look focused on readability and trust details.',
        'file' => __DIR__ . '/gateway-search-variant-horizon.html',
        'tags' => ['gateway', 'search', 'ux', 'public-template'],
        'integrations' => ['blackcat-darkmesh-gateway', 'blackcat-darkmesh-ao', 'blackcat-darkmesh-write'],
        'placeholders' => [
            ['token' => 'SITE_TITLE', 'description' => 'Public title shown to end users.', 'required' => true],
            ['token' => 'SITE_TAGLINE', 'description' => 'Short subtitle under the title.', 'default' => 'Search by route, domain, or page id.'],
            ['token' => 'GATEWAY_ORIGIN', 'description' => 'Gateway origin used for API calls.', 'required' => true],
            ['token' => 'SEARCH_ACTION', 'description' => 'Template action used for route resolution.', 'default' => 'public.resolve-route'],
        ],
    ],
];
