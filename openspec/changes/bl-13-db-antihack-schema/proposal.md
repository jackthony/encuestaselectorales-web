# BL-13 — Database Anti-Hack Schema (MySQL)

## Why

`docs/reference/db-schema-draft.sql` (extracted from `canvas-gemini/` before BL-10 deleted the folder) is a reasonable first pass with two defects that would silently break the product it's meant to protect, both analyzed in `design.md`:

1. `UNIQUE KEY` on `ip_hash` and on `browser_fingerprint` would block legitimate voters under Peru's CGNAT (thousands of mobile subscribers sharing one public IP) and under identical-device fingerprint collisions.
2. `gps_lng DECIMAL(10,8)` overflows outside roughly -99°/+99° — Peru fits by luck, the Peruvian diaspora voting from the US (-74° to -122°) does not.

Owner decision 2026-07-18: GPS is mandatory (not the nullable "declared" tier originally considered) — no vote without coordinates. This schema is built around that constraint from the start, not retrofitted to it.

This item produces only the schema — no PHP, no `/api/`, no data. BL-14 is the endpoint that writes to it.

## What changes

A MySQL/MariaDB schema for `votos_interactivos` incorporating:
- Cryptographic (non-sequential) primary key.
- `ip_hash` and `browser_fingerprint` as plain indexes for rate-limit queries, never `UNIQUE`.
- `gps_lat DECIMAL(10,8) NOT NULL`, `gps_lng DECIMAL(11,8) NOT NULL` — mandatory, correctly sized.
- `tipo_voto ENUM('candidato','blanco','viciado')` instead of a nullable `candidato_id` collapsing two distinct outcomes.
- `estado ENUM('valido','sospechoso','anulado')` — fraud found after the fact is annotated, never deleted.
- `trust_score TINYINT UNSIGNED NULL` — written by BL-14, read only behind BL-15's admin wall, never returned to a client.
- `ip_cifrada` sized and structured for AES-256-GCM (ciphertext + IV + auth tag), not CBC.

## Explicitly out of scope

- Any PHP that reads or writes this table — BL-14.
- The rate-limit query logic and its threshold — BL-14 design, this item only provides the indexes it needs.
- `trust_score`'s computation — BL-14.
- Migration tooling beyond a single numbered `.sql` file, per `CLAUDE.md`'s migration rule.

## Success criterion

The schema applies cleanly to a fresh MySQL 8 / MariaDB 10.x database. A migration test (per constraint 7 in `CLAUDE.md` — logic-bearing items write the failing check first) inserts rows simulating CGNAT (multiple votes sharing one `ip_hash`) and a diaspora coordinate (`gps_lng` beyond ±99°), and both succeed where the draft schema would have failed one or the other.
