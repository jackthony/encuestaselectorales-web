## ADDED Requirements

### Requirement: Party catalog completeness
`data/partido.json` SHALL contain exactly 8 records, one per party that ran a mayoral candidate in the 2022 Miraflores race, each with non-empty string fields `id`, `nombre`, `siglas`, `color` (hex format `^#[0-9a-fA-F]{6}$`), and a `logo` field that is either `null` or a non-empty string.

#### Scenario: Full party catalog validates
- **WHEN** `scripts/validate-partidos.js` runs against a complete `data/partido.json`
- **THEN** it exits 0 and reports 8 valid records

#### Scenario: Missing party fails validation
- **WHEN** `data/partido.json` contains fewer than 8 records
- **THEN** `scripts/validate-partidos.js` exits non-zero and reports the actual count

#### Scenario: Invalid color format fails validation
- **WHEN** any party record's `color` doesn't match `^#[0-9a-fA-F]{6}$`
- **THEN** `scripts/validate-partidos.js` exits non-zero and identifies the offending record

#### Scenario: No duplicate id or siglas
- **WHEN** two or more party records share the same `id` or the same `siglas`
- **THEN** `scripts/validate-partidos.js` exits non-zero and reports the duplicate

### Requirement: Candidate catalog completeness and cross-references
`data/candidato.json` SHALL contain exactly 8 records, one per 2022 Miraflores mayoral candidate, each with `id` (number), `nombre` (non-empty string), `partidoId` (number resolving to a record in `data/partido.json`), `cargo` (exactly `"alcalde_distrital"` for this dataset), `distritoId` (string resolving to a record in `data/distrito.json`), `foto` (`null` or non-empty string), `numero` (`null` or a number), and `activo` (boolean, `false` for every record in this historical dataset).

#### Scenario: Full candidate catalog validates
- **WHEN** `scripts/validate-candidatos.js` runs against a complete `data/candidato.json`
- **THEN** it exits 0 and reports 8 valid records

#### Scenario: Missing candidate fails validation
- **WHEN** `data/candidato.json` contains fewer than 8 records
- **THEN** `scripts/validate-candidatos.js` exits non-zero and reports the actual count

#### Scenario: Unresolvable partidoId fails validation
- **WHEN** a candidate record's `partidoId` does not match any `id` in `data/partido.json`
- **THEN** `scripts/validate-candidatos.js` exits non-zero and identifies the offending record

#### Scenario: Unresolvable distritoId fails validation
- **WHEN** a candidate record's `distritoId` does not match any `id` in `data/distrito.json`
- **THEN** `scripts/validate-candidatos.js` exits non-zero and identifies the offending record

#### Scenario: Historical dataset marked inactive
- **WHEN** any candidate record's `activo` is not `false`
- **THEN** `scripts/validate-candidatos.js` exits non-zero, flagging that this historical (2022) dataset must not claim an active/current candidacy

#### Scenario: No duplicate candidate id
- **WHEN** two or more candidate records share the same `id`
- **THEN** `scripts/validate-candidatos.js` exits non-zero and reports the duplicate
