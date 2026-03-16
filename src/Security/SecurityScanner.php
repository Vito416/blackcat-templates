<?php

declare(strict_types=1);

namespace BlackCat\Templates\Security;

use BlackCat\Templates\Config\TemplateConfig;
use BlackCat\Templates\Registry\TemplateDefinition;
use RuntimeException;

final class SecurityScanner
{
    public function __construct(private readonly TemplateConfig $config)
    {
    }

    /**
     * @return list<string>
     */
    public function scan(TemplateDefinition $definition): array
    {
        $issues = [];
        $extension = strtolower((string) pathinfo($definition->path(), PATHINFO_EXTENSION));
        $allowedExtensions = $this->config->allowedExtensions();
        if ($allowedExtensions !== [] && !in_array($extension, $allowedExtensions, true)) {
            $issues[] = 'Disallowed extension .' . $extension;
        }

        $content = (string) file_get_contents($definition->path());
        if ($this->config->disallowPhpTags() && str_contains($content, '<?php')) {
            $issues[] = 'Detected PHP tags in template body.';
        }

        if (preg_match('/\{\{\s*SECRET/i', $content)) {
            $issues[] = 'Secret placeholder detected; use ${env:}/blackcat-config references instead.';
        }

        $requiredIntegrations = $this->config->requiredSecurityIntegrations();
        if ($requiredIntegrations !== []) {
            $matches = array_intersect($requiredIntegrations, $definition->integrations());
            if ($matches === []) {
                $issues[] = 'Template missing required integration references (' . implode(', ', $requiredIntegrations) . ').';
            }
        }

        return $issues;
    }

    public function assertSafe(TemplateDefinition $definition): void
    {
        $issues = $this->scan($definition);
        if ($issues !== []) {
            throw new RuntimeException('Security issues detected: ' . implode('; ', $issues));
        }
    }
}
