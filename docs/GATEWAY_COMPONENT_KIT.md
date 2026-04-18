# Gateway Component Kit (Stavebnice)

This kit is the project-default model for the gateway search UX.

Instead of one locked page, you compose the public front-end from reusable parts:

1. `gateway_search_shell_core` (layout/runtime shell)
2. `menu` fragment
3. `search` fragment
4. `results` fragment
5. `footer` fragment

All fragments are public templates and deterministic artifacts for AR publish.

## Available style families

- `pulse`: neon cinematic/glass style
- `atlas`: bold brutalist style
- `lumen`: editorial serif style

Each family has menu/search/results/footer so teams can keep consistent visual identity.

## Compose command

```bash
export BLACKCAT_TEMPLATES_CONFIG=$(pwd)/config/example.templates.php

php bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose pulse \
  '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' \
  var/gateway-search-pulse.html
```

Swap profile `pulse` -> `atlas` or `lumen` to render different full experiences from the same skeleton flow.

## Why this model

- Keeps templates modular and reusable (web builder can mix/extend parts).
- Keeps gateway runtime universal while UX stays customizable per site.
- Keeps deterministic output suitable for integrity checks and AR releases.
- Avoids hard dependency on PHP templates (plain HTML/CSS/JS artifacts).
