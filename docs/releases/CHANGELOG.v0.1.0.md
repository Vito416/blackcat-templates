# Changelog v0.1.0

Release date: 2026-04-18

## Highlights

- Added three gateway search variants: `signal`, `bastion`, and `horizon`.
- Published the first release map at `docs/releases/gateway-search-v0.1.0.json`.
- Formalized the component-kit profiles used for composed pages: `pulse`, `atlas`, and `lumen`.

## What shipped

- `signal` - ambient/glass search UX
- `bastion` - high-contrast operational search UX
- `horizon` - editorial/readable search UX

## Placeholder semantics

- Placeholders are uppercase tokens resolved locally from JSON payloads or `@file` inputs.
- Required placeholders must be supplied; defaults apply only when a token defines one.
- Release artifacts are deterministic: identical inputs should yield the same rendered bytes and hashes.
- Release content stays public; secrets and private gateway values remain outside the template payload.

## Release references

- Release guide: `docs/GATEWAY_SEARCH_RELEASE.md`
- Release map: `docs/releases/gateway-search-v0.1.0.json`
