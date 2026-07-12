## Why

Phase 0 (governance, `BL-01`-`BL-05`) and Phase 1 (`BL-06` authority shell) are done. Nothing further can render — nav, district pages, poll results — without a canonical list of the 43 Lima Metropolitana districts. This is the first Phase 2 item and the first data file in the repo: it exists independently of JNE's Aug 5/Sept 5 calendar (district boundaries are fixed geography, not candidate data), so it's safe to build now per `CLAUDE.md` constraint 1.

## What Changes

- Add `data/distrito.json`: all 43 Lima Metropolitana districts, each with `id` (URL-usable slug), `nombre`, `provincia`, `region`, matching the shape already documented in `docs/data-model.md` #1.
- Add `scripts/validate-data.js`: the repo's first data-validation script (plain Node, no dependencies). For this item it checks `distrito.json` specifically — exactly 43 records, all required fields present, `id` values unique and slug-shaped, matching `docs/engineering-standards.md` §5's "logic item" rule (write the failing check first, then the data that makes it pass).
- This is the first `.js` file in the repo — re-attempt CodeQL default setup per the `BL-01` deferred note (`docs/devsecops.md`), documented as a follow-up, not blocking this item.

## Capabilities

### New Capabilities
- `district-catalog`: the canonical, validated list of the 43 Lima Metropolitana districts that every later district-scoped page/data file (`BL-09` candidates, `BL-10` nav, `BL-11` district pages, `BL-12` polls) keys off of.

### Modified Capabilities
(none)

## Impact

- New `/data/` folder (first data file in the repo) and new `/scripts/` folder (first script in the repo), both per the layout already reserved in `docs/engineering-standards.md` §0.
- No page renders this data yet — that's `BL-10` (nav) and `BL-11` (district page). This item only produces and validates the source-of-truth JSON.
- Logic-bearing item per `CLAUDE.md` constraint 7: `tasks.md` sequences the failing `validate-data.js` check before the data file exists.
- No backend, no build-tooling impact — `node scripts/validate-data.js` runs with zero dependencies.
