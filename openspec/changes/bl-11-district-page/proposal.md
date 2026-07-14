## Why

`data/candidato.json` and `data/partido.json` exist (`BL-09`, Miraflores 2022 historical set) but nothing renders them — `distrito.html` (`BL-10`) only ever shows a bare "página en construcción" message, even for the pilot district that already has real candidate data loaded. `docs/backlog.md` BL-11's "done when" requires the pilot district page to actually show its candidates.

## What Changes

- Extract the candidate-lookup logic (which candidates belong to a district, joined with their party) into a small reusable module (`scripts/candidatos-por-distrito.js`) that works in both Node (for testing) and the browser (loaded by `distrito.html`) — no build step, no framework.
- `distrito.html` renders a candidate directory when the current district has entries in `data/candidato.json`: each card shows the candidate's name, party name + color swatch, list number (or "no disponible" — every 2022 historical record has `numero: null`), and a generic placeholder avatar (not a real JNE photo).
- Districts with no candidates keep today's plain "página en construcción" message, unchanged.
- **Explicit exclusions this session (user decision, 2026-07-14)**: no real JNE photos (`BL-05` hasn't verified JNE photo-reuse terms — constraint 8, "when in doubt, publish less"), no real list numbers (2022 historical data, not sourced this item).

## Capabilities

### New Capabilities
- `district-candidate-directory`: the candidate-lookup module and the district page's conditional candidate-directory rendering.

### Modified Capabilities
- `landing-nav-shell`: `distrito.html`'s requirement "district link renders a placeholder" gains a scenario for districts that DO have candidate data (renders the directory, not the placeholder message).

## Impact

- New file: `scripts/candidatos-por-distrito.js` (shared lookup module).
- New file: `scripts/test-candidatos-por-distrito.js` (plain-Node assertions, same pattern as this repo's `validate-*.js` scripts).
- Modified: `distrito.html` (loads the module, renders the directory conditionally), `styles.css` (candidate card styling).
- No change to `data/candidato.json` / `data/partido.json` shape — consumes what `BL-09` already published.
