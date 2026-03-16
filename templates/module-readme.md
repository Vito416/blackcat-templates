# {{MODULE_NAME}}

{{DESCRIPTION}}

## Configuration & Secrets

This module consumes config profiles from `blackcat-config` and references shared credentials via `${env:}` placeholders.
Document environment specific overrides in `config/example.*` so installer/orchestrator flows stay deterministic.

## CLI & Runtime

{{CLI_USAGE}}

The CLI surface exposes `*:list`, `*:show`, and `*:run` commands so `blackcat-cli` and installers can programmatically scaffold the repo.

## Telemetry & Observability

{{TELEMETRY_DESC}}
Metrics land in `var/metrics.prom` and are scraped by `blackcat-observability` for downstream analytics.

## Integrations

- Database schema bootstrap via `blackcat-database` migrations.
- Authn/Authz controls wired through `blackcat-auth`.
- Workflows dispatched to `blackcat-orchestrator` once templates are rendered.

## Security Checklist

- Template security scans executed via `bin/templates security:scan`.
- Secrets never embedded in template payloads; rely on `${file:}` references.
