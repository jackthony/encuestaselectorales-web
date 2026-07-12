## Why

`BL-02` shipped `politica-editorial.html`/`styles.css` with a generic dark-blue placeholder theme inherited from the pre-launch `index.html` — never a deliberate choice, nobody signed off on it. User feedback (2026-07-12): it "falta identidad/carácter" — reads as generic placeholder, not the FiveThirtyEight/Pew Research-level editorial authority `docs/business-model.md` targets. Fixing this now, before more content pages ship (`BL-05` legal policy, `BL-06` authority shell), means every future page inherits the real system from birth instead of accumulating retrofit debt page by page.

## What Changes

- Replace the placeholder tokens in `styles.css` with a validated light-theme system: surfaces, ink, and a single accent color sourced from the harness's `dataviz` skill reference palette (`references/palette.md`) — chosen so the site's UI accent and the future chart categorical palette's first slot are the same blue, not two competing hues.
- Add a headline/body typography pairing with editorial character: serif system-font stack for headings (`ui-serif`, Georgia fallback), sans for body — zero webfonts, zero external requests (consistent with the no-tracking stance in `BL-03`).
- Retrofit `politica-editorial.html` (automatic via shared `styles.css`) and re-verify its accessibility/contrast under the new tokens.
- Document the concrete hex values in `docs/engineering-standards.md` §6 (currently says "neutral palette" with no actual values — this closes that gap).

## Capabilities

### New Capabilities
- `visual-identity`: the site's design-token system (color + typography) as a defined, checkable set of CSS custom properties, distinct from any individual page's content.

### Modified Capabilities
(none — `editorial-independence-policy` from `BL-02` isn't changing its requirements, only its rendering via shared tokens)

## Impact

- `styles.css` (shared, already exists from `BL-02`) — token values change, structure doesn't.
- `politica-editorial.html` — no content change, visual re-skin only (inherits from `styles.css`).
- `docs/engineering-standards.md` §6 — gains concrete hex values, replacing vague prose.
- Sets precedent for every subsequent page (`BL-05` onward) — no retrofit needed for pages that don't exist yet.
- Chart-specific palettes (categorical/sequential/diverging) are explicitly NOT decided here — deferred to `BL-13` when the `dataviz` skill's validator can be run against an actual chart, per the skill's own "color comes last" rule.
