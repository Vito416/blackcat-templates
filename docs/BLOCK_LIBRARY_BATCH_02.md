# Block Library Batch 02

This batch adds four page families to the composition library:

- `auth` for sign-in and challenge flows
- `commerce` for cart, checkout, and payment flows
- `account` for profile and security management
- `content` for editorial pages and reading layouts

## Shared composition pattern

- Use the page composition schema from `contracts/schemas/page.composition.v0.1.schema.json`.
- Keep block references immutable: `blockId`, `version`, `txId`, and `sha256` should be pinned per release.
- Treat `variantRef` as the visual skin and `propsRef` as the site-specific data payload.
- Keep shell logic separate from block logic so gateway rendering stays deterministic and auditable.

## Family notes

### Auth

- Purpose: login, MFA, recovery, and trust-device prompts.
- Recommended shell: a narrow auth shell with a single primary action area and a secondary support rail.
- Integration notes: gateway should keep challenge endpoints idempotent; web builder should avoid storing secrets in props and should surface only public labels, copy, and state hints.

### Commerce

- Purpose: cart summaries, checkout forms, payment method choice, and order confirmation states.
- Recommended shell: a transaction shell with a strong main column and a persistent summary/sidebar region.
- Integration notes: gateway should preserve action ordering and idempotency metadata; web builder should map totals, currencies, and line items into props while keeping payment credentials outside the composition.

### Account

- Purpose: profile overview, security settings, subscriptions, and notification preferences.
- Recommended shell: an account dashboard shell with a stable sidebar for navigation and a content surface for editable sections.
- Integration notes: gateway should bind the composition to the active site/user context; web builder should split read-only and editable panels so updates can be staged without re-rendering the entire page.

### Content

- Purpose: article headers, body blocks, related links, and reading rails.
- Recommended shell: a content shell with a generous main reading column and a lighter related-content rail.
- Integration notes: gateway should keep content identity stable for caching and integrity checks; web builder should preserve canonical URLs, author metadata, and publish timestamps in the props payload.

## Gateway and web-builder guidance

- Gateway should validate that the composition shell and each block reference belong to the active release set before serving rendered output.
- Web builder should treat the composition as the source of truth for layout, but keep authoring UX focused on slots, variants, and props rather than raw manifest fields.
- Both sides should prefer family-specific presets for shell spacing, action placement, and mobile stacking so the same block family can render consistently across products.
