## Why

Laravel currently renders the public portal but does not own the electoral domain, database schema, import pipeline, or vote API. The production cutover cannot be considered complete until those backend responsibilities are migrated into tested framework contracts and the public UI consumes them directly.

## What Changes

- Add a normalized Laravel domain for territorial scopes, political parties, candidates, survey rounds, survey options, and interactive votes.
- Replace standalone SQL as the application source of truth with Laravel migrations that preserve non-sequential identifiers and production data.
- Add repository and service contracts so controllers and views do not query files or raw tables directly.
- Add an idempotent catalog import command for the approved CSV/JSON mappings, including the corrected party-logo and candidate-photo fields.
- Add a Laravel vote API with server-side validation, geographic evidence, encrypted and hashed IP signals, device/fingerprint signals, and atomic duplicate prevention.
- Add read APIs/services for active district, province, and region rounds, including explicit blocked states when candidate data is unavailable.
- Seed the approved Lima provincial and Callao regional rounds through 5 August 2026 without fabricating candidates or media.
- Add backend feature, integration, and migration tests before the public frontend is switched to these contracts.
- **BREAKING**: the legacy `/api/votar.php`, root SQL migrations, and file-backed public data access cease to be production entrypoints after parity and data verification.

## Capabilities

### New Capabilities

- `electoral-catalog`: Normalized territorial scopes, parties, candidates, candidacies, and media references with stable opaque identifiers.
- `survey-round-management`: District, province, and region survey rounds, candidate options, publication windows, active-round lookup, and blocked states.
- `secure-vote-registration`: Validated vote registration with privacy-preserving network signals, geographic evidence, atomic duplicate prevention, and a stable JSON contract.
- `electoral-catalog-import`: Idempotent import and reconciliation of the approved CSV/JSON candidate datasets into the normalized database.

### Modified Capabilities

- `national-home-portal`: Active survey cards must be supplied by the Laravel survey-round application contract instead of legacy files or root helpers.

## Impact

- Adds Laravel models, migrations, repositories, application services, form requests, API resources, commands, seeders, and tests at the repository root.
- Migrates the production MySQL schema without `AUTO_INCREMENT` and preserves existing BL-13/BL-14 data.
- Changes the frontend data source and vote endpoint after backend parity is proven.
- Retires duplicate root `db/`, `includes/`, `api/`, and import logic only after production verification.
- Requires Hostinger environment configuration for database credentials, encryption keys, trusted proxy handling, and Laravel storage permissions.
