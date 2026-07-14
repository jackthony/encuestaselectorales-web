## Why

The site still ships its pre-launch "coming soon" `index.html` — an old placeholder unrelated to the visual identity system (`BL-04`) and with no navigation at all. Every downstream district page (`BL-11` onward) needs a real entry point and a way for a visitor to reach any of the 43 Lima districts. Nothing else in Phase 3/4 can be reached without a working nav.

## What Changes

- Replace `index.html`'s placeholder content with a real landing page using the `styles.css` design system (shared with `metodologia.html`/`quienes-somos.html`/etc.).
- Add a shared `<header>`/`<nav>` (not present on any page today) with a "Distritos de Lima" dropdown listing all 43 districts from `data/distrito.json`, each linking to `/distritos/<id>.html`.
- Apply the same header/nav to the existing published pages (`index.html`, `metodologia.html`, `quienes-somos.html`, `politica-editorial.html`, `politica-privacidad.html`, `fuentes-correcciones.html`) so navigation is consistent site-wide.
- Add `scripts/validate-nav.js`: fails if the nav's district links don't match `data/distrito.json` exactly (missing id, extra id, or a link pointing to a non-existent id).
- District link targets (`/distritos/<id>.html`) do not need to render real content yet (`BL-11` fills them in) — a placeholder page is acceptable, but the link itself must resolve (not 404).

## Capabilities

### New Capabilities
- `landing-nav-shell`: the shared header/nav component, its "Distritos de Lima" dropdown, and the validation rule keeping it in sync with `data/distrito.json`.

### Modified Capabilities
(none — no existing capability's requirements change; other pages just gain the shared nav, not a requirement change to their own specs)

## Impact

- Modified files: `index.html` (full rewrite), `metodologia.html`, `quienes-somos.html`, `politica-editorial.html`, `politica-privacidad.html`, `fuentes-correcciones.html` (header/nav inserted).
- New files: `scripts/validate-nav.js`, 43 placeholder district pages under `/distritos/` (or a single templated pattern — decided in design.md).
- `docs/data-model.md` entity #1 (District) is the source; no schema change.
