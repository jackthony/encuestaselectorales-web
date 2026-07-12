## ADDED Requirements

### Requirement: District catalog data file
The system SHALL provide `data/distrito.json` as an array of exactly 43 records, one per Lima Metropolitana district, each with the fields `id` (string), `nombre` (string), `provincia` (string), `region` (string), matching the shape in `docs/data-model.md` #1.

#### Scenario: Exactly 43 districts
- **WHEN** `data/distrito.json` is parsed
- **THEN** it contains exactly 43 records

#### Scenario: Required fields present on every record
- **WHEN** any record in `data/distrito.json` is read
- **THEN** it has non-empty `id`, `nombre`, `provincia`, and `region` string fields

#### Scenario: All records scoped to Lima Metropolitana
- **WHEN** any record in `data/distrito.json` is read
- **THEN** `provincia` is `"lima"` and `region` is `"lima"`

### Requirement: District id is a unique, URL-safe slug
Each district record's `id` SHALL be unique across the catalog and SHALL match the slug pattern `^[a-z0-9]+(-[a-z0-9]+)*$` (lowercase ASCII, hyphen-separated, no accents or spaces) so it is directly usable in a URL path.

#### Scenario: No duplicate ids
- **WHEN** all `id` values in `data/distrito.json` are collected
- **THEN** no value appears more than once

#### Scenario: Id matches slug shape
- **WHEN** any record's `id` is checked
- **THEN** it matches `^[a-z0-9]+(-[a-z0-9]+)*$`

### Requirement: Deterministic validation script
The system SHALL provide `scripts/validate-data.js`, a dependency-free Node script that validates `data/distrito.json` against the above requirements and exits non-zero if any check fails.

#### Scenario: Script fails on missing or malformed data
- **WHEN** `node scripts/validate-data.js` runs against a missing, empty, or malformed `data/distrito.json`
- **THEN** the script exits with a non-zero status and prints which check failed

#### Scenario: Script passes on valid data
- **WHEN** `node scripts/validate-data.js` runs against a `data/distrito.json` satisfying all requirements above
- **THEN** the script exits with status 0
