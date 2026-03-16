<?php

declare(strict_types=1);

namespace BlackCat\Templates\Renderer;

use BlackCat\Templates\Registry\TemplateRegistry;
use BlackCat\Templates\Security\SecurityScanner;
use BlackCat\Templates\Telemetry\TemplateMetrics;
use RuntimeException;

final class TemplateRenderer
{
    public function __construct(
        private readonly TemplateRegistry $registry,
        private readonly TemplateMetrics $metrics,
        private readonly SecurityScanner $scanner
    ) {
    }

    /**
     * @param array<string,string> $values
     */
    public function render(string $templateId, array $values): string
    {
        $definition = $this->registry->get($templateId);
        $this->scanner->assertSafe($definition);
        $this->metrics->recordSecurityScan($definition->id(), 0);

        $content = file_get_contents($definition->path());
        if ($content === false) {
            throw new RuntimeException('Unable to read template: ' . $definition->path());
        }

        $rendered = $this->applyPlaceholders($content, $definition->placeholders(), $values);
        $this->metrics->recordRender($definition->id());

        return $rendered;
    }

    /**
     * @param array<string,string> $values
     */
    public function writeTo(string $templateId, array $values, string $destination): void
    {
        $rendered = $this->render($templateId, $values);
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException('Unable to create directory: ' . $dir);
            }
        }

        file_put_contents($destination, $rendered);
    }

    /**
     * @param array<int,array{token:string,description:string,required:bool,default:?string}> $placeholders
     * @param array<string,string> $values
     */
    private function applyPlaceholders(string $content, array $placeholders, array $values): string
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            $normalized[strtoupper((string) $key)] = (string) $value;
        }

        foreach ($placeholders as $placeholder) {
            $token = $placeholder['token'];
            $value = $normalized[$token] ?? ($placeholder['default'] ?? '');
            if ($value === '' && $placeholder['required']) {
                throw new RuntimeException('Missing placeholder: ' . $token);
            }

            $content = $this->replaceToken($content, $token, $value);
        }

        return $content;
    }

    private function replaceToken(string $content, string $token, string $value): string
    {
        $tokenPattern = preg_quote($token, '/');
        $patterns = [
            '/\{\{\s*' . $tokenPattern . '\s*\}\}/i',
            '/%%\s*' . $tokenPattern . '\s*%%/i',
        ];

        foreach ($patterns as $pattern) {
            $content = (string) preg_replace_callback($pattern, static fn() => $value, $content);
        }

        return $content;
    }
}
