## 1. Failing checks first (TDD, CLAUDE.md constraint 7)

- [x] 1.1 Write `scripts/validate-partidos.js` implementing the rules from `design.md` decision 6 (count=8, required fields, hex color format, unique id/siglas)
- [x] 1.2 Write `scripts/validate-candidatos.js` implementing the rules from `design.md` decision 6 (count=8, required fields with nullable foto/numero, cargo enum, distritoId/partidoId cross-refs, unique id)
- [x] 1.3 Create stub `data/partido.json` (1-2 records) and stub `data/candidato.json` (1-2 records, deliberately incomplete)
- [x] 1.4 Run both scripts and confirm they fail (non-zero exit, report wrong count / broken cross-ref) — red

## 2. Real data (green)

- [x] 2.1 Replace the stub with the full 8-record `data/partido.json` (the 2022 Miraflores mayoral race parties, per `design.md` decision 1/4)
- [x] 2.2 Replace the stub with the full 8-record `data/candidato.json` (the 2022 Miraflores mayoral race candidates, `activo: false`, `foto`/`numero` null, per `design.md` decisions 2/3/5)
- [x] 2.3 Run both scripts and confirm they pass (exit 0) — green

## 3. Verification

- [x] 3.1 Cross-check the 8 candidate names/parties against the cited sources (El Comercio, La República, Andina) for accuracy
- [x] 3.2 Confirm every `candidato.distritoId` is `"miraflores"` and every `candidato.partidoId` resolves in `partido.json`
- [x] 3.3 Update `docs/backlog.md` BL-09 status to `done` with today's date and a one-line note

## 4. PR

- [x] 4.1 Open PR from `feat/bl-09-pilot-candidates` into `main`, description cites the real-world sources and states the red check and what made it green
