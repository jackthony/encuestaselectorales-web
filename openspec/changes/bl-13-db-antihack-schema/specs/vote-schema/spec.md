# vote-schema

## ADDED Requirements

### Requirement: IP and device signals never constrain uniqueness
`ip_hash`, `browser_fingerprint` and `device_token` SHALL be plain indexes, never part of a `UNIQUE` constraint, so that CGNAT-sharing users and identical devices are never blocked from voting.

#### Scenario: Multiple votes share one IP hash without error
- **WHEN** six votes are inserted for the same `encuesta_id` and `ubigeo_votacion`, all sharing the same `ip_hash`
- **THEN** all six inserts succeed

#### Scenario: Multiple votes share one browser fingerprint without error
- **WHEN** two votes are inserted with an identical `browser_fingerprint` value
- **THEN** both inserts succeed

### Requirement: GPS coordinates are mandatory and correctly sized
`gps_lat` and `gps_lng` SHALL be `NOT NULL`, and `gps_lng` SHALL accommodate the full ±180° longitude range.

#### Scenario: A vote without coordinates is rejected
- **WHEN** an insert omits `gps_lat` or `gps_lng`
- **THEN** the insert fails

#### Scenario: A diaspora coordinate is stored without truncation
- **WHEN** a vote is inserted with `gps_lng = -110.00000000` (within the US longitude range, outside Peru's)
- **THEN** the value is stored exactly, not truncated or rejected

### Requirement: Blank and spoiled votes are distinguishable from candidate votes
`tipo_voto` SHALL be an explicit enum distinguishing `candidato`, `blanco` and `viciado`, rather than collapsing blank/spoiled into a `NULL` `candidato_id`.

#### Scenario: A blank vote and a spoiled vote are stored distinctly
- **WHEN** one vote is inserted with `tipo_voto = 'blanco'` and another with `tipo_voto = 'viciado'`
- **THEN** querying by `tipo_voto` returns each independently, and neither has a `candidato_id`

### Requirement: Fraud is annotated, never deleted
`estado` SHALL default to `valido` and support marking a row `sospechoso` or `anulado` without a `DELETE`.

#### Scenario: A vote can be marked suspicious without removing it
- **WHEN** a row's `estado` is updated to `sospechoso`
- **THEN** the row still exists and remains queryable

### Requirement: trust_score is write-only from the application's perspective
`trust_score` SHALL exist as a column BL-14 writes, with no requirement or expectation that it is ever included in an API response.

#### Scenario: Column exists and accepts a computed value
- **WHEN** a vote is inserted with a `trust_score` between 0 and 100
- **THEN** the value is stored

### Requirement: Encrypted IP storage supports authenticated decryption
`ip_cifrada` SHALL be stored alongside its GCM initialization vector and authentication tag as distinct columns, not concatenated into one field.

#### Scenario: Ciphertext, IV and tag are independently addressable
- **WHEN** the table schema is inspected
- **THEN** `ip_cifrada`, `ip_iv` and `ip_tag` exist as separate columns
