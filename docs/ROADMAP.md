# Templates – Roadmap

## Stage 1 – Foundation ✅
- Catalog + config loader (`config/example.templates.json`) with deterministic CLI rendering.
- TypeScript CLI `bin/templates` (`catalog:list|show`, `template:run`, `security:scan`, `integrations:list`) plus compatibility wrapper `bin/template`.
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

## Stage 5 – Release Automation ✅
- Added `gateway:release:build` plus pinned release-map generation (`variant -> txid/hash`) for gateway operators.
- Added CI checks that rendered bundles remain deterministic across runs.
- Added `release:validate` command and schema contract for release manifest structure.

## Stage 6 – Template Marketplace Readiness (in progress)
- Added formal template metadata fields (compatibility matrix, required gateway minimum version, release channel).
- Added release notes/changelog per template variant.
- Added starter snippets for `blackcat-darkmesh-web` builder integration using `docs/WEB_BUILDER_INTEGRATION.md`.
- Added builder-side mapping for direct variants -> component profiles so sites can migrate without changing token values.
- Added release-map consumption examples for pinned web builds.
