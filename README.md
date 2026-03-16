# BlackCat Templates

CLI + registry for scaffolding README/ROADMAP templates that reference shared config, database, auth, and orchestrator flows across the ecosystem. Stage 1 now ships a fully automated loader, telemetry, security scanner, and smoke tests so installers can safely compose docs.

## Stage 1 – Foundation ✅
- **Config loader** – `config/example.templates.php` resolves `${env:}`/`${file:}` secrets and syncs with `blackcat-config` profiles.
- **CLI** – `bin/templates` exposes `catalog:list`, `catalog:show`, `template:run`, `security:scan`, and `integrations:list` commands that downstream tooling can call.
- **Security & integrations** – every template declares required integrations (database/auth/orchestrator) enforced by the scanner.
- **Telemetry** – metrics land in `var/metrics.prom` (`template_catalog_total`, `template_render_total`, `template_security_issues`).
- **Docs & tests** – README/ROADMAP updated and `tests/SmokeTest.php` validates rendering + scanners.

## Getting Started
```bash
export BLACKCAT_TEMPLATES_CONFIG=$(pwd)/blackcat-templates/config/example.templates.php

# Inspect catalog
php blackcat-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG catalog:list
php blackcat-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG catalog:show module_readme

# Render templates (inline JSON or @file)
php blackcat-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG template:run module_readme '{"MODULE_NAME":"BlackCat Payments","DESCRIPTION":"Stage 1 bootstrap"}'
php blackcat-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG template:run module_roadmap @blackcat-templates/tests/fixtures/module-readme.json var/generated-roadmap.md

# Security/integration checks
php blackcat-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG security:scan
```

Backwards-compatible shorthand:
```bash
php blackcat-templates/bin/template readme "BlackCat Foo" "Short description"
```
Uses the same config loader + telemetry pipeline under the hood.

## Telemetry & Integrations
- Prometheus metrics: `var/metrics.prom` (scraped by `blackcat-observability`).
- Registry integrates with `blackcat-config` (profiles), `blackcat-database` (schema references), `blackcat-auth`, and `blackcat-orchestrator` workflow manifests defined in config.
- Security scans enforce that templates reference required integrations before publishing.

## Testing
```
php blackcat-templates/tests/SmokeTest.php
```
Runs registry boot, renders both templates, writes temp roadmap, executes security scan, and ensures metrics are emitted. Wire this into CI or `blackcat-cli verify` for smoke coverage.
