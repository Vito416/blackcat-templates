# Templates – Roadmap

## Stage 1 – Foundation ✅
- Catalog + config loader (`config/example.templates.php`) with deterministic CLI rendering.
- CLI `bin/templates` (`catalog:list|show`, `template:run`, `security:scan`, `integrations:list`) plus compatibility wrapper `bin/template`.
- Telemetry (`var/metrics.prom`) and smoke tests for rendering + scanner behavior.

## Stage 2 – Gateway Search Templates ✅
- Added three distinct gateway-search UX variants (`signal`, `bastion`, `horizon`) as public templates.
- Added placeholders required for universal gateway routing (`SITE_TITLE`, `GATEWAY_ORIGIN`, `SEARCH_ACTION`).
- Added public-index placeholders (`INDEX_FETCH_MODE`, `PUBLIC_INDEX_ENDPOINT`, `INDEX_REQUEST_BODY_JSON`) so the index list can move to the public-read path without changing the OTP-first action boundary.
- Kept templates stateless and secret-free so bundles can be published to Arweave.

## Stage 3 – Componentized Front-End Kit ✅
- Added composable shell `gateway_search_shell_core`.
- Added chunked fragments for `menu`, `search`, `results`, `footer` in three original styles (`pulse`, `atlas`, `lumen`).
- Added `gateway:compose` CLI flow to assemble full pages from fragments while keeping deterministic render output.

## Stage 4 – Block Spec Scaffolding ✅
- Added v0.1 schemas for `block.manifest`, `block.variant`, `shell.manifest`, and `page.composition`.
- Added example payloads for `auth.login` and gateway search shell composition.
- Locked the direction to TS/JS frontend-logic blocks with worker-only secret/sign boundaries.

## Stage 5 – Release Automation (next)
- Add `gateway:release:build` plus pinned release-map generation (`variant -> txid/hash`) for gateway operators.
- Add CI checks that rendered bundles remain deterministic across runs.
- Add schema validation for template release metadata consumed by gateway config.

## Stage 6 – Template Marketplace Readiness (next)
- Add formal template metadata (compatibility matrix, required gateway minimum version).
- Add release notes/changelog per template variant.
- Add starter snippets for `blackcat-darkmesh-web` builder integration.
