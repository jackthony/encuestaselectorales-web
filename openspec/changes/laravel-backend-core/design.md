## Context

Laravel currently renders part of the public portal, while the electoral catalog, survey
rounds, imports, and vote registration still depend on legacy PHP entrypoints, standalone
SQL, and file-backed data. This split makes the public UI dependent on implementation
details and prevents a safe production cutover.

The backend must become the source of truth before the frontend is switched. It must
support surveys at district, province, and region level, distinguish territories that
share a name, expose explicit blocked states when a round has no valid candidates, and
preserve the existing BL-13/BL-14 production data.

The target runtime is PHP 8 and Laravel on Hostinger with MySQL/MariaDB. Production does
not require a Node.js build. Database credentials, IP-encryption keys, HMAC keys, and
trusted-proxy configuration must remain outside the public document root. All SQL access
uses prepared statements through Laravel's query builder or Eloquent bindings.

The main stakeholders are voters using mobile browsers, editors importing electoral
catalogs, operators deploying to Hostinger, and frontend code consuming stable
application contracts.

## Goals / Non-Goals

**Goals:**

- Make Laravel the authoritative backend for territories, parties, candidates,
  candidacies, survey rounds, options, and votes.
- Establish clean boundaries between domain rules, application use cases,
  infrastructure persistence, and HTTP/console presentation.
- Use opaque, non-sequential UUID or ULID identifiers for every public and internal
  aggregate; no table introduced by this change uses `AUTO_INCREMENT`.
- Preserve existing production records through a zero-downtime, forward-compatible
  database migration.
- Register votes atomically with server-side validation, privacy-preserving network
  signals, geographic evidence, and database-enforced duplicate protection.
- Import approved CSV/JSON sources idempotently, including the corrected mapping where
  `Link Foto Candidato` is the party logo and `Foto Adicional` is the candidate photo.
- Provide stable read contracts for active district, province, and region rounds and an
  explicit blocked state when candidates are unavailable.
- Support a reversible Hostinger deployment with database and application rollback
  checkpoints.

**Non-Goals:**

- Redesigning the public pages or changing the established Canvas visual language.
- Building an administration panel, candidate self-service media upload, or a complete
  editorial workflow in this change.
- Fabricating missing candidates, party logos, candidate photos, or electoral facts.
- Treating GPS, browser fingerprint, or device tokens as independently trustworthy
  identity signals.
- Implementing Cloudflare-specific hardening beyond configurable trusted-proxy handling.
- Dropping legacy tables or entrypoints during the initial production cutover.

## Decisions

### 1. Backend-first delivery through stable application contracts

Implementation proceeds in this order: schema and domain model, repositories and use
cases, imports and seed data, vote API, read APIs/services, contract tests, and only then
frontend adoption. Controllers, commands, and Blade views depend on application
interfaces rather than querying files or tables directly.

The main application contracts are:

- `TerritoryCatalog`: resolves a territory by opaque ID or canonical type/code pair.
- `SurveyRoundQuery`: returns published active rounds and their readiness state.
- `RegisterVote`: validates and records one vote, returning a typed result.
- `ElectoralCatalogImporter`: stages, validates, reconciles, and reports an import.

Laravel JSON Resources define the public representations. Form Requests validate HTTP
input and map it to immutable application data objects. Domain-specific failures are
translated to stable JSON error codes instead of leaking database exceptions.

Alternative considered: migrate each existing PHP page directly into a Laravel
controller. This was rejected because it would preserve file/table coupling and make the
frontend migration dictate the backend design.

### 2. Laravel clean architecture with pragmatic framework integration

Code is divided into four dependency directions under `app/`:

- `Domain`: entities, value objects, domain policies, and repository interfaces with no
  Laravel HTTP dependency.
- `Application`: commands, queries, data objects, and transaction-oriented use cases.
- `Infrastructure`: Eloquent models, repository implementations, encryption adapters,
  import readers, and external storage adapters.
- `Http` and `Console`: Form Requests, controllers, resources, middleware, and commands.

Service-provider bindings connect domain interfaces to infrastructure implementations.
Eloquent models remain persistence records and do not become the only representation of
domain rules. Controllers remain thin: authenticate if required, validate, invoke one
use case, and serialize its result.

Alternative considered: a repository for every table. This was rejected in favor of
repositories around aggregate boundaries and explicit query services, avoiding
ceremonial abstractions that add no test or substitution value.

### 3. Normalized territorial and electoral model

The core schema uses:

- `territories`: opaque ID, official UBIGEO/code, `scope_type` (`region`, `province`, or
  `district`), canonical name, parent ID, and publication metadata.
- `political_parties`: opaque ID, official/source key, name, acronym, and logo media
  reference.
- `candidates`: opaque ID, stable source key when available, full name, and candidate
  photo media reference.
- `candidacies`: opaque ID, candidate ID, party ID, territory ID, office type, election
  cycle, source provenance, and status.
- `survey_rounds`: opaque ID, territory ID, round number, title, opening/closing
  timestamps, publication state, and readiness state.
- `survey_options`: opaque ID, survey round ID, candidacy ID, display order, and status.
- `interactive_votes`: opaque ID, survey round ID, option ID, privacy signals,
  geographic evidence, validation result, and timestamps.
- `import_runs` and `import_rows`: opaque IDs, source identity, checksum, status,
  counters, and row-level diagnostics.

Territories are identified by both type and official code. Names are display values and
are never used alone to join data, so a district, province, and region with the same name
remain distinct.

A round is `ready` only when its publication window is valid and it has at least one
eligible option. A published round without eligible options is returned as `blocked`
with a machine-readable reason; it is not silently omitted and cannot accept votes.

Alternative considered: separate region, province, and district tables. A typed
hierarchical table was chosen because it provides one referential model for rounds and
imports while retaining scope-specific uniqueness constraints.

### 4. Opaque identifiers and database constraints

New aggregate IDs use ULIDs generated in the application before insert and stored in
canonical 26-character form. ULIDs are opaque to clients and non-sequential in business
meaning while retaining useful index locality. Existing UUID/string IDs are preserved;
a legacy mapping column or reconciliation table links them to the new aggregate rather
than rewriting referenced production rows in place.

No migration introduces `AUTO_INCREMENT`. Public lookup uses opaque IDs or canonical
territory codes, never row offsets. Natural uniqueness is enforced separately, for
example:

- territory: `(scope_type, official_code)`
- candidacy: `(candidate_id, party_id, territory_id, office_type, election_cycle)`
- round: `(territory_id, round_number, election_cycle)`
- option: `(survey_round_id, candidacy_id)`

Alternative considered: UUIDv4 for all new rows. UUIDv4 satisfies opacity but creates
more random B-tree writes; ULID is preferred for new records while repository contracts
accept existing UUID identifiers during migration.

### 5. Stable read and vote API contracts

Read responses expose the territory `scope_type` and label, round state, publication
window, candidates, party name, party logo, candidate photo, and source provenance.
Missing candidate photos resolve to a configured default portrait at presentation time;
the importer does not write a fake media URL into candidate data.

The Laravel vote endpoint accepts an opaque survey option identifier, the round
identifier, GPS latitude/longitude and accuracy, interaction timing, and a device token.
The server derives network and request metadata. Success uses a stable JSON envelope;
validation, duplicate, blocked-round, closed-round, and geographic-rejection results use
documented machine-readable codes and appropriate HTTP status codes.

The legacy `/api/votar.php` contract remains available through an adapter during parity.
The adapter translates its `candidato_id` and `distrito_id` fields into the new
round/option command and returns its historical response shape. It is retired only after
the frontend and production smoke checks use the Laravel endpoint.

Alternative considered: switch the frontend and endpoint in one release. The adapter is
preferred because it separates backend correctness from presentation cutover and keeps
rollback possible.

### 6. Atomic vote registration and privacy by design

`RegisterVote` executes one database transaction with this sequence:

1. Resolve and lock the survey round and selected option.
2. Recheck publication window, readiness, option eligibility, and territory match.
3. Derive the client IP only from a trusted proxy chain; otherwise use
   `REMOTE_ADDR`.
4. Normalize the IP and compute a keyed HMAC for duplicate/rate-limit lookup.
5. Encrypt the normalized IP using AES-256-GCM with a random nonce and authentication
   tag; encryption keys are versioned outside the public root.
6. Hash normalized device-token and fingerprint signals with a separate keyed HMAC.
7. Validate GPS range, accuracy threshold, and server-side territory evidence; store
   coordinates, accuracy, validation method, and result with bounded precision.
8. Insert the vote and commit.

Database unique constraints provide the final duplicate-prevention boundary for the
active duplicate policy, including at minimum `(survey_round_id, ip_hmac)` and
`(survey_round_id, device_token_hmac)` where the signal is available. Application checks
produce friendly errors, but correctness does not depend on a check-before-insert race.
A duplicate-key exception is translated to the same duplicate-vote result.

Raw IP addresses are never logged or stored in plaintext. HMAC, encryption, and device
keys are distinct and rotatable. Operational logs contain correlation IDs and result
codes, not GPS coordinates, tokens, fingerprints, or encrypted IP payloads. Retention
and deletion can later remove encrypted IP/GPS evidence without changing aggregate vote
counts.

Alternative considered: rely on cookie/device fingerprint uniqueness. This was rejected
because browser-controlled values are forgeable and differ between Safari and Chrome.
The server-derived network signal and database constraints are the minimum enforcement
floor, while device, fingerprint, GPS, timing, and accuracy remain supporting signals.

### 7. Idempotent staged imports with provenance

Imports run through an Artisan command and never write source rows directly into live
catalog tables. Each run:

1. Identifies the source, election cycle, file checksum, mapping version, and operator.
2. Parses into normalized staging records.
3. Validates required territorial, party, candidate, office, and media fields.
4. Resolves records using official source keys or documented composite natural keys.
5. Upserts changed aggregates in a transaction and records row-level outcomes.
6. Reconciles removals by status when the source is authoritative; it does not hard
   delete records implicitly.
7. Publishes rounds/options only after validation thresholds pass.

Reprocessing an identical checksum returns the previous successful result without
duplicating records. Reprocessing corrected content updates the matched aggregates and
keeps provenance. Candidate and party media mappings are explicit:
`Link Foto Candidato` maps to the party logo, and `Foto Adicional` maps to the candidate
photo. Broken or absent media stays null and uses presentation fallback behavior.

Alternative considered: database seeders as the long-term import mechanism. Seeders are
appropriate for deterministic initial rounds, but an audited idempotent command is
required for recurring nationwide data deliveries and partial corrections.

### 8. Expand-migrate-contract database rollout

Laravel migrations are additive during the cutover:

- Expand: create normalized tables, indexes, constraints, key-version fields, import
  audit tables, and legacy mapping fields without renaming or dropping live structures.
- Migrate: backfill existing BL-13/BL-14 catalog, rounds, options, and votes in bounded,
  restartable batches. Record source-to-target mappings and verification counts.
- Dual-read verification: compare legacy and Laravel results for active rounds and vote
  totals while legacy remains authoritative for the public frontend.
- Cutover: make Laravel authoritative after reconciliation, contract tests, and
  production smoke checks pass.
- Contract: remove legacy entrypoints and tables only in a later change after the agreed
  observation window and a final backup.

Large indexes are created separately and during the lowest-traffic window because
Hostinger-managed MySQL may not guarantee online DDL for every operation. Migrations do
not wrap unsupported DDL assumptions in a transaction. Backfills use checkpoints so
they can resume without duplicate writes.

Alternative considered: rename existing tables and transform them in place. Additive
tables and backfills are preferred because they keep the live application compatible
throughout migration and make rollback independent of reverse data conversion.

### 9. Testing and observability gates

Backend delivery requires:

- unit tests for domain policies, readiness, duplicate classification, and geographic
  validation;
- feature tests for read and vote JSON contracts;
- database integration tests for unique constraints, concurrent duplicate submissions,
  transaction rollback, encryption round trips, and legacy adapters;
- import fixtures proving first import, identical rerun, corrected rerun, malformed-row
  quarantine, and media-field mapping;
- migration tests from a sanitized snapshot of the current schema;
- production smoke checks for Lima provincial and Callao regional rounds through
  5 August 2026.

Structured operational events include import run ID, release ID, correlation ID,
duration, counts, and result code. Health checks verify application bootstrap, database
connectivity, migration compatibility, and storage permissions without exposing secrets.

Alternative considered: rely on page-level regression checks. Those remain useful for
presentation parity but cannot prove transactional, privacy, or migration guarantees.

## Risks / Trade-offs

- [Hostinger MySQL DDL may lock large tables] -> Use additive migrations, inspect table
  sizes and engine capabilities, create indexes separately at low traffic, and abort
  before cutover if lock estimates are unsafe.
- [Dual schemas can temporarily diverge] -> Use deterministic mappings, reconciliation
  reports, count/checksum gates, and a single declared write authority per phase.
- [ULIDs expose approximate creation ordering] -> Do not encode business information in
  IDs, never use IDs as authorization, and use UUIDv4 for any future aggregate where
  timestamp opacity outweighs index locality.
- [Network-level duplicate checks can affect shared connections] -> Return a clear
  duplicate result, retain additional device and geographic evidence, monitor rejection
  rates, and keep policy parameters configurable without weakening database atomicity.
- [IP HMAC values permit internal correlation if keys leak] -> Isolate and rotate a
  dedicated HMAC key, restrict access, avoid logging hashes, and version derived signals.
- [GPS can be denied, inaccurate, or spoofed] -> Treat it as evidence, enforce explicit
  accuracy and validation outcomes, provide clear UX errors, and never regard GPS alone
  as proof of identity.
- [Source files can change format or contain incorrect media] -> Version mappings,
  quarantine invalid rows, require import summaries before publication, and preserve
  source provenance.
- [Legacy and Laravel contracts can disagree] -> Maintain an adapter, run contract tests
  against both paths, and switch consumers only after parity is demonstrated.
- [A rollback after Laravel accepts new votes could lose writes] -> Keep normalized
  tables intact, roll application code back to a compatibility release that can read the
  new schema, and never reverse-delete migrated or newly accepted records.
- [Secrets under `public_html` could be disclosed by server misconfiguration] -> Store
  environment and key material outside the document root, expose only Laravel `public/`,
  and fail closed when required secrets are absent.

## Migration Plan

1. Capture a database backup, schema dump, row counts, checksums, current release
   artifact, PHP/extensions, document-root configuration, and Laravel storage
   permissions in a timestamped Hostinger deployment record.
2. Deploy a release containing the additive Laravel migrations and backend code while
   leaving legacy routes and tables active. Configure secrets outside `public_html` and
   verify trusted-proxy behavior.
3. Run expand migrations. Verify tables, non-sequential primary keys, foreign keys,
   duplicate-prevention constraints, and required indexes before any backfill.
4. Backfill legacy records in restartable batches. Reconcile IDs, rounds, options, vote
   counts, timestamps, and privacy fields. Quarantine records that cannot be mapped
   instead of inventing values.
5. Run the idempotent imports for the approved datasets and deterministic seed process
   for Lima provincial and Callao regional rounds. Re-run each import to prove no
   duplicate records are created.
6. Exercise Laravel read and vote contracts on a non-public Hostinger route or
   subdomain. Run concurrency, duplicate, mobile-location, blocked-round, and closed
   round smoke tests.
7. Enable dual-read comparison and observe reconciliation metrics while public traffic
   remains on the legacy presentation path. Do not dual-write votes unless a tested
   outbox/replication mechanism is introduced; use one write authority.
8. Deploy the cutover release and atomically switch routing/document-root configuration
   to Laravel `public/`. Keep the legacy API adapter and old release available.
9. Verify home cards, scope labels, candidate and party data, missing-photo fallback,
   vote persistence, duplicate rejection, encryption fields, and application logs.
10. After the observation window, schedule the separate contract phase that archives
    legacy code and eventually drops unused structures after another backup.

Hostinger rollback is release-based and non-destructive:

1. Stop the cutover if migrations, reconciliation, or smoke gates fail.
2. Restore routing/document-root configuration to the previous known-good release or
   redeploy its immutable artifact.
3. Keep additive tables and all votes written after cutover. The rollback release must
   be schema-compatible and may use the legacy adapter to read/write the normalized
   backend.
4. Clear Hostinger/application caches and verify the previous health checks.
5. Restore the database backup only for confirmed destructive corruption and only after
   preserving a forensic copy of the current database. Ordinary application rollback
   must not restore an old snapshot because that would discard valid votes.
6. Record the failed release, migration checkpoint, reconciliation output, and recovery
   action before retrying.

## Open Questions

- What exact server-side territorial boundary source and tolerance will determine
  whether GPS evidence validates a district, province, or region?
- What are the approved duplicate-policy window and exception process for legitimate
  voters sharing a carrier-grade NAT or household connection?
- What retention periods apply to encrypted IP, IP HMAC, device/fingerprint HMAC, and
  precise GPS evidence after a survey closes?
- Does the current Hostinger plan support an atomic document-root or release-directory
  switch, or must the deployment use a short maintenance window for the final file swap?
- Which source identifiers are authoritative when candidate data arrives without DNI or
  another stable official candidate key?
- How long must dual-read verification run before Laravel becomes the sole production
  source and before the legacy contract phase may begin?
