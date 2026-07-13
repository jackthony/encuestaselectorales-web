## Why

The whole Phase 2-4 chain (candidates, polls, results, district pages) needs a stable, complete list of the 43 Lima Metropolitana districts to key off of. Nothing downstream can render without it, and every later item (BL-09, BL-10, BL-11...) references `distritoId` values that must already be correct and stable.

## What Changes

- Add `data/distrito.json`: 43 records, one per Lima Metropolitana district, shape `{ id, nombre, provincia, region }` per `docs/data-model.md`.
- `id` is a URL-usable kebab-case slug derived from the district name (e.g. `san-isidro`), stable once published — later items link to it.
- Add `scripts/validate-distritos.js`: fails if the file doesn't have exactly 43 records, any record is missing a required field, any `id` is duplicated, or any `id` isn't a valid URL slug (lowercase, hyphen-separated, no accents/spaces).
- Add a failing-test-first checkpoint per `CLAUDE.md` constraint 7: the validation script is written and run against an empty/incomplete `distrito.json` first (red), then the data is filled in until it passes (green).

## Capabilities

### New Capabilities
- `district-catalog`: the static 43-district data file and its validation rule (record count, required fields, unique URL-safe slugs).

### Modified Capabilities
(none)

## Impact

- New files: `data/distrito.json`, `scripts/validate-distritos.js`.
- No existing pages consume this yet (BL-10 nav and BL-11 district page are later items) — this is pure data foundation, `docs/data-model.md` entity #1.
