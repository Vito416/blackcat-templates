# Gateway Component Kit (Stavebnice)

This kit is the project-default model for the gateway search UX.

Instead of one locked page, you compose the public front-end from reusable parts:

1. `gateway_search_shell_core` (layout/runtime shell)
2. `menu` fragment
3. `search` fragment
4. `results` fragment
5. `footer` fragment

All fragments are public templates and deterministic artifacts for AR publish.

## Builder integration model

`blackcat-darkmesh-web` should treat the templates repo as a build-time content source:

1. Pick a template ID or component profile from the catalog.
2. Resolve its placeholder/token map.
3. Render either a direct variant (`template:run`) or a composed page (`gateway:compose`).
4. Persist the rendered artifact, hash, and release metadata in the web builder output.

Example builder mapping:

```json
{
  "site": "darkmesh-search",
  "variant": "signal",
  "templateId": "gateway_search_variant_signal",
  "componentProfile": "pulse",
  "tokens": {
    "SITE_TITLE": "Darkmesh Search",
    "SITE_TAGLINE": "Composable public UX",
    "GATEWAY_ORIGIN": "https://gateway.example",
    "SEARCH_ACTION": "public.resolve-route",
    "INDEX_ACTION": "public.site-index",
    "INDEX_FETCH_MODE": "public_read",
    "PUBLIC_INDEX_ENDPOINT": "/api/public/site-index",
    "INDEX_REQUEST_BODY_JSON": "{}"
  }
}
```

## Minimal render payloads

Direct variant render:

```bash
bin/templates "$BLACKCAT_TEMPLATES_CONFIG" template:run gateway_search_variant_signal \
  '{"SITE_TITLE":"Darkmesh Search","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' \
  var/search-signal.html
```

Composed page render:

```bash
bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose pulse \
  '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' \
  var/gateway-search-pulse.html
```

## Available style families

- `pulse`: neon cinematic/glass style
- `atlas`: bold brutalist style
- `lumen`: editorial serif style

Each family has menu/search/results/footer so teams can keep consistent visual identity.

## Compose command

```bash
export BLACKCAT_TEMPLATES_CONFIG=$(pwd)/config/example.templates.json

bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose pulse \
  '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' \
  var/gateway-search-pulse.html
```

Swap profile `pulse` -> `atlas` or `lumen` to render different full experiences from the same skeleton flow.

## Migration path

If a builder currently ships direct variants, migrate in two steps:

1. Keep rendering the direct variant ID for the current release so the public URL and release map stay stable.
2. Add a component-profile render path in parallel, then switch new sites to `gateway:compose` while preserving the same token values.

Suggested mapping:

- `gateway_search_variant_signal` -> `pulse`
- `gateway_search_variant_bastion` -> `atlas`
- `gateway_search_variant_horizon` -> `lumen`

This lets `blackcat-darkmesh-web` keep the same UX contract while moving from monolithic pages to shell + fragments.

## Why this model

- Keeps templates modular and reusable (web builder can mix/extend parts).
- Keeps gateway runtime universal while UX stays customizable per site.
- Keeps deterministic output suitable for integrity checks and AR releases.
- Avoids hard dependency on PHP templates (plain HTML/CSS/JS artifacts).
