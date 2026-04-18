# Gateway Search Template Release

This runbook defines how to render and publish the gateway-search templates to Arweave.

The preferred model is now a **componentized front-end kit** (shell + fragments), not a single locked page.

## Variants

- `gateway_search_variant_signal` (ambient/glass)
- `gateway_search_variant_bastion` (high-contrast operational)
- `gateway_search_variant_horizon` (editorial/readable)

Component-kit profiles (recommended):
- `pulse` (neon, cinematic, layered glass)
- `atlas` (brutalist, sharp, bold)
- `lumen` (editorial, serif, minimal)

## Render local artifacts

```bash
export BLACKCAT_TEMPLATES_CONFIG=$(pwd)/config/example.templates.php

php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" template:run gateway_search_variant_signal '{"SITE_TITLE":"Darkmesh Search","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/search-signal.html
php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" template:run gateway_search_variant_bastion '{"SITE_TITLE":"Darkmesh Search","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/search-bastion.html
php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" template:run gateway_search_variant_horizon '{"SITE_TITLE":"Darkmesh Search","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/search-horizon.html

# Compose shell + fragments (recommended)
php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose pulse '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/search-pulse.html
php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose atlas '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/search-atlas.html
php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose lumen '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' var/search-lumen.html

php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" security:scan
```

## Publish flow

1. Upload each rendered file to Arweave.
2. Capture tx ids and sha256 hashes.
3. Write a release map (see `docs/gateway-search-variant-map.example.json`).
4. Commit the release map in the templates repo.
5. Mirror the selected variant in gateway config (site -> variant + tx id).

## Notes

- Templates stay public and deterministic by design.
- Secret values are never embedded in templates; secrets remain worker-side.
- Gateway owns policy/routing, templates own UX markup.
- Keep component fragments and shell published together so audit/replay can verify exact composition.
- In production, the gateway front-controller should serve the active AR bundle (root `/` or `/front-controller/search`) and cache refreshes by tx id.
