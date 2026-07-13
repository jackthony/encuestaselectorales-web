## Context

`docs/data-model.md` already fixes both the shape (`{ id, nombre, tipo, web }`) and the exact 5-source list this catalog needs: 4 institutional pollsters (IEP, Ipsos, Datum, CPI — all confirmed as real, PDF-publishing Peruvian pollsters in `docs/design-references.md`) plus our own online opt-in poll (`propia`). `encuesta.json` (`BL-12`) references pollsters by `encuestadoraId`, so this catalog needs to exist and be stable before any real or dummy poll round can be loaded. Runs in parallel with `BL-07` — no shared dependency.

## Goals / Non-Goals

**Goals:**
- One authoritative `data/encuestadora.json` with exactly the 5 documented sources.
- A deterministic, collision-free `id` slug per source, matching `BL-07`'s slug convention so `encuestadoraId` lookups behave the same way `distritoId` lookups do.
- A repeatable validation script (`scripts/validate-encuestadoras.js`) so a future hand-edit can't silently break the count, a required field, a duplicate/malformed slug, or an invalid `tipo`.

**Non-Goals:**
- Any real poll/result data (`encuesta.json`, `BL-12`) — this item only builds the source catalog those records will point to.
- Dynamic pollster onboarding — explicitly out per `docs/backlog.md` BL-08 (fixed hand-maintained catalog).
- CI wiring (`BL-21`) — the script just needs to exist and be runnable locally now, same precedent as `BL-07`.

## Decisions

- **5 records, ids matching `docs/data-model.md`'s own naming**: `iep`, `ipsos`, `datum`, `cpi`, `propia` — already used as example `encuestadoraId` values in the doc, so reusing them exactly avoids a second identifier scheme for the same sources.
- **`tipo` is a closed enum**: `"institucional"` (the 4 third-party pollsters) or `"propia"` (our own) — matches `docs/data-model.md`'s own wording exactly, no third value exists yet.
- **`web` values**:
  - `iep`: `https://estudiosdeopinion.iep.org.pe` (given directly in `docs/data-model.md`'s example row)
  - `ipsos`: `https://www.ipsos.com/es-pe` (official Ipsos Perú site; `docs/design-references.md` describes it as a global-brand pollster co-publishing with El Comercio, no dedicated polling-only URL documented)
  - `datum`: `https://www.datum.com.pe` (per `docs/design-references.md`'s cited URL pattern)
  - `cpi`: `https://cpi.pe` (per `docs/design-references.md`)
  - `propia`: `https://encuestaselectorales.pe` (this site's own domain, per `CLAUDE.md` Stack — there's no separate external site for our own poll, so `web` points to the property itself rather than being left empty, keeping the field non-empty like every other record for a uniform validation rule)
- **Validation script location/tooling**: `scripts/validate-encuestadoras.js`, plain Node, zero dependencies, run via `node scripts/validate-encuestadoras.js`, exits non-zero on any failure — mirrors `BL-07`'s `validate-distritos.js` structure exactly (one script per data file, not a single generic `validate-data.js`, matching the precedent actually shipped in `BL-07` rather than the older aspirational name in `docs/engineering-standards.md` §5).
- **Validation rules** (all must pass):
  1. File parses as valid JSON, is an array.
  2. Array length is exactly 5.
  3. Every record has non-empty string fields `id`, `nombre`, `tipo`, `web`.
  4. Every `id` matches `/^[a-z0-9]+(-[a-z0-9]+)*$/` (same slug rule as `BL-07`).
  5. All 5 `id` values are unique.
  6. `tipo` is exactly `"institucional"` or `"propia"` for every record.

## Risks / Trade-offs

- **[Risk]** A wrong/stale pollster URL (sites change over time). **Mitigation**: low stakes — `web` is a citation courtesy link, not the basis of any legal/attribution claim (`BL-05` already governs the actual citation rule for poll data); a dead link is a cheap future fix, not a validation failure mode worth encoding.
- **[Trade-off]** Giving `propia` a `web` value pointing at our own domain, rather than leaving the field empty/null for that one record. **Accepted**: keeps the validation rule uniform (every record has a non-empty `web`) instead of adding a conditional exception for exactly one row.
