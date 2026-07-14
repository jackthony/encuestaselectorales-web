## 1. Failing check first (TDD, CLAUDE.md constraint 7)

- [x] 1.1 Write `scripts/candidatos-por-distrito.js`: pure function `candidatosPorDistrito(distritoId, candidatos, partidos)` per `design.md`'s contract, with the Node/browser UMD-lite export guard
- [x] 1.2 Write `scripts/test-candidatos-por-distrito.js`: plain-Node assertions covering the 3 scenarios in `specs/district-candidate-directory/spec.md` (Miraflores returns 8 enriched, empty district returns [], unresolvable partidoId returns `partido: null`)
- [x] 1.3 Run `node scripts/test-candidatos-por-distrito.js` against a stub/empty implementation and confirm it fails — red

## 2. Implement the lookup + rendering

- [x] 2.1 Implement `candidatosPorDistrito` body (filter by `distritoId`, join `partidoId` → party, sort by `numero` then `nombre`, `partido: null` on unresolved id)
- [x] 2.2 Run `node scripts/test-candidatos-por-distrito.js` and confirm it passes — green
- [x] 2.3 Add candidate-card CSS to `styles.css` (party color swatch, placeholder avatar, "N.º no disponible" fallback styling)
- [x] 2.4 Update `distrito.html`: load `scripts/candidatos-por-distrito.js`, call it after `data/candidato.json`/`data/partido.json` fetch, render the directory when non-empty, otherwise keep today's placeholder message

## 3. Verification

- [x] 3.1 Browser-check `distrito.html?id=miraflores` (all 8 candidates, party name + color swatch, "N.º no disponible", placeholder avatar) and `distrito.html?id=barranco` (unchanged placeholder message) on a local server, confirm no console errors
- [x] 3.2 Re-run `node scripts/validate-nav.js`, `validate-distritos.js`, `validate-encuestadoras.js`, `validate-partidos.js`, `validate-candidatos.js` — confirm no regressions
- [x] 3.3 Update `docs/backlog.md` BL-11 status to `done` with today's date and a one-line note
