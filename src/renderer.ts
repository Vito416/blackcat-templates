import fs from 'node:fs';
import path from 'node:path';
import { TemplateRegistry } from './registry.js';
import { SecurityScanner } from './security.js';
import { TemplateMetrics } from './metrics.js';

export class TemplateRenderer {
  constructor(
    private readonly registry: TemplateRegistry,
    private readonly metrics: TemplateMetrics,
    private readonly scanner: SecurityScanner,
  ) {}

  render(templateId: string, values: Record<string, string>): string {
    const definition = this.registry.get(templateId);
    this.scanner.assertSafe(definition);
    this.metrics.recordSecurityScan(definition.id(), 0);

    const content = fs.readFileSync(definition.path(), 'utf8');
    const rendered = this.applyPlaceholders(content, definition.placeholders(), values);

    this.metrics.recordRender(definition.id());
    return rendered;
  }

  writeTo(templateId: string, values: Record<string, string>, destination: string): void {
    const rendered = this.render(templateId, values);
    fs.mkdirSync(path.dirname(destination), { recursive: true });
    fs.writeFileSync(destination, rendered, 'utf8');
  }

  private applyPlaceholders(
    content: string,
    placeholders: { token: string; required: boolean; default: string | null }[],
    values: Record<string, string>,
  ): string {
    const normalized: Record<string, string> = {};
    for (const [key, value] of Object.entries(values)) {
      normalized[key.toUpperCase()] = String(value);
    }

    let output = content;
    for (const placeholder of placeholders) {
      const value = normalized[placeholder.token] ?? placeholder.default ?? '';
      if (value === '' && placeholder.required) {
        throw new Error(`Missing placeholder: ${placeholder.token}`);
      }
      output = this.replaceToken(output, placeholder.token, value);
    }

    return output;
  }

  private replaceToken(content: string, token: string, value: string): string {
    const tokenPattern = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return content
      .replace(new RegExp(`\\{\\{\\s*${tokenPattern}\\s*\\}\\}`, 'gi'), value)
      .replace(new RegExp(`%%\\s*${tokenPattern}\\s*%%`, 'gi'), value);
  }
}
