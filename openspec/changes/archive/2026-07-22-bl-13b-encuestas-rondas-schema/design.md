## Context

BL-13 already defined the schema conventions this project uses for MySQL (crypto PK via `random_bytes`, no `AUTO_INCREMENT`, `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4`) for `votos_interactivos`, but that table is *votes*, not the *survey round* a vote belongs to. Nothing in the codebase today models "a district has an open online poll from date A to date B." `data/encuesta.json`'s `campo_externa` records (real, third-party pollster studies) are static and manually curated — fine as JSON. `online_propia` rounds are created by this site's own operators, repeatedly, on a schedule ("weekly online voting rounds" per the 2026-07-19 scope-lock reopening) — that's a live, growing dataset, which is what a database is for.

This is also the first change to establish a working `/config/db.php` connection against the real Hostinger MySQL instance, ahead of BL-12's Cloudflare hardening (owner decision, 2026-07-20) and ahead of BL-14's vote endpoint. The connection-bootstrap decision made here is reused by both.

## Goals / Non-Goals

**Goals:**
- A MySQL table that holds one row per online survey round, queryable by district and by whether it's currently open.
- A read path (`includes/encuestas.php`) that the public pages (`index.php`, `sondeos.php`, `distrito.php`) can call without knowing SQL.
- A connection bootstrap (`includes/db.php`) that resolves `/config/db.php` correctly in both local dev (file lives inside the repo, gitignored) and production (file lives outside `public_html/`, per `CLAUDE.md`'s secret-isolation rule) — without hardcoding either machine's absolute path.
- A `prueba`/`producción` gate so an operator can create and preview a round before it's publicly visible.

**Non-Goals:**
- Writing votes (BL-14 owns `votos_interactivos` inserts).
- A session-authenticated admin UI for creating rounds (BL-15). This change's "creation path" is a small CLI/script an operator runs by hand.
- Full national UBIGEO catalog acquisition (~1874 distritos). Only Lima Metropolitana (43 distritos, already in `data/distrito.json`) + Callao get a `ubigeo` value now, since those are the only districts with real candidate/encuesta data today.
- Rate-limiting or anti-hack concerns for round *creation* — an operator creating a round is a trusted-server-side action, not a public-facing attack surface (unlike BL-14's vote submission).

## Decisions

### `encuestas` table shape
One row per round (a "ronda" is not a separate table — it's just another row scoped to the same `distrito_id` with its own open/close window and an incrementing `numero_ronda` for display, e.g. "Ronda 3"). Columns:
- `id CHAR(32)` — `bin2hex(random_bytes(16))`, matches BL-13's PK convention.
- `distrito_id VARCHAR(64) NOT NULL` — matches `data/distrito.json`'s string ids (e.g. `"miraflores"`); validated against that JSON at the application layer, same precedent as BL-13 task 2.5 (`ubigeo_votacion` isn't a DB foreign key either).
- `tipo ENUM('online_propia','campo_externa') NOT NULL` — kept even though only `online_propia` rows will ever be written here; `campo_externa` stays reserved so a future migration path off `data/encuesta.json` doesn't require a schema change, but that migration is not part of this change.
- `nivel ENUM('distrito','provincia','region') NOT NULL DEFAULT 'distrito'` — the same slug can appear at different territorial levels, so public labels must say whether the round belongs to a district, province, or region.
- `numero_ronda TINYINT UNSIGNED NOT NULL DEFAULT 1`.
- `titulo VARCHAR(255) NOT NULL` — e.g. "¿Quién va ganando en Miraflores?".
- `fecha_apertura DATETIME NOT NULL`, `fecha_cierre DATETIME NOT NULL` — `fecha_cierre > fecha_apertura` enforced at the application layer (MySQL 8/MariaDB 10.x `CHECK` constraints are inconsistently enforced across the versions `CLAUDE.local.md` flags as unverified; not relied on here).
- `estado_publicacion ENUM('prueba','produccion') NOT NULL DEFAULT 'prueba'` — a round defaults to invisible; an operator flips it explicitly. This is the gate, not a separate boolean, so a future third state (e.g. `archivada`) doesn't require an app-wide `is_produccion` rename.
- `created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP`.

A round is "active" (shows in `index.php`'s hub column / `distrito.php`'s sidebar / `territorio.php`'s territory banner) when `estado_publicacion = 'producción'` AND `NOW() BETWEEN fecha_apertura AND fecha_cierre` AND the requested territorial `nivel` matches the round's `nivel`. This is a query condition in `includes/encuestas.php`, not a stored/generated column — keeps "what counts as active" in one place, in PHP, next to the code that already defines the rest of the site's empty-state logic.

### Connection bootstrap: `includes/db.php`
Tries two paths in order, first match wins:
1. `__DIR__ . '/../config/db.php'` — local dev; the repo root (== `public_html`'s local equivalent) holds `config/db.php` directly, gitignored, per `CLAUDE.local.md`.
2. `dirname($_SERVER['DOCUMENT_ROOT']) . '/config/db.php'` — production; `DOCUMENT_ROOT` on Hostinger resolves to `.../domains/encuestaselectorales.pe/public_html`, so `dirname()` of that is the sibling directory one level up, outside the web root, matching `CLAUDE.md`'s secret-isolation rule exactly.

Each candidate `config/db.php` returns a PDO DSN + credentials array (mirrors the shape `includes/data.php` already uses for JSON — `require` returning a value, not a global). Neither path is hardcoded to this specific Hostinger username, so the same code works if the account changes.

### Why not extend `data/encuesta.json` instead of a new table
Considered and rejected: `online_propia` rounds are written by an operator, repeatedly, with real timestamps that matter for "is this open right now" — exactly what a database timestamp comparison is for and a flat JSON file re-read-and-rewritten on every creation is not (concurrent-write risk once BL-15's dashboard exists, even though this change's own creation path is a single-operator CLI). Keeping `campo_externa` in JSON and `online_propia` in MySQL is a deliberate split, not an inconsistency: one is manually curated reference data, the other is operational data with a lifecycle.

### `ubigeo` field on `data/distrito.json`
Additive, optional (`null` where absent). Populated only for districts with real data today (Lima Metropolitana's 43 + Callao once its own distrito entries exist — Callao today only has a regional-level candidate list, no distrito.json entries yet, so no `ubigeo` values ship for it in this change). No consumer reads it yet; it exists so `encuestas.distrito_id` has a documented path to a real INEI code without inventing a second identifier scheme later.

## Risks / Trade-offs

- **[Risk]** A single `/config/db.php` per environment means local dev and production point at different physical databases by construction — a migration applied locally does not apply itself to production. → **Mitigation**: `db/migrations/*.sql` files are applied by hand (via phpMyAdmin, since this sandbox's outbound network is restricted to allowlisted hosts and cannot reach MySQL directly) to both, and this is logged in the change's own tasks — not automated, matching `CLAUDE.md`'s migration-tooling rule (single numbered `.sql` file, no framework).
- **[Risk]** No DB-level `CHECK` that `fecha_cierre > fecha_apertura`, given MySQL/MariaDB version uncertainty. → **Mitigation**: enforced in the (only) creation script; a malformed row would only matter if someone edits the DB directly, which is already outside this project's threat model (same posture BL-13 takes toward direct DB access).
- **[Trade-off]** `campo_externa` stays split across two stores (JSON today, an unused enum value in this table). → Accepted: unifying them is real work (a migration off `data/encuesta.json` entirely) that isn't blocking today's actual gap, which is `online_propia` having nowhere to live at all.

## Migration Plan

1. Apply `db/migrations/002_create_encuestas.sql` to the Hostinger MySQL instance via phpMyAdmin (same DB `votos_interactivos` lives in), then `db/migrations/004_add_encuestas_nivel.sql` for the level discriminator/backfill.
2. Confirm `includes/db.php` connects from both a local `php -S` run and the deployed site.
3. No rollback beyond `DROP TABLE encuestas` — no other table references it yet.

## Open Questions

- Full national UBIGEO catalog acquisition (source, licensing, exact INEI dataset version) is deferred to a future change, per the proposal's scope — not resolved here.
