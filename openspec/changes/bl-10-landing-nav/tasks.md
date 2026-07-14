## 1. Failing check first (TDD, CLAUDE.md constraint 7)

- [x] 1.1 Write `scripts/validate-nav.js` implementing all 3 rules from `design.md` (nav present on all 6 pages, exact 43-id match per page, no missing/duplicate/unknown ids), reading `data/distrito.json` and the 6 published HTML files
- [x] 1.2 Run `node scripts/validate-nav.js` against the current (nav-less) pages and confirm it fails (missing nav marker on all 6) — red

## 2. Nav markup + CSS

- [x] 2.1 Add header/nav CSS to `styles.css` (`.nav-dropdown` `<details>`/`<summary>` styling, scrollable district list, per `design.md`)
- [x] 2.2 Build the shared header/nav HTML block (site name/logo text + "Distritos de Lima" `<details>` dropdown with all 43 links from `data/distrito.json`, `id="distritos-nav"` marker on the `<ul>`)
- [x] 2.3 Insert the header/nav block into `metodologia.html`, `quienes-somos.html`, `politica-editorial.html`, `politica-privacidad.html`, `fuentes-correcciones.html`

## 3. Landing page + district placeholder page

- [x] 3.1 Replace `index.html`'s placeholder content with a real landing page using `styles.css` tokens, including the shared header/nav
- [x] 3.2 Create `distrito.html`: reads `data/distrito.json` client-side, resolves `?id=` query param, renders the district's name with a "página en construcción" placeholder note

## 4. Green + verification

- [x] 4.1 Run `node scripts/validate-nav.js` and confirm it passes (exit 0) for all 6 pages — green
- [x] 4.2 Manually open `index.html` and `distrito.html?id=san-isidro` (and one more district) in a browser to confirm the nav renders, is keyboard-operable, and the district page shows the right name
- [x] 4.3 Update `docs/backlog.md` BL-10 status to `done` with today's date and a one-line note
