## MODIFIED Requirements

### Requirement: Hub columns render only from real data, each with its own empty state
The "Encuestas Web Activas" and "Últimos Estudios de Campo" columns SHALL each render cards only from real, non-placeholder JSON records, and each SHALL show its own distinct empty state when it has none — never a shared generic message, and never a fabricated example card. "Encuestas Web Activas" SHALL render active survey rounds from the public survey portal with explicit territorial labels; "Últimos Estudios de Campo" SHALL continue to read `campo_externa` records from `data/resultado.json`.

#### Scenario: Zero open online rounds shows an invitation, not a blank column
- **WHEN** no district, province, or region has an open `online_propia` round
- **THEN** "Encuestas Web Activas" shows the search-invitation empty state, not an empty div or an example card

#### Scenario: An active online round appears as a real card
- **WHEN** at least one `encuestas` row is active for a scope
- **THEN** "Encuestas Web Activas" renders a card linking to the correct district or territory page, and the card shows the territorial level next to the place name rather than a bare slug

#### Scenario: Zero real campo studies shows absence-of-evidence, not an invitation
- **WHEN** no real campo study exists in `data/resultado.json`
- **THEN** "Últimos Estudios de Campo" shows a one-line "aún no hay estudios publicados" note, not a CTA and not an example card

#### Scenario: The ejemplo placeholder never renders in either column
- **WHEN** either hub column is rendered
- **THEN** no card is produced from a record whose `encuestadoraId` (or equivalent) is the `ejemplo` placeholder

