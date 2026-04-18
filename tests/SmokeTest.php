<?php

declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';

use BlackCat\Templates\Config\TemplateConfig;
use BlackCat\Templates\Registry\TemplateRegistry;
use BlackCat\Templates\Renderer\TemplateRenderer;
use BlackCat\Templates\Security\SecurityScanner;
use BlackCat\Templates\Telemetry\TemplateMetrics;

$config = TemplateConfig::fromArray([
    'templates_dir' => __DIR__ . '/../templates',
    'catalog_file' => __DIR__ . '/../templates/catalog.php',
    'telemetry' => [
        'prometheus_file' => sys_get_temp_dir() . '/templates-metrics-' . bin2hex(random_bytes(4)) . '.prom',
    ],
    'security' => [
        'allowed_extensions' => ['md', 'html'],
        'disallow_php' => true,
        'require_integrations' => ['blackcat-darkmesh-gateway'],
    ],
]);

$metrics = new TemplateMetrics($config->telemetryFile());
$registry = new TemplateRegistry($config, $metrics);
$scanner = new SecurityScanner($config);
$renderer = new TemplateRenderer($registry, $metrics, $scanner);

$templates = $registry->all();
assert(count($templates) >= 2, 'Expected at least two templates in catalog.');

$readme = $renderer->render('module_readme', [
    'MODULE_NAME' => 'BlackCat Smoke',
    'DESCRIPTION' => 'Testing automation wiring.',
    'CLI_USAGE' => 'php bin/smoke smoke:run',
]);
assert(str_contains($readme, '# BlackCat Smoke'));
assert(str_contains($readme, 'Testing automation wiring.'));

$tmpRoadmap = sys_get_temp_dir() . '/blackcat-roadmap-' . bin2hex(random_bytes(4)) . '.md';
$renderer->writeTo('module_roadmap', [
    'MODULE_NAME' => 'BlackCat Smoke',
    'VISION' => 'Ship safe templates quickly.',
    'STAGE1_FOCUS' => 'Wire config + telemetry.',
    'STAGE2_FOCUS' => 'Automate CLI scaffolding.',
    'COMPLIANCE_OWNER' => 'qa-bot@blackcat',
], $tmpRoadmap);
assert(is_file($tmpRoadmap));

$issues = $scanner->scan($registry->get('module_readme'));
assert($issues === [], 'Expected template security scan to pass.');

$searchTemplate = $renderer->render('gateway_search_variant_signal', [
    'SITE_TITLE' => 'Darkmesh Search',
    'GATEWAY_ORIGIN' => 'https://gateway.example',
    'SEARCH_ACTION' => 'public.resolve-route',
]);
assert(str_contains($searchTemplate, 'Darkmesh Search'));
assert(str_contains($searchTemplate, 'public.resolve-route'));

$menuFragment = $renderer->render('gateway_component_menu_pulse', [
    'SITE_TITLE' => 'Darkmesh Search',
]);
$searchFragment = $renderer->render('gateway_component_search_pulse', [
    'SITE_TITLE' => 'Darkmesh Search',
    'SITE_TAGLINE' => 'Composable UX',
]);
$resultsFragment = $renderer->render('gateway_component_results_pulse', [
    'INDEX_MODE_LABEL' => 'fair-order',
]);
$footerFragment = $renderer->render('gateway_component_footer_pulse', [
    'FOOTER_NOTE' => 'Deterministic templates',
]);

$composedTemplate = $renderer->render('gateway_search_shell_core', [
    'SITE_TITLE' => 'Darkmesh Search',
    'GATEWAY_ORIGIN' => 'https://gateway.example',
    'MENU_COMPONENT' => $menuFragment,
    'SEARCH_COMPONENT' => $searchFragment,
    'RESULTS_COMPONENT' => $resultsFragment,
    'FOOTER_COMPONENT' => $footerFragment,
]);
assert(str_contains($composedTemplate, 'gateway-search-form'));
assert(str_contains($composedTemplate, 'gateway-results-list'));

$metricsFile = $config->telemetryFile();
assert(is_string($metricsFile));
assert(is_file($metricsFile));

echo "Templates smoke test ok\n";
