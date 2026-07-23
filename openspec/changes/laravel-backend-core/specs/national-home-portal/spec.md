## MODIFIED Requirements

### Requirement: Home portal is scoped nationally, not to Lima
The Laravel national home route SHALL present national copy ("Perú 2026" / "Elecciones Regionales y Municipales 2026") and a global electoral-location search, not Lima-scoped branding.

#### Scenario: No Lima-only branding remains
- **WHEN** the Laravel national home page is rendered
- **THEN** it contains no "Lima 2026" text in the hero, header tagline, or navigation

### Requirement: Hero search resolves any district in the data catalog
The hero search SHALL query the Laravel electoral-catalog application contract and SHALL resolve every published region, province, and district without reading `data/distrito.json` or a root helper directly. Each result SHALL link to its canonical Laravel territory route and SHALL display the territory name together with its scope type and parent context so homonymous locations remain distinguishable.

The shared header search control is outside this requirement unless it is wired to the same application contract during the frontend cutover.

#### Scenario: A search term matches and links correctly
- **WHEN** a user types a territory name or matching substring into the hero search
- **THEN** every matching published territory appears with its scope type and parent context and links to its canonical Laravel territory route

#### Scenario: Homonymous locations match
- **WHEN** a search term matches territories with the same name at different scope levels
- **THEN** the results distinguish each region, province, or district and route to the selected territory identifier

#### Scenario: A search term with no match shows a message, not silence
- **WHEN** a search term matches no published territory in the electoral catalog
- **THEN** the page shows an explicit "no encontramos esa ubicación" message rather than an empty result container

### Requirement: Hub columns render only from real data, each with its own empty state
The "Encuestas Web Activas" column SHALL render cards only from the Laravel active-survey-round application contract, and the "Últimos Estudios de Campo" column SHALL render cards only from its verified publication contract. Neither column SHALL read legacy JSON, root helpers, or raw tables directly. Each column SHALL show its own distinct empty state when it has no records and SHALL never render fabricated examples.

#### Scenario: Active rounds render from the Laravel contract
- **WHEN** the active-survey-round application contract returns published rounds inside their publication windows
- **THEN** "Encuestas Web Activas" renders one card per returned round with the territory name, scope type, electoral office, and canonical survey link

#### Scenario: Zero open online rounds shows an invitation, not a blank column
- **WHEN** the active-survey-round application contract returns no active rounds
- **THEN** "Encuestas Web Activas" shows the search-invitation empty state, not an empty container or an example card

#### Scenario: Zero real field studies shows absence of evidence, not an invitation
- **WHEN** the verified field-study publication contract returns no real studies
- **THEN** "Últimos Estudios de Campo" shows a one-line "aún no hay estudios publicados" note, not a CTA and not an example card

#### Scenario: Fabricated placeholders never render in either column
- **WHEN** either hub column is rendered
- **THEN** no card is produced from an example, placeholder, unverified, or unpublished record
