# Templates – Roadmap

## Stage 1 – Foundation ✅
- Catalog + config loader (`config/example.templates.php`) with deterministic CLI rendering.
- CLI `bin/templates` (`catalog:list|show`, `template:run`, `security:scan`, `integrations:list`) plus compatibility wrapper `bin/template`.
- Telemetry (`var/metrics.prom`) and smoke tests for rendering + scanner behavior.

## Stage 2 – Gateway Search Templates ✅
- Added three distinct gateway-search UX variants (`signal`, `bastion`, `horizon`) as public templates.
- Added placeholders required for universal gateway routing (`SITE_TITLE`, `GATEWAY_ORIGIN`, `SEARCH_ACTION`).
- Kept templates stateless and secret-free so bundles can be published to Arweave.

## Stage 3 – Release Automation (next)
- Add template release manifest generation (`variant -> txid/hash`) for gateway operators.
- Add CI checks that rendered bundles remain deterministic across runs.
- Add schema validation for template release metadata consumed by gateway config.

## Stage 4 – Template Marketplace Readiness (next)
- Add formal template metadata (compatibility matrix, required gateway minimum version).
- Add release notes/changelog per template variant.
- Add starter snippets for `blackcat-darkmesh-web` builder integration.
