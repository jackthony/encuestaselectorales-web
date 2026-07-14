## Context

No page today has a `<header>`/`<nav>` — every published page (`index.html`, `metodologia.html`, `quienes-somos.html`, `politica-editorial.html`, `politica-privacidad.html`, `fuentes-correcciones.html`) only has a footer with a handful of links. `index.html` itself is still the pre-launch "coming soon" placeholder, unrelated to the `styles.css` design system (`BL-04`). This item makes the site navigable for the first time and gives every one of the 43 Lima districts (`data/distrito.json`, `BL-07`) a reachable URL, per `docs/backlog.md` BL-10's "done when."

No build step exists (`CLAUDE.md` principle 5), so the header/nav markup is hand-duplicated into each HTML file rather than templated/included — the same static-site tradeoff already made for the footer on earlier items. The risk that duplication invites (one file's nav silently drifting from `data/distrito.json` or from the other files) is exactly what `scripts/validate-nav.js` exists to catch.

## Goals / Non-Goals

**Goals:**
- Every page gets the same header/nav, built from `styles.css` tokens.
- The nav's "Distritos de Lima" dropdown lists all 43 districts and every link resolves (no 404).
- A validation script that fails if any page's nav drifts from `data/distrito.json` (missing/extra/duplicate district link) or if a page is missing the nav entirely.
- Replace `index.html` with a real landing page.

**Non-Goals:**
- Presidencial / Regiones / Callao nav items — out of scope until Lima districts are proven (`docs/backlog.md` BL-10 Out, constraint 4).
- Real district content (candidates, polls) — `BL-11` onward. District URLs need to resolve, not render real data.
- A JS framework/router or build tooling — stays plain static HTML/CSS/(minimal) JS.

## Decisions

- **District URL shape: one templated `distrito.html` + `?id=<slug>` query param, not 43 static files.** A single page reads `data/distrito.json` client-side (`fetch`), finds the record matching `?id=`, and renders its name in a "página en construcción" placeholder (explicitly noting `BL-11` will fill in real content). Chosen over generating 43 physical `/distritos/<id>.html` files because: (a) no build step exists to generate them, so 43 files would mean 43 hand-copies of a placeholder — pure duplication risk with zero content difference today; (b) `BL-11`'s real per-district page will need to read district+candidate data dynamically anyway, so this establishes the pattern early. Revisit if SEO (`BL-17`, static per-URL `<title>`/meta) later requires one physical file per district — not needed yet since these are placeholders with no unique content to index.
- **Nav markup: native `<details>`/`<summary>` disclosure, no JS required for basic function.** `<details class="nav-dropdown"><summary>Distritos de Lima</summary><ul>...43 <a> links...</ul></details>`. Chosen over a JS-driven dropdown or a `<select onchange>` — `<details>` is keyboard/screen-reader accessible by default (`BL-20` accessibility baseline gets this for free), degrades to a plain expandable list with zero script, and needs no click-outside/focus-trap logic to hand-roll.
- **Nav duplicated by hand across all 6 published pages, not templated.** Consistent with the existing footer pattern (`BL-06`) and the no-build constraint. `scripts/validate-nav.js` is the safety net for the duplication risk this creates.
- **`scripts/validate-nav.js` rules**:
  1. For each of the 6 published HTML files, extract `distrito.html?id=<slug>` links from its nav.
  2. Each page's nav must contain exactly 43 such links, one per `data/distrito.json` id, no duplicates, no ids absent from `data/distrito.json`, no extra/unknown ids.
  3. Every one of the 6 files must contain a nav (detect via a fixed marker, e.g. `id="distritos-nav"` on the `<ul>`) — catches a page where the nav insertion was missed entirely.
  Plain Node, regex-based extraction (no HTML parser dependency, consistent with `BL-07`/`BL-08`/`BL-09`'s no-dependency validation scripts) — acceptable because the nav markup shape is fixed/known, not arbitrary HTML.

## Risks / Trade-offs

- [Risk] Regex-based HTML extraction in `validate-nav.js` is more brittle than a real HTML parser if the nav markup shape changes → Mitigation: nav markup is fixed by this change and only touched by future items intentionally; the script fails loudly (wrong count) rather than silently passing if the shape breaks the regex, so drift gets caught, not masked.
- [Risk] Hand-duplicating the nav into 6 files (and every future page) is real ongoing toil → Mitigation: accepted tradeoff of the no-build/no-framework decision (`CLAUDE.md` principle 5); `validate-nav.js` is the compensating control, same shape as the footer's existing (undetected) duplication risk today.
- [Risk] `distrito.html?id=` placeholder pages have no unique per-district `<title>`/meta yet, which slightly under-serves `BL-17` (SEO) if it lands before `BL-11` gives per-district pages real content → Mitigation: acceptable — `BL-17` is explicitly a later Phase 5 item, and the query-param page can still set `document.title` client-side once JS runs.

## Open Questions

None — scope is fixed by `docs/backlog.md` BL-10's definition and BL-07's already-published `data/distrito.json`.
