# BL-13 — Tasks

This item is pure schema logic (constraints, sizing, uniqueness) — `CLAUDE.md`'s
test-first rule applies. The failing test is task 1, before the schema exists.

## 1. The failing test (before the schema exists)
- [ ] 1.1 Write `db/migrations/test/001_schema_test.sql` (or a small PHP/PDO script under `scripts/` if plain SQL assertions are too limited) that, against a fresh test database:
  - Inserts 6 votes sharing the same `ip_hash` for the same `encuesta_id` + `ubigeo_votacion` (simulating 6 CGNAT-sharing mobile users) and asserts all 6 succeed.
  - Inserts a vote with `gps_lng = -110.00000000` (a diaspora coordinate west of Peru but within the draft's broken ±99° ceiling) and asserts it succeeds without truncation or error.
  - Attempts to insert a vote with `gps_lat` or `gps_lng` `NULL` and asserts it is rejected (GPS is mandatory).
  - Inserts one `blanco` and one `viciado` vote and asserts they are distinguishable from each other and from a candidate vote (not collapsed into the same `NULL`).
- [ ] 1.2 Run it against `docs/reference/db-schema-draft.sql` as-is. **Confirm it fails** — the CGNAT assertion fails on the 2nd insert (unique constraint violation), the diaspora assertion fails or silently truncates. If it passes at this stage, the test isn't actually exercising the draft's defects — fix the test, not the finding.
- [ ] 1.3 Commit the failing test.

## 2. The corrected schema
- [ ] 2.1 `db/migrations/001_create_votos_interactivos.sql`. Base it on the draft, apply every fix from `design.md`:
  - `id CHAR(32)` from `random_bytes(16)` — no `AUTO_INCREMENT` anywhere in this table.
  - `gps_lat DECIMAL(10,8) NOT NULL`, `gps_lng DECIMAL(11,8) NOT NULL`.
  - `tipo_voto ENUM('candidato','blanco','viciado') NOT NULL`; `candidato_id` nullable, non-`NULL` only when `tipo_voto = 'candidato'`.
  - `estado ENUM('valido','sospechoso','anulado') NOT NULL DEFAULT 'valido'`.
  - `trust_score TINYINT UNSIGNED NULL`.
  - `user_agent VARCHAR(512)`.
  - `ip_cifrada`, `ip_iv`, `ip_tag` — separate columns for GCM's ciphertext, IV and auth tag rather than one delimited blob (a missing/mismatched tag must be independently checkable, not parsed out of a concatenated string).
  - `ip_hash CHAR(64)` — from `hash_hmac('sha256', ...)`, plain `KEY`, not `UNIQUE`.
  - `browser_fingerprint CHAR(64)` — plain `KEY`, not `UNIQUE`.
  - `device_token CHAR(64)` — plain `KEY`.
  - `cf_pais CHAR(2) NULL` — nullable; stays `NULL` until BL-12 ships, which is expected, not an error.
- [ ] 2.2 Indexes for the BL-14 rate-limit queries: `KEY idx_ratelimit_ip (encuesta_id, ip_hash, created_at)`, `KEY idx_ratelimit_device (encuesta_id, device_token, created_at)`.
- [ ] 2.3 Index for the admin heatmap: `KEY idx_geo_heatmap (ubigeo_votacion, gps_lat, gps_lng)`.
- [ ] 2.4 `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`, matching the draft.
- [ ] 2.5 Foreign-key-shaped validation (`ubigeo_votacion` should correspond to a real district) is application-level, not a DB constraint — `data/distrito.json` is the source of truth, not a MySQL table. Note this explicitly in the migration file's header comment so a future reader doesn't go looking for a missing `FOREIGN KEY`.

## 3. Green
- [ ] 3.1 Apply `001_create_votos_interactivos.sql` to a fresh test DB.
- [ ] 3.2 Run `001_schema_test.sql`. All assertions pass.
- [ ] 3.3 Commit the schema and the now-passing test together with the migration.

## 4. Documentation
- [ ] 4.1 Update `data-model.md` (or recreate it — BL-10's hard reset deleted the old one; check whether it needs to exist again as a single-file entity index) with the `votos_interactivos` shape.
- [ ] 4.2 Update `docs/backlog.md`: BL-13 → `done`.

## Out of scope — do not touch
- `/api/`, any PHP — BL-14.
- `/admin/` — BL-15.
- Applying this migration to the real Hostinger MySQL instance — that's a deploy action for whoever runs BL-14's endpoint live, not a task here.

## Known unknown to report, not solve
`CLAUDE.local.md` lists the Hostinger MySQL plan/version as unverified (hPanel → Databases). This schema targets MySQL 8 / MariaDB 10.x syntax (`CHAR(32)` PK, `ENUM`, generated defaults). If the plan runs an older MariaDB, `DECIMAL(11,8)` and `ENUM` are both fine back to MySQL 5.x, but confirm before BL-14 depends on this being live.
