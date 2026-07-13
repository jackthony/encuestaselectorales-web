## Purpose

Static, validated catalog of the 5 polling sources (`docs/data-model.md` entity #6) — 4 institutional pollsters plus our own opt-in poll — that `encuesta.json` (`BL-12`) will reference by `encuestadoraId` (`BL-08`).

## Requirements

### Requirement: Pollster data file completeness
`data/encuestadora.json` SHALL contain exactly 5 records — the 4 institutional pollsters (`iep`, `ipsos`, `datum`, `cpi`) and our own (`propia`) — each with non-empty string fields `id`, `nombre`, `tipo`, `web`.

#### Scenario: Full catalog validates
- **WHEN** `scripts/validate-encuestadoras.js` runs against a complete `data/encuestadora.json`
- **THEN** it exits 0 and reports 5 valid records

#### Scenario: Missing pollster fails validation
- **WHEN** `data/encuestadora.json` contains fewer than 5 records
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and reports the actual count

#### Scenario: Record missing a required field fails validation
- **WHEN** any record in `data/encuestadora.json` is missing `id`, `nombre`, `tipo`, or `web`
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and identifies the offending record

### Requirement: Stable URL-safe pollster id
Every pollster record's `id` SHALL be a unique, lowercase, kebab-case, ASCII slug (`^[a-z0-9]+(-[a-z0-9]+)*$`).

#### Scenario: Slug format enforced
- **WHEN** a pollster record's `id` contains an uppercase letter, space, or double/leading/trailing hyphen
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and identifies the offending `id`

#### Scenario: No duplicate slugs
- **WHEN** two or more pollster records share the same `id`
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and reports the duplicated `id`

### Requirement: Pollster type is a closed enum
Every pollster record's `tipo` SHALL be exactly `"institucional"` or `"propia"`.

#### Scenario: Invalid tipo fails validation
- **WHEN** a pollster record's `tipo` is any value other than `"institucional"` or `"propia"`
- **THEN** `scripts/validate-encuestadoras.js` exits non-zero and identifies the offending record
