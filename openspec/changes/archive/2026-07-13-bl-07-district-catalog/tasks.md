## 1. Failing check first (TDD, CLAUDE.md constraint 7)

- [x] 1.1 Write `scripts/validate-distritos.js` implementing all 5 rules from `design.md` (exact-43 count, required fields, slug format, uniqueness), reading `data/distrito.json`
- [x] 1.2 Create a stub `data/distrito.json` with 1-2 records (deliberately incomplete)
- [x] 1.3 Run `node scripts/validate-distritos.js` and confirm it fails (non-zero exit, reports wrong count) — red

## 2. District data

- [x] 2.1 Replace the stub with the full 43-record `data/distrito.json` (`id`/`nombre`/`provincia`/`region`, `provincia`/`region` both `"lima"`), slugs per the accent-stripping algorithm in `design.md`
- [x] 2.2 Run `node scripts/validate-distritos.js` and confirm it passes (exit 0, reports 43 valid records) — green

## 3. Verification

- [x] 3.1 Manually diff the 43 district names against the known Lima Metropolitana administrative division to catch a wrong-but-plausible name (not caught by the count/format checks)
- [x] 3.2 Update `docs/backlog.md` BL-07 status to `done` with today's date and a one-line note
