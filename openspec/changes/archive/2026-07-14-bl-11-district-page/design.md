## Context

`distrito.html` (`BL-10`) already resolves `?id=` to a district name via a client-side `fetch('/data/distrito.json')`. `data/candidato.json` (`BL-09`) has 8 Miraflores 2022 records with `distritoId: "miraflores"`, `partidoId` pointing into `data/partido.json`, and `foto`/`numero` both `null` (real values deliberately deferred — see proposal). This item makes those records visible on the district page for the first time.

No build step exists, so the candidate-matching logic needs a form that's both testable from plain Node (per `CLAUDE.md` constraint 7's failing-test-first) and loadable directly by the browser via a `<script src>` tag — the same tension `BL-10` resolved for `scripts/validate-nav.js` (logic lives in a script, but that script only ever runs in Node). Here the logic must also run in the browser, so it can't be Node-only.

## Goals / Non-Goals

**Goals:**
- A pure, side-effect-free function that, given a district id and the loaded `candidato.json`/`partido.json`, returns the enriched candidate list for that district (empty array if none) — testable in Node without a browser.
- The exact same function runs unmodified in the browser (no duplicate implementation to drift).
- `distrito.html` renders a candidate directory when that list is non-empty, and keeps today's placeholder message when it's empty.
- List number and photo render safe fallbacks (`numero: null` → "no disponible"; `foto: null` → generic placeholder avatar), not broken UI.

**Non-Goals:**
- Real JNE photos, real list numbers — explicit user decision this session, deferred.
- Judicial record badges (`BL-24`).
- Any district other than Miraflores having real candidate data — `BL-28` replicates by data later; this item only needs the lookup to be correct for whichever districts DO have entries (today, only Miraflores).

## Decisions

- **Shared module via a UMD-lite pattern, not a build step.** `scripts/candidatos-por-distrito.js` defines `function candidatosPorDistrito(distritoId, candidatos, partidos) { ... }` and ends with:
  ```js
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = candidatosPorDistrito;
  }
  ```
  In Node (`require(...)`), `module.exports` is set and the test script uses it. In the browser (`<script src="/scripts/candidatos-por-distrito.js">`), `module` is undefined, the guard is skipped, and the function is simply a global — `distrito.html`'s inline script calls it directly. Chosen over duplicating the join logic once in the test and once inline in `distrito.html` (the exact drift risk `BL-10`'s design explicitly called out and wanted to avoid) and over adding a bundler/module system (rejected — no-build stance, `CLAUDE.md` principle 5).
- **Function contract**: `candidatosPorDistrito(distritoId, candidatos, partidos)` returns an array of `{ id, nombre, numero, foto, partido: { nombre, siglas, color } | null }`, sorted by `numero` ascending then `nombre` (all `numero` are `null` today, so this degrades to alphabetical — documented so it's not surprising once real numbers land). A `partidoId` that doesn't resolve to a party yields `partido: null` rather than throwing — the caller decides how to render that (defensive, since `BL-09`'s validator already guarantees this won't happen in committed data, but the render function shouldn't assume it forever).
- **Fallback rendering**: `numero == null` → the literal text "N.º no disponible"; `foto == null` → a CSS-only placeholder avatar (initials or a generic icon, no external image request — consistent with the privacy-by-default stance of no third-party asset calls).
- **`landing-nav-shell` gets a MODIFIED requirement**, not a brand-new page — `distrito.html` is the same file/URL scheme from `BL-10`, just with a second render branch. Splitting into a new "page" capability would misrepresent it as a different surface.

## Risks / Trade-offs

- [Risk] The UMD-lite guard (`typeof module !== 'undefined'`) is an easy-to-miss detail if someone edits the module later and forgets it → Mitigation: `scripts/test-candidatos-por-distrito.js` running successfully in Node is itself a smoke test that the guard still works; a browser console error would surface a break in `distrito.html` immediately (manual browser check, same as `BL-10`'s task 4.2 pattern).
- [Risk] Sorting by `numero` (currently always `null`) means today's order is just alphabetical by `nombre` — could look arbitrary to a visitor expecting ballot order → Mitigation: acceptable for the historical 2022 placeholder dataset; real 2026 data will have real `numero` values and the same sort will then produce true ballot order with no code change.
- [Risk] Generic placeholder avatar reads as visually unfinished → Mitigation: intentional and disclosed (proposal, user decision) — resolves once `BL-05`'s JNE photo-terms verification unblocks real photos.

## Open Questions

None — scope fixed by this session's two explicit user decisions (no real photos, no real list numbers) and `docs/backlog.md` BL-11's "done when."
