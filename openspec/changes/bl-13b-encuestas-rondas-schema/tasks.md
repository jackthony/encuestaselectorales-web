# BL-13b — Tasks

## 1. Connection bootstrap (shared with BL-13/BL-14)
- [x] 1.1 `includes/db.php`: PDO connection resolving `/config/db.php` — try `__DIR__ . '/../config/db.php'` (local dev) then `dirname($_SERVER['DOCUMENT_ROOT']) . '/config/db.php'` (production, outside `public_html/`). Throw a clear error if neither exists, don't silently fall back to a default.
- [x] 1.2 `/config/db.php` (local, gitignored): returns `['dsn' => ..., 'user' => ..., 'pass' => ...]` for the local MySQL instance per `CLAUDE.local.md`.
- [x] 1.3 `/config/db.php` (production copy, uploaded manually outside `public_html/` — not through git): same shape, pointing at the Hostinger `u185878096_encuestas` database.
- [ ] 1.4 Verify: a throwaway script connects successfully from both a local `php -S` run and the deployed site.

## 2. Schema
- [x] 2.1 `db/migrations/002_create_encuestas.sql`: `id CHAR(32)` PK from `random_bytes(16)`, `distrito_id VARCHAR(64) NOT NULL`, `tipo ENUM('online_propia','campo_externa') NOT NULL`, `numero_ronda TINYINT UNSIGNED NOT NULL DEFAULT 1`, `titulo VARCHAR(255) NOT NULL`, `fecha_apertura DATETIME NOT NULL`, `fecha_cierre DATETIME NOT NULL`, `estado_publicacion ENUM('prueba','producción') NOT NULL DEFAULT 'prueba'`, `created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP`. `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`.
- [x] 2.2 `KEY idx_distrito_activo (distrito_id, estado_publicacion, fecha_apertura, fecha_cierre)` — the exact shape of the "is this round active" query.
- [x] 2.3 Header comment: `distrito_id` is validated against `data/distrito.json` at the application layer, not a DB foreign key (same precedent as BL-13's `ubigeo_votacion`).
- [x] 2.4 Apply the migration to the live Hostinger MySQL instance via phpMyAdmin (sandboxed network can't reach MySQL directly — use the browser-based phpMyAdmin link).

## 3. Read path
- [x] 3.1 `includes/encuestas.php`: `getRondaActiva(string $distritoId): ?array` — returns the active round for a district (or `null`), using the query condition from design.md (`estado_publicacion = 'producción' AND NOW() BETWEEN fecha_apertura AND fecha_cierre`).
- [x] 3.2 `getRondasActivas(): array` — all currently-active rounds across every district, for `index.php`'s hub column.
- [x] 3.3 Prepared statements only — no string concatenation.

## 4. Wire the public pages
- [x] 4.1 `index.php`'s "Encuestas Web Activas" column: replace the `data/encuesta.json` `online_propia` filter (which never had real rows) with `getRondasActivas()`.
- [x] 4.2 `sondeos.php` / `distrito.php` sidebar: replace the `VOTACION_EN_VIVO` static flag gate with `getRondaActiva($distritoId)` — show the real round if active, the existing WhatsApp CTA otherwise.
- [x] 4.3 `data/encuesta.json`: remove the (always-empty) `online_propia` handling from any page that reads it; confirm `campo_externa` records still round-trip through `data/resultado.json` unchanged.

## 5. Data catalog
- [x] 5.1 Add optional `ubigeo` field to `data/distrito.json` for Lima Metropolitana's 43 districts (real INEI codes). Leave `null` for any district without a confirmed code rather than guessing.
- [x] 5.2 `scripts/validate-distritos.js`: accept the new optional field without failing existing records that lack it.

## 6. Creation path (operator-only, not public)
- [x] 6.1 `scripts/crear-encuesta.php`: CLI script (run via `php scripts/crear-encuesta.php`, not HTTP-reachable) that inserts one `encuestas` row from arguments or an interactive prompt — district, título, fechas, `numero_ronda`. Defaults `estado_publicacion` to `prueba`.
- [x] 6.2 A second small flag/command to flip a round from `prueba` to `producción` once an operator has verified it.

## 7. Green
- [ ] 7.1 Create a `prueba` round via the script; confirm it does NOT appear on `index.php` or `distrito.php`.
- [ ] 7.2 Flip it to `producción` with a window covering now; confirm it DOES appear in both places.
- [ ] 7.3 `scripts/check-refactor.php` and all `validate-*.js` still green.
- [x] 7.4 Update `docs/backlog.md`: BL-13b → `done`.

## Out of scope — do not touch
- `/api/votar.php`, `votos_interactivos` writes — BL-14.
- Session-authenticated round creation UI — BL-15.
- Full national UBIGEO catalog acquisition — future change, per design.md's Open Questions.
- Cloudflare/WAF — BL-12, deferred (owner decision 2026-07-20).
