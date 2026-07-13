## Why

`BL-07` (districts) and `BL-08` (pollsters) are done, but nothing downstream (`BL-10` nav, `BL-11` district page, `BL-12` polls) has a candidate/party record to point at. `CLAUDE.md` constraint 1 blocks real 2026 candidate data until JNE publishes admitted lists (Aug 5, 2026) — but `BL-09`'s own backlog entry explicitly allows "a documented dummy/historical set to unblock the stack" before that date. Real historical data (an actual past election) is preferable to invented names: it's verifiable, publicly reported, and exercises the real shape/edge-cases (8 candidates, 8 different parties) that dummy placeholder rows wouldn't.

**Pilot district: Miraflores** (user decision, 2026-07-13) — high media profile, historically a concentrated mayoral race, good showcase before real 2026 data exists.

## What Changes

- Add `data/partido.json`: 8 party records — the parties that ran for Miraflores mayor in the 2022 municipal election (Renovación Popular, Podemos Perú, Avanza País, Alianza para el Progreso, Somos Perú, Acción Popular, Partido Morado, Fuerza Popular), shape `{ id, nombre, siglas, color, logo }` per `docs/data-model.md`. `logo` is `null` for every record — no verified-reusable party logo asset exists yet (parallel to `BL-05`'s "don't inherit simulatuvoto's reuse assumption" stance on JNE photos).
- Add `data/candidato.json`: 8 candidate records — the real, ONPE/JNE-reported candidates who ran for Miraflores mayor in 2022, `cargo: "alcalde_distrital"`, `distritoId: "miraflores"`. Every record has `activo: false` (this is 2022 historical data, explicitly NOT a claim that these people are 2026 candidates) and `foto: null`, `numero: null` (JNE list numbers and photo assets not sourced/verified for this interim dataset — documented gap, not fabricated).
- Add `scripts/validate-partidos.js` and `scripts/validate-candidatos.js` (plain Node, zero dependencies, same per-file style as `BL-07`/`BL-08`): party catalog checks (required fields, unique `id`/`siglas`, hex color format) and candidate catalog checks (required fields, `cargo` enum, `distritoId` exists in `data/distrito.json`, `partidoId` exists in `data/partido.json`, unique `id`).
- Failing-test-first per `CLAUDE.md` constraint 7: each script is written and run against an incomplete stub first (red), then the real data fills in until green.

## Capabilities

### New Capabilities
- `pilot-candidate-roster`: the Miraflores 2022 mayoral race — 8 historical, cited candidates and their 8 parties — that unblocks `BL-10`/`BL-11` before real 2026 JNE data exists, with its cross-referential validation rule (every candidate's `distritoId`/`partidoId` must resolve).

### Modified Capabilities
(none)

## Impact

- New files: `data/partido.json`, `data/candidato.json`, `scripts/validate-partidos.js`, `scripts/validate-candidatos.js`.
- No page renders this yet — `BL-11` (district page) is the first consumer.
- Sourced from real, publicly reported 2022 ONPE/JNE election results (cited in `design.md`) — not invented names, per constraint 8's "legal exposure is first-class" stance; but explicitly historical (`activo: false`), not presented as 2026 candidacy.
- `numero` (JNE list number), `foto` (candidate photo), and `logo` (party logo) are `null` across the board for this interim dataset — real assets/values need separate sourcing once real 2026 data exists, tracked as a `BL-11` follow-up rather than guessed here.
