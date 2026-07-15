## 1. Failing check first (TDD, CLAUDE.md constraint 7)

- [x] 1.1 Update `scripts/validate-encuestadoras.js`: `EXPECTED_COUNT` 5→6, `VALID_TIPOS` gains `ejemplo`, `web` required-field check relaxed to allow `null` when `tipo === 'ejemplo'`
- [x] 1.2 Write `scripts/validate-encuestas.js` (structural + cross-reference rules from `design.md`/spec: required fields, `distritoId`/`encuestadoraId` resolve, `fechaInicio` <= `fechaFin`)
- [x] 1.3 Write `scripts/validate-resultados.js` (cross-reference + percentage-sum rules: `encuestaId` resolves, `candidatoId` resolves and matches district/cargo, `sum(porcentaje) + indecisos + votoBlancoNulo` within ±0.5 of 100)
- [x] 1.4 Create stub `data/encuesta.json` (`[]`) and `data/resultado.json` (`[]`); run all 3 validators and confirm `validate-encuestadoras.js` fails (still 5 records, old enum) — red

## 2. Data

- [x] 2.1 Add the `ejemplo` record to `data/encuestadora.json` (id `ejemplo`, `tipo: "ejemplo"`, `web: null`, `nombre` stating it's fictitious in Spanish)
- [x] 2.2 Add one Miraflores poll-round record to `data/encuesta.json` (`encuestadoraId: "ejemplo"`, `metodologia` text disclosing it's example data, `fuentePdf: null`)
- [x] 2.3 Add one result record to `data/resultado.json` (all 8 Miraflores `candidatoId`s, percentages + `indecisos` + `votoBlancoNulo` summing to 100)

## 3. Green + verification

- [x] 3.1 Run `node scripts/validate-encuestadoras.js`, `validate-encuestas.js`, `validate-resultados.js` and confirm all 3 pass — green
- [x] 3.2 Re-run `node scripts/validate-nav.js`, `validate-distritos.js`, `validate-partidos.js`, `validate-candidatos.js`, `test-candidatos-por-distrito.js` — confirm no regressions
- [x] 3.3 Update `docs/backlog.md` BL-12 status to `done` with today's date and a one-line note
