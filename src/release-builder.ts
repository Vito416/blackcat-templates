import fs from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { TemplateRenderer } from './renderer.js';
import { ReleaseDeterminism } from './release-determinism.js';

const VARIANT_TEMPLATE_IDS: Record<string, string> = {
  signal: 'gateway_search_variant_signal',
  bastion: 'gateway_search_variant_bastion',
  horizon: 'gateway_search_variant_horizon',
};

const COMPOSED_PROFILES: Record<string, {
  menu: string;
  search: string;
  results: string;
  footer: string;
  shell_body_class: string;
  shell_extra_css: string;
}> = {
  pulse: {
    menu: 'gateway_component_menu_pulse',
    search: 'gateway_component_search_pulse',
    results: 'gateway_component_results_pulse',
    footer: 'gateway_component_footer_pulse',
    shell_body_class: '',
    shell_extra_css: '',
  },
  atlas: {
    menu: 'gateway_component_menu_forge',
    search: 'gateway_component_search_atlas',
    results: 'gateway_component_results_atlas',
    footer: 'gateway_component_footer_atlas',
    shell_body_class: 'text-black',
    shell_extra_css: ':root { --mesh-bg-1: #ffcd38; --mesh-bg-2: #ff7e1f; --mesh-fx-a: rgba(255,255,255,0.35); --mesh-fx-b: rgba(0,0,0,0.15); }',
  },
  lumen: {
    menu: 'gateway_component_menu_zenith',
    search: 'gateway_component_search_lumen',
    results: 'gateway_component_results_lumen',
    footer: 'gateway_component_footer_lumen',
    shell_body_class: 'text-slate-900',
    shell_extra_css: ':root { --mesh-bg-1: #dfe7ff; --mesh-bg-2: #9db8ff; --mesh-fx-a: rgba(255,255,255,0.45); --mesh-fx-b: rgba(164,179,255,0.30); }',
  },
};

function sha256(content: string): string {
  return createHash('sha256').update(content).digest('hex');
}

function ensureDirectory(directory: string): void {
  if (!directory.trim()) {
    throw new Error('Output directory must not be empty.');
  }
  fs.mkdirSync(directory, { recursive: true });
}

function writeFile(filePath: string, content: string): void {
  ensureDirectory(path.dirname(filePath));
  fs.writeFileSync(filePath, content, 'utf8');
}

function normalizeProfileId(profile: string): string {
  return profile.toLowerCase().trim();
}

export class GatewayReleaseBuilder {
  constructor(private readonly renderer: TemplateRenderer) {}

  composeProfile(profile: string, payload: Record<string, string>): string {
    const selected = COMPOSED_PROFILES[normalizeProfileId(profile)];
    if (!selected) {
      throw new Error(`Unknown gateway profile: ${profile}. Expected pulse|atlas|lumen.`);
    }

    const menu = this.renderer.render(selected.menu, payload);
    const search = this.renderer.render(selected.search, payload);
    const results = this.renderer.render(selected.results, payload);
    const footer = this.renderer.render(selected.footer, payload);

    const shellPayload: Record<string, string> = {
      ...payload,
      MENU_COMPONENT: menu,
      SEARCH_COMPONENT: search,
      RESULTS_COMPONENT: results,
      FOOTER_COMPONENT: footer,
    };

    if (!('SHELL_BODY_CLASS' in shellPayload)) {
      shellPayload.SHELL_BODY_CLASS = selected.shell_body_class;
    }
    if (!('SHELL_EXTRA_CSS' in shellPayload)) {
      shellPayload.SHELL_EXTRA_CSS = selected.shell_extra_css;
    }

    return this.renderer.render('gateway_search_shell_core', shellPayload);
  }

  build(
    profiles: string[],
    payload: Record<string, string>,
    outputDir: string,
    releaseVersion: string,
  ): string {
    if (profiles.length === 0) {
      throw new Error('At least one profile must be provided.');
    }
    if (!releaseVersion.trim()) {
      throw new Error('Release version must not be empty.');
    }

    ensureDirectory(outputDir);

    const variants: Record<string, {
      variant: string;
      templateId: string;
      file: string;
      sha256: string;
      bytes: number;
    }> = {};

    for (const profile of profiles) {
      const normalized = normalizeProfileId(profile);
      let rendered = '';
      let templateId = '';
      let outputFile = '';

      if (COMPOSED_PROFILES[normalized]) {
        rendered = this.composeProfile(normalized, payload);
        templateId = 'gateway_search_shell_core';
        outputFile = `gateway-search-${normalized}.html`;
      } else if (VARIANT_TEMPLATE_IDS[normalized]) {
        templateId = VARIANT_TEMPLATE_IDS[normalized];
        rendered = this.renderer.render(templateId, payload);
        outputFile = `gateway-search-${normalized}.html`;
      } else {
        throw new Error(`Unknown release profile: ${profile}. Expected signal|bastion|horizon|pulse|atlas|lumen.`);
      }

      writeFile(path.join(outputDir, outputFile), rendered);

      variants[normalized] = {
        variant: normalized,
        templateId,
        file: outputFile,
        sha256: sha256(rendered),
        bytes: Buffer.byteLength(rendered, 'utf8'),
      };
    }

    const sortedVariants = Object.fromEntries(
      Object.keys(variants)
        .sort((a, b) => a.localeCompare(b))
        .map((key) => [key, variants[key]]),
    );

    const manifest = {
      releaseVersion,
      generatedAt: ReleaseDeterminism.resolveGeneratedAt(payload),
      variants: sortedVariants,
    };

    const manifestPath = path.join(outputDir, 'manifest.json');
    writeFile(`${manifestPath}`, `${JSON.stringify(manifest, null, 2)}\n`);
    return manifestPath;
  }
}
