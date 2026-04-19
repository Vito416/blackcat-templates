import fs from 'node:fs';
import path from 'node:path';
import { TemplateConfigPayload } from './types.js';

function isObject(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function readJsonFile(filePath: string): unknown {
  const raw = fs.readFileSync(filePath, 'utf8');
  return JSON.parse(raw);
}

function toStringMap(value: unknown): Record<string, string> {
  if (!isObject(value)) return {};
  const result: Record<string, string> = {};
  for (const [key, raw] of Object.entries(value)) {
    result[String(key)] = String(raw ?? '');
  }
  return result;
}

function loadProfileEnv(configPath: string, payload: TemplateConfigPayload): Record<string, string> {
  const profile = payload.config_profile;
  if (!profile?.file) return {};
  const profileFile = path.resolve(path.dirname(configPath), profile.file);
  if (!fs.existsSync(profileFile)) return {};

  const data = readJsonFile(profileFile);
  if (!Array.isArray(data)) return {};

  for (const item of data) {
    if (!isObject(item)) continue;
    const name = typeof item.name === 'string' ? item.name : undefined;
    const environment = typeof item.environment === 'string' ? item.environment : undefined;
    const match = (profile.name && profile.name === name) || (profile.environment && profile.environment === environment);
    if (!match) continue;

    return toStringMap(item.env);
  }

  return {};
}

function resolveString(value: string, profileEnv: Record<string, string>): string {
  const envMatch = value.match(/^\$\{env:([^}]+)\}$/);
  if (envMatch) {
    const key = envMatch[1];
    return profileEnv[key] ?? process.env[key] ?? '';
  }

  const fileMatch = value.match(/^\$\{file:([^}]+)\}$/);
  if (fileMatch) {
    const file = fileMatch[1];
    if (!fs.existsSync(file)) return '';
    return fs.readFileSync(file, 'utf8').trim();
  }

  return value;
}

function resolveValue(value: unknown, profileEnv: Record<string, string>): unknown {
  if (typeof value === 'string') return resolveString(value, profileEnv);
  if (Array.isArray(value)) return value.map((entry) => resolveValue(entry, profileEnv));
  if (isObject(value)) {
    const out: Record<string, unknown> = {};
    for (const [k, v] of Object.entries(value)) {
      out[k] = resolveValue(v, profileEnv);
    }
    return out;
  }
  return value;
}

export class TemplateConfig {
  private constructor(
    private readonly payload: TemplateConfigPayload,
    private readonly configPath: string,
  ) {}

  static fromFile(configPath: string): TemplateConfig {
    if (!fs.existsSync(configPath)) {
      throw new Error(`Template config missing: ${configPath}`);
    }

    const raw = readJsonFile(configPath);
    if (!isObject(raw)) {
      throw new Error('Template config must be a JSON object.');
    }

    const typedRaw = raw as TemplateConfigPayload;
    const profileEnv = loadProfileEnv(configPath, typedRaw);
    const resolved = resolveValue(typedRaw, profileEnv);
    if (!isObject(resolved)) {
      throw new Error('Resolved config must be a JSON object.');
    }

    return new TemplateConfig(resolved as TemplateConfigPayload, configPath);
  }

  static fromObject(payload: TemplateConfigPayload): TemplateConfig {
    const resolved = resolveValue(payload, {});
    return new TemplateConfig(resolved as TemplateConfigPayload, process.cwd());
  }

  private resolvePath(maybePath: string | undefined, fallbackRelative: string): string {
    const candidate = maybePath ?? fallbackRelative;
    if (path.isAbsolute(candidate)) return candidate;
    return path.resolve(path.dirname(this.configPath), candidate);
  }

  templatesDir(): string {
    return this.resolvePath(this.payload.templates_dir, 'templates');
  }

  catalogFile(): string {
    return this.resolvePath(this.payload.catalog_file, path.join('templates', 'catalog.json'));
  }

  storageDir(): string {
    return this.resolvePath(this.payload.storage_dir, 'var');
  }

  telemetryFile(): string | null {
    const file = this.payload.telemetry?.prometheus_file;
    if (!file) return null;
    return this.resolvePath(file, file);
  }

  integrations(): Record<string, string> {
    return this.payload.integrations ? toStringMap(this.payload.integrations) : {};
  }

  allowedExtensions(): string[] {
    const allowed = this.payload.security?.allowed_extensions;
    if (!Array.isArray(allowed)) return [];
    return allowed.map((ext) => String(ext).toLowerCase());
  }

  disallowPhpTags(): boolean {
    const raw = this.payload.security?.disallow_php;
    if (typeof raw !== 'boolean') return true;
    return raw;
  }

  requiredSecurityIntegrations(): string[] {
    const required = this.payload.security?.require_integrations;
    if (!Array.isArray(required)) return [];
    return required.map((value) => String(value));
  }
}
