# Block Spec v0.1 (TS/JS-first)

This spec defines the composable frontend model for BlackCat Darkmesh templates.

The goal is to keep templates as public deterministic artifacts while allowing full frontend logic per block.

## Runtime model

- Block UI + logic run in browser (`TS/JS`, ESM bundles).
- Secrets/signing never live in blocks.
- Gateway remains untrusted translator/enforcer.
- Worker remains the only secret/signing authority.

## Spec files

- `contracts/schemas/block.manifest.v0.1.schema.json`
- `contracts/schemas/block.variant.v0.1.schema.json`
- `contracts/schemas/shell.manifest.v0.1.schema.json`
- `contracts/schemas/page.composition.v0.1.schema.json`

## Example payloads

- `contracts/examples/auth.login.block.manifest.json`
- `contracts/examples/auth.login.neon.variant.json`
- `contracts/examples/gateway.search.shell.manifest.json`
- `contracts/examples/page.home.composition.json`

## Why this format

- Separates **logic** (`block.manifest`) from **visual style** (`block.variant`).
- Keeps shell layout stable (`shell.manifest`) while site UX stays customizable.
- Supports trustless publish flow by pinning tx ids + hashes in `page.composition`.

## Integration notes

- Gateway front-controller should serve the active search shell bundle from AR and cache it.
- `/template/call` stays for strict backend action contracts (allowlisted actions only).
- AO/WRITE/worker contracts are referenced by block action schema refs.
