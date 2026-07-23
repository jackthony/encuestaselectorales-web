# Schema Context for Opus

This document is the current data-contract reference for the project.

## Ground Rules

- `survey_rounds`, `survey_options`, `interactive_votes`, `electoral_territories`, `electoral_parties`, `electoral_candidates`, `electoral_candidacies`, `import_runs`, `import_rows`, and `legacy_mappings` are the canonical Laravel runtime tables.
- `encuestas`, `votos_interactivos`, `polls`, `poll_results`, `candidates`, `candidacies`, `election_scopes`, and `political_organizations` are legacy Hostinger/MySQL tables. Keep them only for compatibility, migration, or historical reads.
- Frontend and public portal logic should read the canonical tables first.
- Do not reintroduce `AUTO_INCREMENT` IDs. Use ULIDs or deterministic IDs only.
- `source_system` + `source_key` are the stable upsert anchors.
- `legacy_mappings` is the bridge between old identifiers and canonical identifiers.

## Canonical Runtime Tables

### `electoral_territories`
Purpose: normalized territorial tree for regions, provinces, and districts.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `official_code` | Official ubigeo-like code for the territory. |
| `scope_type` | Territory level: `region`, `province`, or `district`. |
| `name` | Human-readable display name. |
| `canonical_name` | Normalized name used for matching and search. |
| `slug` | Public URL slug. |
| `parent_id` | Self-reference to the parent territory. |
| `source_system` | Ingestion source identifier. |
| `source_key` | Stable unique key from the source. |
| `publication_state` | `draft` or `published`. Controls visibility. |
| `published_at` | Timestamp when the territory became public. |
| `source_url` | Original source URL. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `electoral_parties`
Purpose: party registry used by candidacies and vote cards.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `source_system` | Ingestion source identifier. |
| `source_key` | Stable unique key from the source. |
| `name` | Official party name. |
| `acronym` | Short label shown in UI. |
| `logo_url` | Public logo URL. |
| `logo_storage_disk` | Storage disk for local assets. |
| `logo_storage_path` | Path to the local logo asset. |
| `logo_source_attribution` | License or attribution note. |
| `source_url` | Original source URL. |
| `status` | Usually `active`. Inactive records should not surface. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `electoral_candidates`
Purpose: candidate registry used by candidacies and vote cards.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `source_system` | Ingestion source identifier. |
| `source_key` | Stable unique key from the source. |
| `full_name` | Candidate display name. |
| `photo_url` | Public photo URL. |
| `photo_storage_disk` | Storage disk for local assets. |
| `photo_storage_path` | Path to the local photo asset. |
| `photo_source_attribution` | License or attribution note. |
| `source_url` | Original source URL. |
| `status` | Usually `active`. Inactive records should not surface. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `electoral_candidacies`
Purpose: join table that binds candidate, party, territory, and office.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `candidate_id` | FK to `electoral_candidates`. |
| `political_party_id` | FK to `electoral_parties`. |
| `territory_id` | FK to `electoral_territories`. |
| `office_type` | Race type such as governor or mayor. |
| `election_cycle` | Election cycle code, e.g. `ERM2026`. |
| `source_system` | Ingestion source identifier. |
| `source_key` | Stable unique key from the source. |
| `ballot_order` | Optional ordering on the ballot. |
| `status` | Usually `active`. |
| `source_file` | Raw source file name if imported. |
| `source_row` | Row number in the imported source. |
| `source_url` | Original source URL. |
| `retrieved_at` | When the source row was captured. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `survey_rounds`
Purpose: canonical survey round header. This is what the home and territory pages read.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `territory_id` | FK to `electoral_territories`. |
| `round_number` | Survey round number within the territory. |
| `election_cycle` | Election cycle code. |
| `survey_type` | Survey subtype, usually `online_owned`. |
| `office_type` | Office being measured. |
| `title` | Public title shown in the UI. |
| `opens_at` | Start of the public voting window. |
| `closes_at` | End of the public voting window. |
| `publication_state` | `draft` or `published`. |
| `readiness_state` | `active`, `blocked`, `scheduled`, etc. |
| `blocked_reason` | Machine-readable reason when the round is blocked. |
| `source_system` | Ingestion source identifier. |
| `source_key` | Stable unique key from the source. |
| `source_url` | Original source URL. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `survey_options`
Purpose: candidate options inside a survey round.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `survey_round_id` | FK to `survey_rounds`. |
| `candidacy_id` | FK to `electoral_candidacies`. |
| `display_order` | UI ordering within the round. |
| `status` | Usually `eligible`; ineligible options should not show. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `interactive_votes`
Purpose: canonical vote ledger for the live product.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `survey_round_id` | FK to `survey_rounds`. |
| `survey_option_id` | FK to `survey_options`. |
| `validated_territory_id` | Territory used to validate the vote location. |
| `vote_type` | Usually `candidate`; supports blank/invalid if needed. |
| `gps_latitude` | Latitude used for geo validation. |
| `gps_longitude` | Longitude used for geo validation. |
| `gps_accuracy_meters` | Client GPS accuracy. |
| `geo_validation_method` | Name of the validation method used. |
| `geo_validation_result` | Validation outcome string. |
| `interaction_time_ms` | Time spent before submitting. |
| `ip_ciphertext` | Encrypted IP payload. |
| `ip_nonce` | AES-GCM nonce. |
| `ip_auth_tag` | AES-GCM auth tag. |
| `ip_encryption_key_version` | Key version for encryption. |
| `ip_hmac` | HMAC of the IP for dedup/rate limiting. |
| `ip_hmac_key_version` | Key version for the HMAC. |
| `device_token_hmac` | Optional HMAC of the device token. |
| `device_hmac_key_version` | Key version for the device HMAC. |
| `browser_fingerprint_hmac` | Optional HMAC of the browser fingerprint. |
| `browser_hmac_key_version` | Key version for the browser HMAC. |
| `status` | Vote state, usually `accepted`. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `import_runs`
Purpose: audit trail for each catalog import execution.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `territory_id` | Territory targeted by the import. |
| `source_system` | Import source. |
| `source_identity` | Human or machine identity for the source batch. |
| `source_checksum` | Batch checksum for idempotency. |
| `mapping_version` | Mapping revision used for normalization. |
| `election_cycle` | Election cycle imported. |
| `office_type` | Office imported. |
| `source_file` | Source filename. |
| `source_size_bytes` | File size. |
| `operator_identifier` | Optional operator id. |
| `status` | Import status. |
| `total_rows` | Total rows scanned. |
| `created_rows` | Rows created. |
| `updated_rows` | Rows updated. |
| `unchanged_rows` | Rows unchanged. |
| `rejected_rows` | Rows rejected. |
| `failure_summary` | Error summary if the import failed. |
| `started_at` | Import start time. |
| `completed_at` | Import end time. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `import_rows`
Purpose: row-by-row result log for each import run.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `import_run_id` | FK to `import_runs`. |
| `source_row_number` | Row number in the source file. |
| `source_key` | Source row identity if present. |
| `status` | Row status. |
| `action` | Create, update, skip, reject, etc. |
| `entity_type` | Target entity type. |
| `entity_id` | Target entity id if mapped. |
| `normalized_payload` | Normalized row payload as JSON. |
| `diagnostics` | JSON diagnostics for debugging. |
| `message` | Human-readable note. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `legacy_mappings`
Purpose: bridge table that maps old identifiers to canonical identifiers.

| Column | Purpose |
| --- | --- |
| `id` | ULID primary key. |
| `source_table` | Legacy table name. |
| `legacy_id` | Old identifier value. |
| `target_table` | Canonical table name. |
| `target_id` | Canonical identifier value. |
| `created_at` | Laravel timestamp. |
| `updated_at` | Laravel timestamp. |

### `migrations`
Purpose: Laravel framework bookkeeping table. Do not use as business data.

| Column | Purpose |
| --- | --- |
| `id` | Framework migration id. |
| `migration` | Migration filename. |
| `batch` | Migration batch number. |

## Legacy Tables

Legacy Hostinger/MySQL tables are historical only and out of scope for new work.
Do not extend them, depend on them, or revive them unless a task explicitly says
to backfill or migrate legacy data.

## Practical Use For Opus

- For the home page and current survey cards, use `survey_rounds`, `survey_options`, `electoral_candidacies`, `electoral_candidates`, and `electoral_parties`.
- For live vote counts, aggregate `interactive_votes` by `survey_option_id`.
- For geography and the district search, use `electoral_territories`.
- Legacy tables stay ignored unless a migration task explicitly reopens them.
- If a new feature needs a persistent record, extend the canonical runtime tables, not the legacy ones.
