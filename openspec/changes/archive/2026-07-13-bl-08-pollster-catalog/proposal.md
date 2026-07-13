## Why

`BL-07` shipped the district catalog; `encuesta.json` (`BL-12`) references both a `distritoId` and an `encuestadoraId`, and neither `BL-11` nor `BL-14` can attribute a poll to its source without a fixed pollster catalog first. `docs/data-model.md` already fixes the shape and the 5-source list (IEP, Ipsos, Datum, CPI + our own online opt-in poll) — this item makes it real data, in parallel with `BL-07`, since it has no dependency on district data or JNE's calendar.

## What Changes

- Add `data/encuestadora.json`: 5 records — `iep`, `ipsos`, `datum`, `cpi` (`tipo: "institucional"`) and `propia` (`tipo: "propia"`), shape `{ id, nombre, tipo, web }` per `docs/data-model.md`.
- Add `scripts/validate-encuestadoras.js` (plain Node, no dependencies, same style as `BL-07`'s `validate-distritos.js`): fails if the file doesn't have exactly 5 records, any record is missing a required field, any `id` is duplicated, any `id` isn't a valid URL slug, or `tipo` isn't one of `institucional`/`propia`.
- Failing-test-first per `CLAUDE.md` constraint 7: write the script and run it against an incomplete/stub `encuestadora.json` first (red), then fill in the 5 real records until it passes (green).

## Capabilities

### New Capabilities
- `pollster-catalog`: the static pollster/source catalog (institutional + our own) and its validation rule (record count, required fields, unique slugs, valid `tipo`).

### Modified Capabilities
(none)

## Impact

- New files: `data/encuestadora.json`, `scripts/validate-encuestadoras.js`.
- No existing page consumes this yet — `encuesta.json` (`BL-12`) is the first consumer via `encuestadoraId`, and `BL-14`'s results view is the first to render source attribution from it. This item is pure data foundation, `docs/data-model.md` entity #6.
- No candidate/poll data is added — only the fixed catalog of who the sources are, which doesn't depend on JNE's Aug 5/Sept 5 calendar (`CLAUDE.md` constraint 1).
