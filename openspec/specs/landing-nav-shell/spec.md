## Purpose

Site-wide navigation shell — the shared header/nav, its "Distritos de Lima" dropdown, and the validation rule keeping it in sync with `data/distrito.json` — plus the real landing page that replaces the pre-launch placeholder (`BL-10`).

## Requirements

### Requirement: Site-wide navigation with district dropdown
Every published page SHALL include a shared `<header>`/`<nav>` containing a "Distritos de Lima" dropdown that lists all 43 districts from `data/distrito.json`, each linking to `distrito.html?id=<slug>`.

#### Scenario: Nav present on every published page
- **WHEN** `scripts/validate-nav.js` runs against `index.html`, `metodologia.html`, `quienes-somos.html`, `politica-editorial.html`, `politica-privacidad.html`, `fuentes-correcciones.html`
- **THEN** it exits 0 and confirms all 6 pages contain the nav marker

#### Scenario: A page missing the nav fails validation
- **WHEN** any of the 6 published pages doesn't contain the nav marker
- **THEN** `scripts/validate-nav.js` exits non-zero and names the offending page

### Requirement: Nav district links match the data catalog exactly
Each page's nav SHALL link to exactly the 43 district ids present in `data/distrito.json` — no missing, no duplicate, no unknown id.

#### Scenario: Full match validates
- **WHEN** a page's nav contains all 43 `data/distrito.json` ids exactly once each, and no others
- **THEN** `scripts/validate-nav.js` exits 0 for that page

#### Scenario: Missing district link fails validation
- **WHEN** a page's nav is missing a link for a district id present in `data/distrito.json`
- **THEN** `scripts/validate-nav.js` exits non-zero and names the missing id and page

#### Scenario: Unknown or duplicate district link fails validation
- **WHEN** a page's nav contains a link to an id not present in `data/distrito.json`, or contains the same id twice
- **THEN** `scripts/validate-nav.js` exits non-zero and names the offending id and page

### Requirement: Every district link resolves
Every district link in the nav SHALL resolve to a page (`distrito.html?id=<slug>`) that renders without erroring, even before real district content (`BL-11`) exists.

#### Scenario: District link renders a placeholder
- **WHEN** a visitor opens `distrito.html?id=san-isidro` (or any valid district id)
- **THEN** the page loads and displays that district's name from `data/distrito.json`, with a "página en construcción" placeholder rather than an error or blank page
