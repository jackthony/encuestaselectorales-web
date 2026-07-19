# Connected backlog — encuestaselectorales-web

> Feature backlog, numbered in execution order (BL-01 first → BL-23 last).
> Confirmed start: PHP Architecture Refactoring, Security Perimeter, Data Intelligence.
> Scope lock (2026-07-13): Data requirements are locked (GPS, AES-encrypted IP, Trust Score). No further complex data features in MVP.
> **Scope lock reopened (2026-07-19):** owner decision to add weekly online voting rounds, an explicit `online_propia` vs `campo_externa` survey type, and national (all-Peru) UBIGEO scale, ahead of national launch on 2026-07-20. Tracked as `bl-13b-encuestas-rondas-schema` (not yet drafted — sequenced after `bl-11`/`bl-11b`/`bl-11c` since it's infrastructure, not blocking tomorrow's launch). See `openspec/changes/bl-11-responsive-wcag/proposal.md`'s 2026-07-19 update for the front-end half of this decision.

## How progress is tracked
- **Status** = `not-started | in-progress | blocked | done`.
- Never skip a dependency.

---

## Phase 0 to 2 — Legal & Data Foundation
- **Status**: done (2026-07-13)
- **Note**: Baseline policies, JSON catalogs, and static foundations are locked. Unified contact (contacto@ + WhatsApp) is the standard.

---

## Phase 3 — Core Architecture Refactoring (The PHP Switch)
### BL-10 — PHP Architecture, Naming & Cleanup
- **Status**: done (2026-07-18)
- **Input**: 8 HTML prototypes in `/canvas-gemini/` (backup preserved at commit `2a6e18f`) and old `/styles.css`.
- **Output**: MVC-lite PHP structure — `index.php`, `sondeos.php`, `distrito.php`, `encuesta.php`, `candidato.php`, `encuestadoras.php`, `metodologia.php`, `quienes-somos.php`, `partials/` (head, header, footer, widget-gps, card-sondeo), `includes/` (data.php, helpers.php), `assets/css/styles.css`, `assets/js/` (app.js, voto-gps.js). `scripts/check-refactor.php` verifies structural parity against the canvas originals (8/8) plus shared header/footer partial consistency; `canvas-gemini/` deleted after it went green (recoverable at `2a6e18f`).
- **Finding carried to BL-16**: `distrito.php` had no canvas-gemini source (none of the 8 prototypes was a district-detail page) — it ships as a minimal page built only from the shared partials, no fabricated content. BL-16 wires it to `data/*.json` once a Canvas prototype exists for it. See `openspec/changes/bl-10-php-architecture/tasks.md` for the full decision log (header/footer "newest prototype wins", legal scrub, party-color data sourcing).

### BL-11 — Responsive UI & WCAG Validation
- **Status**: in-progress (reprioritized 2026-07-19)
- **Depends on**: BL-10
- **Action**: `distrito.php` hybrid rebuild (national growth-hack template) ships first, ahead of the GPS modal and WCAG audit — see proposal.md's 2026-07-19 update. Mobile-first + WCAG AA contrast passes follow after.

### BL-11b — National Home Portal (`index.php`)
- **Status**: not-started
- **Depends on**: BL-10
- **Action**: Rebuild `index.php` nationally from `canvas-gemini/portal_nacional_home.html` — global UBIGEO search, real-data-only hub columns. Ships alongside BL-11, same urgency (2026-07-20 national launch).

### BL-11c — Purge Fictitious Poll Data
- **Status**: not-started
- **Depends on**: none (independent of BL-11/BL-11b's own diffs, touches `sondeos.php`/`encuesta.php`/`candidato.php`/`data/*.json`)
- **Action**: Remove the `"ejemplo"` pollster/survey/result records and every hardcoded reference to them from production. Same urgency as BL-11/BL-11b.

### BL-13b — Encuestas / Rondas / UBIGEO Schema
- **Status**: not-started (not yet drafted as an openspec change)
- **Depends on**: BL-13
- **Action**: Design the `encuestas` DB table (`modalidad`/`tipo` ENUM `online_propia`/`campo_externa`, ronda open/close dates, `estado_publicacion` prueba/producción gate) and the national UBIGEO catalog acquisition. Sequenced after BL-11/BL-11b/BL-11c — real online voting cannot launch faster than BL-12→BL-14's security requirements allow, so this does not block tomorrow's informational/lead-capture launch.

---

## Phase 4 — Security Perimeter
### BL-12 — Cloudflare WAF Config Prep
- **Status**: not-started
- **Action**: Ensure PHP headers are prepared to receive `HTTP_CF_CONNECTING_IP` and `HTTP_CF_IPCOUNTRY`.

---

## Phase 5 — Backend & Data Intelligence (Anti-Hack)
### BL-13 — Database Anti-Hack Schema (MySQL)
- **Status**: not-started
- **Output**: MySQL script creating tables with NO AUTO_INCREMENT (use UUIDv4/NanoID). Includes `gps_lat` (DECIMAL 10,8), `gps_lng` (DECIMAL 11,8), `trust_score` (INT).

### BL-14 — IP Traceability & Geolocation Logic (PHP)
- **Status**: not-started
- **Depends on**: BL-13
- **Output**: `/api/votar.php` endpoint that hashes IP (salted) for deduplication, encrypts real IP via `AES-256-GCM`, and calculates `trust_score` (0-100) server-side.
- **Never**: return `trust_score` in the API response. Exposing it lets an attacker tune submissions until they score 100. It is written to the DB and read only behind the BL-15 admin wall.

---

## Phase 6 — Internal Control & Auth
### BL-15 — Secure Admin Dashboard
- **Status**: not-started
- **Depends on**: BL-14
- **Output**: `/admin/index.php` protected by strict session-based authentication.

---

## Phase 7 & 8 — Content, SEO & Scale
### BL-16 to BL-21 — Data Hookups, Chart.js & SEO
- **Status**: not-started
- **Output**: Dynamic public views feeding from MySQL, OG tags, XML Sitemap, cookieless analytics.

### BL-22 & BL-23 — WhatsApp Agent & Scale Up
- **Status**: not-started

---
## Execution order (Strict)
1. BL-10 Refactor to PHP Partials
2. BL-11 / BL-11b / BL-11c — national launch trio (distrito.php hybrid, index.php nacional, purge ficticios) — 2026-07-20
3. BL-11 (cont.) — GPS modal + WCAG audit
4. BL-12 Cloudflare Config
5. BL-13 DB Anti-Hack Schema (votos_interactivos)
6. BL-13b Encuestas/Rondas/UBIGEO Schema
7. BL-14 IP Traceability Logic
8. BL-15 Secure Admin Dashboard (Auth) — canvas input already collected: `canvas-gemini/panel_de_inteligencia_admin.html`
9. BL-16 to BL-21 Public UI Data & SEO
10. BL-22 WhatsApp Agent Integration
11. BL-23 Scale up
