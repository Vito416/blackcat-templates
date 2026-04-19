import fs from 'node:fs';
import path from 'node:path';
import { JsonValue, TemplateDefinitionData, TemplatePlaceholder } from './types.js';

function normalizeMetadataValue(value: unknown): JsonValue {
  if (Array.isArray(value)) {
    return value.map((entry) => normalizeMetadataValue(entry));
  }

  if (typeof value === 'object' && value !== null) {
    const out: Record<string, JsonValue> = {};
    for (const [key, inner] of Object.entries(value)) {
      out[String(key)] = normalizeMetadataValue(inner);
    }
    return out;
  }

  if (
    typeof value === 'string' ||
    typeof value === 'number' ||
    typeof value === 'boolean' ||
    value === null
  ) {
    return value;
  }

  return String(value);
}

export class TemplateDefinition {
  private constructor(private readonly data: TemplateDefinitionData) {}

  static fromObject(payload: Record<string, unknown>, baseDir: string): TemplateDefinition {
    const id = String(payload.id ?? '');
    const name = String(payload.name ?? '');
    const description = String(payload.description ?? '');
    const file = payload.file ?? payload.path;

    if (!id || !name || !description || !file) {
      throw new Error('Invalid template definition payload.');
    }

    const filePath = String(file);
    const resolvedPath = path.isAbsolute(filePath)
      ? filePath
      : path.resolve(baseDir, filePath.replace(/^\/+/, ''));

    if (!fs.existsSync(resolvedPath)) {
      throw new Error(`Template file not found for ${id}: ${resolvedPath}`);
    }

    const rawPlaceholders = Array.isArray(payload.placeholders) ? payload.placeholders : [];
    const placeholders: TemplatePlaceholder[] = rawPlaceholders
      .map((placeholder) => {
        if (typeof placeholder !== 'object' || placeholder === null) return null;
        const token = String((placeholder as Record<string, unknown>).token ?? '').toUpperCase();
        if (!token) return null;

        const defaultValue =
          (placeholder as Record<string, unknown>).default === undefined
            ? null
            : String((placeholder as Record<string, unknown>).default);
        const rawRequired = (placeholder as Record<string, unknown>).required;
        // If placeholder defines a default, it is optional unless explicitly marked required.
        const required = rawRequired === undefined ? defaultValue === null : Boolean(rawRequired);

        return {
          token,
          description: String((placeholder as Record<string, unknown>).description ?? ''),
          required,
          default: defaultValue,
        };
      })
      .filter((entry): entry is TemplatePlaceholder => entry !== null);

    const rawTags = Array.isArray(payload.tags) ? payload.tags : [];
    const tags = rawTags.map((value) => String(value));

    const rawIntegrations = Array.isArray(payload.integrations) ? payload.integrations : [];
    const integrations = rawIntegrations.map((value) => String(value));

    const metadataSource =
      typeof payload.metadata === 'object' && payload.metadata !== null ? payload.metadata : {};
    const metadata: Record<string, JsonValue> = {};
    for (const [key, value] of Object.entries(metadataSource)) {
      metadata[String(key)] = normalizeMetadataValue(value);
    }

    return new TemplateDefinition({
      id,
      name,
      description,
      path: resolvedPath,
      placeholders,
      metadata,
      tags,
      integrations,
    });
  }

  id(): string {
    return this.data.id;
  }

  name(): string {
    return this.data.name;
  }

  description(): string {
    return this.data.description;
  }

  path(): string {
    return this.data.path;
  }

  placeholders(): TemplatePlaceholder[] {
    return this.data.placeholders;
  }

  metadata(): Record<string, JsonValue> {
    return this.data.metadata;
  }

  tags(): string[] {
    return this.data.tags;
  }

  integrations(): string[] {
    return this.data.integrations;
  }

  toJSON(): TemplateDefinitionData {
    return { ...this.data };
  }
}
