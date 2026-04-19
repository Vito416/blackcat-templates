import fs from 'node:fs';

const REQUIRED_TOP_LEVEL = ['releaseVersion', 'generatedAt', 'variants'] as const;
const REQUIRED_VARIANT_KEYS = ['variant', 'templateId', 'file', 'sha256', 'bytes'] as const;

function isObject(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

export class ReleaseManifestValidator {
  validateFile(filePath: string): { ok: boolean; errors: string[] } {
    if (!fs.existsSync(filePath)) {
      return { ok: false, errors: [`Manifest file not found: ${filePath}`] };
    }

    let parsed: unknown;
    try {
      parsed = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    } catch (error) {
      return { ok: false, errors: [`Invalid JSON: ${(error as Error).message}`] };
    }

    const errors = this.validate(parsed);
    return { ok: errors.length === 0, errors };
  }

  validate(data: unknown): string[] {
    const errors: string[] = [];

    if (!isObject(data)) {
      errors.push('Manifest must be a JSON object.');
      return errors;
    }

    for (const key of REQUIRED_TOP_LEVEL) {
      if (!(key in data)) {
        errors.push(`Missing top-level key: ${key}`);
      }
    }

    if (typeof data.releaseVersion !== 'string' || data.releaseVersion.trim() === '') {
      errors.push('releaseVersion must be a non-empty string.');
    }

    if (typeof data.generatedAt !== 'string' || Number.isNaN(new Date(data.generatedAt).getTime())) {
      errors.push('generatedAt must be a valid RFC3339 timestamp string.');
    }

    if (!isObject(data.variants)) {
      errors.push('variants must be an object keyed by variant id.');
      return errors;
    }

    for (const [variantId, variantData] of Object.entries(data.variants)) {
      if (!isObject(variantData)) {
        errors.push(`variants.${variantId} must be an object.`);
        continue;
      }

      for (const key of REQUIRED_VARIANT_KEYS) {
        if (!(key in variantData)) {
          errors.push(`variants.${variantId} missing key: ${key}`);
        }
      }

      if (typeof variantData.variant !== 'string' || variantData.variant.trim() === '') {
        errors.push(`variants.${variantId}.variant must be a non-empty string.`);
      }
      if (typeof variantData.templateId !== 'string' || variantData.templateId.trim() === '') {
        errors.push(`variants.${variantId}.templateId must be a non-empty string.`);
      }
      if (typeof variantData.file !== 'string' || variantData.file.trim() === '') {
        errors.push(`variants.${variantId}.file must be a non-empty string.`);
      }
      if (typeof variantData.sha256 !== 'string' || !/^[a-f0-9]{64}$/i.test(variantData.sha256)) {
        errors.push(`variants.${variantId}.sha256 must be a 64-char hex string.`);
      }
      if (typeof variantData.bytes !== 'number' || !Number.isFinite(variantData.bytes) || variantData.bytes < 0) {
        errors.push(`variants.${variantId}.bytes must be a non-negative number.`);
      }
    }

    return errors;
  }
}
