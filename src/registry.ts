import fs from 'node:fs';
import { TemplateConfig } from './config.js';
import { TemplateMetrics } from './metrics.js';
import { TemplateDefinition } from './template-definition.js';

export class TemplateRegistry {
  private readonly definitions = new Map<string, TemplateDefinition>();
  private booted = false;

  constructor(
    private readonly config: TemplateConfig,
    private readonly metrics: TemplateMetrics,
  ) {}

  all(): TemplateDefinition[] {
    this.boot();
    return Array.from(this.definitions.values());
  }

  get(id: string): TemplateDefinition {
    this.boot();
    const definition = this.definitions.get(id);
    if (!definition) {
      throw new Error(`Unknown template: ${id}`);
    }
    return definition;
  }

  private boot(): void {
    if (this.booted) return;

    const catalogFile = this.config.catalogFile();
    if (!fs.existsSync(catalogFile)) {
      throw new Error(`Template catalog missing: ${catalogFile}`);
    }

    const raw = JSON.parse(fs.readFileSync(catalogFile, 'utf8')) as unknown;
    if (!Array.isArray(raw)) {
      throw new Error('Template catalog must be a JSON array.');
    }

    for (const item of raw) {
      if (typeof item !== 'object' || item === null || Array.isArray(item)) continue;
      const definition = TemplateDefinition.fromObject(
        item as Record<string, unknown>,
        this.config.templatesDir(),
      );
      this.definitions.set(definition.id(), definition);
    }

    this.booted = true;
    this.metrics.recordCatalogCount(this.definitions.size);
  }
}
