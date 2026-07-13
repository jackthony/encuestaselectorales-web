## 1. Failing check first (TDD, CLAUDE.md constraint 7)

- [x] 1.1 Write `scripts/validate-encuestadoras.js` implementing all 6 rules from `design.md` (exact-5 count, required fields, slug format, uniqueness, `tipo` enum), reading `data/encuestadora.json`
- [x] 1.2 Create a stub `data/encuestadora.json` with 1-2 records (deliberately incomplete)
- [x] 1.3 Run `node scripts/validate-encuestadoras.js` and confirm it fails (non-zero exit, reports wrong count) — red

## 2. Pollster data

- [x] 2.1 Replace the stub with the full 5-record `data/encuestadora.json` (`iep`, `ipsos`, `datum`, `cpi`, `propia` — `id`/`nombre`/`tipo`/`web` per `design.md`)
- [x] 2.2 Run `node scripts/validate-encuestadoras.js` and confirm it passes (exit 0, reports 5 valid records) — green

## 3. Verification

- [x] 3.1 Manually confirm the 4 institutional pollster names/URLs match `docs/design-references.md` and `docs/data-model.md`'s existing example row
- [x] 3.2 Update `docs/backlog.md` BL-08 status to `done` with today's date and a one-line note

## 4. PR

- [x] 4.1 Open PR from `feat/bl-08-pollster-catalog` into `main`, description states the red check (1.3) and what made it green (2.2)
