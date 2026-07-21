## Why

`index.php`'s "Encuestas Web Activas" hub column and every district's `distrito.php` sidebar always render their empty state today — not because the UI is broken, but because no table exists to hold a real online survey round. BL-11b/BL-11c already wired the empty-state paths correctly; what's missing is the entity itself. The owner wants candidates imported and real encuestas creatable now, ahead of BL-12's Cloudflare hardening, to unblock the "search finds no encuestas" gap before it becomes a growth-hack liability.

## What Changes

- New MySQL table `encuestas`: one row per online survey round (`online_propia`) or field study record (`campo_externa`), scoped to a `distrito_id`, with an open/close window and a `estado_publicacion` gate (`prueba` vs `producción`) so test rounds never leak to the public site.
- `data/distrito.json` gains an optional `ubigeo` field (INEI 6-digit code) for the 43 Lima Metropolitana districts + Callao, populated as those districts get real data. Full national UBIGEO catalog acquisition (~1874 distritos, per `docs/backlog.md`'s scope-lock note) is **not** in this change — only enough to key `encuestas` rows for districts that actually have one.
- `includes/data.php` / a small new `includes/encuestas.php` reads `encuestas` from MySQL (via the same `/config/db.php` connection BL-13/BL-14 establish) instead of the static, always-empty `data/encuesta.json` `online_propia` path. `data/encuesta.json` keeps holding `campo_externa` (third-party pollster) records only — those stay static/manually-curated, they are not something this site's users create.

## Capabilities

### New Capabilities
- `encuestas-rondas-schema`: MySQL schema and read-path for online survey rounds (`encuestas` table) — a round belongs to one district, has one `tipo`, one open/close window, and a publish-state gate.

### Modified Capabilities
- `national-home-portal`: "Encuestas Web Activas" requirement's empty-state scenario is unchanged, but the column now has a real data source to query (`encuestas` table) instead of a `data/encuesta.json` field that never gets populated by this flow.

## Impact

- New: `db/migrations/002_create_encuestas.sql`, `includes/encuestas.php`.
- Modified: `index.php`, `sondeos.php`, `distrito.php` (hub/sidebar queries switch from `data/encuesta.json`'s `online_propia` filter to `includes/encuestas.php`).
- Modified: `data/distrito.json` (adds optional `ubigeo` field, additive — no existing consumer breaks).
- Depends on: BL-13's schema conventions (crypto PK, no `AUTO_INCREMENT`, `ENGINE=InnoDB utf8mb4`) and the `/config/db.php` connection this change assumes exists (provisioned directly against the live Hostinger MySQL instance, ahead of BL-12).
- Out of scope: `/api/votar.php` writes to `votos_interactivos` (BL-14), the admin auth dashboard for creating rounds through a UI (BL-15) — this change ships a minimal script-based creation path, not a session-authenticated form.
