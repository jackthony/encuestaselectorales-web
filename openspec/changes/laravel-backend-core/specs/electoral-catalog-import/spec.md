## ADDED Requirements

### Requirement: Approved CSV and JSON catalogs are imported through one command
The system SHALL provide a Laravel command that imports the approved electoral CSV and JSON formats into the normalized territory, party, candidate, candidacy, and media catalog.

#### Scenario: An approved source file is imported
- **WHEN** an operator supplies a supported CSV or JSON file and its declared electoral scope and office
- **THEN** the command validates the source and imports normalized catalog records for that scope

#### Scenario: An unsupported source is supplied
- **WHEN** a file lacks the required format, headers, scope, or electoral office
- **THEN** the command fails before mutating catalog data and reports actionable validation errors

### Requirement: Import field mappings preserve candidate and party semantics
The importer SHALL map `Link Foto Candidato` to the political-party logo, `Foto Adicional` to the candidate photo, and SHALL treat `Link Logo Partido` as an untrusted legacy field that cannot override either verified mapping.

#### Scenario: All legacy image columns are populated
- **WHEN** a source row contains `Link Logo Partido`, `Link Foto Candidato`, and `Foto Adicional`
- **THEN** the imported candidacy uses `Link Foto Candidato` as the party logo and `Foto Adicional` as the candidate photo

#### Scenario: The candidate photo is empty
- **WHEN** `Foto Adicional` is blank or invalid
- **THEN** the importer stores no fabricated photo and marks the candidacy for the default face fallback

#### Scenario: The legacy party-logo link is invalid
- **WHEN** `Link Logo Partido` references the known dead or unverified source
- **THEN** the importer does not publish that value as either the party logo or candidate photo

### Requirement: Imports are idempotent and reconcile canonical entities
The importer SHALL use stable source keys and normalized natural keys to reconcile existing territories, parties, candidates, and candidacies so rerunning the same dataset does not create duplicates.

#### Scenario: The same dataset is imported twice
- **WHEN** an operator reruns an unchanged approved file for the same scope and office
- **THEN** the second run reports unchanged or updated records and creates no duplicate entities or candidacies

#### Scenario: A source record changes
- **WHEN** a stable source record contains an updated verified name or media reference
- **THEN** the importer updates the matching canonical record while preserving its opaque identifier

### Requirement: Import respects territorial scope and electoral office
The importer SHALL require every batch to resolve to one normalized region, province, or district and one electoral office before creating candidacies.

#### Scenario: Lima provincial data is imported
- **WHEN** the approved Lima Metropolitana provincial dataset is processed
- **THEN** its candidacies are associated with the Lima province scope and provincial mayor office

#### Scenario: Callao regional data is imported
- **WHEN** the approved Callao regional dataset is processed
- **THEN** its candidacies are associated with the Callao region scope and regional governor office

#### Scenario: A location name is ambiguous
- **WHEN** a source location name could resolve to more than one scope type
- **THEN** the importer requires an official ubigeo or explicit scope selection and does not guess

### Requirement: Invalid rows do not produce fabricated catalog data
The importer SHALL reject or quarantine rows missing the minimum verified candidate, party, territory, or office data and SHALL NOT fabricate names, candidates, parties, identifiers derived from sequence numbers, or media.

#### Scenario: A row lacks a candidate or party name
- **WHEN** a source row lacks a verified candidate name or political-party name
- **THEN** the row is rejected with its source position and no partial candidacy is created

#### Scenario: A row lacks DNI
- **WHEN** an approved source has no DNI column but contains the required verified candidate and candidacy fields
- **THEN** the importer uses a stable non-DNI source identity strategy and does not invent a DNI

### Requirement: Import execution is auditable
The system SHALL record an opaque import-run identifier, source checksum, declared scope, electoral office, timestamps, counts of created, updated, unchanged, rejected records, and row-level rejection reasons.

#### Scenario: An import completes
- **WHEN** the command finishes processing a source
- **THEN** it emits and persists a summary that accounts for every source row

#### Scenario: A source checksum was already imported
- **WHEN** an operator imports an identical source checksum for the same scope and office
- **THEN** the command identifies the prior run and completes idempotently without duplicating catalog records

### Requirement: Import commits atomically
The importer SHALL apply each validated batch transactionally so an unexpected failure cannot leave a partially reconciled electoral roster.

#### Scenario: A database failure occurs during reconciliation
- **WHEN** any required catalog write fails before the batch completes
- **THEN** the importer rolls back the batch and records the run as failed
