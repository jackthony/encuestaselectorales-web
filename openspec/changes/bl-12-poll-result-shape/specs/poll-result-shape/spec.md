## ADDED Requirements

### Requirement: Poll round data validates structurally and cross-references
`data/encuesta.json` SHALL be an array of poll-round records, each with required fields (`id`, `cargo`, `ambito`, `fechaInicio`, `fechaFin`, `tamanoMuestra`, `margenError`, `nivelConfianza`, `modalidad`, `metodologia`, `encuestadoraId`), where `distritoId` (if not null) resolves in `data/distrito.json` and `encuestadoraId` resolves in `data/encuestadora.json`.

#### Scenario: Valid poll record passes
- **WHEN** `scripts/validate-encuestas.js` runs against a `data/encuesta.json` record with all required fields, a resolvable `encuestadoraId`, and (if set) a resolvable `distritoId`
- **THEN** it exits 0

#### Scenario: Unresolvable encuestadoraId fails validation
- **WHEN** a `data/encuesta.json` record's `encuestadoraId` doesn't match any `data/encuestadora.json` id
- **THEN** `scripts/validate-encuestas.js` exits non-zero and names the offending record and id

#### Scenario: Invalid date ordering fails validation
- **WHEN** a `data/encuesta.json` record's `fechaInicio` is after its `fechaFin`
- **THEN** `scripts/validate-encuestas.js` exits non-zero and names the offending record

### Requirement: Result percentages sum to 100
`data/resultado.json` SHALL be an array of result records, each with `encuestaId` resolving in `data/encuesta.json`, every `resultados[].candidatoId` resolving in `data/candidato.json` and belonging to that poll's district/cargo, and `sum(resultados[].porcentaje) + indecisos + votoBlancoNulo` within ±0.5 of 100.

#### Scenario: Percentages summing to 100 pass
- **WHEN** `scripts/validate-resultados.js` runs against a result record whose candidate percentages plus `indecisos` plus `votoBlancoNulo` sum to 100 (±0.5)
- **THEN** it exits 0

#### Scenario: Percentages not summing to 100 fail validation
- **WHEN** a result record's percentages sum to a value more than 0.5 away from 100
- **THEN** `scripts/validate-resultados.js` exits non-zero and reports the actual sum

#### Scenario: Candidate from the wrong race fails validation
- **WHEN** a result record references a `candidatoId` that resolves in `data/candidato.json` but belongs to a different `distritoId`/`cargo` than the poll it's attached to
- **THEN** `scripts/validate-resultados.js` exits non-zero and names the offending candidate

#### Scenario: Unresolvable encuestaId fails validation
- **WHEN** a `data/resultado.json` record's `encuestaId` doesn't match any `data/encuesta.json` id
- **THEN** `scripts/validate-resultados.js` exits non-zero and names the offending id
