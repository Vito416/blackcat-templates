<?php

declare(strict_types=1);

namespace BlackCat\Templates\Release;

use BlackCat\Templates\Renderer\TemplateRenderer;
use RuntimeException;

final class GatewayReleaseBuilder
{
    private const VARIANT_TEMPLATE_IDS = [
        'signal' => 'gateway_search_variant_signal',
        'bastion' => 'gateway_search_variant_bastion',
        'horizon' => 'gateway_search_variant_horizon',
    ];

    /**
     * @var array<string,array{menu:string,search:string,results:string,footer:string,shell_body_class:string,shell_extra_css:string}>
     */
    private const COMPOSED_PROFILES = [
        'pulse' => [
            'menu' => 'gateway_component_menu_pulse',
            'search' => 'gateway_component_search_pulse',
            'results' => 'gateway_component_results_pulse',
            'footer' => 'gateway_component_footer_pulse',
            'shell_body_class' => '',
            'shell_extra_css' => '',
        ],
        'atlas' => [
            'menu' => 'gateway_component_menu_forge',
            'search' => 'gateway_component_search_atlas',
            'results' => 'gateway_component_results_atlas',
            'footer' => 'gateway_component_footer_atlas',
            'shell_body_class' => 'text-black',
            'shell_extra_css' => ':root { --mesh-bg-1: #ffcd38; --mesh-bg-2: #ff7e1f; --mesh-fx-a: rgba(255,255,255,0.35); --mesh-fx-b: rgba(0,0,0,0.15); }',
        ],
        'lumen' => [
            'menu' => 'gateway_component_menu_zenith',
            'search' => 'gateway_component_search_lumen',
            'results' => 'gateway_component_results_lumen',
            'footer' => 'gateway_component_footer_lumen',
            'shell_body_class' => 'text-slate-900',
            'shell_extra_css' => ':root { --mesh-bg-1: #dfe7ff; --mesh-bg-2: #9db8ff; --mesh-fx-a: rgba(255,255,255,0.45); --mesh-fx-b: rgba(164,179,255,0.30); }',
        ],
    ];

    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    /**
     * @param array<string,string> $payload
     */
    public function composeProfile(string $profile, array $payload): string
    {
        $selected = $this->composedProfile($profile);

        $menu = $this->renderer->render($selected['menu'], $payload);
        $search = $this->renderer->render($selected['search'], $payload);
        $results = $this->renderer->render($selected['results'], $payload);
        $footer = $this->renderer->render($selected['footer'], $payload);

        $shellPayload = $payload;
        $shellPayload['MENU_COMPONENT'] = $menu;
        $shellPayload['SEARCH_COMPONENT'] = $search;
        $shellPayload['RESULTS_COMPONENT'] = $results;
        $shellPayload['FOOTER_COMPONENT'] = $footer;
        if (!array_key_exists('SHELL_BODY_CLASS', $shellPayload)) {
            $shellPayload['SHELL_BODY_CLASS'] = $selected['shell_body_class'];
        }
        if (!array_key_exists('SHELL_EXTRA_CSS', $shellPayload)) {
            $shellPayload['SHELL_EXTRA_CSS'] = $selected['shell_extra_css'];
        }

        return $this->renderer->render('gateway_search_shell_core', $shellPayload);
    }

    /**
     * @param list<string> $profiles
     * @param array<string,string> $payload
     */
    public function build(array $profiles, array $payload, string $outputDir, string $releaseVersion): string
    {
        if ($profiles === []) {
            throw new RuntimeException('At least one profile must be provided.');
        }
        if ($releaseVersion === '') {
            throw new RuntimeException('Release version must not be empty.');
        }

        $this->ensureDirectory($outputDir);

        $artifacts = [];
        foreach ($profiles as $profile) {
            $spec = $this->resolveArtifact($profile);
            $rendered = $spec['kind'] === 'composed'
                ? $this->composeProfile($spec['key'], $payload)
                : $this->renderer->render($spec['templateId'], $payload);

            $filePath = $outputDir . DIRECTORY_SEPARATOR . $spec['file'];
            $this->writeFile($filePath, $rendered);

            $artifacts[$spec['key']] = [
                'variant' => $spec['key'],
                'templateId' => $spec['templateId'],
                'file' => $spec['file'],
                'sha256' => hash('sha256', $rendered),
                'bytes' => strlen($rendered),
            ];
        }

        ksort($artifacts, SORT_STRING);

        $manifest = [
            'releaseVersion' => $releaseVersion,
            'generatedAt' => ReleaseDeterminism::generatedAt($payload),
            'variants' => $artifacts,
        ];

        $manifestPath = $outputDir . DIRECTORY_SEPARATOR . 'manifest.json';
        $this->writeFile($manifestPath, $this->encodeJson($manifest));

        return $manifestPath;
    }

    /**
     * @return array{menu:string,search:string,results:string,footer:string,shell_body_class:string,shell_extra_css:string}
     */
    private function composedProfile(string $profile): array
    {
        $selected = self::COMPOSED_PROFILES[strtolower(trim($profile))] ?? null;
        if ($selected === null) {
            throw new RuntimeException('Unknown gateway profile: ' . $profile . '. Expected pulse|atlas|lumen.');
        }

        return $selected;
    }

    /**
     * @return array{key:string,kind:string,templateId:string,file:string}
     */
    private function resolveArtifact(string $profile): array
    {
        $normalized = strtolower(trim($profile));
        if ($normalized === '') {
            throw new RuntimeException('Release profile must not be empty.');
        }

        if (isset(self::COMPOSED_PROFILES[$normalized])) {
            return [
                'key' => $normalized,
                'kind' => 'composed',
                'templateId' => 'gateway_search_shell_core',
                'file' => 'gateway-search-' . $normalized . '.html',
            ];
        }

        if (isset(self::VARIANT_TEMPLATE_IDS[$normalized])) {
            return [
                'key' => $normalized,
                'kind' => 'variant',
                'templateId' => self::VARIANT_TEMPLATE_IDS[$normalized],
                'file' => 'gateway-search-' . $normalized . '.html',
            ];
        }

        foreach (self::VARIANT_TEMPLATE_IDS as $variant => $templateId) {
            if ($normalized === $templateId) {
                return [
                    'key' => $variant,
                    'kind' => 'variant',
                    'templateId' => $templateId,
                    'file' => 'gateway-search-' . $variant . '.html',
                ];
            }
        }

        throw new RuntimeException('Unknown release profile: ' . $profile . '. Expected signal|bastion|horizon|pulse|atlas|lumen.');
    }

    private function ensureDirectory(string $directory): void
    {
        if ($directory === '') {
            throw new RuntimeException('Output directory must not be empty.');
        }

        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create output directory: ' . $directory);
        }
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        $this->ensureDirectory($dir);

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException('Unable to write file: ' . $path);
        }
    }

    /**
     * @param array<string,mixed> $value
     */
    private function encodeJson(array $value): string
    {
        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new RuntimeException('Unable to encode release manifest JSON: ' . json_last_error_msg());
        }

        return $encoded . PHP_EOL;
    }
}
