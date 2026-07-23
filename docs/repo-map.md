# Repo Map

This repository is in transition from a PHP legacy app to a Laravel scaffold.
The map below separates what is production runtime today from what is migration
scaffold or reference material.

## Runtime today

- `index.php`, `sondeos.php`, `distrito.php`, `encuesta.php`, `candidato.php`, `encuestadoras.php`, `metodologia.php`, `quienes-somos.php`
- `territorio.php`
- `api/`
- `assets/`
- `includes/`
- `partials/`
- `data/`
- `db/migrations/`
- `db/seeds/`
- `scripts/import-normalized-catalog.php`

## Migration scaffold

- `laravel-app/`
- `laravel-app/public/api/votar.php` keeps the current vote endpoint alive while Laravel is introduced.
- Persistent scaffold path: `C:\Users\jaaguilar\Documents\neuracode\encuestaselectorales-web\laravel-app`
## Operational docs

- `docs/backlog.md`
- `docs/engineering-standards.md`
- `docs/ops/`
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
- `docs/reference/canvas-gemini/` keeps the HTML prototypes the refactor checks compare against.
- `docs/ops/CLAUDE.local.md` and `docs/ops/CODEX-HANDOFF.md` are session notes, not runtime files.
- Public pages stay at the repo root while the bridge is validated and cleanup proceeds incrementally.
- The Laravel scaffold is the migration path, not a rewrite of the production URLs yet.
- The normalized MySQL catalog lives in `db/migrations/003_create_catalogo_normalizado.sql`.
