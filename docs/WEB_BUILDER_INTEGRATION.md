# BlackCat Web Builder Integration

This guide shows how `blackcat-darkmesh-web` can consume the templates repo as a build-time dependency.

## What the builder should read

- Template IDs from `templates/catalog.json`
- Placeholder/token maps for each chosen template
- Component profile names for composed gateway pages
- Release metadata emitted by the pinned release workflow

## Builder flow

1. Select the site entry in the web builder.
2. Map the site to a template ID or component profile.
3. Fill the template token map from site settings.
4. Render with `template:run` for a direct variant or `gateway:compose` for the shell + fragments path.
5. Store the rendered artifact, `sha256`, and release identifier next to the web build output.

## Token map example

```json
{
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

## Minimal CLI payloads

Direct variant:

```bash
bin/templates "$BLACKCAT_TEMPLATES_CONFIG" template:run gateway_search_variant_signal \
  '{"SITE_TITLE":"Darkmesh Search","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' \
  var/search-signal.html
```

Component kit:

```bash
bin/templates "$BLACKCAT_TEMPLATES_CONFIG" gateway:compose pulse \
  '{"SITE_TITLE":"Darkmesh Search","SITE_TAGLINE":"Composable public UX","GATEWAY_ORIGIN":"https://gateway.example","SEARCH_ACTION":"public.resolve-route"}' \
  var/search-pulse.html
```

## Recommended migration

Move builders from direct variants to component profiles in this order:

1. Start with the direct template ID so existing releases remain stable.
2. Add a component-profile render path for new builds.
3. Keep the same token map across both paths.
4. Cut over sites one at a time by changing only the profile mapping.

Suggested profile mapping:

- `gateway_search_variant_signal` -> `pulse`
- `gateway_search_variant_bastion` -> `atlas`
- `gateway_search_variant_horizon` -> `lumen`

## Notes

- Keep secret values out of the token map.
- Keep OTP-first and route-resolution actions on the gateway worker.
- Use the public-read index path only for the catalog/listing data.
- Treat release maps as pinned outputs, not mutable builder state.
