## Purpose

Static, validated catalog of the 43 Lima Metropolitana districts (`docs/data-model.md` entity #1) — the root data every later item (candidates, polls, nav, district pages) keys off of (`BL-07`).

## Requirements

### Requirement: District data file completeness
`data/distrito.json` SHALL contain exactly 43 records, one per Lima Metropolitana district, each with non-empty string fields `id`, `nombre`, `provincia`, `region`.

#### Scenario: Full catalog validates
- **WHEN** `scripts/validate-distritos.js` runs against a complete `data/distrito.json`
- **THEN** it exits 0 and reports 43 valid records

#### Scenario: Missing district fails validation
- **WHEN** `data/distrito.json` contains fewer than 43 records
- **THEN** `scripts/validate-distritos.js` exits non-zero and reports the actual count

#### Scenario: Record missing a required field fails validation
- **WHEN** any record in `data/distrito.json` is missing `id`, `nombre`, `provincia`, or `region`
- **THEN** `scripts/validate-distritos.js` exits non-zero and identifies the offending record

### Requirement: Stable URL-safe district id
Every district record's `id` SHALL be a unique, lowercase, kebab-case, ASCII slug (`^[a-z0-9]+(-[a-z0-9]+)*$`) derived from the district name, safe to embed directly in a URL path segment.

#### Scenario: Slug format enforced
- **WHEN** a district record's `id` contains an uppercase letter, space, accent, or double/leading/trailing hyphen
- **THEN** `scripts/validate-distritos.js` exits non-zero and identifies the offending `id`

#### Scenario: No duplicate slugs
- **WHEN** two or more district records share the same `id`
- **THEN** `scripts/validate-distritos.js` exits non-zero and reports the duplicated `id`
