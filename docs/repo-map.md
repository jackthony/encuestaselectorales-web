# Repo Map

Laravel is the application source of truth. The former PHP portal remains only as
temporary rollback material until the production observation gate is complete.

## Laravel runtime

- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `tests/`
- `artisan`
- `composer.json`
- `composer.lock`

`public/api/votar.php` is a compatibility front controller that boots Laravel. It does
not execute the former root vote endpoint.
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
- Public-facing pages and API endpoints resolve through Laravel.
- The normalized schema lives in `database/migrations/`.
- Approved import data lives in `data/import/`.
- Root `api/`, `assets/`, `db/`, `includes/`, and `partials/` are rollback-only until
  production verification allows their deletion.
