# Connected backlog — encuestaselectorales-web

> Feature backlog, numbered in execution order (BL-01 first → BL-23 last).
> Confirmed start: PHP Architecture Refactoring, Security Perimeter, Data Intelligence.
> Scope lock: Data requirements are locked (GPS, AES-encrypted IP, Trust Score). No further complex data features in MVP.

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
- **Status**: not-started
- **Input**: 7 HTML prototypes in `/canvas-gemini/` and old `/styles.css`. The folder also holds `controlador_php_backend_seguro.php` and `esquema_base_de_datos_b_veda_gps.sql` — these are **BL-13/BL-14 input, not BL-10 input**; move them to `docs/reference/` before touching the folder.
- **Output**: MVC-lite PHP structure (`/partials/`, `/assets/`, `index.php`, `distrito.php`).
- **Action**: 
  1. Extract repeating UI (head, nav, footer, widget-gps) into `/partials/`.
  2. Route all styling to a single `/assets/css/styles.css`, keeping existing Canvas design 100% intact.
  3. Delete `/canvas-gemini/` only after the refactor is committed and the PHP structure renders correctly — the folder is the only copy of the validated design.

### BL-11 — Responsive UI & WCAG Validation
- **Status**: not-started
- **Depends on**: BL-10
- **Action**: Ensure PHP views are 100% mobile-first, JS animations run without console errors, and WCAG AA contrast passes.

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
2. BL-11 Responsiveness/JS/WCAG
3. BL-12 Cloudflare Config
4. BL-13 DB Anti-Hack Schema
5. BL-14 IP Traceability Logic
6. BL-15 Secure Admin Dashboard (Auth)
7. BL-16 to BL-21 Public UI Data & SEO
8. BL-22 WhatsApp Agent Integration
9. BL-23 Scale up
