# BlackCat Darkmesh Templates

Template registry and CLI for public, verifiable assets used by the Blackcat Darkmesh ecosystem.
This repo now includes:
- docs/scaffolding templates (`module_readme`, `module_roadmap`)
- gateway search UX templates with three distinct monolithic variants:
  - `gateway_search_variant_signal`
  - `gateway_search_variant_bastion`
  - `gateway_search_variant_horizon`
- gateway search **component kit** (front-end building blocks):
  - `gateway_search_shell_core` (page skeleton)
  - menu/search/results/footer fragments in three original styles (`pulse`, `atlas`, `lumen`)
  - `gateway:compose` command that assembles shell + selected style profile
- block-library batch 02 (stavebnice for real site flows):
  - auth blocks (`login`, `register`, `recovery`)
  - commerce blocks (`product-grid`, `cart-drawer`, `checkout-summary`)
  - content blocks (`hero`, `feature-grid`, `faq`, `cta-band`)
  - account blocks (`dashboard`, `orders`, `security`, `notifications`)
- block-spec scaffolding for TS/JS frontend logic:
  - `contracts/schemas/*.v0.1.schema.json`
  - `contracts/examples/*.json`

## What this repo is for
- Keep template bundles outside gateway runtime code.
- Render deterministic, auditable artifacts before publish (for Arweave release flow).
- Enforce a basic security policy and integration declarations per template.

## Quick Start
```bash
export BLACKCAT_TEMPLATES_CONFIG=$(pwd)/blackcat-darkmesh-templates/config/example.templates.php

# Catalog / metadata
php blackcat-darkmesh-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG catalog:list
php blackcat-darkmesh-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG catalog:show gateway_search_variant_signal

# Render docs template
php blackcat-darkmesh-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG template:run module_readme '{"MODULE_NAME":"BlackCat Payments","DESCRIPTION":"Gateway + AO write boundary"}'

# Render one gateway-search variant
php blackcat-darkmesh-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG template:run gateway_search_variant_bastion '{"SITE_TITLE":"Darkmesh Search","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/gateway-search-bastion.html

# Compose a chunked front-end page (shell + fragments)
php blackcat-darkmesh-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG gateway:compose pulse '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/gateway-search-pulse.html

# Security checks
php blackcat-darkmesh-templates/bin/templates $BLACKCAT_TEMPLATES_CONFIG security:scan
```

Backwards-compatible shorthand:
```bash
php blackcat-darkmesh-templates/bin/template readme "BlackCat Foo" "Short description"
```

## Telemetry
- Prometheus output file: `var/metrics.prom`
- Counters: `template_catalog_total`, `template_render_total`, `template_security_issues`

## Testing
```bash
php blackcat-darkmesh-templates/tests/SmokeTest.php
```
Smoke test boots registry, renders templates, runs security scan, and verifies metrics output.

## Gateway search release docs
- Runbook: `docs/GATEWAY_SEARCH_RELEASE.md`
- Component kit guide: `docs/GATEWAY_COMPONENT_KIT.md`
- Block spec v0.1: `docs/BLOCK_SPEC_V0_1.md`
- Block library batch 02: `docs/BLOCK_LIBRARY_BATCH_02.md`
- Variant map example: `docs/gateway-search-variant-map.example.json`
- Release map v0.1.0: `docs/releases/gateway-search-v0.1.0.json`

## Component-kit model (project default)
- Templates are split into reusable parts (`menu`, `search`, `results`, `footer`) rather than one big page.
- The shell stays stable; styles swap per component profile.
- Gateway keeps templates public/deterministic and can cache them safely.
- This lets each site compose unique UX while preserving the same verified runtime flow.
- Frontend logic stays in TS/JS bundles per block (state-machine oriented), while signing/secrets stay worker-side.

## Licensing

This repository is an official component of the Blackcat Covered System. It is licensed under `BFNL-1.0`, and repository separation inside `BLACKCAT_MESH_NEXUS` exists for maintenance, safety, auditability, delivery, and architectural clarity. It does not by itself create a separate unavoidable founder-fee or steward/development-fee event for the same ordinary covered deployment.

Canonical licensing bundle:
- BFNL 1.0: https://github.com/Vito416/blackcat-darkmesh-ao/blob/main/docs/BFNL-1.0.md
- Founder Fee Policy: https://github.com/Vito416/blackcat-darkmesh-ao/blob/main/docs/FEE_POLICY.md
- Covered-System Notice: https://github.com/Vito416/blackcat-darkmesh-ao/blob/main/docs/LICENSING_SYSTEM_NOTICE.md
