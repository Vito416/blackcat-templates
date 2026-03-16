# Templates – Roadmap

## Stage 1 – Foundation ✅
- Catalog + config loader (`config/example.templates.php`) referencing `blackcat-config`, `blackcat-database`, `blackcat-auth`, `blackcat-orchestrator`.
- CLI `bin/templates` with `catalog:list|show`, `template:run`, `security:scan`, `integrations:list` + backward compatible `bin/template readme`.
- Telemetry/metrics + Prometheus writer and smoke tests guarding template rendering and security checks.

## Stage 2 – GitHub/Automation Templates (WIP)
- Expand catalog with workflow/issue templates + JSON Schema metadata.
- Stream rendered outputs to `blackcat-orchestrator` for auto PR creation.
- Add CI contract tests ensuring `blackcat-cli scaffold` consumes the same API.

## Stage 3 – CLI command `blackcat-cli scaffold`
- Package templates as modules for `blackcat-cli`.
- Provide policy enforcement hooks w/ `blackcat-governance` and `blackcat-security`.

## Stage 4 – Cross-Ecosystem Automation
- Wire blackcat-templates services into installer/orchestrator pipelines for push-button deployments.
- Expand contract tests covering dependencies listed in ECOSYSTEM.md.
- Publish metrics/controls so observability, security, and governance repos can reason about blackcat-templates automatically.

## Stage 5 – Continuous AI Augmentation
- Ship AI-ready manifests/tutorials enabling GPT installers to compose blackcat-templates stacks autonomously.
- Add self-healing + policy feedback loops leveraging blackcat-agent, blackcat-governance, and marketplace signals.
- Feed anonymized adoption data to blackcat-usage and reward contributors via blackcat-payout.
