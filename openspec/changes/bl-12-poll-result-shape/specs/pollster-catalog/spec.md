## MODIFIED Requirements

### Requirement: Pollster data file completeness
`data/encuestadora.json` SHALL contain exactly 6 records — the 4 institutional pollsters (`iep`, `ipsos`, `datum`, `cpi`), our own (`propia`), and one explicitly-fictitious example pollster (`ejemplo`) — each with non-empty string fields `id`, `nombre`, `tipo`, and (except for `tipo: "ejemplo"`, where `web` may be `null`) `web`.

#### Scenario: Full catalog validates
- **WHEN** `scripts/validate-encuestadoras.js` runs against a complete `data/encuestadora.json`
- **THEN** it exits 0 and reports 6 valid records

#### Scenario: Missing pollster fails validation
- **WHEN** `data/encuestadora.json` contains fewer than 6 records
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and reports the actual count

#### Scenario: Record missing a required field fails validation
- **WHEN** any record in `data/encuestadora.json` is missing `id`, `nombre`, or `tipo` — or is missing `web` while its `tipo` is not `"ejemplo"`
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and identifies the offending record

### Requirement: Pollster type is a closed enum
Every pollster record's `tipo` SHALL be exactly `"institucional"`, `"propia"`, or `"ejemplo"`.

#### Scenario: Invalid tipo fails validation
- **WHEN** a pollster record's `tipo` is any value other than `"institucional"`, `"propia"`, or `"ejemplo"`
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and identifies the offending record

#### Scenario: Example pollster is unmistakably fictitious
- **WHEN** the `tipo: "ejemplo"` record's `nombre` is inspected
- **THEN** it states in Spanish that it is fictitious/not a real institution, so it cannot be mistaken for real polling data by anyone reading `data/encuestadora.json` directly
