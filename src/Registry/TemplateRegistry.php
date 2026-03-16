<?php

declare(strict_types=1);

namespace BlackCat\Templates\Registry;

use BlackCat\Templates\Config\TemplateConfig;
use BlackCat\Templates\Telemetry\TemplateMetrics;
use RuntimeException;

final class TemplateRegistry
{
    /** @var array<string,TemplateDefinition> */
    private array $definitions = [];
    private bool $booted = false;

    public function __construct(
        private readonly TemplateConfig $config,
        private readonly TemplateMetrics $metrics
    ) {
    }

    /**
     * @return list<TemplateDefinition>
     */
    public function all(): array
    {
        $this->boot();
        return array_values($this->definitions);
    }

    public function get(string $id): TemplateDefinition
    {
        $this->boot();
        if (!isset($this->definitions[$id])) {
            throw new RuntimeException("Unknown template: {$id}");
        }

        return $this->definitions[$id];
    }

    private function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $catalogFile = $this->config->catalogFile();
        if (!is_file($catalogFile)) {
            throw new RuntimeException("Template catalog missing: {$catalogFile}");
        }

        $items = require $catalogFile;
        if (!is_array($items)) {
            throw new RuntimeException('Template catalog must return array.');
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $definition = TemplateDefinition::fromArray($item, $this->config->templatesDir());
            $this->definitions[$definition->id()] = $definition;
        }

        $this->booted = true;
        $this->metrics->recordCatalogCount(count($this->definitions));
    }
}
