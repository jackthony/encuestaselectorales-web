## 1. Failing check first (TDD — `CLAUDE.md` constraint 7)

- [x] 1.1 Create `scripts/validate-data.js` (plain Node, no dependencies) implementing the district checks from `design.md` decision 4: file exists/parses, exactly 43 records, required fields non-empty, `id` unique, `id` matches slug regex
- [x] 1.2 Run `node scripts/validate-data.js` against a missing/empty `data/distrito.json` and confirm it fails (red) — record the failure output in the PR description

## 2. District data (green)

- [x] 2.1 Create `data/distrito.json` with all 43 Lima Metropolitana districts (`id`/`nombre`/`provincia`/`region` per `docs/data-model.md` #1 and the slug rule in `design.md` decision 2)
- [x] 2.2 Run `node scripts/validate-data.js` again and confirm it passes (green)

## 3. Docs

- [x] 3.1 Mark `BL-07` `done` in `docs/backlog.md` with a dated status line (convention from `BL-02`-`BL-06`)

## 4. CodeQL follow-up (per `BL-01` deferred note, non-blocking)

- [x] 4.1 Re-attempt CodeQL default setup now that a `.js` file exists (`docs/devsecops.md`); if it still fails, document why and leave deferred — doesn't block this item's merge

## 5. PR

- [x] 5.1 Open PR from `feat/bl-07-district-catalog` into `main`, description states the red check (1.2) and what made it green (2.2)
