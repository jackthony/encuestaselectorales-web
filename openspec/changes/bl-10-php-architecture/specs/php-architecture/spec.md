# php-architecture

## ADDED Requirements

### Requirement: Single source for the design system
The Tailwind configuration SHALL exist exactly once, in `partials/head.php`. No page or partial may declare its own `tailwind.config` block.

#### Scenario: Only one Tailwind config in the tree
- **WHEN** the repository is searched for `tailwind.config`
- **THEN** exactly one match is returned, and it is in `partials/head.php`

#### Scenario: Palette divergences are reconciled
- **WHEN** the reconciled config is read
- **THEN** `brand.blue` is `#102f86`, `brand.green` is `#15ba75` (lowercase), `brand.bg` is `#f4f5f3`, `brand.card` is `#ffffff`
- **AND** neither `#fcfcfc` nor `#f8fafc` appears as a page background anywhere

### Requirement: Refactor preserves the validated design
Each PHP page SHALL emit the same multiset of element tags and CSS classes as the `canvas-gemini/` prototype it replaces, modulo the palette reconciliation above.

#### Scenario: Automated regression check passes for all pages
- **WHEN** `php scripts/check-refactor.php` runs
- **THEN** it exits 0 and reports 8 of 8 pages matching

#### Scenario: A dropped class fails the check
- **WHEN** any CSS class present in a prototype is absent from its PHP render
- **THEN** the check exits non-zero and names the page and the missing class

### Requirement: Repeated UI lives in partials
Header, footer, document head and the GPS vote widget SHALL each exist in exactly one file under `partials/`.

#### Scenario: No duplicated header markup
- **WHEN** the pages are searched for the nav landmark that `partials/header.php` renders
- **THEN** no page contains that markup inline; each includes the partial

#### Scenario: Partials load safely on pages lacking their targets
- **WHEN** any page is loaded that does not contain a given partial's DOM target
- **THEN** the browser console reports zero errors

### Requirement: Party colors come from data, not literals
Party colors SHALL be resolved through `includes/helpers.php` reading `data/partido.json`. Hex literals for party identity are prohibited in views, partials and JS.

#### Scenario: No party hex literals remain in views
- **WHEN** the views, partials and `assets/js/` are searched for `#B22222`, `#00A99D` or `#F58220`
- **THEN** no matches are returned

#### Scenario: An unmapped party is reported, never invented
- **WHEN** a prototype references a party absent from `data/partido.json`
- **THEN** the task log records the gap and no substitute color is chosen

### Requirement: Naming convention
Public page files SHALL be flat `kebab-case.php` at the repository root, with Spanish slugs, and no accented or substituted characters.

#### Scenario: Encoding damage is repaired
- **WHEN** the page files are listed
- **THEN** `metodologia.php` and `quienes-somos.php` exist
- **AND** no filename contains a `_` standing in for a lost accented character

### Requirement: Legal scrub of fabricated attribution
No page SHALL attribute invented poll figures to a real pollster, and no content SHALL fall outside the Lima Metropolitana district scope lock. Authorized by owner, 2026-07-18.

#### Scenario: No real pollster is named on fabricated data
- **WHEN** the views are searched for `Ipsos`, `Datum`, `CPI` or `IEP` in any heading, title or attribution attached to a demo figure
- **THEN** no matches are returned
- **AND** the attribution reads the `ejemplo` entry from `data/encuestadora.json` — "Encuestadora de ejemplo (dato ficticio, no es una institución real)"

#### Scenario: Real pollsters remain listed where the listing is factual
- **WHEN** `encuestadoras.php` renders the pollster directory
- **THEN** real institutional pollsters MAY be named, because listing that a firm exists and is JNE-registered is a fact, not a fabricated figure
- **AND** no poll result is attributed to any of them

#### Scenario: Out-of-scope geography is removed
- **WHEN** the views are searched for any regional government (GORE) or non-Lima-Metropolitana territory
- **THEN** no matches are returned

### Requirement: Structural refactor only
Apart from the palette reconciliation and the legal scrub above, this change SHALL NOT alter copy, add features, connect to a database, or create any `/api/` endpoint.

#### Scenario: No backend surface is introduced
- **WHEN** the tree is inspected after the change
- **THEN** no `/api/` directory exists, no `PDO` instantiation appears, and `config/` holds no credentials

#### Scenario: Content is otherwise relocated, not rewritten
- **WHEN** the visible text of each PHP page is compared to its prototype, excluding the pollster attributions and GORE entries above
- **THEN** it is identical
