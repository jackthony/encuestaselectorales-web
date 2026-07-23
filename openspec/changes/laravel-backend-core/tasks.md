## 1. Backend foundation and security

- [x] 1.1 Remove tracked production credentials and document the external Laravel and legacy runtime configuration contract.
- [x] 1.2 Configure Laravel API routing, trusted proxies, application timezone, opaque identifiers, and service-provider bindings.
- [x] 1.3 Add domain value objects and enums for territory type, publication state, round availability, vote type, and import status.

## 2. Normalized database and persistence

- [x] 2.1 Add additive Laravel migrations for territories, political parties, candidates, candidacies, survey rounds, and survey options without `AUTO_INCREMENT`.
- [x] 2.2 Add additive Laravel migrations for interactive votes, key-versioned privacy fields, atomic duplicate constraints, import runs, and import row diagnostics.
- [x] 2.3 Add Eloquent persistence models with opaque application-generated identifiers, casts, relationships, and guarded mass assignment.
- [x] 2.4 Add restartable legacy mapping/backfill support that preserves existing BL-13/BL-14 identifiers and vote records.
- [x] 2.5 Add database tests for foreign keys, natural uniqueness, duplicate-vote constraints, and migration compatibility.

## 3. Electoral catalog and survey queries

- [x] 3.1 Implement territory and electoral catalog repository contracts with Laravel infrastructure adapters.
- [x] 3.2 Implement active-round queries for national, region, province, and district scopes with explicit active, blocked, and unavailable results.
- [x] 3.3 Enforce that survey options reference candidacies for the same territory, office, and election cycle.
- [x] 3.4 Add JSON resources and read endpoints for territory search, active-round lists, and round detail.
- [x] 3.5 Add unit and feature tests for homonymous territories, ancestry, media fallbacks, publication windows, blocked rounds, and scoped candidate rosters.

## 4. Idempotent electoral import

- [x] 4.1 Implement versioned CSV/JSON readers and normalized import data objects for the approved package format.
- [x] 4.2 Implement staged validation, provenance, checksum idempotency, transactional reconciliation, and malformed-row diagnostics.
- [x] 4.3 Map `Link Foto Candidato` to party logo and `Foto Adicional` to candidate photo without fabricating absent media.
- [x] 4.4 Add an Artisan import command with dry-run, summary, source-cycle, and controlled publication options.
- [x] 4.5 Add fixtures and tests for first import, identical rerun, corrected rerun, rollback, invalid rows, and corrected media mapping.

## 5. Survey seeds and data reconciliation

- [x] 5.1 Add deterministic Laravel seeders for the approved Lima provincial and Callao regional rounds ending 5 August 2026.
- [x] 5.2 Associate only verified candidacies and expose a blocked state when an approved roster is unavailable.
- [x] 5.3 Add reconciliation commands and reports comparing legacy and Laravel catalog, rounds, options, and vote totals.

## 6. Secure vote registration

- [x] 6.1 Implement trusted client-IP resolution, separate keyed HMAC signals, AES-256-GCM encryption, key versioning, and privacy-safe logging.
- [x] 6.2 Implement geographic evidence validation with coordinate, accuracy, territory, and public-safe failure results.
- [x] 6.3 Implement the transactional `RegisterVote` use case with locked round/option validation and duplicate-key translation.
- [x] 6.4 Add the Laravel vote Form Request, controller, JSON resource, response codes, secure device cookie, and rate limiting.
- [x] 6.5 Add a compatibility adapter for the current `/api/votar.php` payload until the public JavaScript moves to the Laravel endpoint.
- [x] 6.6 Add unit, feature, and database tests for valid votes, malformed input, closed/blocked rounds, geographic rejection, duplicate IP/device signals, concurrent requests, encryption, and rollback.

## 7. Frontend adoption and legacy retirement

- [x] 7.1 Replace `PublicPortalData` legacy file/table access with the Laravel catalog and survey application contracts.
- [x] 7.2 Move the public vote JavaScript to the Laravel JSON endpoint while preserving the tested mobile location and duplicate UX.
- [x] 7.3 Update public routes to canonical Laravel territory and survey URLs while retaining redirects for indexed legacy URLs.
- [x] 7.4 Remove Laravel runtime dependencies on root `includes/`, `api/`, `db/`, and file-backed candidate data after parity tests pass.
- [x] 7.5 Update CI to run Laravel unit, feature, migration, import, vote, security, and public-rendering checks.

## 8. Hostinger rollout and production verification

- [x] 8.1 Build a repeatable Hostinger release artifact with Composer production dependencies, external secret requirements, storage permissions, and health checks.
- [x] 8.2 Back up and inventory production, run additive migrations, apply the approved clean-start vote policy, import approved data, and record reconciliation evidence.
- [x] 8.3 Verify Laravel read and vote contracts on the private deployment target, including mobile GPS, persistence, duplicate rejection, and privacy fields.
- [x] 8.4 Switch production to Laravel `public/`, clear caches, and run desktop/mobile smoke tests for home, territorial rounds, candidates, sharing, and votes.
- [ ] 8.5 Archive duplicate legacy code only after the production observation gate, document rollback, and rotate all credentials previously shared or committed.
