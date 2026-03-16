<?php

declare(strict_types=1);

namespace BlackCat\Templates\Telemetry;

final class TemplateMetrics
{
    private ?string $file;
    private int $catalogCount = 0;
    /** @var array<string,int> */
    private array $renderTotals = [];
    /** @var array<string,int> */
    private array $securityIssues = [];

    public function __construct(?string $file)
    {
        $this->file = $file;
    }

    public function recordCatalogCount(int $count): void
    {
        $this->catalogCount = $count;
        $this->flush();
    }

    public function recordRender(string $templateId): void
    {
        $this->renderTotals[$templateId] = ($this->renderTotals[$templateId] ?? 0) + 1;
        $this->flush();
    }

    public function recordSecurityScan(string $templateId, int $issues): void
    {
        $this->securityIssues[$templateId] = $issues;
        $this->flush();
    }

    private function flush(): void
    {
        if ($this->file === null) {
            return;
        }

        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $lines = [
            '# HELP template_catalog_total Number of templates available.',
            '# TYPE template_catalog_total gauge',
            'template_catalog_total ' . $this->catalogCount,
            '# HELP template_render_total Total renders per template id.',
            '# TYPE template_render_total counter',
        ];

        foreach ($this->renderTotals as $id => $count) {
            $lines[] = sprintf('template_render_total{template="%s"} %d', $id, $count);
        }

        $lines[] = '# HELP template_security_issues Security issues detected per template (0 = clean).';
        $lines[] = '# TYPE template_security_issues gauge';
        foreach ($this->securityIssues as $id => $count) {
            $lines[] = sprintf('template_security_issues{template="%s"} %d', $id, $count);
        }

        file_put_contents($this->file, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
