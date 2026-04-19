import fs from 'node:fs';
import path from 'node:path';

export class TemplateMetrics {
  private catalogCount = 0;
  private readonly renderTotals = new Map<string, number>();
  private readonly securityIssues = new Map<string, number>();

  constructor(private readonly file: string | null) {}

  recordCatalogCount(count: number): void {
    this.catalogCount = count;
    this.flush();
  }

  recordRender(templateId: string): void {
    this.renderTotals.set(templateId, (this.renderTotals.get(templateId) ?? 0) + 1);
    this.flush();
  }

  recordSecurityScan(templateId: string, issues: number): void {
    this.securityIssues.set(templateId, issues);
    this.flush();
  }

  private flush(): void {
    if (!this.file) return;
    fs.mkdirSync(path.dirname(this.file), { recursive: true });

    const lines: string[] = [
      '# HELP template_catalog_total Number of templates available.',
      '# TYPE template_catalog_total gauge',
      `template_catalog_total ${this.catalogCount}`,
      '# HELP template_render_total Total renders per template id.',
      '# TYPE template_render_total counter',
    ];

    for (const [id, count] of this.renderTotals) {
      lines.push(`template_render_total{template="${id}"} ${count}`);
    }

    lines.push('# HELP template_security_issues Security issues detected per template (0 = clean).');
    lines.push('# TYPE template_security_issues gauge');

    for (const [id, count] of this.securityIssues) {
      lines.push(`template_security_issues{template="${id}"} ${count}`);
    }

    fs.writeFileSync(this.file, lines.join('\n') + '\n', 'utf8');
  }
}
