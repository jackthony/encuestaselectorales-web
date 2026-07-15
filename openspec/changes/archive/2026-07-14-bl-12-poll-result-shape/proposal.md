## Why

`docs/data-model.md` already defines the `encuesta.json`/`resultados-lima.json` shapes (entities #7 Poll, #8 Result, #9 Fact sheet), but no data file exercises them yet. `BL-13` (chart component) and `BL-14` (multi-source results view) both need at least one real poll+result pair to build against. Real 2026 municipal poll data doesn't exist until the JNE admits candidate lists (Aug 5) — `BL-12`'s own backlog note allows a dummy/historical stand-in to unblock the shape now.

## What Changes

- Add `data/encuesta.json`: one poll-round record for the pilot district (Miraflores), following `docs/data-model.md`'s shape.
- Add `data/resultado.json`: one denormalized result record (per-candidate percentages, indecisos, voto blanco/nulo) tied to that poll via `encuestaId`.
- **Explicit data-integrity decision (user, 2026-07-14): this poll is clearly-labeled example data, not attributed to any real pollster.** Real institutional pollsters (IEP/Ipsos/Datum/CPI) never published a Miraflores 2022 pre-election poll to our knowledge, and inventing numbers under a real company's name is a data-integrity/legal risk (`CLAUDE.md` constraint 8) — worse than the risk BL-09 accepted (BL-09 used real ONPE-certified election *results*, not a fabricated pre-election *poll* attributed to a real institution). So this item adds a 6th, unmistakably-fictitious pollster catalog entry rather than misattributing to a real one.
- Add `scripts/validate-encuestas.js`: structural + cross-reference validation for `encuesta.json` (enums, date ordering, `distritoId`/`encuestadoraId` resolve).
- Add `scripts/validate-resultados.js`: cross-reference + **percentage-sum validation** for `resultado.json` (every `candidatoId` resolves and belongs to the poll's district/cargo, `sum(resultados[].porcentaje) + indecisos + votoBlancoNulo ≈ 100`).

## Capabilities

### New Capabilities
- `poll-result-shape`: the `encuesta.json`/`resultado.json` data shapes and their validation rules (structural, cross-reference, and percentage-sum).

### Modified Capabilities
- `pollster-catalog`: adds one explicitly-fictitious "ejemplo" pollster (distinct `tipo` value, not `institucional`/`propia`) so example poll data has somewhere honest to point instead of a real institution's id.

## Impact

- New files: `data/encuesta.json`, `data/resultado.json`, `scripts/validate-encuestas.js`, `scripts/validate-resultados.js`.
- Modified: `data/encuestadora.json` (+1 record), `scripts/validate-encuestadoras.js` (count 5→6, `tipo` enum gains `ejemplo`).
- No page renders this data yet (`BL-13`/`BL-14` do) — this item is data-foundation only, consistent with `docs/backlog.md` BL-12's "validate the shape... hand-loaded" scope.
