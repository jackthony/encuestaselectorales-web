# national-home-portal

## ADDED Requirements

### Requirement: Home portal is scoped nationally, not to Lima
`index.php` SHALL present national copy ("Perú 2026" / "Elecciones Regionales y Municipales 2026") and a global location search, not Lima-scoped branding.

#### Scenario: No Lima-only branding remains
- **WHEN** the rebuilt `index.php` is inspected
- **THEN** it contains no "Lima 2026" text in the hero, header tagline, or nav

### Requirement: Global search resolves any district in the data catalog
The header and hero search inputs SHALL filter `data/distrito.json` by name and link each result to `distrito.php?slug=<id>`, working for every entry in the catalog regardless of its size.

#### Scenario: A search term matches and links correctly
- **WHEN** a user types a district's name (or a matching substring) into either search input
- **THEN** the matching district appears as a result linking to `distrito.php?slug=<that district's id>`

#### Scenario: A search term with no match shows a message, not silence
- **WHEN** a search term matches no entry in `data/distrito.json`
- **THEN** the page shows an explicit "no encontramos esa ubicación" message rather than an empty dropdown with no feedback

### Requirement: Hub columns render only from real data, each with its own empty state
The "Encuestas Web Activas" and "Últimos Estudios de Campo" columns SHALL each render cards only from real, non-placeholder JSON records, and each SHALL show its own distinct empty state when it has none — never a shared generic message, and never a fabricated example card.

#### Scenario: Zero open online rounds shows an invitation, not a blank column
- **WHEN** no district has an open `online_propia` round
- **THEN** "Encuestas Web Activas" shows the search-invitation empty state, not an empty div or an example card

#### Scenario: Zero real campo studies shows absence-of-evidence, not an invitation
- **WHEN** no real campo study exists in `data/resultado.json`
- **THEN** "Últimos Estudios de Campo" shows a one-line "aún no hay estudios publicados" note, not a CTA and not an example card

#### Scenario: The ejemplo placeholder never renders in either column
- **WHEN** either hub column is rendered
- **THEN** no card is produced from a record whose `encuestadoraId` (or equivalent) is the `ejemplo` placeholder
