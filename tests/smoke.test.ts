import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { randomBytes } from 'node:crypto';
import { TemplateConfig } from '../src/config.js';
import { TemplateMetrics } from '../src/metrics.js';
import { TemplateRegistry } from '../src/registry.js';
import { SecurityScanner } from '../src/security.js';
import { TemplateRenderer } from '../src/renderer.js';
import { GatewayReleaseBuilder } from '../src/release-builder.js';
import { ReleaseManifestValidator } from '../src/release-validator.js';

const config = TemplateConfig.fromObject({
  templates_dir: path.resolve('templates'),
  catalog_file: path.resolve('templates/catalog.json'),
  telemetry: {
    prometheus_file: path.join(os.tmpdir(), `templates-metrics-${randomBytes(4).toString('hex')}.prom`),
  },
  security: {
    allowed_extensions: ['md', 'html'],
    disallow_php: true,
    require_integrations: ['blackcat-darkmesh-gateway'],
  },
});

const metrics = new TemplateMetrics(config.telemetryFile());
const registry = new TemplateRegistry(config, metrics);
const scanner = new SecurityScanner(config);
const renderer = new TemplateRenderer(registry, metrics, scanner);
const releaseBuilder = new GatewayReleaseBuilder(renderer);
const releaseValidator = new ReleaseManifestValidator();

const templates = registry.all();
assert.ok(templates.length >= 2, 'Expected at least two templates in catalog.');

const shellDefinition = registry.get('gateway_search_shell_core');
assert.deepEqual(shellDefinition.metadata(), {
  gatewayMinVersion: '1.4.0',
  compatibility: {
    gateway: '^1.4',
    runtime: 'browser',
  },
  releaseChannel: 'stable',
});
assert.match(JSON.stringify(shellDefinition), /"metadata"/);

const signalDefinition = registry.get('gateway_search_variant_signal');
assert.equal(signalDefinition.metadata().releaseChannel, 'beta');

const moduleReadmeDefinition = registry.get('module_readme');
assert.deepEqual(moduleReadmeDefinition.metadata(), {});

const readme = renderer.render('module_readme', {
  MODULE_NAME: 'BlackCat Smoke',
  DESCRIPTION: 'Testing automation wiring.',
  CLI_USAGE: 'node bin/templates',
});
assert.match(readme, /# BlackCat Smoke/);
assert.match(readme, /Testing automation wiring\./);

const tmpRoadmap = path.join(os.tmpdir(), `blackcat-roadmap-${randomBytes(4).toString('hex')}.md`);
renderer.writeTo('module_roadmap', {
  MODULE_NAME: 'BlackCat Smoke',
  VISION: 'Ship safe templates quickly.',
  STAGE1_FOCUS: 'Wire config + telemetry.',
  STAGE2_FOCUS: 'Automate CLI scaffolding.',
  COMPLIANCE_OWNER: 'qa-bot@blackcat',
}, tmpRoadmap);
assert.ok(fs.existsSync(tmpRoadmap));

const issues = scanner.scan(moduleReadmeDefinition);
assert.deepEqual(issues, []);

const searchTemplate = renderer.render('gateway_search_variant_signal', {
  SITE_TITLE: 'Darkmesh Search',
  GATEWAY_ORIGIN: 'https://gateway.example',
  SEARCH_ACTION: 'public.resolve-route',
});
assert.match(searchTemplate, /Darkmesh Search/);
assert.match(searchTemplate, /public\.resolve-route/);

const publicIndexTemplate = renderer.render('gateway_search_variant_signal', {
  SITE_TITLE: 'Darkmesh Search',
  SITE_TAGLINE: 'Composable UX',
  GATEWAY_ORIGIN: 'https://gateway.example',
  SEARCH_ACTION: 'public.resolve-route',
  INDEX_ACTION: 'public.site-index',
  INDEX_FETCH_MODE: 'public_read',
  PUBLIC_INDEX_ENDPOINT: '/api/public/site-index',
  INDEX_REQUEST_BODY_JSON: '{}',
  INDEX_LIMIT: '24',
  INDEX_SEED_JSON: '[]',
});
assert.match(publicIndexTemplate, /indexFetchMode/);
assert.match(publicIndexTemplate, /public_read/);
assert.match(publicIndexTemplate, /publicIndexEndpoint/);
assert.match(publicIndexTemplate, /indexRequestBodyJson/);

const menuFragment = renderer.render('gateway_component_menu_pulse', { SITE_TITLE: 'Darkmesh Search' });
const searchFragment = renderer.render('gateway_component_search_pulse', {
  SITE_TITLE: 'Darkmesh Search',
  SITE_TAGLINE: 'Composable UX',
});
const resultsFragment = renderer.render('gateway_component_results_pulse', {
  INDEX_MODE_LABEL: 'fair-order',
});
const footerFragment = renderer.render('gateway_component_footer_pulse', {
  FOOTER_NOTE: 'Deterministic templates',
});

const composedTemplate = renderer.render('gateway_search_shell_core', {
  SITE_TITLE: 'Darkmesh Search',
  GATEWAY_ORIGIN: 'https://gateway.example',
  MENU_COMPONENT: menuFragment,
  SEARCH_COMPONENT: searchFragment,
  RESULTS_COMPONENT: resultsFragment,
  FOOTER_COMPONENT: footerFragment,
});
assert.match(composedTemplate, /gateway-search-form/);
assert.match(composedTemplate, /gateway-results-list/);

const authLogin = renderer.render('gateway_component_auth_login_cipher', {
  LOGIN_TITLE: 'Sign in',
  LOGIN_ACTION: '/auth/login',
});
assert.match(authLogin, /Sign in/);
assert.match(authLogin, /\/auth\/login/);

const commerceGrid = renderer.render('gateway_component_commerce_product_grid_rally', {
  PRODUCT_GRID_TITLE: 'Featured products',
  PRODUCT_CARD_ITEMS_HTML: '<article>Item</article>',
});
assert.match(commerceGrid, /Featured products/);
assert.match(commerceGrid, /<article>Item<\/article>/);

const contentFaq = renderer.render('gateway_component_content_faq_prism', {
  HEADING: 'FAQ',
  FAQ_ITEMS_HTML: '<details><summary>Q</summary><p>A</p></details>',
});
assert.match(contentFaq, /FAQ/);
assert.match(contentFaq, /<details>/);

const accountDashboard = renderer.render('gateway_component_account_dashboard_axis', {
  ACCOUNT_TITLE: 'Account center',
});
assert.match(accountDashboard, /Account center/);

const manifestPath = releaseBuilder.build(
  ['signal', 'pulse'],
  {
    SITE_TITLE: 'Darkmesh Search',
    GATEWAY_ORIGIN: 'https://gateway.example',
    SEARCH_ACTION: 'public.resolve-route',
    RELEASE_GENERATED_AT: '2026-01-01T00:00:00.000Z',
  },
  path.join(os.tmpdir(), `release-${randomBytes(4).toString('hex')}`),
  '0.1.0',
);
const validation = releaseValidator.validateFile(manifestPath);
assert.equal(validation.ok, true);

const metricsFile = config.telemetryFile();
assert.ok(typeof metricsFile === 'string');
assert.ok(fs.existsSync(metricsFile));

console.log('Templates smoke test ok');
