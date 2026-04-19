#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { TemplateConfig } from './config.js';
import { TemplateMetrics } from './metrics.js';
import { TemplateRegistry } from './registry.js';
import { SecurityScanner } from './security.js';
import { TemplateRenderer } from './renderer.js';
import { GatewayReleaseBuilder } from './release-builder.js';
import { ReleaseManifestValidator } from './release-validator.js';

function usage(): string {
  return `Usage: templates <config> <command>

Commands:
  catalog:list                       List available template definitions.
  catalog:show <templateId>          Show template metadata + placeholders.
  template:run <templateId> <data>   Render template with JSON payload (use @file for file input) and optional destination path.
  gateway:compose <profile> <data>   Compose shell + component fragments into one gateway search page.
  gateway:release:build <profile[,profile...]> <data> <output-dir> <version>
                                     Build local gateway release artifacts and emit a manifest.
  release:validate <manifest-file>   Validate generated release manifest structure.
  security:scan [templateId]         Run security checks for one or all templates.
  integrations:list                  Output configured integration targets.`;
}

function parsePayload(arg: string): Record<string, string> {
  if (!arg) return {};
  let source = arg;
  if (arg.startsWith('@')) {
    const payloadFile = arg.slice(1);
    if (!fs.existsSync(payloadFile)) {
      throw new Error(`Payload file not found: ${payloadFile}`);
    }
    source = fs.readFileSync(payloadFile, 'utf8');
  }

  const parsed = JSON.parse(source) as unknown;
  if (typeof parsed !== 'object' || parsed === null || Array.isArray(parsed)) {
    throw new Error('Payload must be a JSON object.');
  }

  const normalized: Record<string, string> = {};
  for (const [key, value] of Object.entries(parsed as Record<string, unknown>)) {
    normalized[String(key).toUpperCase()] = String(value ?? '');
  }

  return normalized;
}

function writeOutput(destination: string, content: string): void {
  fs.mkdirSync(path.dirname(destination), { recursive: true });
  fs.writeFileSync(destination, content, 'utf8');
}

function normalizeReleaseProfiles(profileArgs: string[]): string[] {
  const out = new Set<string>();
  for (const arg of profileArgs) {
    for (const raw of String(arg).split(',')) {
      const value = raw.trim();
      if (value) out.add(value);
    }
  }
  return [...out];
}

export async function runCli(argv: string[]): Promise<number> {
  const configPath = argv[2] ?? process.env.BLACKCAT_TEMPLATES_CONFIG ?? path.resolve('config/example.templates.json');
  const command = argv[3] ?? 'help';
  const args = argv.slice(4);

  let config: TemplateConfig;
  try {
    config = TemplateConfig.fromFile(configPath);
  } catch (error) {
    console.error((error as Error).message);
    return 1;
  }

  const metrics = new TemplateMetrics(config.telemetryFile());
  const scanner = new SecurityScanner(config);
  const registry = new TemplateRegistry(config, metrics);
  const renderer = new TemplateRenderer(registry, metrics, scanner);
  const releaseBuilder = new GatewayReleaseBuilder(renderer);
  const releaseValidator = new ReleaseManifestValidator();

  try {
    switch (command) {
      case 'catalog:list': {
        console.log(JSON.stringify(registry.all().map((item) => item.toJSON()), null, 2));
        return 0;
      }
      case 'catalog:show': {
        const id = args[0];
        if (!id) {
          console.error('Usage: templates <config> catalog:show <templateId>');
          return 1;
        }
        console.log(JSON.stringify(registry.get(id).toJSON(), null, 2));
        return 0;
      }
      case 'template:run': {
        const [templateId, payloadArg, destination] = args;
        if (!templateId || !payloadArg) {
          console.error('Usage: templates <config> template:run <templateId> <JSON payload|@file> [destination]');
          return 1;
        }

        const payload = parsePayload(payloadArg);
        if (destination) {
          renderer.writeTo(templateId, payload, destination);
          console.log(`Template written to ${destination}`);
        } else {
          process.stdout.write(`${renderer.render(templateId, payload)}\n`);
        }
        return 0;
      }
      case 'gateway:compose': {
        const [profileId, payloadArg, destination] = args;
        if (!profileId || !payloadArg) {
          console.error('Usage: templates <config> gateway:compose <pulse|atlas|lumen> <JSON payload|@file> [destination]');
          return 1;
        }

        const payload = parsePayload(payloadArg);
        const rendered = releaseBuilder.composeProfile(profileId, payload);
        if (destination) {
          writeOutput(destination, rendered);
          console.log(`Gateway profile rendered to ${destination}`);
        } else {
          process.stdout.write(`${rendered}\n`);
        }
        return 0;
      }
      case 'gateway:release:build': {
        if (args.length < 4) {
          console.error('Usage: templates <config> gateway:release:build <profile[,profile...]> <JSON payload|@file> <output-dir> <version>');
          return 1;
        }
        const releaseVersion = args[args.length - 1];
        const outputDir = args[args.length - 2];
        const payloadArg = args[args.length - 3];
        const profileArgs = args.slice(0, -3);

        const profiles = normalizeReleaseProfiles(profileArgs);
        if (profiles.length === 0) {
          console.error('At least one profile must be provided.');
          return 1;
        }

        const payload = parsePayload(payloadArg);
        const manifestPath = releaseBuilder.build(profiles, payload, outputDir, releaseVersion);
        console.log(`Gateway release manifest written to ${manifestPath}`);
        return 0;
      }
      case 'release:validate': {
        const manifestFile = args[0];
        if (!manifestFile) {
          console.error('Usage: templates <config> release:validate <manifest-file>');
          return 1;
        }

        const result = releaseValidator.validateFile(manifestFile);
        if (!result.ok) {
          for (const message of result.errors) {
            console.error(`- ${message}`);
          }
          return 1;
        }

        console.log(`Release manifest is valid: ${manifestFile}`);
        return 0;
      }
      case 'security:scan': {
        const targetId = args[0];
        const definitions = targetId ? [registry.get(targetId)] : registry.all();
        let hasIssues = false;
        for (const definition of definitions) {
          const issues = scanner.scan(definition);
          metrics.recordSecurityScan(definition.id(), issues.length);
          if (issues.length === 0) {
            console.log(`${definition.id()} OK`);
            continue;
          }
          hasIssues = true;
          console.log(`${definition.id()} issues:`);
          for (const issue of issues) {
            console.log(`  - ${issue}`);
          }
        }
        return hasIssues ? 1 : 0;
      }
      case 'integrations:list': {
        console.log(JSON.stringify(config.integrations(), null, 2));
        return 0;
      }
      case 'help':
      default:
        console.log(usage());
        return command === 'help' ? 0 : 1;
    }
  } catch (error) {
    console.error((error as Error).message);
    return 1;
  }
}

if (import.meta.url === `file://${process.argv[1]}`) {
  runCli(process.argv).then((code) => process.exit(code));
}
