# Repo Map

This project is intentionally PHP-first and hosted on Hostinger. The goal of this map is to separate runtime code from reference material so the repo stays navigable without a framework rewrite.

## Runtime

- `index.php`, `sondeos.php`, `distrito.php`, `encuesta.php`, `candidato.php`, `encuestadoras.php`, `metodologia.php`, `quienes-somos.php`
- `api/`
- `assets/`
- `includes/`
- `partials/`
- `data/`
- `db/migrations/`

## Operational docs

- `docs/backlog.md`
- `docs/engineering-standards.md`
- `openspec/`
- `.claude/`
- `.agents/`

## Reference material

- `docs/reference/`
- `canvas-gemini/`
- `lista-candidatos/`
- `fuentes-correcciones.html`
- `politica-editorial.html`
- `politica-privacidad.html`
- `CODEX-HANDOFF.md`

## Notes

- `docs/reference/hostinger-api.openapi.json` is a vendor API export kept only as reference.
- Public pages stay at the repo root for now to avoid changing production URLs during cleanup.
- If more cleanup is needed, do it incrementally and keep the root route files stable.
