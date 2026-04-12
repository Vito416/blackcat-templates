# {{MODULE_NAME}}

{{DESCRIPTION}}

## Configuration & Secrets

This module consumes gateway/runtime profiles from `blackcat-darkmesh-gateway` and references shared credentials via `${env:}` placeholders.
Document environment specific overrides in `config/example.*` so installer/orchestrator flows stay deterministic.

## CLI & Runtime

{{CLI_USAGE}}

The CLI surface exposes `*:list`, `*:show`, and `*:run` commands so deploy tooling can programmatically scaffold the repo.

## Telemetry & Observability

{{TELEMETRY_DESC}}
Metrics land in `var/metrics.prom` and can be scraped by the gateway observability stack.

## Integrations

- Write-side schema and command compatibility checked against `blackcat-darkmesh-write`.
- Authn/Authz controls routed through `blackcat-darkmesh-gateway`.
- Public/read flows validated against `blackcat-darkmesh-ao` process contracts.

## Security Checklist

- Template security scans executed via `bin/templates security:scan`.
- Secrets never embedded in template payloads; rely on `${file:}` references.
